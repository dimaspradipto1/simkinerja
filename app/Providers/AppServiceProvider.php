<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        Paginator::useBootstrapFive();

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'App\Models\Kegiatan'     => \App\Models\Kegiatan::class,
            'App\Models\RencanaKerja' => \App\Models\RencanaKerja::class,
            'App\Models\Kepanitiaan'  => \App\Models\Kepanitiaan::class,
            'App\Models\Insidentil'   => \App\Models\Insidentil::class,
            'kegiatan'                => \App\Models\Kegiatan::class,
            'rencana_kerja'           => \App\Models\RencanaKerja::class,
            'kepanitiaan'             => \App\Models\Kepanitiaan::class,
            'insidentil'              => \App\Models\Insidentil::class,
        ]);
    }
}
