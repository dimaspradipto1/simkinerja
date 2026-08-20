<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiMiladKetiga extends Model
{
    use HasFactory;

    protected $table = 'absensi_milad_ketigas';

    protected $fillable = [
        'user_id',
        'hadir_datang',
        'waktu_datang',
        'catatan_hadir_datang',
        'hadir_pulang',
        'waktu_pulang',
        'catatan_hadir_pulang',
        'bukti_izin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
