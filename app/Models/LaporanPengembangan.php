<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanPengembangan extends Model
{
    use HasFactory;

    protected $fillable = [
        'periode_akademik_id',
        'tahun_akademik',
        'judul',
        'capaian_program_kerja',
        'data_mahasiswa',
        'data_prodi',
        'data_prestasi',
        'data_sdm',
        'data_penelitian_pkm',
        'data_kerjasama',
        'data_sarpras',
        'kendala_solusi',
        'tindak_lanjut',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data_mahasiswa' => 'array',
        'data_prodi' => 'array',
        'data_prestasi' => 'array',
        'data_sdm' => 'array',
        'data_penelitian_pkm' => 'array',
        'data_kerjasama' => 'array',
        'data_sarpras' => 'array',
    ];

    public function periodeAkademik()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
