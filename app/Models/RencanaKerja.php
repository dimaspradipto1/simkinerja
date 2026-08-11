<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RencanaKerja extends Model
{
    use HasFactory;

    protected $table = 'rencana_kerjas';

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
        'hasil_kerja',
        'rencana_tindak_lanjut',
        'saran_pimpinan',
        'status',
        'estimasi_unlock_reason',
        'estimasi_unlock_requested_at',
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
        return $this->belongsToMany(User::class, 'rencana_kerja_user', 'rencana_kerja_id', 'user_id')->withTimestamps();
    }

    public function milestones()
    {
        return $this->morphMany(Milestone::class, 'milestonable');
    }
}
