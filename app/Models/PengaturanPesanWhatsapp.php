<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PengaturanPesanWhatsapp extends Model
{
    public const STATUSES = ['Belum Dimulai', 'Proses/Berjalan', 'Gagal'];

    protected $table = 'pengaturan_pesan_whatsapp';

    protected $fillable = [
        'kategori',
        'status',
        'isi_pesan',
        'mode_pengiriman',
        'waktu_kirim',
        'tujuan_penerima',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'pengaturan_pesan_whatsapp_penerimas',
            'pengaturan_pesan_whatsapp_id',
            'user_id'
        );
    }
}
