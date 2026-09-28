<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Kepanitiaan extends Kegiatan
{
    protected $table = 'kegiatans';

    protected static function booted(): void
    {
        static::addGlobalScope('kategori', function (Builder $builder) {
            $builder->where('kegiatans.kategori', 'Kepanitiaan');
        });

        static::creating(function ($model) {
            if (empty($model->kategori)) {
                $model->kategori = 'Kepanitiaan';
            }
        });
    }

    /**
     * Otomatis menghentikan tugas kepanitiaan yang telah melewati estimasi waktu selesai
     * dan menandainya sebagai Selesai tanpa memerlukan persetujuan atasan.
     */
    public static function autoStopExpiredTasks(): void
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        // Cari tugas kepanitiaan yang belum selesai namun estimasi waktu telah habis
        $expiredTasks = self::where('status', '!=', 'Selesai')
            ->whereNotNull('estimasi_tanggal_selesai')
            ->where(function ($q) use ($today, $currentTime) {
                $q->where('estimasi_tanggal_selesai', '<', $today)
                  ->orWhere(function ($sub) use ($today, $currentTime) {
                      $sub->where('estimasi_tanggal_selesai', $today)
                          ->whereNotNull('estimasi_jam_selesai')
                          ->where('estimasi_jam_selesai', '<=', $currentTime);
                  });
            })
            ->get();

        foreach ($expiredTasks as $task) {
            $endTime = $task->estimasi_jam_selesai ?: '23:59:59';
            $endDate = $task->estimasi_tanggal_selesai ?: $today;
            $estimasiEnd = Carbon::parse($endDate . ' ' . $endTime);

            // Hitung durasi jika tugas sebelumnya berstatus Berjalan
            $durasiDetik = (int) ($task->durasi_detik ?? 0);
            if ($task->status === 'Berjalan' && $task->last_started_at) {
                $startCarbon = Carbon::parse($task->last_started_at);
                $endCarbon = $now->lt($estimasiEnd) ? $now : $estimasiEnd;
                if ($endCarbon->gt($startCarbon)) {
                    $durasiDetik += (int) abs($endCarbon->diffInSeconds($startCarbon));
                }
            }

            $updateData = [
                'status' => 'Selesai',
                'waktu_selesai' => $task->waktu_selesai ?: $endTime,
                'tanggal_selesai' => $task->tanggal_selesai ?: $endDate,
                'durasi_detik' => $durasiDetik,
                'last_started_at' => null,
                'estimasi_unlock_reason' => null,
                'estimasi_unlock_requested_at' => null,
            ];

            if (empty($task->waktu_mulai) || $task->waktu_mulai === '00:00:00') {
                $updateData['waktu_mulai'] = $task->estimasi_jam_mulai ?: '08:00:00';
                $updateData['tanggal_mulai'] = $task->estimasi_tanggal_mulai ?: $endDate;
            }

            $task->update($updateData);

            // Jika ada milestone yang belum selesai, tandai selesai juga
            if ($task->milestones()->exists()) {
                $task->milestones()->where('status', '!=', 'Selesai')->update([
                    'status' => 'Selesai',
                    'waktu_selesai' => $endTime,
                    'tanggal_selesai' => $endDate,
                ]);
            }
        }
    }
}
