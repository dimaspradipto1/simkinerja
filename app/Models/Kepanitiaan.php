<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Kepanitiaan extends Kegiatan
{
    protected $table = 'kegiatans';

    protected static function booted(): void
    {
        static::addGlobalScope('kategori', function (Builder $builder) {
            $builder->where('kegiatans.kategori', 'Kepanitiaan');
        });

        static::creating(function ($model) {
            if (empty($model->kategori)) {
                $model->kategori = 'Kepanitiaan';
            }
        });
    }

    /**
     * Otomatis menghentikan tugas kepanitiaan yang telah melewati estimasi waktu selesai.
     */
    public static function autoStopExpiredTasks(): void
    {
        Kegiatan::autoStopExpiredTasks();
    }
}
