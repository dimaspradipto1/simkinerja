<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PeriodeAkademik;
use App\Models\RencanaKerja;
use App\Models\Kegiatan;
use App\Models\Kepanitiaan;
use App\Models\Insidentil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanKinerjaPegawaiController extends Controller
{
    /**
     * Helper untuk mengecek apakah user adalah Dosen
     */
    protected function isDosenUser($user): bool
    {
        $nidn = trim((string)($user->nidn ?? ''));
        if (!empty($nidn) && $nidn !== '-' && $nidn !== '0') {
            return true;
        }

        $jabatan = strtolower($user->jabatan ?? '');
        $unit = strtolower($user->unit ?? '');
        $roles = strtolower($user->roles ?? '');

        $dosenKeywords = ['dosen', 'lektor', 'asisten ahli', 'guru besar', 'profesor', 'dekan', 'kaprodi', 'ka. prodi', 'sekprodi', 'pengajar'];
        foreach ($dosenKeywords as $kw) {
            if (str_contains($jabatan, $kw) || str_contains($roles, $kw)) {
                return true;
            }
        }

        if (str_contains($unit, 'fakultas') || str_contains($unit, 'prodi') || str_contains($unit, 'jurusan')) {
            return true;
        }

        return false;
    }

    /**
     * Helper untuk menentukan bidang tugas pegawai
     */
    protected function getBidangTugas($user): string
    {
        $unit = strtoupper(trim((string)($user->unit ?? '')));
        $jabatan = strtoupper(trim((string)($user->jabatan ?? '')));

        if (str_contains($unit, 'FAKULTAS') || str_contains($unit, 'PRODI') || str_contains($jabatan, 'DOSEN') || str_contains($jabatan, 'DEKAN')) {
            return 'Pendidikan & Akademik';
        }
        if (str_contains($unit, 'LPPM') || str_contains($jabatan, 'PENELITIAN') || str_contains($jabatan, 'PENGABDIAN')) {
            return 'Penelitian & Pengabdian Masyarakat';
        }
        if (str_contains($unit, 'LPMI') || str_contains($unit, 'MUTU') || str_contains($jabatan, 'EVALUASI')) {
            return 'Penjaminan Mutu & Akreditasi';
        }
        if (str_contains($unit, 'ICT') || str_contains($unit, 'LPTI') || str_contains($unit, 'IT')) {
            return 'Teknologi Informasi & Sistem';
        }
        if (str_contains($unit, 'KEUANGAN') || str_contains($jabatan, 'KASIR') || str_contains($jabatan, 'BENDAHARA')) {
            return 'Keuangan & Akuntansi';
        }
        if (str_contains($unit, 'SARPRAS') || str_contains($unit, 'UMUM') || str_contains($jabatan, 'SARANA')) {
            return 'Umum & Sarana Prasarana';
        }
        if (str_contains($unit, 'PERPUSTAKAAN') || str_contains($jabatan, 'PUSTAKAWAN')) {
            return 'Layanan Perpustakaan & Literasi';
        }
        if (str_contains($unit, 'LABORATORIUM') || str_contains($jabatan, 'LABORAN')) {
            return 'Laboratorium & Praktikum';
        }
        if (str_contains($unit, 'REKTORAT') || str_contains($jabatan, 'REKTOR')) {
            return 'Manajemen & Kepemimpinan Universitas';
        }

        return 'Administrasi & Layanan Teknis';
    }

    /**
     * Helper untuk mengkategorikan Tri Dharma dari suatu uraian tugas
     */
    protected function classifyTriDharma(string $uraian): string
    {
        $text = strtolower($uraian);

        // Penelitian
        if (str_contains($text, 'penelitian') || str_contains($text, 'riset') || str_contains($text, 'jurnal') || 
            str_contains($text, 'publikasi') || str_contains($text, 'sinta') || str_contains($text, 'scopus') || 
            str_contains($text, 'hki') || str_contains($text, 'paten') || str_contains($text, 'prosiding') || 
            str_contains($text, 'seminar nasional') || str_contains($text, 'seminar internasional')) {
            return 'penelitian';
        }

        // Pengabdian kepada Masyarakat
        if (str_contains($text, 'pengabdian') || str_contains($text, 'pkm') || str_contains($text, 'desa binaan') || 
            str_contains($text, 'penyuluhan') || str_contains($text, 'pelatihan masyarakat') || str_contains($text, 'umkm') || 
            str_contains($text, 'bina desa') || str_contains($text, 'sosialisasi masyarakat')) {
            return 'pkm';
        }

        // Default untuk dosen: Pendidikan & Pengajaran / Penunjang Akademik
        return 'pendidikan';
    }

    /**
     * Mengumpulkan dan mengolah seluruh data kinerja pegawai
     */
    protected function gatherData(Request $request): array
    {
        $authUser = Auth::user();

        // 1. Ambil list periode akademik
        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();
        $selectedPeriodeId = $request->input('periode_akademik_id');
        if (!$selectedPeriodeId) {
            $selectedPeriode = PeriodeAkademik::latest()->first();
            $selectedPeriodeId = $selectedPeriode ? $selectedPeriode->id : null;
        } else {
            $selectedPeriode = PeriodeAkademik::find($selectedPeriodeId);
        }

        // 2. Filter Unit
        $unitsList = User::whereNotNull('unit')
            ->where('unit', '!=', '')
            ->select('unit')
            ->distinct()
            ->orderBy('unit', 'asc')
            ->pluck('unit');

        $selectedUnit = $request->input('unit');
        if ($authUser && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && $authUser->isPimpinanUnit()) {
            $selectedUnit = $authUser->unit;
        }

        // 3. Query Pegawai (Users)
        $userQuery = User::where('is_active', true);
        if ($selectedUnit) {
            $userQuery->where('unit', $selectedUnit);
        }
        if ($request->filled('status_pegawai')) {
            $userQuery->where('status', $request->status_pegawai);
        }
        if ($request->filled('search')) {
            $kw = $request->search;
            $userQuery->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                  ->orWhere('nidn', 'like', "%{$kw}%")
                  ->orWhere('jabatan', 'like', "%{$kw}%");
            });
        }

        $pegawaiList = $userQuery->orderBy('unit', 'asc')->orderBy('name', 'asc')->get();
        $pegawaiIds = $pegawaiList->pluck('id')->toArray();

        // 4. Query Tasks (Rencana Kerja, Kegiatan, Kepanitiaan, Insidentil)
        $rkQuery = RencanaKerja::query();
        $kgQuery = Kegiatan::query();
        $kpQuery = Kepanitiaan::query();
        $inQuery = Insidentil::query();

        // Filter tugas untuk pegawai yang terpilih sesuai filter unit / pencarian (baik sebagai pembuat maupun yang ditag)
        $filterByUserOrTagged = function ($q) use ($pegawaiIds) {
            $q->where(function ($sub) use ($pegawaiIds) {
                $sub->whereIn('user_id', $pegawaiIds)
                    ->orWhereHas('taggedUsers', fn($qu) => $qu->whereIn('users.id', $pegawaiIds));
            });
        };

        $filterByUserOrTagged($rkQuery);
        $filterByUserOrTagged($kgQuery);
        $filterByUserOrTagged($kpQuery);
        $filterByUserOrTagged($inQuery);

        if ($selectedPeriodeId) {
            $rkQuery->where('periode_akademik_id', $selectedPeriodeId);
            $kgQuery->where('periode_akademik_id', $selectedPeriodeId);
            $kpQuery->where('periode_akademik_id', $selectedPeriodeId);
            $inQuery->where('periode_akademik_id', $selectedPeriodeId);
        }

        $allRk = $rkQuery->with(['user', 'taggedUsers'])->get();
        $allKg = $kgQuery->with(['user', 'taggedUsers'])->get();
        $allKp = $kpQuery->with(['user', 'taggedUsers'])->get();
        $allIn = $inQuery->with(['user', 'taggedUsers'])->get();

        // Index tasks per user_id
        $tasksByUser = [];
        $dosenTriDharma = [];
        $kendalaList = [];
        $tindakLanjutList = [];

        // Helper mapper untuk rekap task dan pembersihan tag HTML
        $stripHtml = function ($value, $default = '-') {
            if ($value === null || $value === '') return $default;
            $decoded = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $clean = strip_tags($decoded);
            $clean = trim(preg_replace('/\s+/', ' ', str_replace(["\xc2\xa0", '&nbsp;'], ' ', $clean)));
            return ($clean !== '') ? $clean : $default;
        };

        $processTask = function ($task, $typeLabel) use (&$tasksByUser, &$dosenTriDharma, &$kendalaList, &$tindakLanjutList, $stripHtml, $pegawaiIds) {
            $uraian = (string)($task->uraian_tugas ?? '');
            $status = $task->status;
            $kategoriDharma = $this->classifyTriDharma($uraian);

            $participantIds = collect([$task->user_id])
                ->merge($task->taggedUsers ? $task->taggedUsers->pluck('id') : [])
                ->filter()
                ->unique();

            $prefix = match($typeLabel) {
                'Rencana Kerja' => 'rk_',
                'Kegiatan' => 'kg_',
                'Kepanitiaan' => 'kp_',
                'Insidentil' => 'in_',
                default => 'rk_'
            };

            foreach ($participantIds as $uid) {
                if (!in_array($uid, $pegawaiIds)) {
                    continue;
                }

                if (!isset($tasksByUser[$uid])) {
                    $tasksByUser[$uid] = [
                        'rk_total' => 0, 'rk_selesai' => 0,
                        'kg_total' => 0, 'kg_selesai' => 0,
                        'kp_total' => 0, 'kp_selesai' => 0,
                        'in_total' => 0, 'in_selesai' => 0,
                        'total' => 0, 'selesai' => 0, 'proses' => 0, 'belum' => 0,
                    ];
                }

                $tasksByUser[$uid][$prefix . 'total']++;
                $tasksByUser[$uid]['total']++;

                if ($status === 'Selesai') {
                    $tasksByUser[$uid][$prefix . 'selesai']++;
                    $tasksByUser[$uid]['selesai']++;
                } elseif (in_array($status, ['Proses', 'Berjalan'])) {
                    $tasksByUser[$uid]['proses']++;
                } else {
                    $tasksByUser[$uid]['belum']++;
                }

                // Tri Dharma categorization jika user adalah dosen
                if (!isset($dosenTriDharma[$uid])) {
                    $dosenTriDharma[$uid] = [
                        'pendidikan_total' => 0, 'pendidikan_selesai' => 0,
                        'penelitian_total' => 0, 'penelitian_selesai' => 0,
                        'pkm_total' => 0, 'pkm_selesai' => 0,
                    ];
                }

                $dosenTriDharma[$uid][$kategoriDharma . '_total']++;
                if ($status === 'Selesai') {
                    $dosenTriDharma[$uid][$kategoriDharma . '_selesai']++;
                }
            }

            // Tangkap kendala & permasalahan
            $hasProblem = !empty($task->estimasi_unlock_reason) 
                || !empty($task->is_paused) 
                || (method_exists($task, 'isLate') && $task->isLate())
                || ($status === 'Belum Dimulai' && !empty($task->hasil_kerja));

            if ($hasProblem || !empty($task->estimasi_unlock_reason)) {
                $kendalaRaw = $task->estimasi_unlock_reason ?? 'Tugas memerlukan perpanjangan waktu atau mengalami penundaan.';
                $solusiRaw = $task->rencana_tindak_lanjut ?? $task->saran_pimpinan ?? 'Dilakukan penyesuaian jadwal dan koordinasi dengan pimpinan unit.';

                $kendalaList[] = [
                    'user' => $task->user ? $task->user->name : 'Pegawai',
                    'unit' => $task->user ? $task->user->unit : '-',
                    'jabatan' => $task->user ? $task->user->jabatan : '-',
                    'tugas' => $stripHtml($uraian),
                    'jenis' => $typeLabel,
                    'kendala' => $stripHtml($kendalaRaw, 'Tugas memerlukan perpanjangan waktu atau mengalami penundaan.'),
                    'solusi' => $stripHtml($solusiRaw, 'Dilakukan penyesuaian jadwal dan koordinasi dengan pimpinan unit.'),
                    'status' => $status
                ];
            }

            // Tangkap tindak lanjut
            if (!empty($task->rencana_tindak_lanjut) || !empty($task->saran_pimpinan)) {
                $tindakLanjutList[] = [
                    'user' => $task->user ? $task->user->name : 'Pegawai',
                    'unit' => $task->user ? $task->user->unit : '-',
                    'tugas' => $stripHtml($uraian),
                    'hasil_kerja' => $stripHtml($task->hasil_kerja, '-'),
                    'tindak_lanjut' => $stripHtml($task->rencana_tindak_lanjut, '-'),
                    'saran_pimpinan' => $stripHtml($task->saran_pimpinan, '-')
                ];
            }
        };

        foreach ($allRk as $t) { $processTask($t, 'Rencana Kerja'); }
        foreach ($allKg as $t) { $processTask($t, 'Kegiatan'); }
        foreach ($allKp as $t) { $processTask($t, 'Kepanitiaan'); }
        foreach ($allIn as $t) { $processTask($t, 'Insidentil'); }

        // 5. Olah data pegawai & matriks capaian
        $rekapUnit = [];
        $capaianPegawai = [];
        $evaluasiDosen = [];

        $totalPegawai = $pegawaiList->count();
        $totalDosen = 0;
        $totalTendik = 0;
        $totalTugasSemua = 0;
        $totalSelesaiSemua = 0;

        foreach ($pegawaiList as $p) {
            $isDosen = $this->isDosenUser($p);
            $bidangTugas = $this->getBidangTugas($p);

            if ($isDosen) {
                $totalDosen++;
            } else {
                $totalTendik++;
            }

            // Rekap demografi unit
            $uName = $p->unit ?: 'Lainnya / Belum Ditentukan';
            if (!isset($rekapUnit[$uName])) {
                $rekapUnit[$uName] = [
                    'unit' => $uName,
                    'dosen' => 0,
                    'tendik' => 0,
                    'total' => 0,
                    'bidang' => $bidangTugas
                ];
            }
            if ($isDosen) {
                $rekapUnit[$uName]['dosen']++;
            } else {
                $rekapUnit[$uName]['tendik']++;
            }
            $rekapUnit[$uName]['total']++;

            // Capaian tugas user
            $uTasks = $tasksByUser[$p->id] ?? [
                'rk_total' => 0, 'rk_selesai' => 0,
                'kg_total' => 0, 'kg_selesai' => 0,
                'kp_total' => 0, 'kp_selesai' => 0,
                'in_total' => 0, 'in_selesai' => 0,
                'total' => 0, 'selesai' => 0, 'proses' => 0, 'belum' => 0,
            ];

            $pct = $uTasks['total'] > 0 ? round(($uTasks['selesai'] / $uTasks['total']) * 100, 1) : 0;
            
            // Predikat Kinerja
            if ($uTasks['total'] == 0) {
                $predikat = 'Belum Ada Tugas';
                $badgeClass = 'bg-secondary';
            } elseif ($pct >= 90) {
                $predikat = 'Sangat Baik';
                $badgeClass = 'bg-success';
            } elseif ($pct >= 75) {
                $predikat = 'Baik';
                $badgeClass = 'bg-primary';
            } elseif ($pct >= 50) {
                $predikat = 'Cukup';
                $badgeClass = 'bg-warning text-dark';
            } else {
                $predikat = 'Perlu Peningkatan';
                $badgeClass = 'bg-danger';
            }

            $capaianPegawai[] = [
                'id' => $p->id,
                'name' => $p->name,
                'nidn' => $p->nidn ?: '-',
                'unit' => $p->unit ?: '-',
                'jabatan' => $p->jabatan ?: '-',
                'status' => $p->status ?: 'Aktif',
                'bidang' => $bidangTugas,
                'is_dosen' => $isDosen,
                'tasks' => $uTasks,
                'persentase' => $pct,
                'predikat' => $predikat,
                'badgeClass' => $badgeClass
            ];

            $totalTugasSemua += $uTasks['total'];
            $totalSelesaiSemua += $uTasks['selesai'];

            // Evaluasi Tri Dharma Dosen
            if ($isDosen) {
                $dharma = $dosenTriDharma[$p->id] ?? [
                    'pendidikan_total' => 0, 'pendidikan_selesai' => 0,
                    'penelitian_total' => 0, 'penelitian_selesai' => 0,
                    'pkm_total' => 0, 'pkm_selesai' => 0,
                ];

                $totalDharma = $dharma['pendidikan_total'] + $dharma['penelitian_total'] + $dharma['pkm_total'];
                $selesaiDharma = $dharma['pendidikan_selesai'] + $dharma['penelitian_selesai'] + $dharma['pkm_selesai'];
                $pctDharma = $totalDharma > 0 ? round(($selesaiDharma / $totalDharma) * 100, 1) : 0;

                $evaluasiDosen[] = [
                    'name' => $p->name,
                    'nidn' => $p->nidn ?: '-',
                    'unit' => $p->unit ?: '-',
                    'jabatan' => $p->jabatan ?: 'Dosen',
                    'dharma' => $dharma,
                    'total_dharma' => $totalDharma,
                    'selesai_dharma' => $selesaiDharma,
                    'persentase' => $pctDharma,
                    'status_tri_dharma' => ($dharma['pendidikan_total'] > 0 && $dharma['penelitian_total'] > 0 && $dharma['pkm_total'] > 0) 
                        ? 'Lengkap 3 Dharma' 
                        : 'Sebagian Dharma'
                ];
            }
        }

        $persentaseRataRata = $totalTugasSemua > 0 ? round(($totalSelesaiSemua / $totalTugasSemua) * 100, 1) : 0;

        return [
            'periodeAkademiks' => $periodeAkademiks,
            'selectedPeriode' => $selectedPeriode,
            'unitsList' => $unitsList,
            'selectedUnit' => $selectedUnit,
            'totalPegawai' => $totalPegawai,
            'totalDosen' => $totalDosen,
            'totalTendik' => $totalTendik,
            'totalTugasSemua' => $totalTugasSemua,
            'totalSelesaiSemua' => $totalSelesaiSemua,
            'persentaseRataRata' => $persentaseRataRata,
            'rekapUnit' => array_values($rekapUnit),
            'capaianPegawai' => $capaianPegawai,
            'evaluasiDosen' => $evaluasiDosen,
            'kendalaList' => $kendalaList,
            'tindakLanjutList' => $tindakLanjutList,
        ];
    }

    /**
     * Tampilan utama Laporan Kinerja Pegawai
     */
    public function index(Request $request)
    {
        $data = $this->gatherData($request);
        return view('pages.laporan-kinerja-pegawai.index', $data);
    }

    /**
     * Cetak PDF Resmi Laporan Kinerja Pegawai
     */
    public function exportPdf(Request $request)
    {
        $data = $this->gatherData($request);
        $periodeText = $data['selectedPeriode'] ? $data['selectedPeriode']->nama_periode : 'Semua Periode';
        $unitText = $data['selectedUnit'] ?: 'Semua Unit';

        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));
        $filename = 'Laporan_Kinerja_Pegawai_UIS_' . ($safePeriode ?: 'Periode') . '.pdf';

        $pdf = Pdf::loadView('pages.laporan-kinerja-pegawai.pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    /**
     * Ekspor Excel Laporan Kinerja Pegawai
     */
    public function exportExcel(Request $request)
    {
        $data = $this->gatherData($request);
        $periodeText = $data['selectedPeriode'] ? $data['selectedPeriode']->nama_periode : 'Semua Periode';

        $spreadsheet = new Spreadsheet();
        
        // Sheet 1: Rekapitulasi Capaian Kinerja Pegawai
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Capaian Kinerja Pegawai');

        // Header Title
        $sheet1->setCellValue('A1', 'UNIVERSITAS IBNU SINA - YAYASAN PENDIDIKAN IBNU SINA BATAM');
        $sheet1->setCellValue('A2', 'LAPORAN KINERJA PEGAWAI - PERIODE ' . strtoupper($periodeText));
        $sheet1->mergeCells('A1:J1');
        $sheet1->mergeCells('A2:J2');
        $sheet1->getStyle('A1:A2')->getFont()->setBold(true)->setSize(12);
        $sheet1->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Header
        $headers = ['No', 'Nama Pegawai', 'NIDN/NUP', 'Unit Kerja', 'Jabatan', 'Status', 'Bidang Tugas', 'Total Tugas', 'Selesai', '% Capaian', 'Predikat'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet1->setCellValue($col . '4', $h);
            $col++;
        }

        $sheet1->getStyle('A4:K4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle('A4:K4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('15432D');
        $sheet1->getStyle('A4:K4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 5;
        $no = 1;
        foreach ($data['capaianPegawai'] as $p) {
            $sheet1->setCellValue('A' . $rowNum, $no++);
            $sheet1->setCellValue('B' . $rowNum, $p['name']);
            $sheet1->setCellValueExplicit('C' . $rowNum, (string)$p['nidn'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet1->setCellValue('D' . $rowNum, $p['unit']);
            $sheet1->setCellValue('E' . $rowNum, $p['jabatan']);
            $sheet1->setCellValue('F' . $rowNum, $p['status']);
            $sheet1->setCellValue('G' . $rowNum, $p['bidang']);
            $sheet1->setCellValue('H' . $rowNum, $p['tasks']['total']);
            $sheet1->setCellValue('I' . $rowNum, $p['tasks']['selesai']);
            $sheet1->setCellValue('J' . $rowNum, $p['persentase'] . '%');
            $sheet1->setCellValue('K' . $rowNum, $p['predikat']);

            $sheet1->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('F' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('H' . $rowNum . ':K' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowNum++;
        }

        $sheet1->getStyle('A4:K' . ($rowNum - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'K') as $c) {
            $sheet1->getColumnDimension($c)->setAutoSize(true);
        }

        // Sheet 2: Evaluasi Dosen (Tri Dharma)
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Evaluasi Tri Dharma Dosen');
        $sheet2->setCellValue('A1', 'EVALUASI KINERJA DOSEN (TRI DHARMA PERGURUAN TINGGI)');
        $sheet2->mergeCells('A1:H1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        $dHeaders = ['No', 'Nama Dosen', 'NIDN', 'Unit / Prodi', 'Pendidikan & Pengajaran', 'Penelitian', 'Pengabdian (PkM)', 'Kelengkapan Dharma'];
        $col = 'A';
        foreach ($dHeaders as $h) {
            $sheet2->setCellValue($col . '3', $h);
            $col++;
        }
        $sheet2->getStyle('A3:H3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet2->getStyle('A3:H3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('15432D');

        $rDosen = 4;
        $noD = 1;
        foreach ($data['evaluasiDosen'] as $ed) {
            $sheet2->setCellValue('A' . $rDosen, $noD++);
            $sheet2->setCellValue('B' . $rDosen, $ed['name']);
            $sheet2->setCellValueExplicit('C' . $rDosen, (string)$ed['nidn'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet2->setCellValue('D' . $rDosen, $ed['unit']);
            $sheet2->setCellValue('E' . $rDosen, $ed['dharma']['pendidikan_selesai'] . ' / ' . $ed['dharma']['pendidikan_total'] . ' Selesai');
            $sheet2->setCellValue('F' . $rDosen, $ed['dharma']['penelitian_selesai'] . ' / ' . $ed['dharma']['penelitian_total'] . ' Selesai');
            $sheet2->setCellValue('G' . $rDosen, $ed['dharma']['pkm_selesai'] . ' / ' . $ed['dharma']['pkm_total'] . ' Selesai');
            $sheet2->setCellValue('H' . $rDosen, $ed['status_tri_dharma']);
            $rDosen++;
        }
        $sheet2->getStyle('A3:H' . ($rDosen - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'H') as $c) {
            $sheet2->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan_Kinerja_Pegawai_UIS_' . date('Ymd_His') . '.xlsx';

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
