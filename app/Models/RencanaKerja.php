<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class RencanaKerja extends Kegiatan
{
    protected $table = 'kegiatans';

    protected static function booted(): void
    {
        static::addGlobalScope('kategori', function (Builder $builder) {
            $builder->where('kegiatans.kategori', 'Rencana Kerja');
        });

        static::creating(function ($model) {
            if (empty($model->kategori)) {
                $model->kategori = 'Rencana Kerja';
            }
        });
    }
}
