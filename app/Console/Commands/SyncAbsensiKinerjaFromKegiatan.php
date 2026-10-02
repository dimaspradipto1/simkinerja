<?php

namespace App\Console\Commands;

use App\Helpers\AbsensiKinerjaHelper;
use Illuminate\Console\Command;

class SyncAbsensiKinerjaFromKegiatan extends Command
{
    protected $signature = 'absensi-kinerja:sync-kegiatan';

    protected $description = 'Sinkronkan tugas berjalan dan selesai ke Absensi Kinerja';

    public function handle(): int
    {
        $synced = AbsensiKinerjaHelper::syncExistingStartedTasks();

        $this->info("{$synced} tugas berjalan/selesai berhasil disinkronkan ke Absensi Kinerja.");

        return self::SUCCESS;
    }
}
