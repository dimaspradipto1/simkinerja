<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KepanitiaanSesiAbsensi extends Model
{
    use HasFactory;

    protected $table = 'kepanitiaan_sesi_absensis';

    protected $fillable = [
        'kepanitiaan_id',
        'nama_sesi',
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
