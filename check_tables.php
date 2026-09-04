<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$dbs = DB::select('SHOW DATABASES');
foreach ($dbs as $db) {
    $dbName = array_values((array)$db)[0];
    if (in_array($dbName, ['information_schema', 'performance_schema', 'mysql', 'sys'])) continue;
    try {
        $tables = DB::select("SHOW TABLES FROM `$dbName`");
        foreach ($tables as $t) {
            $tName = array_values((array)$t)[0];
            if (str_contains($tName, 'absen')) {
                $count = DB::select("SELECT COUNT(*) as c FROM `$dbName`.`$tName`")[0]->c;
                if ($count > 0) {
                    echo "$dbName.$tName: $count\n";
                }
            }
        }
    } catch (\Exception $e) {}
}
