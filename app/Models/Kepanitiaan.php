<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kepanitiaan extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'kepanitiaans';

    protected $fillable = [
        'user_id',
        'periode_akademik_id',
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
        'status',
    ];

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
        return $this->belongsToMany(User::class, 'kepanitiaan_user', 'kepanitiaan_id', 'user_id')->withTimestamps();
    }
}
