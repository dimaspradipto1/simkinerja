<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatans';

    protected $fillable = [
        'user_id',
        'periode_akademik_id',
        'kategori',
        'source_type',
        'source_id',
        'uraian_tugas',
        'hari',
        'estimasi_jam_mulai',
        'estimasi_jam_selesai',
        'estimasi_tanggal_mulai',
        'estimasi_tanggal_selesai',
        'waktu_mulai',
        'waktu_selesai',
        'tanggal_mulai',
        'tanggal_selesai',
        'file',
        'url_external',
        'hasil_kerja',
        'rencana_tindak_lanjut',
        'status',
        'is_paused',
        'paused_at',
        'total_paused_seconds',
        'estimasi_unlock_reason',
        'estimasi_unlock_requested_at',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::saved(function ($kegiatan) {
            $kegiatan->syncToSource();
        });

        static::deleting(function ($kegiatan) {
            // 1. Delete uploaded file
            if ($kegiatan->file && \Illuminate\Support\Facades\Storage::disk('public')->exists($kegiatan->file)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($kegiatan->file);
            }

            // 2. Detach tagged users
            if (method_exists($kegiatan, 'taggedUsers')) {
                $kegiatan->taggedUsers()->detach();
            }

            // 3. Delete polymorphic milestones
            if (method_exists($kegiatan, 'milestones')) {
                $kegiatan->milestones()->delete();
            }
        });

        static::deleted(function ($kegiatan) {
            // Find and delete the corresponding source model (RencanaKerja, Kepanitiaan, Insidentil)
            $sourceModel = null;
            if ($kegiatan->source_type && $kegiatan->source_id && class_exists($kegiatan->source_type)) {
                $sourceModel = $kegiatan->source_type::find($kegiatan->source_id);
            }

            // Fallback: If source_id was null or not matched, find by user_id and uraian_tugas
            if (!$sourceModel) {
                $targetClass = match($kegiatan->kategori) {
                    'Kepanitiaan' => \App\Models\Kepanitiaan::class,
                    'Insidentil' => \App\Models\Insidentil::class,
                    default => \App\Models\RencanaKerja::class,
                };
                $sourceModel = $targetClass::where('user_id', $kegiatan->user_id)
                    ->where('uraian_tugas', $kegiatan->uraian_tugas)
                    ->first();
            }

            if ($sourceModel) {
                // Delete source model instance so its own events and cleanups fire
                $sourceModel->delete();
            }
        });
    }

    /**
     * Synchronize updates from Kegiatan back to its source model (RencanaKerja, Kepanitiaan, Insidentil).
     */
    public function syncToSource(): void
    {
        if ($this->source_type && $this->source_id && class_exists($this->source_type)) {
            $sourceClass = $this->source_type;
            $sourceClass::withoutEvents(function () use ($sourceClass) {
                $sourceModel = $sourceClass::find($this->source_id);
                if ($sourceModel) {
                    $sourceModel->update([
                        'user_id' => $this->user_id,
                        'periode_akademik_id' => $this->periode_akademik_id,
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
                        'status' => $this->status,
                        'is_paused' => $this->is_paused,
                        'paused_at' => $this->paused_at,
                        'total_paused_seconds' => $this->total_paused_seconds,
                        'estimasi_unlock_reason' => $this->estimasi_unlock_reason,
                        'estimasi_unlock_requested_at' => $this->estimasi_unlock_requested_at,
                    ]);

                    if (method_exists($sourceModel, 'taggedUsers')) {
                        $taggedUserIds = $this->taggedUsers()->pluck('users.id')->toArray();
                        $sourceModel->taggedUsers()->sync($taggedUserIds);
                    }
                }
            });
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function periodeAkademik()
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_akademik_id');
    }

    public function taggedUsers()
    {
        return $this->belongsToMany(User::class, 'kegiatan_user', 'kegiatan_id', 'user_id')->withTimestamps();
    }

    public function milestones()
    {
        return $this->morphMany(Milestone::class, 'milestonable');
    }
}
