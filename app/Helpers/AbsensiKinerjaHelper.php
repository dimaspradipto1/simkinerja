<?php

namespace App\Helpers;

use App\Models\AbsensiKinerjaHarian;
use App\Models\Kegiatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * AbsensiKinerjaHelper – Absensi Kinerja Harian (1 Record Per Hari Per User)
 *
 * Konsep yang BENAR:
 *   - 1 absensi kinerja = 1 hari kerja untuk 1 user.
 *   - Kolom keterangan_kerja (JSON) berisi daftar pekerjaan yang dikerjakan hari itu.
 *   - Jam kerja standar: 8 jam / hari ditambah 1 jam istirahat.
 *   - Kehadiran dimulai otomatis saat pemilik memulai tugas.
 *   - Keterangan tugas disinkronkan otomatis ke catatan hari yang sama.
 */
class AbsensiKinerjaHelper
{
    /**
     * Backfill attendance entries from tasks that have already been started.
     */
    public static function syncExistingStartedTasks(): int
    {
        $synced = 0;

        Kegiatan::query()
            ->whereIn('kategori', ['Rencana Kerja', 'Kepanitiaan', 'Insidentil'])
            ->whereIn('status', ['Berjalan', 'Proses', 'Di-pause', 'Selesai'])
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '!=', '00:00:00')
            ->chunkById(100, function ($tasks) use (&$synced): void {
                foreach ($tasks as $task) {
                    static::syncTaskKeterangan($task);
                    $synced++;
                }
            });

