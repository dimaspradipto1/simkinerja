<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'estimasi_tanggal_selesai_awal',
        'estimasi_jam_selesai_awal',
        'waktu_mulai',
        'waktu_selesai',
        'tanggal_mulai',
        'tanggal_selesai',
        'file',
        'url_external',
        'hasil_kerja',
        'rencana_tindak_lanjut',
        'saran_pimpinan',
        'status',
        'alasan_pause',
        'last_started_at',
        'durasi_detik',
        'is_paused',
        'paused_at',
        'total_paused_seconds',
        'estimasi_unlock_reason',
        'estimasi_unlock_requested_at',
        'estimasi_unlock_approved_at',
        'reopen_reason',
        'reopen_requested_at',
        'reopened_at',
        'reopened_by',
    ];

    protected $casts = [
        'last_started_at' => 'datetime',
        'reopened_at' => 'datetime',
        'reopen_requested_at' => 'datetime',
        'estimasi_unlock_requested_at' => 'datetime',
        'estimasi_unlock_approved_at' => 'datetime',
        'is_paused' => 'boolean',
    ];

    /**
     * Use base Kegiatan morph class for all subclasses (STI)
     */
    public function getMorphClass()
    {
        return Kegiatan::class;
    }

    public static function boot(): void
    {
        parent::boot();

        static::deleting(function ($kegiatan) {
            // 1. Delete uploaded file
            if ($kegiatan->file && Storage::disk('public')->exists($kegiatan->file)) {
                Storage::disk('public')->delete($kegiatan->file);
            }

            // 2. Detach tagged users
            if (method_exists($kegiatan, 'taggedUsers')) {
                $kegiatan->taggedUsers()->detach();
            }

            // 3. Delete polymorphic milestones
            if (method_exists($kegiatan, 'milestones')) {
                $kegiatan->milestones()->delete();
            }

            // 4. Cascade delete Kepanitiaan sessions and attendance records if any
            if (method_exists($kegiatan, 'sesiAbsensis')) {
                foreach ($kegiatan->sesiAbsensis as $sesi) {
                    foreach ($sesi->presensis as $presensi) {
                        if ($presensi->bukti_izin && Storage::disk('public')->exists($presensi->bukti_izin)) {
                            Storage::disk('public')->delete($presensi->bukti_izin);
                        }
                        $presensi->delete();
                    }
                    $sesi->delete();
                }
            }
        });
    }

    /**
     * Backward-compatible no-op for sync methods.
     */
    public function syncToKegiatan(): self
    {
        return $this;
    }

    public function syncToSource(): void
    {
        // No-op: Data is directly stored in the unified kegiatans table
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

    public function sesiAbsensis()
    {
        return $this->hasMany(KepanitiaanSesiAbsensi::class, 'kepanitiaan_id');
    }
}
