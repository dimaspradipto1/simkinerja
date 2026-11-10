<?php

namespace App\Http\Controllers;

use App\Helpers\KinerjaWaktuHelper;
use App\Models\Kegiatan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PemantauanStaffController extends Controller
{
    /**
     * Tampilan utama Pemantauan Staff sesuai struktur OTK.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Tentukan Bulan & Tahun terpilih (Default bulan & tahun berjalan)
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        if ($bulan < 1 || $bulan > 12) {
            $bulan = now()->month;
        }
        if ($tahun < 2020 || $tahun > 2035) {
            $tahun = now()->year;
        }

        $dateCarbon = Carbon::createFromDate($tahun, $bulan, 1);
        $namaBulanIndonesia = $this->getNamaBulan($bulan);
        $startOfMonth = $dateCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth = $dateCarbon->copy()->endOfMonth()->toDateString();

        $startOfYear = Carbon::createFromDate($tahun, 1, 1)->startOfYear()->toDateString();
        $endOfYear = Carbon::createFromDate($tahun, 12, 31)->endOfYear()->toDateString();

        // Ambil daftar seluruh Pimpinan/Atasan OTK yang memiliki bawahan
        $supervisorIdsInDb = User::whereNotNull('supervisor_id')->select('supervisor_id')->distinct();
        $supervisorsList = User::whereIn('users.id', $supervisorIdsInDb)
            ->leftJoin('otk_members', 'users.id', '=', 'otk_members.user_id')
            ->orderByRaw('COALESCE(otk_members.sort_order, 9999) ASC')
            ->select('users.id', 'users.name', 'users.jabatan', 'users.unit')
            ->get();

        // Ambil daftar unit kerja yang tersedia dalam OTK
        $unitsList = User::whereNotNull('unit')
            ->where('unit', '!=', '')
            ->distinct()
            ->orderBy('unit', 'asc')
            ->pluck('unit');

        // Cek apakah user adalah Pimpinan atau Kepala yang berhak memantau staf bawahan
        $isLeader = $this->isPimpinanAtauKepala($authUser);

        // Filter Scope MURNI BERDASARKAN STRUKTUR OTK
        $selectedSupervisorId = $request->input('supervisor_id');
        $selectedUnit = $request->input('unit');
        $selectedBidangOtk = $request->input('bidang_otk');
        $pimpinanTerpilih = null;

        if (!$isLeader) {
            // Selain akun pimpinan atau kepala: HANYA MENAMPILKAN DIRI SENDIRI
            $selectedSupervisorId = null;
            $selectedUnit = null;
            $selectedBidangOtk = null;
        } else {
            // Default untuk akun pimpinan/kepala:
            // Jika user memiliki bawahan dalam struktur OTK, otomatis tampilkan staf bawahannya sendiri
            $hasSubs = $authUser->subordinates()->exists();
            if (empty($selectedSupervisorId) && empty($selectedBidangOtk) && empty($selectedUnit) && !$request->has('all')) {
                if ($hasSubs) {
                    $selectedSupervisorId = $authUser->id;
                } elseif ($authUser->unit) {
                    $selectedUnit = $authUser->unit;
                }
            } elseif ($selectedSupervisorId === 'all') {
                $selectedSupervisorId = null;
            }
        }

        // Base Query User (HANYA PERSONEL YANG TERDAFTAR RESMI PADA OTK UIS)
        $userQuery = User::where('users.is_active', true)
            ->join('otk_members', 'users.id', '=', 'otk_members.user_id')
            ->leftJoin('users as sup', 'users.supervisor_id', '=', 'sup.id')
            ->select(
                'users.*',
                'otk_members.sort_order as otk_sort_order',
                'sup.name as supervisor_name',
                'sup.jabatan as supervisor_jabatan'
            );

        if (!$isLeader) {
            // Selain akun pimpinan atau kepala: Kunci ke akun diri sendiri
            $userQuery->where('users.id', $authUser->id);
        } else {
            // Filter Tim Berdasarkan Pimpinan / Atasan Terpilih
            if ($selectedSupervisorId) {
                $pimpinanTerpilih = User::find($selectedSupervisorId);
                if ($pimpinanTerpilih) {
                    // Ambil seluruh bawahan langsung dan berjenjang dari pimpinan ini
                    $subordinateIds = $pimpinanTerpilih->getAllSubordinateIds();
                    $userQuery->whereIn('users.id', $subordinateIds);
                }
            } elseif (empty($selectedBidangOtk) && empty($selectedUnit) && empty($request->search)) {
                // Jika melihat seluruh OTK tanpa pimpinan tertentu, staf adalah seluruh anggota yang memiliki atasan
                $userQuery->whereNotNull('users.supervisor_id');
            }

            // Filter Unit
            if ($selectedUnit) {
                $userQuery->where('users.unit', $selectedUnit);
            }

            // Filter Grup / Bidang Koordinasi OTK
            if ($selectedBidangOtk) {
                $this->applyBidangOtkFilter($userQuery, $selectedBidangOtk);
            }
        }

        // Search Keyword
        if ($request->filled('search')) {
            $kw = trim($request->search);
            $userQuery->where(function ($q) use ($kw) {
                $q->where('users.name', 'like', "%{$kw}%")
                  ->orWhere('users.nidn', 'like', "%{$kw}%")
                  ->orWhere('users.jabatan', 'like', "%{$kw}%")
                  ->orWhere('users.unit', 'like', "%{$kw}%");
            });
        }

        // Urutkan STRICT SESUAI OTK
        $pegawaiList = $userQuery
            ->orderByRaw('COALESCE(otk_members.sort_order, 9999) ASC')
            ->orderBy('users.name', 'asc')
            ->get();

        $pegawaiIds = $pegawaiList->pluck('id')->toArray();

        // Ambil seluruh kegiatan dalam tahun berjalan untuk pegawai terpilih
        $kegiatanTahun = Kegiatan::query()
            ->where(function ($q) use ($pegawaiIds) {
                $q->whereIn('user_id', $pegawaiIds)
                  ->orWhereHas('taggedUsers', fn($qu) => $qu->whereIn('users.id', $pegawaiIds));
            })
            ->where(function ($q) use ($startOfYear, $endOfYear) {
                $q->whereBetween('tanggal_mulai', [$startOfYear, $endOfYear])
                  ->orWhereBetween('created_at', [$startOfYear . ' 00:00:00', $endOfYear . ' 23:59:59'])
                  ->orWhereBetween('estimasi_tanggal_mulai', [$startOfYear, $endOfYear]);
            })
            ->with(['taggedUsers:id,name'])
            ->get();

        // Rekap kinerja per pegawai
        $pegawaiData = [];
        $totalKegiatanBulanIniSemua = 0;
        $totalDetikBulanIniSemua = 0;
        $totalAktifBulanIni = 0;

        foreach ($pegawaiList as $pegawai) {
            $pid = $pegawai->id;

            // Filter kegiatan pegawai ini pada tahun berjalan
            $userTasksAnnual = $kegiatanTahun->filter(function ($item) use ($pid) {
                if ($item->user_id == $pid) return true;
                return $item->taggedUsers && $item->taggedUsers->contains('id', $pid);
            });

            // Filter kegiatan pada bulan terpilih
            $userTasksMonth = $userTasksAnnual->filter(function ($item) use ($bulan, $tahun) {
                $tgl = $item->tanggal_mulai ?: ($item->estimasi_tanggal_mulai ?: ($item->created_at ? $item->created_at->format('Y-m-d') : null));
                if (!$tgl) return false;
                try {
                    $c = Carbon::parse($tgl);
                    return $c->month == $bulan && $c->year == $tahun;
                } catch (\Exception $e) {
                    return false;
                }
            });

            $jumKegiatanBulan = $userTasksMonth->count();

            // Hitung jam kerja dan rincian status bulan ini
            $detikBulan = 0;
            $selesaiBulan = 0;
            $prosesBulan = 0;
            $belumBulan = 0;

            foreach ($userTasksMonth as $task) {
                $detikBulan += KinerjaWaktuHelper::hitungDetikRealisasi($task);
                if ($task->status === 'Selesai') {
                    $selesaiBulan++;
                } elseif (in_array($task->status, ['Proses', 'Berjalan'])) {
                    $prosesBulan++;
                } else {
                    $belumBulan++;
                }
            }

            $jamKerjaBulan = round($detikBulan / 3600, 1);

            // Akumulasi Tahunan
            $totalTahunan = $userTasksAnnual->count();
            $rutinTahunan = $userTasksAnnual->where('kategori', 'Rencana Kerja')->count();
            $kepanitiaanTahunan = $userTasksAnnual->where('kategori', 'Kepanitiaan')->count();
            $insidentilTahunan = $userTasksAnnual->where('kategori', 'Insidentil')->count();
            $lainnyaTahunan = $totalTahunan - ($rutinTahunan + $kepanitiaanTahunan + $insidentilTahunan);
            if ($lainnyaTahunan > 0) {
                $rutinTahunan += $lainnyaTahunan;
            }

            if ($jumKegiatanBulan > 0) {
                $totalAktifBulanIni++;
            }
            $totalKegiatanBulanIniSemua += $jumKegiatanBulan;
            $totalDetikBulanIniSemua += $detikBulan;

            $persentaseSelesai = $jumKegiatanBulan > 0 ? round(($selesaiBulan / $jumKegiatanBulan) * 100) : 0;

            $pegawaiData[] = [
                'pegawai' => $pegawai,
                'otk_no' => $pegawai->otk_sort_order !== null ? ($pegawai->otk_sort_order + 1) : '-',
                'avatar_initial' => $this->getInitials($pegawai->name),
                'avatar_color' => $this->getAvatarColor($pegawai->id),
                'jum_kegiatan_bulan' => $jumKegiatanBulan,
                'jam_kerja_bulan' => $jamKerjaBulan,
                'selesai_bulan' => $selesaiBulan,
                'proses_bulan' => $prosesBulan,
                'belum_bulan' => $belumBulan,
                'persentase_selesai' => $persentaseSelesai,
                'status_mencatat' => $jumKegiatanBulan > 0,
                'total_tahunan' => $totalTahunan,
                'rutin_tahunan' => $rutinTahunan,
                'kepanitiaan_tahunan' => $kepanitiaanTahunan,
                'insidentil_tahunan' => $insidentilTahunan,
            ];
        }

        // Filter status_pencatatan jika dipilih
        if ($request->filled('status_pencatatan')) {
            $statusFilter = $request->status_pencatatan;
            if ($statusFilter === 'aktif') {
                $pegawaiData = array_values(array_filter($pegawaiData, fn($d) => $d['status_mencatat']));
            } elseif ($statusFilter === 'belum') {
                $pegawaiData = array_values(array_filter($pegawaiData, fn($d) => !$d['status_mencatat']));
            }
        }

        // Summary KPI Stats
        $totalStaff = count($pegawaiList);
        $totalJamKerjaBulanIniSemua = round($totalDetikBulanIniSemua / 3600, 1);
        $avgJamKerja = $totalStaff > 0 ? round($totalJamKerjaBulanIniSemua / $totalStaff, 1) : 0;
        $aktifPercentage = $totalStaff > 0 ? round(($totalAktifBulanIni / $totalStaff) * 100) : 0;

        $kpi = [
            'total_staff' => $totalStaff,
            'total_aktif' => $totalAktifBulanIni,
            'aktif_percentage' => $aktifPercentage,
            'total_kegiatan_bulan' => $totalKegiatanBulanIniSemua,
            'total_jam_kerja_bulan' => $totalJamKerjaBulanIniSemua,
            'avg_jam_kerja' => $avgJamKerja,
        ];

        // Navigasi Prev & Next Month
        $prevDate = $dateCarbon->copy()->subMonth();
        $nextDate = $dateCarbon->copy()->addMonth();

        return view('pages.pemantauan-staff.index', compact(
            'pegawaiData',
            'bulan',
            'tahun',
            'namaBulanIndonesia',
            'unitsList',
            'selectedUnit',
            'supervisorsList',
            'selectedSupervisorId',
            'pimpinanTerpilih',
            'selectedBidangOtk',
            'kpi',
            'prevDate',
            'nextDate',
            'isLeader'
        ));
    }

    /**
     * Helper filter berdasarkan Bidang Koordinasi OTK UIS.
     */
    protected function applyBidangOtkFilter($query, $bidang)
    {
        switch ($bidang) {
            case 'pimpinan_uis':
                $query->where(function ($q) {
                    $q->where('users.jabatan', 'LIKE', '%REKTOR%')
                      ->orWhere('users.unit', 'REKTORAT');
                });
                break;
            case 'warek1':
                // Wakil Rektor I & jajaran BAAK, Perpustakaan
                $warek = User::where('jabatan', 'LIKE', '%WAKIL REKTOR I%')->where('jabatan', 'NOT LIKE', '%II%')->where('jabatan', 'NOT LIKE', '%III%')->first();
                if ($warek) {
                    $query->whereIn('users.id', array_merge([$warek->id], $warek->getAllSubordinateIds()));
                } else {
                    $query->whereIn('users.unit', ['BAAK', 'PERPUSTAKAAN']);
                }
                break;
            case 'warek2':
                // Wakil Rektor II & jajaran BAUK, Keuangan, Sarpras, Kepegawaian (18 orang di screenshot)
                $warek = User::where('jabatan', 'LIKE', '%WAKIL REKTOR II%')->where('jabatan', 'NOT LIKE', '%III%')->first();
                if ($warek) {
                    $query->whereIn('users.id', array_merge([$warek->id], $warek->getAllSubordinateIds()));
                } else {
                    $query->whereIn('users.unit', ['BAUK']);
                }
                break;
            case 'warek3':
                // Wakil Rektor III & Kemahasiswaan
                $warek = User::where('jabatan', 'LIKE', '%WAKIL REKTOR III%')->first();
                if ($warek) {
                    $query->whereIn('users.id', array_merge([$warek->id], $warek->getAllSubordinateIds()));
                } else {
                    $query->whereIn('users.unit', ['BIRO KEMAHASISWAAN']);
                }
                break;
            case 'feb':
                $query->where('users.unit', 'LIKE', '%EKONOMI%');
                break;
            case 'fst':
                $query->where(function ($q) {
                    $q->where('users.unit', 'LIKE', '%SAINS%')
                      ->orWhere('users.unit', 'LIKE', '%TEKNIK%');
                });
                break;
            case 'fikes':
                $query->where('users.unit', 'LIKE', '%KESEHATAN%');
                break;
            case 'lembaga':
                $query->where(function ($q) {
                    $q->where('users.unit', 'LIKE', '%LPPM%')
                      ->orWhere('users.unit', 'LIKE', '%LPMI%')
                      ->orWhere('users.unit', 'LIKE', '%ICT%')
                      ->orWhere('users.unit', 'LIKE', '%PERPUSTAKAAN%');
                });
                break;
        }
    }

    /**
     * Endpoint AJAX untuk memuat modal rincian kegiatan staf yang dipantau.
     */
    public function detail(Request $request, $id)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            abort(401);
        }

        // Jika bukan pimpinan atau kepala, hanya boleh mengakses detail kinerjanya sendiri
        if (!$this->isPimpinanAtauKepala($authUser) && (int)$id !== (int)$authUser->id) {
            $id = $authUser->id;
        }

        $pegawai = User::where('users.id', $id)
            ->leftJoin('users as sup', 'users.supervisor_id', '=', 'sup.id')
            ->leftJoin('otk_members', 'users.id', '=', 'otk_members.user_id')
            ->select('users.*', 'sup.name as sup_name', 'sup.jabatan as sup_jabatan', 'otk_members.sort_order')
            ->firstOrFail();

        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $kegiatanList = Kegiatan::query()
            ->where(function ($q) use ($id) {
                $q->where('user_id', $id)
                  ->orWhereHas('taggedUsers', fn($qu) => $qu->where('users.id', $id));
            })
            ->where(function ($q) use ($bulan, $tahun) {
                $q->where(function ($sub) use ($bulan, $tahun) {
                    $sub->whereMonth('tanggal_mulai', $bulan)->whereYear('tanggal_mulai', $tahun);
                })->orWhere(function ($sub) use ($bulan, $tahun) {
                    $sub->whereNull('tanggal_mulai')
                        ->whereMonth('created_at', $bulan)
                        ->whereYear('created_at', $tahun);
                })->orWhere(function ($sub) use ($bulan, $tahun) {
                    $sub->whereNull('tanggal_mulai')
                        ->whereMonth('estimasi_tanggal_mulai', $bulan)
                        ->whereYear('estimasi_tanggal_mulai', $tahun);
                });
            })
            ->orderBy('tanggal_mulai', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $tasks = $kegiatanList->map(function ($t) {
            $detik = KinerjaWaktuHelper::hitungDetikRealisasi($t);
            $durasiJam = round($detik / 3600, 1);

            return [
                'id' => $t->id,
                'uraian_tugas' => $t->uraian_tugas,
                'kategori' => $t->kategori ?: 'Kegiatan Utama',
                'status' => $t->status,
                'tanggal_mulai' => $t->tanggal_mulai ? Carbon::parse($t->tanggal_mulai)->translatedFormat('d M Y') : ($t->estimasi_tanggal_mulai ? Carbon::parse($t->estimasi_tanggal_mulai)->translatedFormat('d M Y') : '-'),
                'durasi_jam' => $durasiJam,
                'hasil_kerja' => $t->hasil_kerja ?: '-',
                'rencana_tindak_lanjut' => $t->rencana_tindak_lanjut ?: '-',
            ];
        });

        return response()->json([
            'success' => true,
            'pegawai' => [
                'id' => $pegawai->id,
                'name' => $pegawai->name,
                'jabatan' => $pegawai->jabatan ?: 'Staf',
                'unit' => $pegawai->unit ?: 'Universitas Ibnu Sina',
                'atasan' => $pegawai->sup_name ? ($pegawai->sup_name . ' (' . ($pegawai->sup_jabatan ?: 'Atasan') . ')') : 'Pimpinan Utama',
                'nomor_wa' => $pegawai->nomor_wa,
                'otk_no' => $pegawai->sort_order !== null ? ($pegawai->sort_order + 1) : '-',
                'avatar_initial' => $this->getInitials($pegawai->name),
                'avatar_color' => $this->getAvatarColor($pegawai->id),
            ],
            'tasks' => $tasks,
            'total_tasks' => $tasks->count(),
            'total_jam' => round($tasks->sum('durasi_jam'), 1),
            'selesai_count' => $tasks->where('status', 'Selesai')->count(),
            'proses_count' => $tasks->whereIn('status', ['Proses', 'Berjalan'])->count(),
        ]);
    }

    /**
     * Export data pemantauan staf ke format Excel.
     */
    public function exportExcel(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $namaBulan = $this->getNamaBulan($bulan);

        $authUser = Auth::user();
        $isLeader = $this->isPimpinanAtauKepala($authUser);

        $selectedSupervisorId = $request->input('supervisor_id');
        $selectedUnit = $request->input('unit');
        $selectedBidangOtk = $request->input('bidang_otk');

        if (!$isLeader) {
            $selectedSupervisorId = null;
            $selectedUnit = null;
            $selectedBidangOtk = null;
        } else {
            if (empty($selectedSupervisorId) && empty($selectedBidangOtk) && empty($selectedUnit) && !$request->has('all')) {
                if ($authUser->subordinates()->exists()) {
                    $selectedSupervisorId = $authUser->id;
                }
            } elseif ($selectedSupervisorId === 'all') {
                $selectedSupervisorId = null;
            }
        }

        $userQuery = User::where('users.is_active', true)
            ->join('otk_members', 'users.id', '=', 'otk_members.user_id')
            ->leftJoin('users as sup', 'users.supervisor_id', '=', 'sup.id')
            ->select('users.*', 'otk_members.sort_order as otk_sort_order', 'sup.name as supervisor_name');

        if (!$isLeader) {
            $userQuery->where('users.id', $authUser->id);
        } else {
            if ($selectedSupervisorId) {
                $pimpinan = User::find($selectedSupervisorId);
                if ($pimpinan) {
                    $userQuery->whereIn('users.id', $pimpinan->getAllSubordinateIds());
                }
            } elseif (empty($selectedBidangOtk) && empty($selectedUnit) && empty($request->search)) {
                $userQuery->whereNotNull('users.supervisor_id');
            }

            if ($selectedUnit) {
                $userQuery->where('users.unit', $selectedUnit);
            }

            if ($selectedBidangOtk) {
                $this->applyBidangOtkFilter($userQuery, $selectedBidangOtk);
            }
        }

        if ($request->filled('search')) {
            $kw = trim($request->search);
            $userQuery->where(function ($q) use ($kw) {
                $q->where('users.name', 'like', "%{$kw}%")
                  ->orWhere('users.nidn', 'like', "%{$kw}%")
                  ->orWhere('users.jabatan', 'like', "%{$kw}%");
            });
        }

        $pegawaiList = $userQuery
            ->orderByRaw('COALESCE(otk_members.sort_order, 9999) ASC')
            ->orderBy('users.name', 'asc')
            ->get();
        $pegawaiIds = $pegawaiList->pluck('id')->toArray();

        $startOfYear = Carbon::createFromDate($tahun, 1, 1)->startOfYear()->toDateString();
        $endOfYear = Carbon::createFromDate($tahun, 12, 31)->endOfYear()->toDateString();

        $kegiatanTahun = Kegiatan::query()
            ->where(function ($q) use ($pegawaiIds) {
                $q->whereIn('user_id', $pegawaiIds)
                  ->orWhereHas('taggedUsers', fn($qu) => $qu->whereIn('users.id', $pegawaiIds));
            })
            ->where(function ($q) use ($startOfYear, $endOfYear) {
                $q->whereBetween('tanggal_mulai', [$startOfYear, $endOfYear])
                  ->orWhereBetween('created_at', [$startOfYear . ' 00:00:00', $endOfYear . ' 23:59:59']);
            })
            ->with(['taggedUsers:id,name'])
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pemantauan Staff OTK');

        // Header Dokumen
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'UNIVERSITAS IBNU SINA - REKAPITULASI PEMANTAUAN STAFF SESUAI STRUKTUR OTK');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF15432D'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Periode Pemantauan: ' . $namaBulan . ' ' . $tahun . ($selectedUnit ? ' | Unit: ' . $selectedUnit : ''));
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Kolom
        $sheet->mergeCells('A4:A5');
        $sheet->setCellValue('A4', 'No OTK');

        $sheet->mergeCells('B4:B5');
        $sheet->setCellValue('B4', 'Nama Pegawai & Jabatan OTK');

        $sheet->mergeCells('C4:C5');
        $sheet->setCellValue('C4', 'Unit Kerja');

        $sheet->mergeCells('D4:D5');
        $sheet->setCellValue('D4', 'Atasan Langsung OTK');

        $sheet->mergeCells('E4:F4');
        $sheet->setCellValue('E4', 'Bulan ' . $namaBulan . ' ' . $tahun);
        $sheet->setCellValue('E5', 'Jum. Kegiatan');
        $sheet->setCellValue('F5', 'Jum. Jam Kerja');

        $sheet->mergeCells('G4:J4');
        $sheet->setCellValue('G4', 'Tahun Kinerja Berjalan (' . $tahun . ')');
        $sheet->setCellValue('G5', 'Total');
        $sheet->setCellValue('H5', 'Utama / Rutin');
        $sheet->setCellValue('I5', 'Kepanitiaan');
        $sheet->setCellValue('J5', 'Insidental');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '15432D'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
            ],
        ];
        $sheet->getStyle('A4:J5')->applyFromArray($headerStyle);

        $row = 6;
        $no = 1;
        foreach ($pegawaiList as $pegawai) {
            $pid = $pegawai->id;

            $userTasksAnnual = $kegiatanTahun->filter(function ($item) use ($pid) {
                if ($item->user_id == $pid) return true;
                return $item->taggedUsers && $item->taggedUsers->contains('id', $pid);
            });

            $userTasksMonth = $userTasksAnnual->filter(function ($item) use ($bulan, $tahun) {
                $tgl = $item->tanggal_mulai ?: ($item->estimasi_tanggal_mulai ?: ($item->created_at ? $item->created_at->format('Y-m-d') : null));
                if (!$tgl) return false;
                try {
                    $c = Carbon::parse($tgl);
                    return $c->month == $bulan && $c->year == $tahun;
                } catch (\Exception $e) {
                    return false;
                }
            });

            $detikBulan = 0;
            foreach ($userTasksMonth as $task) {
                $detikBulan += KinerjaWaktuHelper::hitungDetikRealisasi($task);
            }
            $jamKerjaBulan = round($detikBulan / 3600, 1);

            $totalTahunan = $userTasksAnnual->count();
            $rutinTahunan = $userTasksAnnual->where('kategori', 'Rencana Kerja')->count();
            $kepanitiaanTahunan = $userTasksAnnual->where('kategori', 'Kepanitiaan')->count();
            $insidentilTahunan = $userTasksAnnual->where('kategori', 'Insidentil')->count();

            $noOtk = $pegawai->otk_sort_order !== null ? ($pegawai->otk_sort_order + 1) : $no;

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $pegawai->name . "\n" . ($pegawai->jabatan ?: 'Staf'));
            $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $sheet->setCellValue('C' . $row, $pegawai->unit ?: 'Universitas');
            $sheet->setCellValue('D' . $row, $pegawai->supervisor_name ?: '-');
            $sheet->setCellValue('E' . $row, $userTasksMonth->count());
            $sheet->setCellValue('F' . $row, $jamKerjaBulan);
            $sheet->setCellValue('G' . $row, $totalTahunan);
            $sheet->setCellValue('H' . $row, $rutinTahunan);
            $sheet->setCellValue('I' . $row, $kepanitiaanTahunan);
            $sheet->setCellValue('J' . $row, $insidentilTahunan);

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row . ':J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($no % 2 == 0) {
                $sheet->getStyle('A' . $row . ':J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

            $row++;
            $no++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Pemantauan_Staff_OTK_' . str_replace(' ', '_', $namaBulan) . '_' . $tahun . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export data pemantauan staf ke format PDF.
     */
    public function exportPdf(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $namaBulanIndonesia = $this->getNamaBulan($bulan);

        $authUser = Auth::user();
        $isLeader = $this->isPimpinanAtauKepala($authUser);

        $selectedSupervisorId = $request->input('supervisor_id');
        $selectedUnit = $request->input('unit');
        $selectedBidangOtk = $request->input('bidang_otk');

        if (!$isLeader) {
            $selectedSupervisorId = null;
            $selectedUnit = null;
            $selectedBidangOtk = null;
        } else {
            if (empty($selectedSupervisorId) && empty($selectedBidangOtk) && empty($selectedUnit) && !$request->has('all')) {
                if ($authUser->subordinates()->exists()) {
                    $selectedSupervisorId = $authUser->id;
                }
            } elseif ($selectedSupervisorId === 'all') {
                $selectedSupervisorId = null;
            }
        }

        $userQuery = User::where('users.is_active', true)
            ->join('otk_members', 'users.id', '=', 'otk_members.user_id')
            ->leftJoin('users as sup', 'users.supervisor_id', '=', 'sup.id')
            ->select('users.*', 'otk_members.sort_order as otk_sort_order', 'sup.name as supervisor_name');

        if (!$isLeader) {
            $userQuery->where('users.id', $authUser->id);
        } else {
            if ($selectedSupervisorId) {
                $pimpinan = User::find($selectedSupervisorId);
                if ($pimpinan) {
                    $userQuery->whereIn('users.id', $pimpinan->getAllSubordinateIds());
                }
            } elseif (empty($selectedBidangOtk) && empty($selectedUnit) && empty($request->search)) {
                $userQuery->whereNotNull('users.supervisor_id');
            }

            if ($selectedUnit) {
                $userQuery->where('users.unit', $selectedUnit);
            }

            if ($selectedBidangOtk) {
                $this->applyBidangOtkFilter($userQuery, $selectedBidangOtk);
            }
        }

        if ($request->filled('search')) {
            $kw = trim($request->search);
            $userQuery->where(function ($q) use ($kw) {
                $q->where('users.name', 'like', "%{$kw}%")
                  ->orWhere('users.nidn', 'like', "%{$kw}%")
                  ->orWhere('users.jabatan', 'like', "%{$kw}%");
            });
        }

        $pegawaiList = $userQuery
            ->orderByRaw('COALESCE(otk_members.sort_order, 9999) ASC')
            ->orderBy('users.name', 'asc')
            ->get();
        $pegawaiIds = $pegawaiList->pluck('id')->toArray();

        $startOfYear = Carbon::createFromDate($tahun, 1, 1)->startOfYear()->toDateString();
        $endOfYear = Carbon::createFromDate($tahun, 12, 31)->endOfYear()->toDateString();

        $kegiatanTahun = Kegiatan::query()
            ->where(function ($q) use ($pegawaiIds) {
                $q->whereIn('user_id', $pegawaiIds)
                  ->orWhereHas('taggedUsers', fn($qu) => $qu->whereIn('users.id', $pegawaiIds));
            })
            ->where(function ($q) use ($startOfYear, $endOfYear) {
                $q->whereBetween('tanggal_mulai', [$startOfYear, $endOfYear])
                  ->orWhereBetween('created_at', [$startOfYear . ' 00:00:00', $endOfYear . ' 23:59:59']);
            })
            ->with(['taggedUsers:id,name'])
            ->get();

        $pegawaiData = [];
        foreach ($pegawaiList as $pegawai) {
            $pid = $pegawai->id;

            $userTasksAnnual = $kegiatanTahun->filter(function ($item) use ($pid) {
                if ($item->user_id == $pid) return true;
                return $item->taggedUsers && $item->taggedUsers->contains('id', $pid);
            });

            $userTasksMonth = $userTasksAnnual->filter(function ($item) use ($bulan, $tahun) {
                $tgl = $item->tanggal_mulai ?: ($item->estimasi_tanggal_mulai ?: ($item->created_at ? $item->created_at->format('Y-m-d') : null));
                if (!$tgl) return false;
                try {
                    $c = Carbon::parse($tgl);
                    return $c->month == $bulan && $c->year == $tahun;
                } catch (\Exception $e) {
                    return false;
                }
            });

            $detikBulan = 0;
            foreach ($userTasksMonth as $task) {
                $detikBulan += KinerjaWaktuHelper::hitungDetikRealisasi($task);
            }
            $jamKerjaBulan = round($detikBulan / 3600, 1);

            $totalTahunan = $userTasksAnnual->count();
            $rutinTahunan = $userTasksAnnual->where('kategori', 'Rencana Kerja')->count();
            $kepanitiaanTahunan = $userTasksAnnual->where('kategori', 'Kepanitiaan')->count();
            $insidentilTahunan = $userTasksAnnual->where('kategori', 'Insidentil')->count();

            $pegawaiData[] = [
                'pegawai' => $pegawai,
                'otk_no' => $pegawai->otk_sort_order !== null ? ($pegawai->otk_sort_order + 1) : '-',
                'jum_kegiatan_bulan' => $userTasksMonth->count(),
                'jam_kerja_bulan' => $jamKerjaBulan,
                'total_tahunan' => $totalTahunan,
                'rutin_tahunan' => $rutinTahunan,
                'kepanitiaan_tahunan' => $kepanitiaanTahunan,
                'insidentil_tahunan' => $insidentilTahunan,
            ];
        }

        $pdf = Pdf::loadView('pages.pemantauan-staff.pdf', compact(
            'pegawaiData',
            'bulan',
            'tahun',
            'namaBulanIndonesia',
            'selectedUnit'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('Pemantauan_Staff_OTK_' . str_replace(' ', '_', $namaBulanIndonesia) . '_' . $tahun . '.pdf');
    }

    /**
     * Helper nama inisial untuk avatar pegawai.
     */
    protected function getInitials($name): string
    {
        $words = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach ($words as $w) {
            $w = trim($w, ".,_ \t\n\r\0\x0B");
            if (!empty($w)) {
                $initials .= strtoupper($w[0]);
                if (strlen($initials) >= 2) break;
            }
        }
        return $initials ?: 'ST';
    }

    /**
     * Helper palet warna elegan untuk avatar.
     */
    protected function getAvatarColor($id): string
    {
        $colors = [
            '#15432D', '#1D4ED8', '#B45309', '#047857',
            '#4338CA', '#BE185D', '#0E7490', '#475569',
            '#6D28D9', '#0F766E', '#C2410C', '#334155'
        ];
        return $colors[$id % count($colors)];
    }

    /**
     * Helper nama bulan bahasa Indonesia.
     */
    protected function getNamaBulan(int $bulan): string
    {
        $daftarBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $daftarBulan[$bulan] ?? 'Bulan ' . $bulan;
    }

    /**
     * Cek apakah akun merupakan Pimpinan atau Kepala yang berhak memantau staf bawahan.
     * Akun selain pimpinan atau kepala hanya boleh melihat kinerjanya sendiri.
     */
    protected function isPimpinanAtauKepala($user): bool
    {
        if (!$user) {
            return false;
        }

        // Super Admin sistem selalu memiliki hak pemantauan pimpinan
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Memiliki staf bawahan langsung/berjenjang dalam struktur hierarki OTK
        if ($user->subordinates()->exists()) {
            return true;
        }

        // Periksa kata kunci pimpinan atau kepala pada jabatan resmi OTK
        $jabatan = strtoupper(trim($user->jabatan ?? ''));
        $pimpinanKeywords = [
            'REKTOR', 'WAKIL REKTOR', 'DEKAN', 'WAKIL DEKAN',
            'KEPALA', 'KA. ', 'KA.', 'KAPRODI', 'KETUA PROGRAM STUDI',
            'KETUA JURUSAN', 'KABID', 'DIREKTUR', 'PIMPINAN'
        ];

        foreach ($pimpinanKeywords as $kw) {
            if (str_contains($jabatan, $kw)) {
                return true;
            }
        }

        return false;
    }
}
