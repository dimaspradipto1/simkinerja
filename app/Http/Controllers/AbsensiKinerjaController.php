<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKinerjaHarian;
use App\Models\Kegiatan;
use App\Models\PeriodeAkademik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AbsensiKinerjaController extends Controller
{
    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'cakupan' => ['nullable', 'in:semua,saya,bawahan'],
            'periode_akademik_id' => ['nullable', 'integer', 'exists:periode_akademiks,id'],
            'search' => ['nullable', 'string', 'max:200'],
        ]);

        $authUser = Auth::user();
        $isAdmin = $authUser->isAdmin() || $authUser->isSuperAdmin();
        $isPimpinan = $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit();
        $hasPimpinanScope = $isPimpinan && !$authUser->isSuperAdmin();

        $query = AbsensiKinerjaHarian::with('user');
        if (!$isAdmin && !$isPimpinan) {
            $query->where('user_id', $authUser->id);
        } elseif ($hasPimpinanScope) {
            $userIds = $this->getPimpinanUserIds($authUser);
            $scope = $validated['cakupan'] ?? 'semua';
            if ($scope === 'saya') {
                $userIds = [$authUser->id];
            } elseif ($scope === 'bawahan') {
                $userIds = array_values(array_diff($userIds, [$authUser->id]));
            }
            $query->whereIn('user_id', $userIds);
        }

        if (!empty($validated['dari'])) {
            $query->whereDate('tanggal', '>=', $validated['dari']);
        }
        if (!empty($validated['sampai'])) {
            $query->whereDate('tanggal', '<=', $validated['sampai']);
        }
        if (!empty($validated['user_id']) && ($isAdmin || $isPimpinan)) {
            $query->where('user_id', $validated['user_id']);
        }

        $search = trim($validated['search'] ?? '');
        if ($search !== '') {
            $query->where(function ($records) use ($search) {
                $records->whereHas('user', function ($users) use ($search) {
                    $users->where('name', 'like', "%{$search}%")
                        ->orWhere('jabatan', 'like', "%{$search}%");
                })->orWhere('tanggal', 'like', "%{$search}%")
                    ->orWhere('status_datang', 'like', "%{$search}%")
                    ->orWhere('status_pulang', 'like', "%{$search}%")
                    ->orWhere('keterangan_kerja', 'like', "%{$search}%");
            });
        }

        $records = $query->orderByDesc('tanggal')->orderByDesc('updated_at')->get();
        $selectedPeriod = !empty($validated['periode_akademik_id'])
            ? PeriodeAkademik::find($validated['periode_akademik_id'])
            : null;
        $records = $this->filterRecordsByAcademicPeriod($records, $selectedPeriod?->id);
        $taskIds = $records
            ->flatMap(fn (AbsensiKinerjaHarian $record) => collect($record->keterangan_kerja ?? [])->pluck('task_id'))
            ->filter()
            ->unique();
        $taskTimers = $taskIds->isEmpty()
            ? collect()
            : Kegiatan::query()
                ->whereIn('id', $taskIds)
                ->get(['id', 'durasi_detik', 'last_started_at', 'status', 'tanggal_mulai', 'waktu_mulai', 'tanggal_selesai', 'waktu_selesai'])
                ->keyBy('id');
        $now = Carbon::now();

        $rows = $records->map(function (AbsensiKinerjaHarian $record) use ($taskTimers, $now) {
            $activities = $this->resolveActivityDurations(
                $record->keterangan_kerja ?? [],
                $taskTimers,
                $record->tanggal,
                $now
            );
            $totalSeconds = array_sum(array_column($activities, 'seconds'));
            $assessment = $this->assessWorkDuration($totalSeconds);

            return [
                'tanggal' => $record->tanggal ? Carbon::parse($record->tanggal)->translatedFormat('d F Y') : '-',
                'nama' => $record->user?->name ?? '-',
                'jabatan' => $record->user?->jabatan ?? '-',
                'datang_status' => $record->status_datang ?? ($record->waktu_datang ? 'Hadir' : 'Belum'),
                'datang_waktu' => $record->waktu_datang ? substr($record->waktu_datang, 0, 5) . ' WIB' : '-',
                'pulang_status' => $record->status_pulang ?? ($record->waktu_pulang ? 'Hadir' : 'Belum'),
                'pulang_waktu' => $record->waktu_pulang ? substr($record->waktu_pulang, 0, 5) . ' WIB' : '-',
                'total_durasi' => $this->formatDuration($totalSeconds),
                'progress' => $assessment['progress'] . '%',
                'status_kinerja' => $assessment['label'],
                'evaluasi' => $assessment['detail'],
                'kegiatan' => array_map(function (array $activityData): array {
                    $entry = $activityData['entry'];
                    return [
                        'kategori' => $this->categoryLabel($entry['jenis'] ?? $activityData['task']?->kategori),
                        'uraian' => $entry['uraian'] ?? '-',
                        'status' => $entry['status'] ?? '',
                        'durasi' => $this->formatDuration($activityData['seconds']),
                    ];
                }, $activities),
            ];
        });

        $periodText = $selectedPeriod?->nama_periode ?? 'Semua tanggal';
        if (!$selectedPeriod && !empty($validated['dari']) && !empty($validated['sampai'])) {
            $periodText = Carbon::parse($validated['dari'])->format('d/m/Y')
                . ' s/d ' . Carbon::parse($validated['sampai'])->format('d/m/Y');
        } elseif (!$selectedPeriod && !empty($validated['dari'])) {
            $periodText = 'Mulai ' . Carbon::parse($validated['dari'])->format('d/m/Y');
        } elseif (!$selectedPeriod && !empty($validated['sampai'])) {
            $periodText = 'Sampai ' . Carbon::parse($validated['sampai'])->format('d/m/Y');
        }

        $employeeText = 'Semua pegawai';
        if (!empty($validated['user_id'])) {
            $selectedUser = User::find($validated['user_id']);
            $employeeText = $selectedUser
                ? $selectedUser->name . ' (' . ($selectedUser->jabatan ?? '-') . ')'
                : 'Pegawai terpilih';
        } elseif ($hasPimpinanScope) {
            $employeeText = match ($validated['cakupan'] ?? 'semua') {
                'saya' => $authUser->name . ' (Absensi sendiri)',
                'bawahan' => 'Semua bawahan terkait',
                default => 'Pimpinan dan bawahan terkait',
            };
        } elseif (!$isAdmin && !$isPimpinan) {
            $employeeText = $authUser->name . ' (Absensi sendiri)';
        }

        $signatureUser = !empty($validated['user_id'])
            ? User::find($validated['user_id'])
            : null;
        if (!$signatureUser && $records->pluck('user_id')->unique()->count() === 1) {
            $signatureUser = $records->first()?->user;
        }
        if (!$signatureUser && (!$isAdmin && !$isPimpinan || ($validated['cakupan'] ?? null) === 'saya')) {
            $signatureUser = $authUser;
        }

        $logoPath = public_path('assets/img/logouis.png');
        $logoData = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $pdf = Pdf::loadView('pages.absensi-kinerja.pdf', [
            'rows' => $rows,
            'periodText' => $periodText,
            'employeeText' => $employeeText,
            'signatureEmployeeName' => $signatureUser?->name ?? 'Pegawai Yang Bersangkutan',
            'signatureEmployeeLabel' => $signatureUser?->jabatan ?: 'Pegawai Yang Bersangkutan',
            'signatureEmployeeNup' => $signatureUser?->nidn ?: '-',
            'searchText' => $search,
            'generatedAt' => Carbon::now()->translatedFormat('d/m/Y H:i') . ' WIB',
            'logoData' => $logoData,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('absensi-kinerja-' . now()->format('Ymd-His') . '.pdf');
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'cakupan' => ['nullable', 'in:semua,saya,bawahan'],
            'periode_akademik_id' => ['nullable', 'integer', 'exists:periode_akademiks,id'],
            'search' => ['nullable', 'string', 'max:200'],
        ]);

        $authUser = Auth::user();
        $isAdmin = $authUser->isAdmin() || $authUser->isSuperAdmin();
        $isPimpinan = $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit();
        $hasPimpinanScope = $isPimpinan && !$authUser->isSuperAdmin();

        $query = AbsensiKinerjaHarian::with('user');
        if (!$isAdmin && !$isPimpinan) {
            $query->where('user_id', $authUser->id);
        } elseif ($hasPimpinanScope) {
            $scope = $validated['cakupan'] ?? 'semua';
            $userIds = $this->getPimpinanUserIds($authUser);
            if ($scope === 'saya') {
                $userIds = [$authUser->id];
            } elseif ($scope === 'bawahan') {
                $userIds = array_values(array_diff($userIds, [$authUser->id]));
            }
            $query->whereIn('user_id', $userIds);
        }

        if (!empty($validated['dari'])) {
            $query->whereDate('tanggal', '>=', $validated['dari']);
        }
        if (!empty($validated['sampai'])) {
            $query->whereDate('tanggal', '<=', $validated['sampai']);
        }
        if (!empty($validated['user_id']) && ($isAdmin || $isPimpinan)) {
            $query->where('user_id', $validated['user_id']);
        }

        $search = trim($validated['search'] ?? '');
        if ($search !== '') {
            $query->where(function ($records) use ($search) {
                $records->whereHas('user', function ($users) use ($search) {
                    $users->where('name', 'like', "%{$search}%")
                        ->orWhere('jabatan', 'like', "%{$search}%");
                })->orWhere('tanggal', 'like', "%{$search}%")
                    ->orWhere('status_datang', 'like', "%{$search}%")
                    ->orWhere('status_pulang', 'like', "%{$search}%")
                    ->orWhere('keterangan_kerja', 'like', "%{$search}%");
            });
        }

        $records = $query->orderByDesc('tanggal')->orderByDesc('updated_at')->get();
        $selectedPeriod = !empty($validated['periode_akademik_id'])
            ? PeriodeAkademik::find($validated['periode_akademik_id'])
            : null;
        $records = $this->filterRecordsByAcademicPeriod($records, $selectedPeriod?->id);
        $periodText = $selectedPeriod?->nama_periode ?? 'SEMUA TANGGAL';
        if (!$selectedPeriod && !empty($validated['dari']) && !empty($validated['sampai'])) {
            $periodText = Carbon::parse($validated['dari'])->format('d/m/Y')
                . ' s/d ' . Carbon::parse($validated['sampai'])->format('d/m/Y');
        } elseif (!$selectedPeriod && !empty($validated['dari'])) {
            $periodText = 'MULAI ' . Carbon::parse($validated['dari'])->format('d/m/Y');
        } elseif (!$selectedPeriod && !empty($validated['sampai'])) {
            $periodText = 'SAMPAI ' . Carbon::parse($validated['sampai'])->format('d/m/Y');
        }

        $employeeText = 'SEMUA PEGAWAI';
        if (!empty($validated['user_id'])) {
            $selectedUser = User::find($validated['user_id']);
            $employeeText = $selectedUser
                ? strtoupper($selectedUser->name . ' (' . ($selectedUser->jabatan ?? '-') . ')')
                : 'PEGAWAI TERPILIH';
        } elseif ($hasPimpinanScope) {
            $employeeText = match ($validated['cakupan'] ?? 'semua') {
                'saya' => strtoupper($authUser->name . ' (ABSENSI SENDIRI)'),
                'bawahan' => 'SEMUA BAWAHAN TERKAIT',
                default => 'PIMPINAN DAN BAWAHAN TERKAIT',
            };
        } elseif (!$isAdmin && !$isPimpinan) {
            $employeeText = strtoupper($authUser->name . ' (ABSENSI SENDIRI)');
        }

        $taskIds = $records
            ->flatMap(fn (AbsensiKinerjaHarian $record) => collect($record->keterangan_kerja ?? [])->pluck('task_id'))
            ->filter()
            ->unique();
        $taskTimers = $taskIds->isEmpty()
            ? collect()
            : Kegiatan::query()
                ->whereIn('id', $taskIds)
                ->get(['id', 'durasi_detik', 'last_started_at', 'status', 'tanggal_mulai', 'waktu_mulai', 'tanggal_selesai', 'waktu_selesai'])
                ->keyBy('id');
        $now = Carbon::now();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Absensi Kinerja');
        foreach (['A1:B1', 'C1:F1', 'G1:H1', 'I1:M1', 'A2:B2', 'C2:F2', 'G2:H2', 'I2:M2'] as $range) {
            $sheet->mergeCells($range);
        }
        $sheet->setCellValue('A1', 'PERIODE / WAKTU :');
        $sheet->setCellValue('C1', $periodText);
        $sheet->setCellValue('G1', 'STATUS :');
        $sheet->setCellValue('I1', 'SEMUA STATUS');
        $sheet->setCellValue('A2', 'KATEGORI :');
        $sheet->setCellValue('C2', 'SEMUA KEGIATAN');
        $sheet->setCellValue('G2', 'PEGAWAI :');
        $sheet->setCellValue('I2', $employeeText);
        $sheet->getStyle('A1:M2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '15432D'], 'name' => 'Arial', 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '86EFAC']],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(30);
        $sheet->getRowDimension(3)->setRowHeight(8);

        $headers = ['Tanggal', 'Nama Pegawai', 'Jabatan', 'Status Datang', 'Waktu Datang', 'Status Pulang', 'Waktu Pulang', 'Total Durasi Kerja', 'Target Kerja', 'Progres', 'Status Kinerja', 'Kategori', 'Keterangan Kerja'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '4', $header);
        }

        $row = 5;
        foreach ($records as $record) {
            $activities = [];
            $categories = [];
            $durations = $this->resolveActivityDurations(
                $record->keterangan_kerja ?? [],
                $taskTimers,
                $record->tanggal,
                $now
            );
            $totalSeconds = array_sum(array_column($durations, 'seconds'));
            foreach ($durations as $index => $activityData) {
                $item = $activityData['entry'];
                $task = $activityData['task'];
                $categories[] = ($index + 1) . '. ' . $this->categoryLabel($item['jenis'] ?? $task?->kategori);
                $activity = ($index + 1) . '. ' . ($item['uraian'] ?? '-')
                    . ' [' . $this->formatDuration($activityData['seconds']) . ']';
                if (!empty($item['status'])) {
                    $activity .= ' - ' . $item['status'];
                }
                $activities[] = $activity;
            }

            $assessment = $this->assessWorkDuration($totalSeconds);
            $values = [
                $record->tanggal?->format('d-m-Y') ?? '',
                $record->user?->name ?? '-',
                $record->user?->jabatan ?? '-',
                $record->status_datang ?? ($record->waktu_datang ? 'Hadir' : 'Belum'),
                $record->waktu_datang ? substr($record->waktu_datang, 0, 5) : '',
                $record->status_pulang ?? ($record->waktu_pulang ? 'Hadir' : 'Belum'),
                $record->waktu_pulang ? substr($record->waktu_pulang, 0, 5) : '',
                $this->formatDuration($totalSeconds),
                '7 jam kerja + 1 jam istirahat (total 8 jam)',
                $assessment['progress'] . '%',
                $assessment['label'] . ($assessment['detail'] !== '' ? ' - ' . $assessment['detail'] : ''),
                implode("\n", $categories),
                implode("\n", $activities),
            ];
            foreach ($values as $index => $value) {
                $coordinate = Coordinate::stringFromColumnIndex($index + 1) . $row;
                $sheet->setCellValueExplicit($coordinate, (string) $value, DataType::TYPE_STRING);
            }
            $row++;
        }

        $lastRow = max(4, $row - 1);
        $sheet->getStyle('A4:M4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        if ($lastRow >= 2) {
            $sheet->getStyle('A5:M' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle('K5:M' . $lastRow)->getAlignment()->setWrapText(true);
        }
        $sheet->freezePane('A5');
        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->getColumnDimension('K')->setWidth(24);
        $sheet->getColumnDimension('L')->setWidth(24);
        $sheet->getColumnDimension('M')->setWidth(70);
        $sheet->setAutoFilter('A4:M' . $lastRow);

        $filename = 'absensi-kinerja-' . now()->format('Ymd-His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer, $spreadsheet): void {
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        \App\Helpers\AbsensiKinerjaHelper::backfillMissingCheckins();
        AbsensiKinerjaHarian::closeCompletedWorkdays();

        $authUser = Auth::user();
        $isAdmin  = $authUser && ($authUser->isAdmin() || $authUser->isSuperAdmin());
        $isPimpinan = $authUser && ($authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit());
        $hasPimpinanScope = $isPimpinan && !$authUser->isSuperAdmin();

        if ($request->ajax()) {
            $query = AbsensiKinerjaHarian::with('user')
                ->orderByDesc('tanggal')
                ->orderByDesc('updated_at');

            // Filter berdasarkan hak akses
            if (!$isAdmin && !$isPimpinan) {
                // Staff regular: hanya milik sendiri
                $query->where('user_id', $authUser->id);
            } elseif ($hasPimpinanScope) {
                $scope = $request->input('cakupan', 'semua');
                $userIds = $this->getPimpinanUserIds($authUser);
                if ($scope === 'saya') {
                    $userIds = [$authUser->id];
                } elseif ($scope === 'bawahan') {
                    $userIds = array_values(array_diff($userIds, [$authUser->id]));
                }
                $query->whereIn('user_id', $userIds);
            }

            // Filter tanggal
            if ($request->filled('dari')) {
                $query->where('tanggal', '>=', $request->dari);
            }
            if ($request->filled('sampai')) {
                $query->where('tanggal', '<=', $request->sampai);
            }
            // Filter pegawai (admin/pimpinan only)
            if ($request->filled('user_id') && ($isAdmin || $isPimpinan)) {
                $query->where('user_id', $request->user_id);
            }

            $taskIds = (clone $query)
                ->pluck('keterangan_kerja')
                ->flatMap(function ($entries): array {
                    if (is_string($entries)) {
                        $entries = json_decode($entries, true) ?? [];
                    }

                    return collect($entries ?? [])->pluck('task_id')->all();
                })
                ->filter()
                ->unique()
                ->values();
            $taskTimers = $taskIds->isEmpty()
                ? collect()
                : Kegiatan::query()
                    ->whereIn('id', $taskIds)
                    ->get([
                        'id',
                        'durasi_detik',
                        'last_started_at',
                        'status',
                        'tanggal_mulai',
                        'waktu_mulai',
                        'tanggal_selesai',
                        'waktu_selesai',
                    ])
                    ->keyBy('id');
            $now = Carbon::now();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nama_pegawai', fn($r) => $r->user?->name ?? '-')
                ->addColumn('jabatan', fn($r) => $r->user?->jabatan ?? '-')
                ->addColumn('tanggal_fmt', fn($r) => $r->tanggal ? Carbon::parse($r->tanggal)->translatedFormat('d F Y') : '-')
                ->addColumn('datang_col', function ($r) {
                    if ($r->waktu_datang) {
                        $badge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:0.75rem;">Hadir</span>';
                        return $badge . '<br><small class="text-dark fw-bold text-nowrap">'
                            . substr($r->waktu_datang, 0, 5) . '&nbsp;WIB</small>';
                    }
                    return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size:0.75rem;">Belum</span>';
                })
                ->addColumn('pulang_col', function ($r) {
                    if ($r->waktu_pulang) {
                        $badge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size:0.75rem;">Hadir</span>';
                        return $badge . '<br><small class="text-dark fw-bold text-nowrap">'
                            . substr($r->waktu_pulang, 0, 5) . '&nbsp;WIB</small>';
                    }
                    return '<span class="badge bg-light text-muted border px-2 py-1" style="font-size:0.75rem;">Belum</span>';
                })
                ->addColumn('total_durasi_col', function ($r) use ($taskTimers, $now) {
                    $activities = $this->resolveActivityDurations(
                        $r->keterangan_kerja ?? [],
                        $taskTimers,
                        $r->tanggal,
                        $now
                    );
                    $totalSeconds = array_sum(array_column($activities, 'seconds'));
                    $assessment = $this->assessWorkDuration($totalSeconds);
                    $progressWidth = min(100, $assessment['progress']);
                    $progressColor = match ($assessment['label']) {
                        'Bagus' => 'bg-success',
                        'Over' => 'bg-danger',
                        default => 'bg-warning',
                    };
                    $statusBadge = match ($assessment['label']) {
                        'Bagus' => 'bg-success-subtle text-success',
                        'Over' => 'bg-danger-subtle text-danger',
                        default => 'bg-warning-subtle text-warning',
                    };
                    return '<div class="fw-semibold">' . $this->formatDuration($totalSeconds) . '</div>'
                        . '<div class="progress mt-1" style="height:5px;">'
                        . '<div class="progress-bar ' . $progressColor . '" style="width:' . $progressWidth . '%"></div></div>'
                        . '<small class="text-muted d-block mt-1">' . $assessment['progress']
                        . '% dari target 7 jam kerja</small>'
                        . '<div class="border-top mt-2 pt-2">'
                        . '<small class="text-muted d-block">Status kinerja</small>'
                        . '<span class="badge ' . $statusBadge . ' mt-1">' . e($assessment['label']) . '</span>'
                        . '</div>'
                        . '<div class="border-top mt-2 pt-2">'
                        . '<small class="text-muted d-block">Evaluasi</small>'
                        . '<small class="text-dark d-block mt-1">' . e($assessment['detail']) . '</small>'
                        . '</div>';
                })
                ->addColumn('kategori_col', function ($r) use ($taskTimers, $now) {
                    $activities = $this->resolveActivityDurations(
                        $r->keterangan_kerja ?? [],
                        $taskTimers,
                        $r->tanggal,
                        $now
                    );

                    if (empty($activities)) {
                        return '<span class="text-muted">-</span>';
                    }

                    $categories = [];
                    foreach ($activities as $activityData) {
                        $category = e($this->categoryLabel(
                            $activityData['entry']['jenis'] ?? $activityData['task']?->kategori
                        ));
                        $categories[] = '<div class="mb-1" style="font-size:0.8rem;">'
                            . '<div><span class="badge bg-info-subtle text-info border">' . $category . '</span></div>'
                            . '<small class="d-block">&nbsp;</small></div>';
                    }

                    return implode('', $categories);
                })
                ->addColumn('keterangan_col', function ($r) use ($taskTimers, $now) {
                    $list = $r->keterangan_kerja ?? [];
                    if (empty($list)) return '<span class="text-muted fst-italic">-</span>';
                    $items = [];
                    $activities = $this->resolveActivityDurations($list, $taskTimers, $r->tanggal, $now);
                    foreach ($activities as $i => $activityData) {
                        $item = $activityData['entry'];
                        $uraian = htmlspecialchars($item['uraian'] ?? '-', ENT_QUOTES);
                        $status = $item['status'] ?? null;
                        $durasiKerja = $this->formatDuration($activityData['seconds']);
                        $pill   = '';
                        if ($status === 'Selesai') $pill = ' <span class="badge bg-success-subtle text-success" style="font-size:0.63rem;">✓</span>';
                        elseif (in_array($status, ['Berjalan', 'Proses'])) $pill = ' <span class="badge bg-warning-subtle text-warning" style="font-size:0.63rem;">▶</span>';
                        elseif ($status === 'Di-pause') $pill = ' <span class="badge bg-secondary-subtle text-secondary" style="font-size:0.63rem;">⏸</span>';
                        $items[] = '<div class="mb-1" style="font-size:0.8rem;">'
                            . '<div>' . ($i + 1) . '. ' . $uraian . $pill . '</div>'
                            . '<small class="text-muted ms-3">Durasi kegiatan: '
                            . $durasiKerja . '</small></div>';
                    }
                    return implode('', $items);
                })
                ->rawColumns(['datang_col', 'pulang_col', 'total_durasi_col', 'kategori_col', 'keterangan_col'])
                ->make(true);
        }

        // Untuk view: daftar user (admin/pimpinan bisa filter, staff tidak)
        $users = collect();
        $periods = PeriodeAkademik::orderByDesc('nama_periode')->get(['id', 'nama_periode']);
        if ($isAdmin && !$hasPimpinanScope) {
            $users = User::whereNotNull('name')->orderBy('name')->get(['id', 'name', 'jabatan', 'nidn', 'unit']);
        } elseif ($hasPimpinanScope) {
            $users = User::whereIn('id', $this->getPimpinanUserIds($authUser))
                ->orderBy('name')
                ->get(['id', 'name', 'jabatan', 'nidn', 'unit']);
        } else {
            $users = User::where('id', $authUser->id)->get(['id', 'name', 'jabatan', 'nidn', 'unit']);
        }

        // ==========================================
        // DATA MATRIX MONITORING HARIAN (TANGGAL 01 - AKHIR BULAN)
        // ==========================================
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        if ($bulan < 1 || $bulan > 12) $bulan = now()->month;
        if ($tahun < 2020 || $tahun > 2035) $tahun = now()->year;

        $namaBulanIndonesia = $this->getNamaBulan($bulan);
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        $matrixDays = [];
        $totalWorkdays = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cDate = Carbon::createFromDate($tahun, $bulan, $d);
            $shortDay = match($cDate->dayOfWeek) {
                0 => 'Minggu',
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
            };
            $isSunday = $cDate->isSunday();
            if (!$isSunday) {
                $totalWorkdays++;
            }
            $matrixDays[] = [
                'day' => $d,
                'day_padded' => str_pad($d, 2, '0', STR_PAD_LEFT),
                'date' => $cDate->toDateString(),
                'day_name' => $shortDay,
                'is_sunday' => $isSunday,
                'is_weekend' => $cDate->isWeekend(),
            ];
        }

        // Tentukan daftar user untuk Matrix
        $matrixUsersQuery = User::where('is_active', true);
        if (!$isAdmin && !$isPimpinan) {
            $matrixUsersQuery->where('id', $authUser->id);
        } elseif ($hasPimpinanScope) {
            $matrixUsersQuery->whereIn('id', $this->getPimpinanUserIds($authUser));
        }

        if ($request->filled('filter_user_matrix') && ($isAdmin || $isPimpinan)) {
            $matrixUsersQuery->where('id', $request->filter_user_matrix);
        }

        $matrixUsers = $matrixUsersQuery->orderBy('name', 'asc')->get();
        $matrixUserIds = $matrixUsers->pluck('id')->all();

        $startOfMonth = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        // 1. Ambil data AbsensiKinerjaHarian bulan ini
        $attendanceRecords = AbsensiKinerjaHarian::whereIn('user_id', $matrixUserIds)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy(function ($rec) {
                return $rec->user_id . '_' . $rec->tanggal->format('Y-m-d');
            });

        // 2. Ambil data Kegiatan yang dimulai pada bulan ini (Rutinitas, Kepanitiaan, Insidentil)
        // Acuan hadir: jika 1 kegiatan dimulai maka otomatis hadir
        $kegiatanRecords = Kegiatan::where(function ($q) use ($matrixUserIds) {
                $q->whereIn('user_id', $matrixUserIds)
                  ->orWhereHas('taggedUsers', fn($qu) => $qu->whereIn('users.id', $matrixUserIds));
            })
            ->whereIn('kategori', ['Rencana Kerja', 'Kepanitiaan', 'Insidentil'])
            ->whereIn('status', ['Berjalan', 'Proses', 'Di-pause', 'Selesai'])
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('last_started_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])
                  ->orWhere(function ($sub) use ($startOfMonth, $endOfMonth) {
                      $sub->whereNull('tanggal_mulai')
                          ->whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59']);
                  });
            })
            ->with(['taggedUsers:id,name'])
            ->get();

        $startedActivitiesMap = [];
        foreach ($kegiatanRecords as $task) {
            $tgl = $task->tanggal_mulai 
                ?: ($task->last_started_at ? $task->last_started_at->toDateString() : ($task->created_at ? $task->created_at->toDateString() : null));
            if (!$tgl) continue;

            $startedActivitiesMap[$task->user_id . '_' . $tgl][] = $task->uraian_tugas;
            foreach ($task->taggedUsers as $tu) {
                $startedActivitiesMap[$tu->id . '_' . $tgl][] = $task->uraian_tugas;
            }
        }

        // 3. Susun data matrix per user
        $matrixData = [];
        foreach ($matrixUsers as $u) {
            $userDays = [];
            $countHadir = 0;
            $countSakit = 0;
            $countIzin = 0;
            $countAlfa = 0;

            foreach ($matrixDays as $dayInfo) {
                $dDate = $dayInfo['date'];
                $key = $u->id . '_' . $dDate;

                $att = $attendanceRecords[$key][0] ?? null;
                $hasStarted = !empty($startedActivitiesMap[$key]);

                $status = null;
                $tooltip = '';

                if ($att) {
                    $rawStatus = strtoupper(trim($att->status_datang ?? ''));
                    if (str_starts_with($rawStatus, 'SAK') || $rawStatus === 'S') {
                        $status = 'S';
                        $tooltip = 'Sakit' . ($att->catatan ? ': ' . $att->catatan : '');
                    } elseif (str_starts_with($rawStatus, 'IZ') || $rawStatus === 'I') {
                        $status = 'I';
                        $tooltip = 'Izin' . ($att->catatan ? ': ' . $att->catatan : '');
                    } elseif (str_starts_with($rawStatus, 'ALF') || str_starts_with($rawStatus, 'ALP') || $rawStatus === 'A') {
                        $status = 'A';
                        $tooltip = 'Alfa' . ($att->catatan ? ': ' . $att->catatan : '');
                    } elseif (str_starts_with($rawStatus, 'HAD') || $rawStatus === 'H' || !empty($att->waktu_datang) || !empty($att->keterangan_kerja)) {
                        $status = 'H';
                        $tooltip = 'Hadir (' . ($att->waktu_datang ? substr($att->waktu_datang, 0, 5) . ' WIB' : 'Otomatis Kinerja') . ')';
                    }
                }

                // Jika belum ada status manual dan user memulai kegiatan pada hari tersebut -> Otomatis Hadir
                if (!$status && $hasStarted) {
                    $status = 'H';
                    $firstTask = $startedActivitiesMap[$key][0] ?? 'Kegiatan dimulai';
                    $tooltip = 'Hadir Otomatis: ' . $firstTask;
                }

                if ($status === 'H') $countHadir++;
                elseif ($status === 'S') $countSakit++;
                elseif ($status === 'I') $countIzin++;
                elseif ($status === 'A') $countAlfa++;

                $userDays[$dayInfo['day']] = [
                    'status' => $status,
                    'tooltip' => $tooltip,
                    'is_sunday' => $dayInfo['is_sunday'],
                ];
            }

            // Hitung persentase kehadiran
            $persentase = $totalWorkdays > 0 ? round(($countHadir / $totalWorkdays) * 100) : 0;

            $matrixData[] = [
                'user' => $u,
                'nik' => $u->nidn ?: '-',
                'days' => $userDays,
                'hadir' => $countHadir,
                'sakit' => $countSakit,
                'izin' => $countIzin,
                'alfa' => $countAlfa,
                'persentase' => $persentase,
            ];
        }

        return view('pages.absensi-kinerja.index', compact(
            'users',
            'periods',
            'isAdmin',
            'isPimpinan',
            'hasPimpinanScope',
            'matrixDays',
            'matrixData',
            'bulan',
            'tahun',
            'namaBulanIndonesia',
            'totalWorkdays'
        ));
    }

    private function filterRecordsByAcademicPeriod($records, ?int $periodId)
    {
        if (!$periodId) {
            return $records;
        }

        $taskIds = $records->flatMap(fn (AbsensiKinerjaHarian $record) => $this->attendanceEntries($record))
            ->pluck('task_id')
            ->filter()
            ->unique()
            ->values();

        if ($taskIds->isEmpty()) {
            return collect();
        }

        $periodTaskIds = Kegiatan::query()
            ->whereIn('id', $taskIds)
            ->where('periode_akademik_id', $periodId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        if ($periodTaskIds->isEmpty()) {
            return collect();
        }

        return $records->map(function (AbsensiKinerjaHarian $record) use ($periodTaskIds) {
            $entries = $this->attendanceEntries($record);
            $matchingEntries = array_values(array_filter(
                $entries,
                fn (array $entry) => isset($entry['task_id']) && $periodTaskIds->has((int) $entry['task_id'])
            ));

            if (empty($matchingEntries)) {
                return null;
            }

            $record->setAttribute('keterangan_kerja', $matchingEntries);
            return $record;
        })->filter()->values();
    }

    private function attendanceEntries(AbsensiKinerjaHarian $record): array
    {
        $entries = $record->keterangan_kerja ?? [];
        if (is_string($entries)) {
            $entries = json_decode($entries, true) ?? [];
        }

        return is_array($entries) ? $entries : [];
    }

    /**
     * Resolve the pimpinan's own user ID and active direct/indirect subordinates.
     *
     * @return int[]
     */
    private function getPimpinanUserIds(User $pimpinan): array
    {
        return array_values(array_unique([
            (int) $pimpinan->id,
            ...$pimpinan->getAllSubordinateIds(),
        ]));
    }

    private function resolveTaskDurationSeconds($task, array $entry, Carbon $attendanceDate, ?string $status, Carbon $now): int
    {
        $storedSeconds = max(0, (int) ($task?->durasi_detik ?? $entry['durasi_detik'] ?? 0));
        $lastStartedAt = $task?->last_started_at
            ?? (!empty($entry['last_started_at']) ? Carbon::parse($entry['last_started_at']) : null);
        $isRunning = in_array($status, ['Berjalan', 'Proses'], true);

        if ($storedSeconds > 0) {
            if ($isRunning && $lastStartedAt && $lastStartedAt->lessThanOrEqualTo($now)) {
                $storedSeconds += (int) $lastStartedAt->diffInSeconds($now);
            }

            return $storedSeconds;
        }

        $startTime = $task?->waktu_mulai ?? $entry['mulai'] ?? null;
        if (!$startTime || $startTime === '00:00:00') {
            return 0;
        }

        $startDate = $task?->tanggal_mulai ?: $attendanceDate->toDateString();
        $startedAt = Carbon::parse($startDate . ' ' . $startTime, config('app.timezone'));

        if ($isRunning) {
            $endAt = $lastStartedAt && $lastStartedAt->lessThanOrEqualTo($now)
                ? $lastStartedAt
                : $startedAt;
            return $now->greaterThan($endAt)
                ? (int) $endAt->diffInSeconds($now)
                : 0;
        }

        $endTime = $task?->waktu_selesai ?? $entry['selesai'] ?? null;
        if (!$endTime || $endTime === '00:00:00') {
            return 0;
        }

        $endDate = $task?->tanggal_selesai ?: $startDate;
        $finishedAt = Carbon::parse($endDate . ' ' . $endTime, config('app.timezone'));
        if ($finishedAt->lessThan($startedAt)) {
            $finishedAt->addDay();
        }

        return $finishedAt->greaterThan($startedAt)
            ? (int) $startedAt->diffInSeconds($finishedAt)
            : 0;
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv(max(0, $seconds), 60);
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $hours > 0
            ? $hours . 'j ' . $remainingMinutes . 'm'
            : $remainingMinutes . 'm';
    }

    private function resolveActivityDurations(array $entries, $taskTimers, Carbon $attendanceDate, Carbon $now): array
    {
        $activities = [];
        foreach ($entries as $entry) {
            $task = $taskTimers->get((int) ($entry['task_id'] ?? 0));
            $seconds = $this->resolveTaskDurationSeconds(
                $task,
                $entry,
                $attendanceDate,
                $task?->status ?? ($entry['status'] ?? null),
                $now
            );
            $activities[] = [
                'entry' => $entry,
                'task' => $task,
                'seconds' => intdiv(max(0, $seconds), 60) * 60,
            ];
        }

        return $activities;
    }

    private function assessWorkDuration(int $totalSeconds): array
    {
        $targetMinutes = AbsensiKinerjaHarian::STANDARD_WORK_HOURS * 60;
        $totalMinutes = intdiv(max(0, $totalSeconds), 60);
        $difference = $totalMinutes - $targetMinutes;

        if ($difference > 0) {
            return [
                'label' => 'Over',
                'detail' => 'Melebihi target ' . $this->formatDuration($difference * 60)
                    . '. Evaluasi kebutuhan tambahan staf.',
                'progress' => 100,
            ];
        }

        if ($difference === 0) {
            return [
                'label' => 'Bagus',
                'detail' => 'Target 7 jam kerja tercapai.',
                'progress' => 100,
            ];
        }

        return [
            'label' => 'Kurang Bagus',
            'detail' => 'Kurang ' . $this->formatDuration(abs($difference) * 60) . ' dari target 7 jam kerja.',
            'progress' => (int) round(($totalMinutes / $targetMinutes) * 100),
        ];
    }

    private function categoryLabel(?string $category): string
    {
        return match ($category) {
            'rencana_kerja', 'Rencana Kerja' => 'Rencana Kerja',
            'kepanitiaan', 'Kepanitiaan' => 'Kepanitiaan',
            'insidentil', 'Insidentil' => 'Insidentil',
            default => 'Lainnya',
        };
    }

    /**
     * Simpan absensi manual (Sakit, Izin, Alfa, Hadir).
     */
    public function storeManual(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'tanggal_dari' => ['required', 'date'],
            'tanggal_sampai' => ['required', 'date', 'after_or_equal:tanggal_dari'],
            'status' => ['required', 'in:Hadir,Sakit,Izin,Alfa'],
            'waktu_datang' => ['nullable', 'string', 'max:10'],
            'waktu_pulang' => ['nullable', 'string', 'max:10'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'bukti_izin' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $isAdmin = $authUser->isAdmin() || $authUser->isSuperAdmin();
        $isPimpinan = $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit();

        // Cek izin akses: jika staff biasa, hanya boleh menginput untuk dirinya sendiri
        if (!$isAdmin && !$isPimpinan && (int)$validated['user_id'] !== (int)$authUser->id) {
            abort(403, 'Anda hanya dapat mengisi absensi untuk diri sendiri.');
        }

        // Upload bukti jika ada
        $buktiPath = null;
        if ($request->hasFile('bukti_izin')) {
            $buktiPath = $request->file('bukti_izin')->store('bukti_izin', 'public');
        }

        $startDate = Carbon::parse($validated['tanggal_dari']);
        $endDate = Carbon::parse($validated['tanggal_sampai']);

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $tglStr = $current->toDateString();

            $record = AbsensiKinerjaHarian::firstOrNew([
                'user_id' => $validated['user_id'],
                'tanggal' => $tglStr,
            ]);

            $record->status_datang = $validated['status'];
            $record->status_pulang = $validated['status'];

            if ($validated['status'] === 'Hadir') {
                $record->waktu_datang = !empty($validated['waktu_datang']) ? substr($validated['waktu_datang'], 0, 5) : '08:00';
                $record->waktu_pulang = !empty($validated['waktu_pulang']) ? substr($validated['waktu_pulang'], 0, 5) : '16:00';
            } else {
                $record->waktu_datang = null;
                $record->waktu_pulang = null;
            }

            if (!empty($validated['catatan'])) {
                $record->catatan = $validated['catatan'];
            }
            if ($buktiPath) {
                $record->bukti_izin = $buktiPath;
            }

            $record->save();

            $current->addDay();
        }

        return redirect()->back()->with('success', 'Data absensi manual (' . $validated['status'] . ') berhasil disimpan.');
    }

    /**
     * Hapus absensi manual.
     */
    public function destroyManual($id)
    {
        $authUser = Auth::user();
        $record = AbsensiKinerjaHarian::findOrFail($id);

        $isAdmin = $authUser->isAdmin() || $authUser->isSuperAdmin();
        $isPimpinan = $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit();

        if (!$isAdmin && !$isPimpinan && (int)$record->user_id !== (int)$authUser->id) {
            abort(403);
        }

        $record->delete();
        return redirect()->back()->with('success', 'Data absensi manual berhasil dihapus.');
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
}
