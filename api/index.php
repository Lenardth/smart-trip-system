<?php

define('LARAVEL_START', microtime(true));

foreach ([
    '/tmp/storage/logs',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/app/public',
    '/tmp/bootstrap/cache',
] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0777, true);
}

$envOverrides = [
    'APP_CONFIG_CACHE'   => '/tmp/bootstrap/cache/config.php',
    'APP_SERVICES_CACHE' => '/tmp/bootstrap/cache/services.php',
    'APP_PACKAGES_CACHE' => '/tmp/bootstrap/cache/packages.php',
    'APP_ROUTES_CACHE'   => '/tmp/bootstrap/cache/routes-v7.php',
    'APP_EVENTS_CACHE'   => '/tmp/bootstrap/cache/events.php',
    'CACHE_STORE'        => 'array',
    'CACHE_DRIVER'       => 'array',
    'SESSION_DRIVER'     => 'cookie',
    'QUEUE_CONNECTION'   => 'sync',
    'FILESYSTEM_DISK'    => 'local',
    'LOG_CHANNEL'        => 'stderr',
];
foreach ($envOverrides as $k => $v) {
    putenv("$k=$v");
    $_ENV[$k] = $_SERVER[$k] = $v;
}

if ($dbUrl = getenv('DATABASE_URL')) {
    $url = parse_url($dbUrl);
    parse_str($url['query'] ?? '', $query);
    $vars = [
        'DB_CONNECTION' => 'pgsql',
        'DB_HOST'       => $url['host'] ?? '',
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

foreach ([
    'GROQ_API_KEY', 'APP_KEY', 'APP_ENV', 'APP_DEBUG', 'APP_URL',
    'PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_CLUSTER',
    'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD',
    'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME',
] as $var) {
    if ($val = getenv($var)) {
        $_ENV[$var] = $_SERVER[$var] = $val;
    }
}

try {
    require __DIR__ . '/../vendor/autoload.php';

    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath('/tmp/storage');

    $artisan = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $artisan->bootstrap();

    $db = $app->make(Illuminate\Database\DatabaseManager::class);

    try {
        $schema             = $db->connection()->getSchemaBuilder();
        $hasMigrationsTable = $schema->hasTable('migrations');
        $hasUsersTable      = $schema->hasTable('users');

        if (!$hasMigrationsTable && $hasUsersTable) {
            $artisan->call('migrate:install');
            $migrationFiles = glob(__DIR__ . '/../database/migrations/*.php');
            sort($migrationFiles);
            foreach ($migrationFiles as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                if (!$db->table('migrations')->where('migration', $name)->exists()) {
                    $db->table('migrations')->insert(['migration' => $name, 'batch' => 1]);
                }
            }
        } else {
            $artisan->call('migrate', ['--force' => true]);
        }

        $tablesToCheck = [
            '2025_01_01_000014_create_itineraries_table'             => 'itineraries',
            '2025_01_01_000015_add_plan_trip_columns_to_trips_table' => null,
            '2025_01_01_000016_create_accommodations_table'          => 'accommodations',
            '2025_01_01_000017_add_feeling_note_to_trips_table'      => null,
            '2025_01_01_000018_create_trip_moods_table'              => 'trip_moods',
            '2025_01_01_000019_create_accommodation_searches_table'  => 'accommodation_searches',
            '2025_01_01_000020_change_budget_column_in_trips_table'  => null,
            '2025_01_01_000021_create_monetization_tables'           => 'coupons',
        ];

        $removedAny = false;
        foreach ($tablesToCheck as $migration => $table) {
            if ($table === null) continue;
            if ($hasMigrationsTable && !$schema->hasTable($table)) {
                $db->table('migrations')->where('migration', $migration)->delete();
                $removedAny = true;
                error_log("[SmartBooking] Removed stale record: {$migration}");
            }
        }

        if ($removedAny) {
            $artisan->call('migrate', ['--force' => true]);
        }

    } catch (\Throwable $migrateErr) {
        if (!str_contains($migrateErr->getMessage(), 'already exists')
         && !str_contains($migrateErr->getMessage(), 'Duplicate table')) {
            error_log('[SmartBooking] Migration error: ' . $migrateErr->getMessage());
            throw $migrateErr;
        }
    }

    try {
        $schema2         = $db->connection()->getSchemaBuilder();
        $hasDestinations = $schema2->hasTable('destinations')
                        && $db->table('destinations')->count() >= 100;

        if (!$hasDestinations) {
            $artisan->call('db:seed', ['--force' => true]);
        } else {
            if ($schema2->hasTable('coupons') && $db->table('coupons')->count() === 0) {
                $artisan->call('db:seed', ['--class' => 'Database\\Seeders\\CouponSeeder', '--force' => true]);
            }
            if ($schema2->hasTable('trip_moods') && $db->table('trip_moods')->count() === 0) {
                $artisan->call('db:seed', ['--class' => 'Database\\Seeders\\TripMoodSeeder', '--force' => true]);
            }
            if ($schema2->hasTable('accommodations') && $db->table('accommodations')->count() === 0) {
                $artisan->call('db:seed', ['--class' => 'Database\\Seeders\\AccommodationSeeder', '--force' => true]);
            }
        }
    } catch (\Throwable $seedErr) {
        error_log('[SmartBooking] Seed warning: ' . $seedErr->getMessage());
    }

    $kernel   = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request  = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    $debug = in_array(getenv('APP_ENV'), ['local', 'development'])
          || getenv('APP_DEBUG') === 'true';

    if ($debug) {
        echo '<pre style="font-family:monospace;padding:20px;background:#1a0a00;color:#f5e6d3;">';
        $cur = $e;
        while ($cur) {
            echo '<strong style="color:#c9a96e;">' . get_class($cur) . '</strong>: '
               . htmlspecialchars($cur->getMessage()) . "\n"
               . 'in ' . $cur->getFile() . ':' . $cur->getLine() . "\n\n";
            $cur = $cur->getPrevious();
        }
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Internal Server Error']);
    }
}
