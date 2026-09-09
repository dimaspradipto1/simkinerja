<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KepanitiaanPresensi extends Model
{
    use HasFactory;

    protected $table = 'kepanitiaan_presensis';

    protected $fillable = [
        'kepanitiaan_sesi_absensi_id',
        'user_id',
        'hadir_datang',
        'waktu_datang',
        'catatan_hadir_datang',
        'hadir_pulang',
        'waktu_pulang',
        'catatan_hadir_pulang',
        'bukti_izin',
    ];

    public function sesiAbsensi()
    {
        return $this->belongsTo(KepanitiaanSesiAbsensi::class, 'kepanitiaan_sesi_absensi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getBuktiIzinUrlAttribute()
    {
        return $this->bukti_izin ? asset('storage/' . $this->bukti_izin) : null;
    }
}
