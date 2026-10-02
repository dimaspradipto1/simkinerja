<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKinerjaHarian;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class AbsensiKinerjaController extends Controller
{
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

        if ($request->ajax()) {
            $query = AbsensiKinerjaHarian::with('user')
                ->orderByDesc('tanggal')
                ->orderByDesc('updated_at');

            // Filter berdasarkan hak akses
            if (!$isAdmin && !$isPimpinan) {
                // Staff regular: hanya milik sendiri
                $query->where('user_id', $authUser->id);
            } elseif ($isPimpinan && !$isAdmin) {
                // Pimpinan Unit: unit-nya sendiri
                $query->whereHas('user', function ($q) use ($authUser) {
                    $q->where('unit', $authUser->unit);
                });
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
                        return $badge . '<br><small class="text-dark fw-bold">' . substr($r->waktu_datang, 0, 5) . ' WIB</small>';
                    }
                    return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size:0.75rem;">Belum</span>';
                })
                ->addColumn('pulang_col', function ($r) {
                    if ($r->waktu_pulang) {
                        $badge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size:0.75rem;">Hadir</span>';
                        return $badge . '<br><small class="text-dark fw-bold">' . substr($r->waktu_pulang, 0, 5) . ' WIB</small>';
                    }
                    return '<span class="badge bg-light text-muted border px-2 py-1" style="font-size:0.75rem;">Belum</span>';
                })
                ->addColumn('total_durasi_col', function ($r) use ($taskTimers, $now) {
                    $totalSeconds = 0;
                    foreach ($r->keterangan_kerja ?? [] as $item) {
                        $task = $taskTimers->get((int) ($item['task_id'] ?? 0));
                        $totalSeconds += $this->resolveTaskDurationSeconds(
                            $task,
                            $item,
                            $r->tanggal,
                            $task?->status ?? ($item['status'] ?? null),
                            $now
                        );
                    }

                    $targetSeconds = AbsensiKinerjaHarian::STANDARD_WORK_HOURS * 3600;
                    $progress = min(100, (int) round(($totalSeconds / $targetSeconds) * 100));
                    $progressColor = $progress >= 100 ? 'bg-success' : ($progress >= 75 ? 'bg-warning' : 'bg-danger');

                    return '<strong>' . $this->formatDuration($totalSeconds) . '</strong>'
                        . '<div class="progress mt-1" style="height:5px;">'
                        . '<div class="progress-bar ' . $progressColor . '" style="width:' . $progress . '%"></div></div>'
                        . '<small class="text-muted">' . $progress . '% dari 8 jam kerja'
                        . ' (ditambah 1 jam istirahat)</small>';
                })
                ->addColumn('keterangan_col', function ($r) use ($taskTimers, $now) {
                    $list = $r->keterangan_kerja ?? [];
                    if (empty($list)) return '<span class="text-muted fst-italic">-</span>';
                    $items = [];
                    foreach ($list as $i => $item) {
                        $uraian = htmlspecialchars($item['uraian'] ?? '-', ENT_QUOTES);
                        $status = $item['status'] ?? null;
                        $task = $taskTimers->get((int) ($item['task_id'] ?? 0));
                        $taskStatus = $task?->status ?? $status;
                        $durasiDetik = $this->resolveTaskDurationSeconds(
                            $task,
                            $item,
                            $r->tanggal,
                            $taskStatus,
                            $now
                        );
                        $durasiKerja = $this->formatDuration($durasiDetik);
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
                ->rawColumns(['datang_col', 'pulang_col', 'total_durasi_col', 'keterangan_col'])
                ->make(true);
        }

        // Untuk view: daftar user (admin/pimpinan bisa filter, staff tidak)
        $users = collect();
        if ($isAdmin) {
            $users = User::whereNotNull('name')->orderBy('name')->get(['id', 'name', 'jabatan']);
        } elseif ($isPimpinan) {
            $users = User::where('unit', $authUser->unit)->orderBy('name')->get(['id', 'name', 'jabatan']);
        }

        return view('pages.absensi-kinerja.index', compact('users', 'isAdmin', 'isPimpinan'));
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
}
