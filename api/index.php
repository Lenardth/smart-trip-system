<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

try {
    define('LARAVEL_START', microtime(true));

    if ($dbUrl = getenv('DATABASE_URL')) {
        $url = parse_url($dbUrl);
        putenv('DB_CONNECTION=pgsql');
        putenv('DB_HOST=' . $url['host']);
        putenv('DB_PORT=' . ($url['port'] ?? 5432));
        putenv('DB_DATABASE=' . ltrim($url['path'], '/'));
        putenv('DB_USERNAME=' . $url['user']);
        putenv('DB_PASSWORD=' . $url['pass']);
        putenv('DB_SSLMODE=require');
    }

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

    putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
    putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
    putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
    putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes-v7.php');
    putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

    require __DIR__ . '/../vendor/autoload.php';

    $app = require __DIR__ . '/../bootstrap/app.php';

    $app->useStoragePath('/tmp/storage');

    $artisan = $app->make(Illuminate\Contracts\Console\Kernel::class);

    try {
        $migrated = \Illuminate\Support\Facades\DB::table('migrations')->count();
    } catch (\Throwable $e) {
        $migrated = 0;
    }

    if (!$migrated) {
        $artisan->call('migrate', ['--force' => true]);
        $artisan->call('db:seed', ['--class' => 'DestinationSeeder', '--force' => true]);
        $artisan->call('db:seed', ['--class' => 'CommunitySeeder', '--force' => true]);
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
