<?php

define('LARAVEL_START', microtime(true));

// ── Tmp directories (Vercel serverless has no writable disk except /tmp) ──────
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

// ── Environment overrides for serverless ─────────────────────────────────────
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes-v7.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');
putenv('CACHE_STORE=array');
putenv('CACHE_DRIVER=array');
putenv('SESSION_DRIVER=cookie');
putenv('QUEUE_CONNECTION=sync');
putenv('FILESYSTEM_DISK=local');
putenv('LOG_CHANNEL=stderr');

// ── Parse DATABASE_URL (Neon / Supabase / Railway Postgres) ──────────────────
if ($dbUrl = getenv('DATABASE_URL')) {
    $url = parse_url($dbUrl);
    parse_str($url['query'] ?? '', $query);
    $vars = [
        'DB_CONNECTION' => 'pgsql',
        'DB_HOST'       => $url['host'],
        'DB_PORT'       => $url['port'] ?? 5432,
        'DB_DATABASE'   => ltrim($url['path'] ?? '', '/'),
        'DB_USERNAME'   => $url['user'] ?? '',
        'DB_PASSWORD'   => $url['pass'] ?? '',
        'DB_SSLMODE'    => $query['sslmode'] ?? 'require',
    ];
    foreach ($vars as $k => $v) {
        putenv("$k=$v");
        $_ENV[$k] = $_SERVER[$k] = $v;
    }
}

// ── Forward other env vars ────────────────────────────────────────────────────
foreach (['GROQ_API_KEY', 'APP_KEY', 'APP_ENV', 'APP_DEBUG', 'APP_URL',
          'PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_CLUSTER',
          'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD',
          'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'] as $var) {
    if ($val = getenv($var)) {
        $_ENV[$var] = $_SERVER[$var] = $val;
    }
}

try {
    require __DIR__ . '/../vendor/autoload.php';

    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath('/tmp/storage');

    // ── Run migrations (idempotent — safe on every cold start) ───────────────
    $artisan = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $artisan->call('migrate', ['--force' => true]);

    // ── Seed only if tables are empty (first deploy) ─────────────────────────
    try {
        $db = $app->make('db');
        $destinationCount = $db->table('destinations')->count();
        if ($destinationCount === 0) {
            $artisan->call('db:seed', ['--force' => true]);
        }
    } catch (\Throwable $seedErr) {
        // Seeding failure should not block the app
        error_log('[SmartBooking] Seed error: ' . $seedErr->getMessage());
    }

    // ── Handle HTTP request ───────────────────────────────────────────────────
    $kernel  = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    $debug = getenv('APP_DEBUG') === 'true' || getenv('APP_ENV') === 'local';
    if ($debug) {
        echo '<pre style="font-family:monospace;padding:20px;">';
        $cur = $e;
        while ($cur) {
            echo '<strong>' . get_class($cur) . '</strong>: ' . htmlspecialchars($cur->getMessage()) . "\n";
            echo 'in ' . $cur->getFile() . ':' . $cur->getLine() . "\n\n";
            $cur = $cur->getPrevious();
        }
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
    }
}
