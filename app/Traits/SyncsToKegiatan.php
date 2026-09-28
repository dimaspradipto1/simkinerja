<?php

namespace App\Traits;

use App\Models\Kegiatan;

trait SyncsToKegiatan
{
    /**
     * Boot the trait and register model event listeners.
     */
    public static function bootSyncsToKegiatan(): void
    {
        static::saved(function ($model) {
            $model->syncToKegiatan();
        });

        static::deleting(function ($model) {
            // 1. Delete uploaded file
            if ($model->file && \Illuminate\Support\Facades\Storage::disk('public')->exists($model->file)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($model->file);
            }

            // 2. Detach tagged users
            if (method_exists($model, 'taggedUsers')) {
                $model->taggedUsers()->detach();
            }

            // 3. Delete polymorphic milestones
            if (method_exists($model, 'milestones')) {
                $model->milestones()->delete();
            }

            // 4. Cascade delete Kepanitiaan sessions and attendance records
            if (method_exists($model, 'sesiAbsensis')) {
                foreach ($model->sesiAbsensis as $sesi) {
                    foreach ($sesi->presensis as $presensi) {
                        if ($presensi->bukti_izin && \Illuminate\Support\Facades\Storage::disk('public')->exists($presensi->bukti_izin)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($presensi->bukti_izin);
                        }
                        $presensi->delete();
                    }
                    $sesi->delete();
                }
            }
        });

        static::deleted(function ($model) {
            $kegiatans = Kegiatan::where(function ($q) use ($model) {
                $q->where('source_type', get_class($model))
                  ->where('source_id', $model->id);
            })->orWhere(function ($q) use ($model) {
                $q->where('user_id', $model->user_id)
                  ->where('uraian_tugas', $model->uraian_tugas);
            })->get();

            foreach ($kegiatans as $kegiatan) {
                // Clear source_id to prevent loop
                $kegiatan->source_id = null;
                $kegiatan->delete();
            }
        });
    }

    /**
     * Synchronize this task model to the kegiatans table.
     */
    public function syncToKegiatan(): Kegiatan
    {
        $kategori = match (get_class($this)) {
            \App\Models\Kepanitiaan::class => 'Kepanitiaan',
            \App\Models\Insidentil::class => 'Insidentil',
            default => 'Rencana Kerja',
        };

        // Pluck tagged users from source model first
        $taggedUserIds = method_exists($this, 'taggedUsers')
            ? $this->taggedUsers()->pluck('users.id')->toArray()
            : [];

        $kegiatan = Kegiatan::withoutEvents(function () use ($kategori, $taggedUserIds) {
            $k = Kegiatan::updateOrCreate(
                [
                    'source_type' => get_class($this),
                    'source_id' => $this->id,
                ],
                [
                    'user_id' => $this->user_id,
                    'periode_akademik_id' => $this->periode_akademik_id,
                    'kategori' => $kategori,
                    'uraian_tugas' => $this->uraian_tugas,
                    'hari' => $this->hari,
                    'estimasi_jam_mulai' => $this->estimasi_jam_mulai,
                    'estimasi_jam_selesai' => $this->estimasi_jam_selesai,
                    'estimasi_tanggal_mulai' => $this->estimasi_tanggal_mulai,
                    'estimasi_tanggal_selesai' => $this->estimasi_tanggal_selesai,
                    'waktu_mulai' => $this->waktu_mulai,
                    'waktu_selesai' => $this->waktu_selesai,
                    'tanggal_mulai' => $this->tanggal_mulai,
                    'tanggal_selesai' => $this->tanggal_selesai,
                    'file' => $this->file,
                    'url_external' => $this->url_external,
                    'hasil_kerja' => $this->hasil_kerja,
                    'rencana_tindak_lanjut' => $this->rencana_tindak_lanjut,
                    'status' => $this->status ?? 'Belum Dimulai',
                    'alasan_pause' => $this->alasan_pause,
                    'is_paused' => (bool) ($this->is_paused ?? false),
                    'paused_at' => $this->paused_at,
                    'total_paused_seconds' => (int) ($this->total_paused_seconds ?? 0),
                    'estimasi_unlock_reason' => $this->estimasi_unlock_reason,
                    'estimasi_unlock_requested_at' => $this->estimasi_unlock_requested_at,
                ]
            );

            if (method_exists($k, 'taggedUsers')) {
                $k->taggedUsers()->sync($taggedUserIds);
            }

            return $k;
        });

        return $kegiatan;
    }
}
