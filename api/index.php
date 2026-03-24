<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

try {
    define('LARAVEL_START', microtime(true));

    // Create writable dirs in /tmp
    $dirs = [
        '/tmp/storage/logs',
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/framework/views',
        '/tmp/storage/app/public',
        '/tmp/bootstrap/cache',
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) mkdir($dir, 0777, true);
    }

    // Override storage and bootstrap paths via env
    $_ENV['APP_STORAGE_PATH'] = '/tmp/storage';
    putenv('APP_STORAGE_PATH=/tmp/storage');

    require __DIR__ . '/../vendor/autoload.php';

    $app = require __DIR__ . '/../bootstrap/app.php';

    // Override paths on the app instance
    $app->useStoragePath('/tmp/storage');
    $app->bootstrapPath('/tmp/bootstrap');

    if (!file_exists('/tmp/database.sqlite')) {
        touch('/tmp/database.sqlite');
    }

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    )->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    echo '<pre>' . $e->getMessage() . "\n\n" . $e->getTraceAsString() . '</pre>';
}
