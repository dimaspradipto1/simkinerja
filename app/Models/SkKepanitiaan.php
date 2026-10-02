<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkKepanitiaan extends Model
{
    use HasFactory;

    protected $table = 'sk_kepanitiaans';

    protected $fillable = [
        'nama_kepanitiaan',
        'nomor_sk',
        'tahun_akademik',
        'tanggal_sk',
        'file_sk',
        'keterangan',
        'hasil_ekstraksi',
        'total_anggota',
        'total_matched',
        'status',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
            'hasil_ekstraksi' => 'array',
            'total_anggota' => 'integer',
            'total_matched' => 'integer',
        ];
    }

    /**
     * User who uploaded/created this SK
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
