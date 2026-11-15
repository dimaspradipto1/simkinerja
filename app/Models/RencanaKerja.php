<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class RencanaKerja extends Kegiatan
{
    protected $table = 'kegiatans';

    public function resolveRouteBinding($value, $field = null)
    {
        return static::withoutGlobalScope('kategori')->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }

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
