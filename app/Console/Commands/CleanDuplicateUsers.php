<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CleanDuplicateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:clean-duplicates {--dry-run : Only show what would be done without deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean duplicate users and retain only active user with attendance records';

    /**
     * Attendance tables to check for user_id.
     */
    protected array $attendanceTables = [
        'absensi_pkkmb_pertamas',
        'absensi_pkkmb_keduas',
        'absensi_pkkmb_ketigas',
        'absensi_esq_pertamas',
        'absensi_esq_keduas',
        'absensi_esq_ketigas',
        'absensi_esq_keempats',
        'absensi_milad_pertamas',
        'absensi_milad_keduas',
        'absensi_milad_ketigas',
        'absensi_kuliah_umum_pertamas',
        'absensi_kuliah_umum_keduas',
        'absensi_kuliah_umum_ketigas',
    ];

    /**
     * Other related tables.
     */
    protected array $relatedTables = [
        'kegiatans' => 'user_id',
        'kegiatan_user' => 'user_id',
        'rencana_kerjas' => 'user_id',
        'rencana_kerja_user' => 'user_id',
        'kepanitiaans' => 'user_id',
        'kepanitiaan_user' => 'user_id',
        'insidentils' => 'user_id',
        'insidentil_user' => 'user_id',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info("Scanning duplicate names in users table...");

        $duplicateNames = DB::table('users')
            ->select('name', DB::raw('COUNT(*) as total'))
            ->groupBy('name')
            ->having('total', '>', 1)
            ->pluck('name');

        if ($duplicateNames->isEmpty()) {
            $this->info("No duplicate names found!");
            return 0;
        }

        $this->warn("Found " . $duplicateNames->count() . " names with duplicates.");

        $deletedCount = 0;

        foreach ($duplicateNames as $name) {
            $this->line("--------------------------------------------------");
            $this->info("Checking: {$name}");

            $users = User::where('name', $name)->orderBy('id', 'asc')->get();

            $userStats = [];
            foreach ($users as $u) {
                $absensiTotal = 0;
                foreach ($this->attendanceTables as $tbl) {
                    if (DB::getSchemaBuilder()->hasTable($tbl)) {
                        $absensiTotal += DB::table($tbl)->where('user_id', $u->id)->count();
                    }
                }

                $kegiatanTotal = 0;
                foreach ($this->relatedTables as $tbl => $col) {
                    if (DB::getSchemaBuilder()->hasTable($tbl)) {
                        $kegiatanTotal += DB::table($tbl)->where($col, $u->id)->count();
                    }
                }

                $userStats[] = [
                    'user' => $u,
                    'absensi_count' => $absensiTotal,
                    'kegiatan_count' => $kegiatanTotal,
                    'email' => $u->email,
                    'id' => $u->id,
                ];

                $this->line("  ID: {$u->id} | Email: {$u->email} | Absensi: {$absensiTotal} | Kegiatan: {$kegiatanTotal}");
            }

            // Tentukan user yang dipertahankan (prioritaskan yang ada data absensi terbanyak, lalu kegiatan terbanyak, lalu ID terkecil)
            usort($userStats, function ($a, $b) {
                if ($a['absensi_count'] !== $b['absensi_count']) {
                    return $b['absensi_count'] <=> $a['absensi_count'];
                }
                if ($a['kegiatan_count'] !== $b['kegiatan_count']) {
                    return $b['kegiatan_count'] <=> $a['kegiatan_count'];
                }
                return $a['id'] <=> $b['id'];
            });

            $kept = $userStats[0];
            $this->info("  -> Dipertahankan: ID {$kept['id']} ({$kept['email']}) [Absensi: {$kept['absensi_count']}]");

            // User-user duplikat lainnya
            for ($i = 1; $i < count($userStats); $i++) {
                $dup = $userStats[$i];
                $this->warn("  -> Akan dihapus: ID {$dup['id']} ({$dup['email']}) [Absensi: {$dup['absensi_count']}]");

                if (!$dryRun) {
                    // Jika user duplikat memiliki relasi kegiatan, relasikan/migrasikan ke kept user ID dulu
                    foreach ($this->relatedTables as $tbl => $col) {
                        if (DB::getSchemaBuilder()->hasTable($tbl)) {
                            // Untuk pivot table seperti kegiatan_user, hindari duplikasi primary key
                            if (str_contains($tbl, '_user')) {
                                DB::table($tbl)->where($col, $dup['id'])->delete();
                            } else {
                                DB::table($tbl)->where($col, $dup['id'])->update([$col => $kept['id']]);
                            }
                        }
                    }

                    // Hapus jika ada absensi di user duplikat (atau jika memang 0)
                    foreach ($this->attendanceTables as $tbl) {
                        if (DB::getSchemaBuilder()->hasTable($tbl)) {
                            DB::table($tbl)->where('user_id', $dup['id'])->delete();
                        }
                    }

                    // Hapus user duplikat
                    DB::table('users')->where('id', $dup['id'])->delete();
                    $deletedCount++;
                    $this->info("  [DELETED] User ID {$dup['id']} berhasil dihapus.");
                }
            }
        }

        $this->line("==================================================");
        if ($dryRun) {
            $this->warn("Dry-run selesai. Tidak ada data yang dihapus.");
        } else {
            $this->info("Pembersihan selesai! Total {$deletedCount} user duplikat telah dihapus.");
        }

        return 0;
    }
}
