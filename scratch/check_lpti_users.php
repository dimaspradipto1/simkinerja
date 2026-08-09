<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lptiUsers = App\Models\User::where('unit', 'like', '%LPTI%')->orWhere('jabatan', 'like', '%LPTI%')->get();
echo "LPTI Users count: " . $lptiUsers->count() . "\n";
foreach ($lptiUsers as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Jabatan: {$u->jabatan} | Unit: {$u->unit}\n";
}
