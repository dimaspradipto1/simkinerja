<?php

namespace App\Http\Controllers;

use App\Models\RencanaKerja;
use App\Models\Kepanitiaan;
use App\Models\Insidentil;
use App\Models\PeriodeAkademik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AnalisisKeterlambatanController extends Controller
{
    /**
     * Display the Analisis Keterlambatan Dashboard view.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();
        $defaultPeriode = PeriodeAkademik::first();
        $defaultPeriodeId = $defaultPeriode ? $defaultPeriode->id : null;

        $isStaffOnly = true;
        $usersQuery = User::whereNotNull('jabatan')->where('jabatan', '!=', '');
        if ($authUser && ($authUser->isAdmin() || $authUser->isPimpinanRektorat())) {
            $isStaffOnly = false;
        } else if ($authUser) {
            $usersQuery->where('id', $authUser->id);
        }

        $usersWithJabatan = $usersQuery->orderBy('jabatan')->get(['id', 'name', 'jabatan', 'unit']);

        return view('pages.analisis_keterlambatan.index', compact('periodeAkademiks', 'defaultPeriodeId', 'usersWithJabatan', 'isStaffOnly'));
    }

    /**
     * Get DataTables JSON dataset with overlap delay diagnosis.
     */
    public function getData(Request $request)
    {
        $authUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        $query = RencanaKerja::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones']);

        // Scope by user permissions
        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }
                if ($request->filled('jabatan')) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('jabatan', $request->jabatan);
                    });
                }
            } else {
                $query->where(function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id)
                      ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                          $qu->where('users.id', $authUser->id);
                      });
                });
            }
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        if ($request->filled('jabatan')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('jabatan', $request->jabatan);
            });
        }

        // Fetch all candidates to analyze delay condition
        $allTasks = $query->latest()->get();

        $lateTasks = collect();
        $countInsidentil = 0;
        $countKepanitiaan = 0;
        $countBebanGanda = 0;
        $countMurni = 0;

        foreach ($allTasks as $task) {
            $isLate = false;
            $estimasiSelesai = $task->estimasi_tanggal_selesai;

            if (!empty($task->tanggal_selesai) && !empty($estimasiSelesai) && $task->tanggal_selesai > $estimasiSelesai) {
                $isLate = true;
            } elseif (($task->status !== 'Selesai') && !empty($estimasiSelesai) && $estimasiSelesai < $today) {
                $isLate = true;
            }

            if (!$isLate) {
                continue;
            }

            // Analyze overlap timeframe
            $startDate = $task->estimasi_tanggal_mulai ?? $task->tanggal_mulai ?? $task->created_at->format('Y-m-d');
            $endDate = !empty($task->tanggal_selesai) ? $task->tanggal_selesai : $today;

            // Search overlapping Insidentil
            $overlappingInsidentil = Insidentil::where('user_id', $task->user_id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('estimasi_tanggal_mulai')
                            ->where('estimasi_tanggal_mulai', '<=', $endDate)
                            ->where('estimasi_tanggal_selesai', '>=', $startDate);
                    })->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('tanggal_mulai')
                            ->where('tanggal_mulai', '<=', $endDate)
                            ->where(function ($nest) use ($startDate) {
                                $nest->where('tanggal_selesai', '>=', $startDate)
                                    ->orWhereNull('tanggal_selesai');
                            });
                    });
                })->get();

            // Search overlapping Kepanitiaan
            $overlappingKepanitiaan = Kepanitiaan::where(function ($q) use ($task) {
                $q->where('user_id', $task->user_id)
                    ->orWhereHas('taggedUsers', function ($tu) use ($task) {
                        $tu->where('users.id', $task->user_id);
                    });
            })->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('estimasi_tanggal_mulai')
                        ->where('estimasi_tanggal_mulai', '<=', $endDate)
                        ->where('estimasi_tanggal_selesai', '>=', $startDate);
                })->orWhere(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('tanggal_mulai')
                        ->where('tanggal_mulai', '<=', $endDate)
                        ->where(function ($nest) use ($startDate) {
                            $nest->where('tanggal_selesai', '>=', $startDate)
                                ->orWhereNull('tanggal_selesai');
                        });
                });
            })->get();

            $hasInsidentil = $overlappingInsidentil->count() > 0;
            $hasKepanitiaan = $overlappingKepanitiaan->count() > 0;

            $kategoriKendala = 'murni';
            if ($hasInsidentil && $hasKepanitiaan) {
                $kategoriKendala = 'beban_ganda';
                $countBebanGanda++;
            } elseif ($hasInsidentil) {
                $kategoriKendala = 'insidentil';
                $countInsidentil++;
            } elseif ($hasKepanitiaan) {
                $kategoriKendala = 'kepanitiaan';
                $countKepanitiaan++;
            } else {
                $kategoriKendala = 'murni';
                $countMurni++;
            }

            // Filter by requested category if specified
            if ($request->filled('kategori_kendala') && $request->kategori_kendala !== 'semua') {
                if ($request->kategori_kendala !== $kategoriKendala) {
                    continue;
                }
            }

            $task->kategori_kendala = $kategoriKendala;
            $task->overlapping_insidentil = $overlappingInsidentil;
            $task->overlapping_kepanitiaan = $overlappingKepanitiaan;
            
            // Calculate delay days
            $estDateObj = Carbon::parse($estimasiSelesai);
            $actualEndObj = Carbon::parse($endDate);
            $task->delay_days = max(1, (int) $estDateObj->diffInDays($actualEndObj));

            $lateTasks->push($task);
        }

        $totalLateCount = $lateTasks->count();
        $stats = [
            'total'         => $totalLateCount,
            'insidentil'    => $countInsidentil,
            'kepanitiaan'   => $countKepanitiaan,
            'beban_ganda'   => $countBebanGanda,
            'murni'         => $countMurni,
            'percent_ins'   => $totalLateCount > 0 ? round(($countInsidentil / $totalLateCount) * 100) : 0,
            'percent_pan'   => $totalLateCount > 0 ? round(($countKepanitiaan / $totalLateCount) * 100) : 0,
            'percent_ganda' => $totalLateCount > 0 ? round(($countBebanGanda / $totalLateCount) * 100) : 0,
            'percent_murni' => $totalLateCount > 0 ? round(($countMurni / $totalLateCount) * 100) : 0,
        ];

        return DataTables::of($lateTasks)
            ->addIndexColumn()
            ->addColumn('staff_info', function ($row) {
                $html = '<div class="fw-bold text-dark">' . e($row->user ? $row->user->name : '-') . '</div>';
                $html .= '<div class="small text-muted">' . e($row->user ? ($row->user->jabatan ?? '-') : '-') . '</div>';
                $html .= '<span class="badge bg-light text-dark border mt-1" style="font-size: 0.7rem;">Unit: ' . e($row->user ? ($row->user->unit ?? '-') : '-') . '</span>';
                return $html;
            })
            ->addColumn('task_details', function ($row) {
                $estMulai = $row->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($row->estimasi_tanggal_mulai)) : '-';
                $estSelesai = $row->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) : '-';
                $realSelesai = $row->tanggal_selesai ? date('d/m/Y', strtotime($row->tanggal_selesai)) : 'Belum Selesai (Current)';

                $html = '<div class="fw-bold text-dark mb-1">' . e($row->uraian_tugas) . '</div>';
                $html .= '<div class="small text-muted mb-1"><i class="bi bi-calendar-event me-1 text-secondary"></i><strong>Estimasi:</strong> ' . $estMulai . ' s/d ' . $estSelesai . '</div>';
                $html .= '<div class="small text-danger mb-1"><i class="bi bi-clock-history me-1"></i><strong>Realisasi:</strong> ' . $realSelesai . ' <span class="badge bg-danger-subtle text-danger border border-danger ms-1">Terlambat ' . $row->delay_days . ' Hari</span></div>';
                return $html;
            })
            ->addColumn('diagnostik_kendala', function ($row) {
                if ($row->kategori_kendala === 'beban_ganda') {
                    return '<span class="badge bg-warning text-dark px-2 py-1 fs-7"><i class="bi bi-exclamation-octagon-fill me-1"></i>Beban Ganda (Panitia & Insidentil)</span>';
                } elseif ($row->kategori_kendala === 'insidentil') {
                    return '<span class="badge bg-danger px-2 py-1 fs-7"><i class="bi bi-lightning-charge-fill me-1"></i>Terganggu Tugas Insidentil</span>';
                } elseif ($row->kategori_kendala === 'kepanitiaan') {
                    return '<span class="badge bg-info text-dark px-2 py-1 fs-7"><i class="bi bi-people-fill me-1"></i>Beban Tugas Kepanitiaan</span>';
                }
                return '<span class="badge bg-secondary px-2 py-1 fs-7"><i class="bi bi-person-x-fill me-1"></i>Keterlambatan Murni Staff</span>';
            })
            ->addColumn('rincian_bentrokan', function ($row) {
                $html = '';
                $insCount = $row->overlapping_insidentil ? $row->overlapping_insidentil->count() : 0;
                $panCount = $row->overlapping_kepanitiaan ? $row->overlapping_kepanitiaan->count() : 0;

                if ($insCount === 0 && $panCount === 0) {
                    return '<span class="text-muted small"><em>Tidak ada tugas panitia/insidentil yang bersamaan.</em></span>';
                }

                if ($insCount > 0) {
                    $html .= '<div class="small fw-bold text-danger mb-1"><i class="bi bi-lightning-charge me-1"></i>Tugas Insidentil (' . $insCount . '):</div>';
                    $html .= '<ul class="ps-3 mb-2 small text-dark" style="margin-bottom: 0;">';
                    foreach ($row->overlapping_insidentil as $ins) {
                        $html .= '<li>' . e($ins->uraian_tugas) . ' <span class="text-muted">(' . ($ins->status ?? '-') . ')</span></li>';
                    }
                    $html .= '</ul>';
                }

                if ($panCount > 0) {
                    $html .= '<div class="small fw-bold text-info mb-1"><i class="bi bi-people me-1"></i>Kegiatan Kepanitiaan (' . $panCount . '):</div>';
                    $html .= '<ul class="ps-3 mb-0 small text-dark">';
                    foreach ($row->overlapping_kepanitiaan as $pan) {
                        $html .= '<li>' . e($pan->nama_kegiatan ?? $pan->uraian_tugas) . ' <span class="text-muted">(' . ($pan->status ?? '-') . ')</span></li>';
                    }
                    $html .= '</ul>';
                }

                return $html;
            })
            ->addColumn('rekomendasi_evaluasi', function ($row) use ($authUser) {
                $canEdit = $authUser && ($authUser->isAdmin() || $authUser->isPimpinanRektorat());
                $saranAttr = e($row->saran_pimpinan ?? '');
                $editBtn = '';
                if ($canEdit) {
                    $editBtn = ' <button type="button" class="btn btn-xs btn-outline-primary ms-1 py-0 px-1 btn-edit-saran" data-id="' . $row->id . '" data-task="' . e($row->uraian_tugas) . '" data-staff="' . e($row->user ? $row->user->name : '-') . '" data-saran="' . $saranAttr . '" title="Input / Edit Saran Rektor"><i class="bi bi-pencil-square me-1"></i>Edit Saran</button>';
                }

                if (!empty($row->saran_pimpinan)) {
                    return '<div class="small text-dark p-2 bg-light rounded border border-primary-subtle">'
                         . '<div class="d-flex justify-content-between align-items-center mb-1">'
                         . '<span class="badge bg-primary text-white"><i class="bi bi-shield-check me-1"></i>Catatan / Saran Rektor</span>'
                         . $editBtn
                         . '</div>'
                         . '<div>' . nl2br(e($row->saran_pimpinan)) . '</div>'
                         . '</div>';
                }

                $saranSys = '';
                $borderClass = 'border';
                $iconClass = 'bi-info-circle text-secondary';
                if ($row->kategori_kendala === 'beban_ganda' || $row->kategori_kendala === 'insidentil') {
                    $saranSys = 'Diperlukan redistribusi porsi tugas mendesak atau penyesuaian ulang estimasi deadline tugas utama.';
                    $borderClass = 'border border-warning-subtle';
                    $iconClass = 'bi-info-circle text-warning';
                } elseif ($row->kategori_kendala === 'kepanitiaan') {
                    $saranSys = 'Evaluasi porsi keterlibatan anggota panitia agar tidak mengganggu performa target rutin bulanan.';
                    $borderClass = 'border border-info-subtle';
                    $iconClass = 'bi-info-circle text-info';
                } else {
                    $saranSys = 'Perlu pendampingan dan monitoring manajemen waktu pengerjaan tugas rutin staff.';
                }

                return '<div class="small text-dark p-2 bg-light rounded ' . $borderClass . '">'
                     . '<div class="d-flex justify-content-between align-items-center mb-1">'
                     . '<span><i class="bi ' . $iconClass . ' me-1"></i><strong>Saran Pimpinan (Otomatis):</strong></span>'
                     . $editBtn
                     . '</div>'
                     . '<div class="mb-1">' . $saranSys . '</div>'
                     . '</div>';
            })
            ->with('stats', $stats)
            ->rawColumns(['staff_info', 'task_details', 'diagnostik_kendala', 'rincian_bentrokan', 'rekomendasi_evaluasi'])
            ->make(true);
    }

    /**
     * Export Analisis Keterlambatan to Excel.
     */
    public function exportExcel(Request $request)
    {
        $authUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        $query = RencanaKerja::with(['user', 'periodeAkademik', 'taggedUsers']);

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }
                if ($request->filled('jabatan')) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('jabatan', $request->jabatan);
                    });
                }
            } else {
                $query->where(function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id)
                      ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                          $qu->where('users.id', $authUser->id);
                      });
                });
            }
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        $items = $query->latest()->get();

        $namaStaff = 'SEMUA STAFF';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        if ($request->filled('user_id') && ($u = User::find($request->user_id))) {
            $namaStaff = strtoupper($u->name);
            $jabatanStaff = strtoupper($u->jabatan ?? '-');
            $unitStaff = strtoupper($u->unit ?? '-');
        }

        $periodeText = 'SEMUA PERIODE';
        if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Analisis Keterlambatan');

        // Row 1: Title
        $titleText = 'LAPORAN ANALISIS KENDALA & KETERLAMBATAN KINERJA STAFF (' . $periodeText . ')';
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', $titleText);
        $sheet->getStyle('A1:H1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('8B0000'); // Dark Red Accent for Delay Analysis
        $sheet->getStyle('A1:H1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Metadata
        $sheet->setCellValue('A3', 'NAMA STAFF');
        $sheet->setCellValue('B3', ': ' . $namaStaff);
        $sheet->setCellValue('A4', 'JABATAN');
        $sheet->setCellValue('B4', ': ' . $jabatanStaff);
        $sheet->setCellValue('A5', 'UNIT');
        $sheet->setCellValue('B5', ': ' . $unitStaff);
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);

        // Headers
        $headers = [
            'A7' => 'NO',
            'B7' => 'NAMA STAFF & JABATAN',
            'C7' => 'TUGAS UTAMA (TERLAMBAT)',
            'D7' => 'ESTIMASI VS REALISASI',
            'E7' => 'KETERLAMBATAN',
            'F7' => 'DIAGNOSTIK KENDALA',
            'G7' => 'RINCIAN PEKERJAAN BENTROK',
            'H7' => 'REKOMENDASI EVALUASI',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerRange = 'A7:H7';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('8B0000');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(7)->setRowHeight(25);

        $rowNum = 8;
        $no = 1;

        foreach ($items as $task) {
            $isLate = false;
            $estimasiSelesai = $task->estimasi_tanggal_selesai;

            if (!empty($task->tanggal_selesai) && !empty($estimasiSelesai) && $task->tanggal_selesai > $estimasiSelesai) {
                $isLate = true;
            } elseif (($task->status !== 'Selesai') && !empty($estimasiSelesai) && $estimasiSelesai < $today) {
                $isLate = true;
            }

            if (!$isLate) continue;

            $startDate = $task->estimasi_tanggal_mulai ?? $task->tanggal_mulai ?? $task->created_at->format('Y-m-d');
            $endDate = !empty($task->tanggal_selesai) ? $task->tanggal_selesai : $today;

            $overlappingInsidentil = Insidentil::where('user_id', $task->user_id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('estimasi_tanggal_mulai')
                            ->where('estimasi_tanggal_mulai', '<=', $endDate)
                            ->where('estimasi_tanggal_selesai', '>=', $startDate);
                    })->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('tanggal_mulai')
                            ->where('tanggal_mulai', '<=', $endDate)
                            ->where(function ($nest) use ($startDate) {
                                $nest->where('tanggal_selesai', '>=', $startDate)
                                    ->orWhereNull('tanggal_selesai');
                            });
                    });
                })->get();

            $overlappingKepanitiaan = Kepanitiaan::where(function ($q) use ($task) {
                $q->where('user_id', $task->user_id)
                    ->orWhereHas('taggedUsers', function ($tu) use ($task) {
                        $tu->where('users.id', $task->user_id);
                    });
            })->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('estimasi_tanggal_mulai')
                        ->where('estimasi_tanggal_mulai', '<=', $endDate)
                        ->where('estimasi_tanggal_selesai', '>=', $startDate);
                })->orWhere(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('tanggal_mulai')
                        ->where('tanggal_mulai', '<=', $endDate)
                        ->where(function ($nest) use ($startDate) {
                            $nest->where('tanggal_selesai', '>=', $startDate)
                                ->orWhereNull('tanggal_selesai');
                        });
                });
            })->get();

            $hasIns = $overlappingInsidentil->count() > 0;
            $hasPan = $overlappingKepanitiaan->count() > 0;

            $diagLabel = 'Keterlambatan Murni Staff';
            $rekomendasi = 'Pendampingan & monitoring manajemen waktu rutin.';
            if ($hasIns && $hasPan) {
                $diagLabel = 'Beban Ganda (Panitia & Insidentil)';
                $rekomendasi = 'Redistribusi porsi tugas mendesak & penyesuaian deadline.';
            } elseif ($hasIns) {
                $diagLabel = 'Terganggu Tugas Insidentil';
                $rekomendasi = 'Redistribusi porsi tugas insidentil dari pimpinan.';
            } elseif ($hasPan) {
                $diagLabel = 'Beban Tugas Kepanitiaan';
                $rekomendasi = 'Evaluasi porsi keterlibatan anggota panitia.';
            }

            if (!empty($task->saran_pimpinan)) {
                $rekomendasi = '[Saran Rektor] ' . $task->saran_pimpinan;
            }

            $bentrokanText = [];
            if ($hasIns) {
                $insList = $overlappingInsidentil->pluck('uraian_tugas')->implode(', ');
                $bentrokanText[] = 'Insidentil: ' . $insList;
            }
            if ($hasPan) {
                $panList = $overlappingKepanitiaan->pluck('nama_kegiatan')->implode(', ');
                $bentrokanText[] = 'Kepanitiaan: ' . ($panList ?: 'Kegiatan Panitia');
            }
            $bentrokanStr = !empty($bentrokanText) ? implode("\n", $bentrokanText) : 'Tidak Ada Bentrokan';

            $delayDays = max(1, (int) Carbon::parse($estimasiSelesai)->diffInDays(Carbon::parse($endDate)));

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, ($task->user ? $task->user->name : '-') . "\n(" . ($task->user ? ($task->user->jabatan ?? '-') : '-') . ')');
            $sheet->setCellValue('C' . $rowNum, $task->uraian_tugas);
            $sheet->setCellValue('D' . $rowNum, 'Est: ' . ($task->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($task->estimasi_tanggal_selesai)) : '-') . "\nReal: " . ($task->tanggal_selesai ? date('d/m/Y', strtotime($task->tanggal_selesai)) : 'Belum Selesai'));
            $sheet->setCellValue('E' . $rowNum, $delayDays . ' Hari');
            $sheet->setCellValue('F' . $rowNum, $diagLabel);
            $sheet->setCellValue('G' . $rowNum, $bentrokanStr);
            $sheet->setCellValue('H' . $rowNum, $rekomendasi);

            $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('F' . $rowNum . ':H' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $rowNum++;
        }

        $lastRow = max(7, $rowNum - 1);
        $tableRange = 'A7:H' . $lastRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setRGB('CCCCCC');
        $sheet->getStyle($tableRange)->getAlignment()->setWrapText(true);

        $widths = ['A' => 6, 'B' => 25, 'C' => 35, 'D' => 22, 'E' => 15, 'F' => 28, 'G' => 35, 'H' => 35];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $filename = 'Laporan_Analisis_Keterlambatan_' . date('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'analisis_export_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export Analisis Keterlambatan to PDF.
     */
    public function exportPdf(Request $request)
    {
        $authUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        $query = RencanaKerja::with(['user', 'periodeAkademik', 'taggedUsers']);

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }
                if ($request->filled('jabatan')) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('jabatan', $request->jabatan);
                    });
                }
            } else {
                $query->where(function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id)
                      ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                          $qu->where('users.id', $authUser->id);
                      });
                });
            }
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        $allTasks = $query->latest()->get();
        $lateTasks = collect();

        foreach ($allTasks as $task) {
            $isLate = false;
            $estimasiSelesai = $task->estimasi_tanggal_selesai;

            if (!empty($task->tanggal_selesai) && !empty($estimasiSelesai) && $task->tanggal_selesai > $estimasiSelesai) {
                $isLate = true;
            } elseif (($task->status !== 'Selesai') && !empty($estimasiSelesai) && $estimasiSelesai < $today) {
                $isLate = true;
            }

            if (!$isLate) continue;

            $startDate = $task->estimasi_tanggal_mulai ?? $task->tanggal_mulai ?? $task->created_at->format('Y-m-d');
            $endDate = !empty($task->tanggal_selesai) ? $task->tanggal_selesai : $today;

            $overlappingInsidentil = Insidentil::where('user_id', $task->user_id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('estimasi_tanggal_mulai')
                            ->where('estimasi_tanggal_mulai', '<=', $endDate)
                            ->where('estimasi_tanggal_selesai', '>=', $startDate);
                    })->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('tanggal_mulai')
                            ->where('tanggal_mulai', '<=', $endDate)
                            ->where(function ($nest) use ($startDate) {
                                $nest->where('tanggal_selesai', '>=', $startDate)
                                    ->orWhereNull('tanggal_selesai');
                            });
                    });
                })->get();

            $overlappingKepanitiaan = Kepanitiaan::where(function ($q) use ($task) {
                $q->where('user_id', $task->user_id)
                    ->orWhereHas('taggedUsers', function ($tu) use ($task) {
                        $tu->where('users.id', $task->user_id);
                    });
            })->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('estimasi_tanggal_mulai')
                        ->where('estimasi_tanggal_mulai', '<=', $endDate)
                        ->where('estimasi_tanggal_selesai', '>=', $startDate);
                })->orWhere(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('tanggal_mulai')
                        ->where('tanggal_mulai', '<=', $endDate)
                        ->where(function ($nest) use ($startDate) {
                            $nest->where('tanggal_selesai', '>=', $startDate)
                                ->orWhereNull('tanggal_selesai');
                        });
                });
            })->get();

            $hasIns = $overlappingInsidentil->count() > 0;
            $hasPan = $overlappingKepanitiaan->count() > 0;

            if ($hasIns && $hasPan) {
                $task->kategori_kendala = 'beban_ganda';
            } elseif ($hasIns) {
                $task->kategori_kendala = 'insidentil';
            } elseif ($hasPan) {
                $task->kategori_kendala = 'kepanitiaan';
            } else {
                $task->kategori_kendala = 'murni';
            }

            $task->overlapping_insidentil = $overlappingInsidentil;
            $task->overlapping_kepanitiaan = $overlappingKepanitiaan;
            $task->delay_days = max(1, (int) Carbon::parse($estimasiSelesai)->diffInDays(Carbon::parse($endDate)));

            $lateTasks->push($task);
        }

        $namaStaff = 'SEMUA STAFF';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        if ($request->filled('user_id') && ($u = User::find($request->user_id))) {
            $namaStaff = strtoupper($u->name);
            $jabatanStaff = strtoupper($u->jabatan ?? '-');
            $unitStaff = strtoupper($u->unit ?? '-');
        }

        $periodeText = 'SEMUA PERIODE';
        if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.analisis_keterlambatan.pdf', compact('lateTasks', 'namaStaff', 'jabatanStaff', 'unitStaff', 'periodeText'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Analisis_Keterlambatan_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Update Saran Pimpinan / Rektor for delayed task.
     */
    public function updateSaranPimpinan(Request $request, $id)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isPimpinanRektorat())) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk memberikan saran pimpinan.'
            ], 403);
        }

        $request->validate([
            'saran_pimpinan' => 'nullable|string|max:2000',
        ]);

        $task = RencanaKerja::findOrFail($id);
        $task->saran_pimpinan = $request->saran_pimpinan;
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Saran Pimpinan / Rekomendasi Rektor berhasil diperbarui.',
        ]);
    }
}
