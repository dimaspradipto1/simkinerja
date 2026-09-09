<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lpj extends Model
{
    use HasFactory;

    protected $table = 'lpjs';

    protected $fillable = [
        'user_id',
        'kepanitiaan_id',
        'nama_lpj',
        'nama_kepanitiaan',
        'tanggal_kegiatan',
        'keterangan',
        'file_lpj',
        'link_lpj',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kepanitiaan()
    {
        return $this->belongsTo(Kepanitiaan::class, 'kepanitiaan_id');
    }

    public function getFileUrlAttribute()
    {
        return $this->file_lpj ? asset('storage/' . $this->file_lpj) : null;
    }
}
