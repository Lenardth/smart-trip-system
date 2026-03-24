<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

try {
    define('LARAVEL_START', microtime(true));

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

    // Point all Laravel cache files to /tmp
    putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
    putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
    putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
    putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes-v7.php');
    putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

    require __DIR__ . '/../vendor/autoload.php';

    $app = require __DIR__ . '/../bootstrap/app.php';

    $app->useStoragePath('/tmp/storage');

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
    echo '<pre>';
    $current = $e;
    while ($current) {
        echo get_class($current) . ': ' . $current->getMessage() . "\n";
        echo 'in ' . $current->getFile() . ':' . $current->getLine() . "\n\n";
        $current = $current->getPrevious();
    }
    echo $e->getTraceAsString();
    echo '</pre>';
}
