<?php

namespace App\Console\Commands;

use App\Models\Kepanitiaan;
use App\Models\KepanitiaanPresensi;
use App\Models\KepanitiaanSesiAbsensi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateOldAttendanceToDynamic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-old-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data absensi lama (PKKMB, ESQ, Milad, dll) ke tabel presensi kepanitiaan universal yang dinamis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai migrasi data absensi historis ke sistem dinamis...');

        // Map tabel lama ke nama kepanitiaan & sesi
        $mappings = [
            [
                'table' => 'absensi_pkkmb_pertamas',
                'slug' => 'absensi-pkkmb-pertama',
                'kepanitiaan_nama' => 'Kepanitiaan PKKMB',
                'sesi_nama' => 'Absensi PKKMB Hari Pertama',
                'is_active' => true,
            ],
            [
                'table' => 'absensi_pkkmb_keduas',
                'slug' => 'absensi-pkkmb-kedua',
                'kepanitiaan_nama' => 'Kepanitiaan PKKMB',
                'sesi_nama' => 'Absensi PKKMB Hari Kedua',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_pkkmb_ketigas',
                'slug' => 'absensi-pkkmb-ketiga',
                'kepanitiaan_nama' => 'Kepanitiaan PKKMB',
                'sesi_nama' => 'Absensi PKKMB Hari Ketiga',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_esq_pertamas',
                'slug' => 'absensi-esq-pertama',
                'kepanitiaan_nama' => 'Kepanitiaan ESQ',
                'sesi_nama' => 'Absensi ESQ Hari Pertama',
                'is_active' => true,
            ],
            [
                'table' => 'absensi_esq_keduas',
                'slug' => 'absensi-esq-kedua',
                'kepanitiaan_nama' => 'Kepanitiaan ESQ',
                'sesi_nama' => 'Absensi ESQ Hari Kedua',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_esq_ketigas',
                'slug' => 'absensi-esq-ketiga',
                'kepanitiaan_nama' => 'Kepanitiaan ESQ',
                'sesi_nama' => 'Absensi ESQ Hari Ketiga',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_esq_keempats',
                'slug' => 'absensi-esq-keempat',
                'kepanitiaan_nama' => 'Kepanitiaan ESQ',
                'sesi_nama' => 'Absensi ESQ Hari Keempat',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_milad_pertamas',
                'slug' => 'absensi-milad-pertama',
                'kepanitiaan_nama' => 'Kepanitiaan MILAD',
                'sesi_nama' => 'Absensi MILAD Hari Pertama',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_milad_keduas',
                'slug' => 'absensi-milad-kedua',
                'kepanitiaan_nama' => 'Kepanitiaan MILAD',
                'sesi_nama' => 'Absensi MILAD Hari Kedua',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_kuliah_umum_pertamas',
                'slug' => 'absensi-kuliah-umum-pertama',
                'kepanitiaan_nama' => 'Kepanitiaan Kuliah Umum',
                'sesi_nama' => 'Absensi Kuliah Umum Hari Pertama',
                'is_active' => false,
            ],
        ];

        $totalMigrated = 0;

        foreach ($mappings as $map) {
            $tableName = $map['table'];
            if (!Schema::hasTable($tableName)) {
                $this->line("Tabel {$tableName} tidak ditemukan, dilewati.");
                continue;
            }

            $count = DB::table($tableName)->count();
            if ($count === 0) {
                $this->line("Tabel {$tableName} kosong (0 data).");
                continue;
            }

            $this->info("Menemukan {$count} data pada {$tableName}. Mulai transfer...");

            // 1. Cari atau buat kepanitiaan induk
            $keyword = str_contains($map['slug'], 'pkkmb') ? 'PKKMB' : (str_contains($map['slug'], 'esq') ? 'ESQ' : (str_contains($map['slug'], 'milad') ? 'MILAD' : 'Kuliah Umum'));
            
            $kepanitiaan = Kepanitiaan::where('uraian_tugas', 'like', "%{$keyword}%")->first();
            if (!$kepanitiaan) {
                $defaultUser = \App\Models\User::first();
                $defaultPeriode = \App\Models\PeriodeAkademik::first();
                $kepanitiaan = Kepanitiaan::create([
                    'user_id' => $defaultUser ? $defaultUser->id : 1,
                    'periode_akademik_id' => $defaultPeriode ? $defaultPeriode->id : 1,
                    'uraian_tugas' => $map['kepanitiaan_nama'],
                    'status' => 'Selesai',
                ]);
            }

            // 2. Cari atau buat sesi absensi dinamis
            $sesi = KepanitiaanSesiAbsensi::where('legacy_slug', $map['slug'])->first();
            if (!$sesi) {
                $sesi = KepanitiaanSesiAbsensi::create([
                    'kepanitiaan_id' => $kepanitiaan->id,
                    'nama_sesi' => $map['sesi_nama'],
                    'is_active' => $map['is_active'],
                    'legacy_slug' => $map['slug'],
                ]);
            }

            // 3. Pindahkan data kehadiran
            $records = DB::table($tableName)->get();
            $migratedForTable = 0;

            foreach ($records as $row) {
                $existing = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $sesi->id)
                    ->where('user_id', $row->user_id)
                    ->first();

                if (!$existing) {
                    KepanitiaanPresensi::create([
                        'kepanitiaan_sesi_absensi_id' => $sesi->id,
                        'user_id' => $row->user_id,
                        'hadir_datang' => $row->hadir_datang ?? null,
                        'waktu_datang' => $row->waktu_datang ?? null,
                        'catatan_hadir_datang' => $row->catatan_hadir_datang ?? null,
                        'hadir_pulang' => $row->hadir_pulang ?? null,
                        'waktu_pulang' => $row->waktu_pulang ?? null,
                        'catatan_hadir_pulang' => $row->catatan_hadir_pulang ?? null,
                        'bukti_izin' => $row->bukti_izin ?? null,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ]);
                    $migratedForTable++;
                    $totalMigrated++;
                }
            }

            $this->info("Berhasil memindahkan {$migratedForTable} data presensi untuk {$map['sesi_nama']}.");
        }

        $this->info("=========================================");
        $this->info("SELESAI! Total {$totalMigrated} data presensi berhasil dimigrasikan ke tabel universal baru.");
        $this->info("=========================================");

        return 0;
    }
}
