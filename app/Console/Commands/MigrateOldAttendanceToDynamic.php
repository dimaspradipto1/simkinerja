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

        // Map tabel lama ke kepanitiaan induk, sesi, tanggal, dan slug
        $mappings = [
            [
                'table' => 'absensi_pkkmb_pertamas',
                'slug' => 'absensi-pkkmb-pertama',
                'kepanitiaan_keyword' => 'PKKMB',
                'kepanitiaan_nama' => 'Kegiatan Kepanitiaan PKKMB',
                'sesi_nama' => 'Kepanitian PKKMB Hari 1',
                'tanggal' => '2026-08-21',
                'tanggal_mulai' => '2026-08-21',
                'tanggal_selesai' => '2026-08-23',
                'is_active' => true,
            ],
            [
                'table' => 'absensi_pkkmb_keduas',
                'slug' => 'absensi-pkkmb-kedua',
                'kepanitiaan_keyword' => 'PKKMB',
                'kepanitiaan_nama' => 'Kegiatan Kepanitiaan PKKMB',
                'sesi_nama' => 'Kepanitian PKKMB Hari 2',
                'tanggal' => '2026-08-22',
                'tanggal_mulai' => '2026-08-21',
                'tanggal_selesai' => '2026-08-23',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_pkkmb_ketigas',
                'slug' => 'absensi-pkkmb-ketiga',
                'kepanitiaan_keyword' => 'PKKMB',
                'kepanitiaan_nama' => 'Kegiatan Kepanitiaan PKKMB',
                'sesi_nama' => 'Kepanitian PKKMB Hari 3',
                'tanggal' => '2026-08-23',
                'tanggal_mulai' => '2026-08-21',
                'tanggal_selesai' => '2026-08-23',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_esq_pertamas',
                'slug' => 'absensi-esq-pertama',
                'kepanitiaan_keyword' => 'ESQ',
                'kepanitiaan_nama' => 'Kegiatan Kepanitiaan ESQ',
                'sesi_nama' => 'Kepanitiaan ESQ Hari 1',
                'tanggal' => '2026-09-01',
                'tanggal_mulai' => '2026-09-01',
                'tanggal_selesai' => '2026-09-04',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_esq_keduas',
                'slug' => 'absensi-esq-kedua',
                'kepanitiaan_keyword' => 'ESQ',
                'kepanitiaan_nama' => 'Kegiatan Kepanitiaan ESQ',
                'sesi_nama' => 'Kepanitiaan ESQ Hari 2',
                'tanggal' => '2026-09-02',
                'tanggal_mulai' => '2026-09-01',
                'tanggal_selesai' => '2026-09-04',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_esq_ketigas',
                'slug' => 'absensi-esq-ketiga',
                'kepanitiaan_keyword' => 'ESQ',
                'kepanitiaan_nama' => 'Kegiatan Kepanitiaan ESQ',
                'sesi_nama' => 'Kepanitiaan ESQ Hari 3',
                'tanggal' => '2026-09-03',
                'tanggal_mulai' => '2026-09-01',
                'tanggal_selesai' => '2026-09-04',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_esq_keempats',
                'slug' => 'absensi-esq-keempat',
                'kepanitiaan_keyword' => 'ESQ',
                'kepanitiaan_nama' => 'Kegiatan Kepanitiaan ESQ',
                'sesi_nama' => 'Kepanitiaan ESQ Hari 4',
                'tanggal' => '2026-09-04',
                'tanggal_mulai' => '2026-09-01',
                'tanggal_selesai' => '2026-09-04',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_milad_pertamas',
                'slug' => 'absensi-milad-pertama',
                'kepanitiaan_keyword' => 'MILAD',
                'kepanitiaan_nama' => 'Kegiatan Kepanitiaan MILAD',
                'sesi_nama' => 'Kepanitiaan MILAD Hari 1',
                'tanggal' => '2026-08-29',
                'tanggal_mulai' => '2026-08-29',
                'tanggal_selesai' => '2026-08-30',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_milad_keduas',
                'slug' => 'absensi-milad-kedua',
                'kepanitiaan_keyword' => 'MILAD',
                'kepanitiaan_nama' => 'Kegiatan Kepanitiaan MILAD',
                'sesi_nama' => 'Kepanitiaan MILAD Hari 2',
                'tanggal' => '2026-08-30',
                'tanggal_mulai' => '2026-08-29',
                'tanggal_selesai' => '2026-08-30',
                'is_active' => false,
            ],
            [
                'table' => 'absensi_kuliah_umum_pertamas',
                'slug' => 'absensi-kuliah-umum-pertama',
                'kepanitiaan_keyword' => 'Kuliah Umum',
                'kepanitiaan_nama' => 'Kegiatan Kepanitiaan Kuliah Umum',
                'sesi_nama' => 'Kepanitiaan Kuliah Umum Hari 1',
                'tanggal' => '2026-09-06',
                'tanggal_mulai' => '2026-09-06',
                'tanggal_selesai' => '2026-09-06',
                'is_active' => false,
            ],
        ];

        $totalMigrated = 0;
        $totalUpdated = 0;

        DB::beginTransaction();
        try {
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
                $kepanitiaan = Kepanitiaan::where('uraian_tugas', 'like', "%{$map['kepanitiaan_keyword']}%")->first();
                if (!$kepanitiaan) {
                    $defaultUser = \App\Models\User::first();
                    $defaultPeriode = \App\Models\PeriodeAkademik::first();
                    $kepanitiaan = Kepanitiaan::create([
                        'user_id' => $defaultUser ? $defaultUser->id : 1,
                        'periode_akademik_id' => $defaultPeriode ? $defaultPeriode->id : 1,
                        'uraian_tugas' => $map['kepanitiaan_nama'],
                        'tanggal_mulai' => $map['tanggal_mulai'],
                        'tanggal_selesai' => $map['tanggal_selesai'],
                        'status' => 'Selesai',
                    ]);
                    $this->line("  -> Dibuat Kepanitiaan baru: {$map['kepanitiaan_nama']} (ID: {$kepanitiaan->id})");
                } else {
                    $this->line("  -> Menggunakan Kepanitiaan: {$kepanitiaan->uraian_tugas} (ID: {$kepanitiaan->id})");
                }

                // 2. Cari atau buat sesi absensi dinamis
                // Khusus session 11 (PKKMB Hari 1) yang sudah ada sebelumnya
                $sesi = KepanitiaanSesiAbsensi::where('legacy_slug', $map['slug'])->first();
                if (!$sesi) {
                    // Cek jika ada sesi di kepanitiaan ini dengan nama mirip atau id 11
                    if ($map['slug'] === 'absensi-pkkmb-pertama') {
                        $sesi = KepanitiaanSesiAbsensi::where('kepanitiaan_id', $kepanitiaan->id)
                            ->where(function($q) {
                                $q->where('nama_sesi', 'like', '%Hari 1%')
                                  ->orWhere('id', 11);
                            })->first();
                    }
                }

                if (!$sesi) {
                    $sesi = KepanitiaanSesiAbsensi::create([
                        'kepanitiaan_id' => $kepanitiaan->id,
                        'nama_sesi' => $map['sesi_nama'],
                        'tanggal' => $map['tanggal'],
                        'is_active' => $map['is_active'],
                        'legacy_slug' => $map['slug'],
                    ]);
                    $this->line("  -> Dibuat Sesi Presensi baru: {$sesi->nama_sesi} (ID: {$sesi->id})");
                } else {
                    // Update slug & tanggal
                    $sesi->legacy_slug = $map['slug'];
                    $sesi->tanggal = $map['tanggal'];
                    $sesi->save();
                    $this->line("  -> Menggunakan Sesi Presensi: {$sesi->nama_sesi} (ID: {$sesi->id})");
                }

                // 3. Pindahkan data kehadiran langsung ke DB table untuk menjaga exact created_at & updated_at
                $records = DB::table($tableName)->get();
                $insertedForTable = 0;
                $updatedForTable = 0;

                foreach ($records as $row) {
                    $existing = DB::table('kepanitiaan_presensis')
                        ->where('kepanitiaan_sesi_absensi_id', $sesi->id)
                        ->where('user_id', $row->user_id)
                        ->first();

                    if ($existing) {
                        $updateData = [];

                        // Pertahankan atau perbarui hadir_datang
                        if (!empty($row->hadir_datang)) {
                            $updateData['hadir_datang'] = $row->hadir_datang;
                            $updateData['waktu_datang'] = $row->waktu_datang ?? $existing->waktu_datang;
                            $updateData['catatan_hadir_datang'] = $row->catatan_hadir_datang ?? $existing->catatan_hadir_datang;
                        }

                        // Pertahankan atau perbarui hadir_pulang
                        if (!empty($row->hadir_pulang)) {
                            $updateData['hadir_pulang'] = $row->hadir_pulang;
                            $updateData['waktu_pulang'] = $row->waktu_pulang ?? $existing->waktu_pulang;
                            $updateData['catatan_hadir_pulang'] = $row->catatan_hadir_pulang ?? $existing->catatan_hadir_pulang;
                        }

                        if (!empty($row->bukti_izin)) {
                            $updateData['bukti_izin'] = $row->bukti_izin;
                        }

                        // created_at pakai yang paling awal
                        if ($row->created_at && (!$existing->created_at || $row->created_at < $existing->created_at)) {
                            $updateData['created_at'] = $row->created_at;
                        }

                        // updated_at pakai yang paling akhir
                        if ($row->updated_at && (!$existing->updated_at || $row->updated_at > $existing->updated_at)) {
                            $updateData['updated_at'] = $row->updated_at;
                        }

                        if (!empty($updateData)) {
                            DB::table('kepanitiaan_presensis')
                                ->where('id', $existing->id)
                                ->update($updateData);
                            $updatedForTable++;
                            $totalUpdated++;
                        }
                    } else {
                        $data = [
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
                        ];
                        DB::table('kepanitiaan_presensis')->insert($data);
                        $insertedForTable++;
                        $totalMigrated++;
                    }
                }

                $this->info("  -> Hasil {$map['sesi_nama']}: {$insertedForTable} baru disalin, {$updatedForTable} diperbarui.");
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan: " . $e->getMessage());
            return 1;
        }

        $this->info("=========================================================");
        $this->info("MIGRASI BERHASIL!");
        $this->info("Total {$totalMigrated} data presensi baru dimasukkan.");
        $this->info("Total {$totalUpdated} data presensi diperbarui.");
        $this->info("Tabel absensi lama tetap tersimpan utuh dan tidak diubah.");
        $this->info("=========================================================");

        return 0;
    }
}
