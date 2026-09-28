<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CleanDuplicateUsers extends Command
{
    protected $signature = 'users:clean-duplicates {--dry-run : Only show what would be done without deleting}';

    protected $description = 'Clean duplicate users (exact and normalized title variants) and merge/retain only active user';

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

    protected array $relatedTables = [
        'kegiatans' => 'user_id',
        'kegiatan_user' => 'user_id',
    ];

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info("Scanning normalized duplicate names in users table...");

        $users = User::all();

        $grouped = [];
        foreach ($users as $u) {
            $norm = $this->normalizeName($u->name);
            $grouped[$norm][] = $u;
        }

        $duplicateGroups = array_filter($grouped, function ($list, $key) {
            return count($list) > 1 && strlen($key) > 3;
        }, ARRAY_FILTER_USE_BOTH);

        if (empty($duplicateGroups)) {
            $this->info("No duplicate users found!");
            return 0;
        }

        $this->warn("Found " . count($duplicateGroups) . " duplicate user groups.");

        $deletedCount = 0;

        foreach ($duplicateGroups as $cleanKey => $userList) {
            $this->line("--------------------------------------------------");
            $this->info("Duplicate Group [{$cleanKey}]:");

            $userStats = [];
            foreach ($userList as $u) {
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
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'absensi_count' => $absensiTotal,
                    'kegiatan_count' => $kegiatanTotal,
                ];

                $this->line("  ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Absensi: {$absensiTotal} | Kegiatan: {$kegiatanTotal}");
            }

            // Urutkan: yang absensi terbanyak, lalu kegiatan terbanyak, lalu ID terkecil
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
            $this->info("  -> Dipertahankan: ID {$kept['id']} ({$kept['name']}) [Absensi: {$kept['absensi_count']}]");

            // Proses user-user duplikat
            for ($i = 1; $i < count($userStats); $i++) {
                $dup = $userStats[$i];
                $this->warn("  -> Akan dihapus: ID {$dup['id']} ({$dup['name']}) [Absensi: {$dup['absensi_count']}]");

                if (!$dryRun) {
                    // Migrasikan relasi kegiatan
                    foreach ($this->relatedTables as $tbl => $col) {
                        if (DB::getSchemaBuilder()->hasTable($tbl)) {
                            if (str_contains($tbl, '_user')) {
                                DB::table($tbl)->where($col, $dup['id'])->delete();
                            } else {
                                DB::table($tbl)->where($col, $dup['id'])->update([$col => $kept['id']]);
                            }
                        }
                    }

                    // Migrasikan data absensi jika user duplikat punya absensi yang belum ada di kept user
                    foreach ($this->attendanceTables as $tbl) {
                        if (DB::getSchemaBuilder()->hasTable($tbl)) {
                            $dupAbsensi = DB::table($tbl)->where('user_id', $dup['id'])->get();
                            foreach ($dupAbsensi as $rec) {
                                $existsInKept = DB::table($tbl)->where('user_id', $kept['id'])->exists();
                                if (!$existsInKept) {
                                    DB::table($tbl)->where('id', $rec->id)->update(['user_id' => $kept['id']]);
                                } else {
                                    DB::table($tbl)->where('id', $rec->id)->delete();
                                }
                            }
                        }
                    }

                    // Hapus user duplikat
                    DB::table('users')->where('id', $dup['id'])->delete();
                    $deletedCount++;
                    $this->info("  [DELETED] User ID {$dup['id']} ({$dup['name']}) berhasil dihapus.");
                }
            }
        }

        $this->line("==================================================");
        if ($dryRun) {
            $this->warn("Dry-run selesai. Tidak ada data yang dihapus.");
        } else {
            $this->info("Pembersihan selesai! Total {$deletedCount} akun duplikat berhasil dihapus.");
        }

        return 0;
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtolower($name);
        $name = preg_replace('/(prof\.|dr\.|drs\.|dra\.|ir\.)/i', '', $name);
        $name = preg_replace('/,\s*.*$/', '', $name);
        $name = preg_replace('/\b(s\.kom|s\.t\.|st|s\.e\.|se|m\.m\.|mm|m\.kom|m\.kes|s\.kes|skl|m\.km|s\.m\.|sm|s\.ak|s\.pd|m\.pd|s\.si|m\.si|a\.md|amd|ak\.)\b/i', '', $name);
        $name = preg_replace('/[^a-z]/', '', $name);
        return trim($name);
    }
}
