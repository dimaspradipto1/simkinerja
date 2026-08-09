<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rusdi = App\Models\User::where('name', 'like', '%RUSDIYANTO%')->first();

if ($rusdi) {
    // 1. Create a task completed late
    App\Models\RencanaKerja::create([
        'user_id' => $rusdi->id,
        'periode_akademik_id' => 1,
        'uraian_tugas' => 'Maintenance Server LPTI & Perbaikan Jaringan Rektorat',
        'kategori_tugas' => 'Tugas Utama',
        'status' => 'Selesai',
        'tanggal_mulai' => '2026-08-01',
        'estimasi_tanggal_selesai' => '2026-08-04',
        'tanggal_selesai' => '2026-08-08', // Late by 4 days!
        'kategori_kendala' => 'insidentil',
        'uraian_kendala' => 'Terjadi gangguan jaringan mendadak di gedung Rektorat dan permintaan penanganan cepat (Insidentil).',
    ]);

    // 2. Create another task overdue
    App\Models\RencanaKerja::create([
        'user_id' => $rusdi->id,
        'periode_akademik_id' => 1,
        'uraian_tugas' => 'Pengembangan Modul Troubleshooting SIM KINERJA',
        'kategori_tugas' => 'Tugas Utama',
        'status' => 'Dalam Proses',
        'tanggal_mulai' => '2026-08-02',
        'estimasi_tanggal_selesai' => '2026-08-06', // Overdue!
        'kategori_kendala' => 'beban_ganda',
        'uraian_kendala' => 'Beban pekerjaan bersamaan dengan perbaikan infrastruktur jaringan.',
    ]);

    echo "Sample late tasks created for RUSDIYANTO (ID: {$rusdi->id})\n";
} else {
    echo "RUSDIYANTO not found.\n";
}
