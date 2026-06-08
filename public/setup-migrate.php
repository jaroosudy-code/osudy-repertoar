<?php
// BEZPECNOSTNY KOD - zmen na dlhe nahodne slovo, ktore nikto neuhada
define('ACCESS_CODE', 'osudy2024migrate');

if (($_GET['code'] ?? '') !== ACCESS_CODE) {
    http_response_code(403);
    die('Prístup zamietnutý. Použi: ?code=' . ACCESS_CODE);
}

// Spustenie Laravel artisan migrate
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo '<pre>';
$exitCode = $kernel->call('migrate', ['--force' => true]);
echo '</pre>';
echo $exitCode === 0 ? '<p style="color:green;font-weight:bold">✅ Migrácie úspešné! ZMAŽ tento súbor cez FTP!</p>'
                     : '<p style="color:red">❌ Chyba pri migrácii. Skontroluj .env</p>';
