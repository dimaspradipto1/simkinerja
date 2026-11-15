<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Triwulan extends Model
{
    protected $fillable = [
        'periode_akademik_id',
        'nama',
        'label',
        'rentang_bulan',
        'keterangan',
        'bulan_mulai',
        'bulan_selesai',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'bulan_mulai' => 'integer',
        'bulan_selesai' => 'integer',
        'urutan' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Relasi ke Periode Akademik
     */
    public function periodeAkademik()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }

    /**
     * Scope triwulan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Ambil Triwulan aktif saat ini
     */
    public static function getActive($periodeId = null)
    {
        $query = static::where('is_active', true);
        if ($periodeId) {
            $query->where('periode_akademik_id', $periodeId);
        }
        return $query->first() ?? static::first();
    }

    /**
     * Ketentuan standar Triwulan berdasarkan bulan (Sesuai Ketentuan):
     * TW1 (Sep–Nov): Bulan 9, 10, 11
     * TW2 (Des–Feb): Bulan 12, 1, 2
     * TW3 (Mar–Mei): Bulan 3, 4, 5
     * TW4 (Jun–Agu): Bulan 6, 7, 8
     */
    public static function getStandardDefinitions(): array
    {
        return [
            1 => [
                'nama' => 'TW1',
                'label' => 'TW1 (Sep–Nov)',
                'rentang_bulan' => 'Sep–Nov',
                'keterangan' => 'September – November',
                'bulan_mulai' => 9,
                'bulan_selesai' => 11,
                'months' => [9, 10, 11],
                'urutan' => 1,
            ],
            2 => [
                'nama' => 'TW2',
                'label' => 'TW2 (Des–Feb)',
                'rentang_bulan' => 'Des–Feb',
                'keterangan' => 'Desember – Februari',
                'bulan_mulai' => 12,
                'bulan_selesai' => 2,
                'months' => [12, 1, 2],
                'urutan' => 2,
            ],
            3 => [
                'nama' => 'TW3',
                'label' => 'TW3 (Mar–Mei)',
                'rentang_bulan' => 'Mar–Mei',
                'keterangan' => 'Maret – Mei',
                'bulan_mulai' => 3,
                'bulan_selesai' => 5,
                'months' => [3, 4, 5],
                'urutan' => 3,
            ],
            4 => [
                'nama' => 'TW4',
                'label' => 'TW4 (Jun–Agu)',
                'rentang_bulan' => 'Jun–Agu',
                'keterangan' => 'Juni – Agustus',
                'bulan_mulai' => 6,
                'bulan_selesai' => 8,
                'months' => [6, 7, 8],
                'urutan' => 4,
            ],
        ];
    }

    /**
     * Tentukan kode TW berdasarkan bulan (1-12)
     */
    public static function determineTwCodeByMonth(int $month): string
    {
        if (in_array($month, [9, 10, 11])) {
            return 'TW1';
        }
        if (in_array($month, [12, 1, 2])) {
            return 'TW2';
        }
        if (in_array($month, [3, 4, 5])) {
            return 'TW3';
        }
        return 'TW4';
    }
}
