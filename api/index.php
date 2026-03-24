<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

try {
    define('LARAVEL_START', microtime(true));

    require __DIR__ . '/../vendor/autoload.php';

    $app = require __DIR__ . '/../bootstrap/app.php';

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
    echo $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
    echo '</pre>';
}
