<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class KepanitiaanSesiAbsensi extends Model
{
    use HasFactory;

    protected $table = 'kepanitiaan_sesi_absensis';

    protected $fillable = [
        'kepanitiaan_id',
        'nama_sesi',
        'jenis_absen',
        'tanggal',
        'waktu_mulai_datang',
        'waktu_selesai_datang',
        'waktu_mulai_pulang',
        'waktu_selesai_pulang',
        'is_active',
        'token',
        'legacy_slug',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::random(24);
            }
        });

        static::deleting(function (self $sesi) {
            foreach ($sesi->presensis as $presensi) {
                if ($presensi->bukti_izin && Storage::disk('public')->exists($presensi->bukti_izin)) {
                    Storage::disk('public')->delete($presensi->bukti_izin);
                }
                $presensi->delete();
            }
        });
    }

    public static function deleteWithoutKepanitiaan(): int
    {
        $orphanedSessions = self::whereDoesntHave('kepanitiaan', function ($query) {
            $query->where('kategori', 'Kepanitiaan');
        })->get();

        foreach ($orphanedSessions as $sesi) {
            $sesi->delete();
        }

        return $orphanedSessions->count();
    }

    public function kepanitiaan()
    {
        return $this->belongsTo(Kegiatan::class, 'kepanitiaan_id');
    }

    public function presensis()
    {
        return $this->hasMany(KepanitiaanPresensi::class, 'kepanitiaan_sesi_absensi_id');
    }

    public function presensiUser($userId = null)
    {
        $userId = $userId ?: auth()->id();
        return $this->hasOne(KepanitiaanPresensi::class, 'kepanitiaan_sesi_absensi_id')->where('user_id', $userId);
    }
}
