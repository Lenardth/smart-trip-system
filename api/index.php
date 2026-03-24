<?php

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

if (!file_exists('/tmp/database.sqlite')) {
    touch('/tmp/database.sqlite');
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
}

require __DIR__ . '/../public/index.php';
