<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

try {
    define('LARAVEL_START', microtime(true));

    // Parse Neon DATABASE_URL and force pgsql
    if ($dbUrl = getenv('DATABASE_URL')) {
        $url = parse_url($dbUrl);

        $vars = [
            'DB_CONNECTION' => 'pgsql',
            'DB_HOST'       => $url['host'],
            'DB_PORT'       => $url['port'] ?? 5432,
            'DB_DATABASE'   => ltrim($url['path'], '/'),
            'DB_USERNAME'   => $url['user'],
            'DB_PASSWORD'   => $url['pass'],
            'DB_SSLMODE'    => 'require',
        ];

        foreach ($vars as $key => $value) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

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
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    // Cache paths (required for serverless)
    putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
    putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
    putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
    putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes-v7.php');
    putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

    require __DIR__ . '/../vendor/autoload.php';

    $app = require __DIR__ . '/../bootstrap/app.php';

    // Force storage path to /tmp
    $app->useStoragePath('/tmp/storage');

    // Run migrations safely (Laravel handles duplicates)
    $artisan = $app->make(Illuminate\Contracts\Console\Kernel::class);

    $artisan->call('migrate', ['--force' => true]);

    // Run seeders
    $artisan->call('db:seed', [
        '--class' => 'DestinationSeeder',
        '--force' => true
    ]);

    $artisan->call('db:seed', [
        '--class' => 'CommunitySeeder',
        '--force' => true
    ]);

    // Handle request
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
