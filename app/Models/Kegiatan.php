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
        'parent_id',
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
        'estimasi_durasi_hari',
        'estimasi_durasi_jam',
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
        'estimasi_durasi_hari' => 'integer',
        'estimasi_durasi_jam' => 'integer',
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

        static::saving(function ($kegiatan) {
            // Auto-calculate estimasi durasi jika tidak diisi tapi rentang tanggal & jam tersedia
            $hasDurasi = (!is_null($kegiatan->estimasi_durasi_hari) && (int)$kegiatan->estimasi_durasi_hari > 0) ||
                         (!is_null($kegiatan->estimasi_durasi_jam) && (int)$kegiatan->estimasi_durasi_jam > 0);

            if (!$hasDurasi && !empty($kegiatan->estimasi_tanggal_mulai) && !empty($kegiatan->estimasi_tanggal_selesai)) {
                try {
                    $jamMulai = $kegiatan->estimasi_jam_mulai;
                    $jamSelesai = $kegiatan->estimasi_jam_selesai;

                    if (empty($jamMulai) && empty($jamSelesai)) {
                        $sDate = \Carbon\Carbon::parse($kegiatan->estimasi_tanggal_mulai)->startOfDay();
                        $eDate = \Carbon\Carbon::parse($kegiatan->estimasi_tanggal_selesai)->startOfDay();
                        if ($eDate->gte($sDate)) {
                            $diffDays = (int) $sDate->diffInDays($eDate);
                            $kegiatan->estimasi_durasi_hari = ($diffDays === 0) ? 1 : $diffDays;
                            $kegiatan->estimasi_durasi_jam = 0;
                        }
                    } else {
                        $sStr = $kegiatan->estimasi_tanggal_mulai . ' ' . ($jamMulai ?: '08:00:00');
                        $eStr = $kegiatan->estimasi_tanggal_selesai . ' ' . ($jamSelesai ?: ($jamMulai ?: '08:00:00'));
                        $start = \Carbon\Carbon::parse($sStr);
                        $end = \Carbon\Carbon::parse($eStr);
                        if ($end->gte($start)) {
                            $totalMinutes = $start->diffInMinutes($end);
                            $hari = intdiv($totalMinutes, 1440);
                            $jam = (int) round(($totalMinutes % 1440) / 60);
                            if ($jam >= 24) {
                                $hari += 1;
                                $jam = 0;
                            }
                            $kegiatan->estimasi_durasi_hari = $hari;
                            $kegiatan->estimasi_durasi_jam = $jam;
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore parsing error
                }
            } elseif ($hasDurasi && !empty($kegiatan->estimasi_tanggal_mulai) && empty($kegiatan->estimasi_tanggal_selesai)) {
                try {
                    $start = \Carbon\Carbon::parse($kegiatan->estimasi_tanggal_mulai . ' ' . ($kegiatan->estimasi_jam_mulai ?: '08:00:00'));
                    $end = (clone $start)->addDays((int)($kegiatan->estimasi_durasi_hari ?? 0))->addHours((int)($kegiatan->estimasi_durasi_jam ?? 0));
                    $kegiatan->estimasi_tanggal_selesai = $end->format('Y-m-d');
                    if (empty($kegiatan->estimasi_jam_selesai)) {
                        $kegiatan->estimasi_jam_selesai = $end->format('H:i:s');
                    }
                } catch (\Throwable $e) {
                    // Ignore
                }
            }
        });

        static::saved(function ($kegiatan) {
            if (in_array($kegiatan->kategori, ['Rencana Kerja', 'Kepanitiaan', 'Insidentil'], true)) {
                \App\Helpers\AbsensiKinerjaHelper::syncTaskKeterangan($kegiatan);
            }
        });

        static::deleting(function ($kegiatan) {
            if (in_array($kegiatan->kategori, ['Rencana Kerja', 'Kepanitiaan', 'Insidentil'], true)) {
                \App\Helpers\AbsensiKinerjaHelper::removeTaskKeterangan(
                    (int) $kegiatan->id,
                    match ($kegiatan->kategori) {
                        'Rencana Kerja' => 'rencana_kerja',
                        'Kepanitiaan' => 'kepanitiaan',
                        'Insidentil' => 'insidentil',
                    }
                );
            }

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

    public function parent()
    {
        return $this->belongsTo(Kegiatan::class, 'parent_id');
    }

    public function subKegiatans()
    {
        return $this->hasMany(Kegiatan::class, 'parent_id')->orderBy('id', 'asc');
    }

    public function children()
    {
        return $this->subKegiatans();
    }

    public function isSubKegiatan(): bool
    {
        return !empty($this->parent_id);
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