        return $synced;
    }

    /**
     * Synchronize a started task into its owner's shared daily attendance record.
     */
    public static function syncTaskKeterangan($task): void
    {
        $jenis = match ($task->kategori ?? null) {
            'Rencana Kerja' => 'rencana_kerja',
            'Kepanitiaan' => 'kepanitiaan',
            'Insidentil' => 'insidentil',
            default => null,
        };

        if (!$jenis || empty($task->id)) {
            return;
        }

        DB::transaction(function () use ($task, $jenis): void {
            static::removeTaskKeterangan((int) $task->id, $jenis, false);

            $status = $task->status ?? null;
            $hasStarted = !empty($task->tanggal_mulai)
                && !empty($task->waktu_mulai)
                && $task->waktu_mulai !== '00:00:00';

            if (!$hasStarted) {
                return;
            }

            $userIds = static::resolveUserIds($task);
            if ($userIds === []) {
                return;
            }

            $entry = [
                'task_id' => (int) $task->id,
                'jenis' => $jenis,
                'uraian' => $task->uraian_tugas ?? '-',
                'mulai' => !empty($task->waktu_mulai) && $task->waktu_mulai !== '00:00:00'
                    ? substr($task->waktu_mulai, 0, 5)
                    : null,
                'selesai' => !empty($task->waktu_selesai) && $task->waktu_selesai !== '00:00:00'
                    ? substr($task->waktu_selesai, 0, 5)
                    : null,
                'status' => $status,
                'durasi_detik' => max(0, (int) ($task->durasi_detik ?? 0)),
                'last_started_at' => $task->last_started_at?->toIso8601String(),
            ];
            $tanggal = static::resolveDate($task);
            foreach ($userIds as $userId) {
                $attendance = AbsensiKinerjaHarian::getOrCreate($userId, $tanggal);
                $attendance->addOrUpdateKeterangan($entry);

                if ((int) $userId === (int) $task->user_id) {
                    $attendance->setWaktuDatang(substr($task->waktu_mulai, 0, 5));
                }
            }
        });
    }

    /**
     * Backfill attendance time for started tasks saved before automatic check-in was enabled.
     */
    public static function backfillMissingCheckins(): void
    {
        $records = AbsensiKinerjaHarian::query()
            ->whereNull('waktu_datang')
            ->whereNotNull('keterangan_kerja')
            ->get();

        $taskIds = $records
            ->flatMap(fn (AbsensiKinerjaHarian $record) => collect($record->keterangan_kerja ?? [])
                ->pluck('task_id'))
            ->filter()
            ->unique()
            ->values();

        if ($taskIds->isEmpty()) {
            return;
        }

        $tasks = Kegiatan::query()
            ->whereIn('id', $taskIds)
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '!=', '00:00:00')
            ->get()
            ->keyBy('id');

        foreach ($records as $record) {
            foreach ($record->keterangan_kerja ?? [] as $entry) {
                $task = $tasks->get($entry['task_id'] ?? null);
                if (
                    !$task
                    || (int) $task->user_id !== (int) $record->user_id
                    || substr((string) $task->tanggal_mulai, 0, 10) !== $record->tanggal->toDateString()
                ) {
                    continue;
                }

                $record->setWaktuDatang(substr($task->waktu_mulai, 0, 5));
                break;
            }
        }
    }

    /**
     * Remove a task's daily description when the task is deleted or no longer active.
     */
    public static function removeTaskKeterangan(int $taskId, ?string $jenis = null, bool $transaction = true): void
    {
        $remove = static function () use ($taskId, $jenis): void {
            $records = AbsensiKinerjaHarian::query()
                ->whereJsonContains('keterangan_kerja', ['task_id' => $taskId])
                ->lockForUpdate()
                ->get();

            foreach ($records as $record) {
                $entries = $record->keterangan_kerja ?? [];
                $remaining = array_values(array_filter($entries, static function ($entry) use ($taskId, $jenis): bool {
                    $matchesTask = (int) ($entry['task_id'] ?? 0) === $taskId;
                    $matchesType = $jenis === null || ($entry['jenis'] ?? null) === $jenis;

                    return !$matchesTask || !$matchesType;
                }));

                if (count($remaining) === count($entries)) {
                    continue;
                }

                $record->keterangan_kerja = $remaining;
                if (
                    $remaining === []
                    && empty($record->status_datang)
                    && empty($record->waktu_datang)
                    && empty($record->status_pulang)
                    && empty($record->waktu_pulang)
                    && empty($record->catatan)
                    && empty($record->bukti_izin)
                ) {
                    $record->delete();
                } else {
                    $record->saveQuietly();
                }
            }
        };

        if ($transaction) {
            DB::transaction($remove);
            return;
        }

        $remove();
    }

    /**
     * Tentukan tanggal absensi dari task.
     */
    protected static function resolveDate($task): string
    {
        if (!empty($task->tanggal_mulai)) return $task->tanggal_mulai;
        if (!empty($task->estimasi_tanggal_mulai)) return $task->estimasi_tanggal_mulai;
        return now()->toDateString();
    }

    /**
     * Kumpulkan semua user_id yang terkait dengan tugas (owner + tagged users).
     */
    protected static function resolveUserIds($task): array
    {
        $userIds = [];

        if (!empty($task->user_id)) {
            $userIds[] = (int) $task->user_id;
        }

        if (method_exists($task, 'taggedUsers')) {
            foreach ($task->taggedUsers()->pluck('users.id') as $id) {
                $userIds[] = (int) $id;
            }
        }

        return array_values(array_unique(array_filter($userIds)));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RENDERING — HTML tabel status kehadiran harian
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Render kartu HTML status absensi kinerja harian untuk ditampilkan
     * di dalam detail baris DataTable rencana kerja.
     *
     * @param  mixed  $task   Model tugas (digunakan untuk resolve tanggal & user)
     * @param  int|null $userId  User ID yang dirender (default: auth user atau task owner)
     * @param  string  $jenis  Jenis tugas
     * @return string  HTML
     */
    public static function renderKartuAbsensiHarian(
        $task,
        ?int $userId = null,
        string $jenis = 'rencana_kerja'
    ): string {
        $date   = static::resolveDate($task);
        $userId = $userId ?? (Auth::id() ?? $task->user_id ?? null);

        if (!$userId) return '';

        $absensi = AbsensiKinerjaHarian::where('user_id', $userId)
            ->where('tanggal', $date)
            ->first();

        $standar = 8;
        $istirahat = 1;
        $durasiTarget = $standar + $istirahat;

        // Status datang
        if ($absensi && $absensi->waktu_datang) {
            $statusDatang = '<span class="badge bg-success text-white px-2 py-1" style="font-size:0.76rem;font-weight:600;">Hadir</span>';
            $waktuDatang  = '<span class="fw-bold text-dark" style="font-size:0.82rem;">'
                . substr($absensi->waktu_datang, 0, 5)
                . ' <small class="text-muted fw-normal">WIB</small></span>';
        } else {
            $statusDatang = '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size:0.72rem;">Belum Mulai</span>';
            $waktuDatang  = '<span class="text-muted">-</span>';
        }

        // Status pulang
        if ($absensi && $absensi->waktu_pulang) {
            $statusPulang = '<span class="badge bg-primary text-white px-2 py-1" style="font-size:0.76rem;font-weight:600;">Hadir</span>';
            $waktuPulang  = '<span class="fw-bold text-dark" style="font-size:0.82rem;">'
                . substr($absensi->waktu_pulang, 0, 5)
                . ' <small class="text-muted fw-normal">WIB</small></span>';
        } elseif ($absensi && $absensi->waktu_datang) {
            $statusPulang = '<span class="badge bg-light text-muted border px-2 py-1" style="font-size:0.72rem;">Belum Pulang</span>';
            $waktuPulang  = '<span class="text-muted">-</span>';
        } else {
            $statusPulang = '<span class="text-muted">-</span>';
            $waktuPulang  = '<span class="text-muted">-</span>';
        }

        // Keterangan kerja (daftar tugas hari ini)
        $keteranganHtml = '<span class="text-muted fst-italic" style="font-size:0.78rem;">Belum ada keterangan</span>';
        if ($absensi) {
            $list = $absensi->keterangan_kerja ?? [];
            if (!empty($list)) {
                $items = [];
                foreach ($list as $i => $item) {
                    $uraian = htmlspecialchars($item['uraian'] ?? '-', ENT_QUOTES);
                    $status = $item['status'] ?? null;
                    $mulai  = $item['mulai'] ?? null;
                    $selesai = $item['selesai'] ?? null;
                    $statusBadge = '';
                    if ($status === 'Selesai') {
                        $statusBadge = ' <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.65rem;">✓ Selesai</span>';
                    } elseif (in_array($status, ['Berjalan', 'Proses'])) {
                        $statusBadge = ' <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:0.65rem;">▶ Berjalan</span>';
                    }
                    $waktuStr = '';
                    if ($mulai && $selesai) {
                        $waktuStr = ' <span class="text-muted" style="font-size:0.72rem;">(' . $mulai . '–' . $selesai . ')</span>';
                    } elseif ($mulai) {
                        $waktuStr = ' <span class="text-muted" style="font-size:0.72rem;">(mulai ' . $mulai . ')</span>';
                    }
                    $items[] = '<div class="d-flex align-items-start gap-1" style="font-size:0.8rem;line-height:1.4;">'
                        . '<span class="text-muted fw-bold flex-shrink-0">' . ($i+1) . '.</span>'
                        . '<span class="text-dark">' . $uraian . $waktuStr . $statusBadge . '</span>'
                        . '</div>';
                }
                $keteranganHtml = '<div class="d-flex flex-column gap-1">' . implode('', $items) . '</div>';
            }
        }

        // Durasi aktual
        $durasiHtml = '<span class="text-muted" style="font-size:0.78rem;">-</span>';
        if ($absensi && $absensi->waktu_datang) {
            $menit   = $absensi->durasi_aktual_menit;
            $jam     = floor($menit / 60);
            $sisaMen = $menit % 60;
            $durasiStr = ($jam > 0 ? $jam . ' j ' : '') . $sisaMen . ' m';
            $persen  = round(min(100, ($menit / ($durasiTarget * 60)) * 100));
            $durasiHtml = '<span class="text-dark fw-bold" style="font-size:0.82rem;">' . $durasiStr
                . '</span> <small class="text-muted">/ ' . $standar . ' j kerja + '
                . $istirahat . ' j istirahat</small>'
                . '<div class="progress mt-1" style="height:5px;border-radius:3px;">'
                . '<div class="progress-bar bg-success" style="width:' . $persen . '%"></div></div>';
        }

        $tanggalFormatted = $absensi
            ? $absensi->tanggal->format('d/m/Y')
            : date('d/m/Y', strtotime($date));

        $thStyle = 'background:#f1f5f9!important;color:#1e293b!important;font-weight:700!important;'
            . 'font-size:0.72rem!important;text-transform:uppercase!important;letter-spacing:0.4px!important;'
            . 'padding:8px 10px!important;border:1px solid #cbd5e1!important;text-align:center!important;'
            . 'vertical-align:middle!important;white-space:nowrap!important;';
        $tdStyle = 'padding:8px!important;border:1px solid #e2e8f0!important;text-align:center!important;'
            . 'vertical-align:middle!important;background:#fff!important;';
        $tdLeftStyle = $tdStyle . 'text-align:left!important;';

        $html  = '<div class="mt-2 p-3 bg-white rounded border shadow-sm d-flex flex-column gap-2" '
               . 'style="border-left:4px solid #198754!important;">';
        $html .= '  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-2 border-bottom">';
        $html .= '    <span class="text-dark small fw-bold d-inline-flex align-items-center gap-1">'
               . '<i class="bi bi-calendar-check-fill text-success fs-6 me-1"></i>'
               . 'Absensi Kinerja Harian &mdash; <span class="text-success">' . $tanggalFormatted . '</span>'
               . '</span>';
        $html .= '    <small class="text-muted" style="font-size:0.72rem;">'
               . '<i class="bi bi-clock me-1"></i>Standar: ' . $standar . ' jam kerja, ' . $istirahat . ' jam istirahat'
               . '</small>';
        $html .= '  </div>';

        // Tabel absensi
        $html .= '  <div class="table-responsive" style="overflow-x:auto!important;">';
        $html .= '    <table style="width:100%!important;min-width:540px!important;border-collapse:collapse!important;">';
        $html .= '      <thead><tr>';
        $html .= '        <th style="' . $thStyle . 'min-width:100px;"><i class="bi bi-box-arrow-in-right text-success me-1"></i>DATANG</th>';
        $html .= '        <th style="' . $thStyle . 'min-width:90px;"><i class="bi bi-clock text-success me-1"></i>WAKTU DATANG</th>';
        $html .= '        <th style="' . $thStyle . 'min-width:100px;"><i class="bi bi-box-arrow-right text-primary me-1"></i>PULANG</th>';
        $html .= '        <th style="' . $thStyle . 'min-width:90px;"><i class="bi bi-clock text-primary me-1"></i>WAKTU PULANG</th>';
        $html .= '        <th style="' . $thStyle . 'min-width:120px;"><i class="bi bi-hourglass-split text-secondary me-1"></i>DURASI AKTUAL</th>';
        $html .= '      </tr></thead>';
        $html .= '      <tbody><tr>';
        $html .= '        <td style="' . $tdStyle . '">' . $statusDatang . '</td>';
        $html .= '        <td style="' . $tdStyle . '">' . $waktuDatang . '</td>';
        $html .= '        <td style="' . $tdStyle . '">' . $statusPulang . '</td>';
        $html .= '        <td style="' . $tdStyle . '">' . $waktuPulang . '</td>';
        $html .= '        <td style="' . $tdStyle . '">' . $durasiHtml . '</td>';
        $html .= '      </tr></tbody>';
        $html .= '    </table>';
        $html .= '  </div>';

        // Keterangan kerja
        $html .= '  <div class="pt-2">';
        $html .= '    <div class="text-secondary small fw-bold mb-1">'
               . '<i class="bi bi-list-task me-1"></i>Keterangan Kinerja Hari Ini:</div>';
        $html .= '    <div class="p-2 rounded bg-light border" style="font-size:0.82rem;">'
               . $keteranganHtml . '</div>';
        $html .= '  </div>';
        $html .= '</div>';

        return $html;
    }
}
