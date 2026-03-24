<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

try {
    define('LARAVEL_START', microtime(true));

    require __DIR__ . '/../vendor/autoload.php';

    // Move writable directories to /tmp
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

    // Symlink storage and bootstrap/cache to /tmp
    $links = [
        __DIR__ . '/../storage/logs'               => '/tmp/storage/logs',
        __DIR__ . '/../storage/framework/cache'    => '/tmp/storage/framework/cache',
        __DIR__ . '/../storage/framework/sessions' => '/tmp/storage/framework/sessions',
        __DIR__ . '/../storage/framework/views'    => '/tmp/storage/framework/views',
        __DIR__ . '/../bootstrap/cache'            => '/tmp/bootstrap/cache',
    ];
    foreach ($links as $link => $target) {
        if (!is_link($link) && !is_dir($link)) {
            symlink($target, $link);
        }
    }

    // SQLite
    if (!file_exists('/tmp/database.sqlite')) {
        touch('/tmp/database.sqlite');
    }

    $app = require __DIR__ . '/../bootstrap/app.php';

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    )->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    echo '<pre>' . $e->getMessage() . "\n\n" . $e->getTraceAsString() . '</pre>';
}
