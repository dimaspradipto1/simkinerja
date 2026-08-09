<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rektors = App\Models\User::where('jabatan', 'REKTOR')->get();
echo "Total Rektor users found: " . $rektors->count() . "\n";
foreach ($rektors as $r) {
    echo "ID: {$r->id} | Name: {$r->name} | Email: {$r->email}\n";
}

if ($rektors->count() > 1) {
    $mainRektor = $rektors->where('name', 'like', '%ASEAN%')->first() ?? $rektors->first();
    echo "Main Rektor ID: {$mainRektor->id}\n";

    foreach ($rektors as $r) {
        if ($r->id !== $mainRektor->id) {
            echo "Merging duplicate Rektor ID {$r->id} to {$mainRektor->id}...\n";
            App\Models\RencanaKerja::where('user_id', $r->id)->update(['user_id' => $mainRektor->id]);
            $r->delete();
        }
    }
    echo "Duplicate Rektor cleaned!\n";
}
