<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(239); // Rusdiyanto (tagged user)
\Illuminate\Support\Facades\Auth::login($user);

$task = App\Models\Kepanitiaan::with('taggedUsers', 'user')->find(3);

$controller = app(\App\Http\Controllers\KepanitiaanController::class);
$ref = new \ReflectionClass($controller);
$m = $ref->getMethod('renderActionButtons');
$m->setAccessible(true);

$btnHtml = $m->invoke($controller, $task, $user, 'kepanitiaan', 'deleteKepanitiaan');

echo "RUSDIYANTO (TAGGED USER) BUTTONS HTML:" . PHP_EOL;
echo $btnHtml . PHP_EOL . PHP_EOL;

// Now test as OWNER (Dimas Pradipto)
$owner = App\Models\User::find(129);
\Illuminate\Support\Facades\Auth::login($owner);
$btnOwnerHtml = $m->invoke($controller, $task, $owner, 'kepanitiaan', 'deleteKepanitiaan');

echo "OWNER (DIMAS PRADIPTO) BUTTONS HTML:" . PHP_EOL;
echo $btnOwnerHtml . PHP_EOL;
