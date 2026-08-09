<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tasks = App\Models\RencanaKerja::with('user')->get();
echo "Total Tasks: " . $tasks->count() . "\n";
foreach ($tasks as $t) {
    echo "ID: {$t->id} | User: " . ($t->user ? $t->user->name : 'N/A') . " (ID: {$t->user_id}) | PeriodeID: {$t->periode_akademik_id} | Status: {$t->status} | EstEnd: {$t->estimasi_tanggal_selesai} | ActEnd: {$t->tanggal_selesai}\n";
}
