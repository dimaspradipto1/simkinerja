<?php
$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    $count = $db->query("SELECT COUNT(*) FROM \"$t\"")->fetchColumn();
    if ($count > 0) {
        echo "$t: $count\n";
    }
}
