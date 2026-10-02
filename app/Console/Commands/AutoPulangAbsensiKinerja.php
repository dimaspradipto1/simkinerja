<?php

namespace App\Console\Commands;

use App\Models\AbsensiKinerjaHarian;
use Illuminate\Console\Command;

class AutoPulangAbsensiKinerja extends Command
{
    protected $signature = 'absensi-kinerja:auto-pulang';

    protected $description = 'Set waktu pulang otomatis setelah jam kerja dan istirahat terpenuhi';

    public function handle(): int
    {
        $closed = AbsensiKinerjaHarian::closeCompletedWorkdays();
        $this->info("{$closed} absensi ditutup otomatis.");

        return self::SUCCESS;
    }
}
