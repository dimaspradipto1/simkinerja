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

        static::deleted(function ($model) {
            Kegiatan::where('source_type', get_class($model))
                ->where('source_id', $model->id)
                ->delete();
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

        $kegiatan = Kegiatan::updateOrCreate(
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
                'is_paused' => (bool) ($this->is_paused ?? false),
                'paused_at' => $this->paused_at,
                'total_paused_seconds' => (int) ($this->total_paused_seconds ?? 0),
                'estimasi_unlock_reason' => $this->estimasi_unlock_reason,
                'estimasi_unlock_requested_at' => $this->estimasi_unlock_requested_at,
            ]
        );

        // Sync tagged users if loaded or present
        $taggedUserIds = $this->taggedUsers()->pluck('users.id')->toArray();
        $kegiatan->taggedUsers()->sync($taggedUserIds);

        return $kegiatan;
    }
}
