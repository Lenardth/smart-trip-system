<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

/**
 * Setup Routes - For Initial Deployment Only
 * These routes help with database setup on serverless environments
 * TODO: Remove or protect these routes after initial deployment
 */

Route::get('/setup', fn () => view('setup'));

Route::get('/setup/debug', function () {
    try {
        DB::purge('pgsql');
        DB::reconnect('pgsql');
        
        $total = DB::table('destinations')->count();
        $active = DB::table('destinations')->where('is_active', 1)->count();
        $notHidden = DB::table('destinations')->where('is_hidden_gem', 0)->count();
        $activeNotHidden = DB::table('destinations')
            ->where('is_active', 1)
            ->where('is_hidden_gem', 0)
            ->count();
        
        $sample = DB::table('destinations')
            ->select('id', 'name', 'country', 'is_active', 'is_hidden_gem', 'category', 'region')
            ->limit(5)
            ->get();
        
        // Test the actual API endpoint
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM destinations WHERE is_active = 1 AND is_hidden_gem = 0");
        $stmt->execute();
        $pdoCount = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return response()->json([
            'success' => true,
            'counts' => [
                'total' => $total,
                'is_active_1' => $active,
                'is_hidden_gem_0' => $notHidden,
                'active_and_not_hidden' => $activeNotHidden,
                'pdo_active_not_hidden' => $pdoCount['count'] ?? 0,
            ],
            'sample_destinations' => $sample,
            'query_that_discover_uses' => 'SELECT * FROM destinations WHERE is_active = 1 AND is_hidden_gem = 0',
            'note' => 'If pdo_active_not_hidden is 0, the data is not in the database'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/setup/status', function () {
    try {
        DB::purge('pgsql');
        DB::reconnect('pgsql');
        
        $destinationCount = DB::table('destinations')->count();
        $userCount = DB::table('users')->count();
        $tripMoodCount = DB::table('trip_moods')->count();
        $communityTopicCount = DB::table('community_topics')->count();
        
        return response()->json([
            'success' => true,
            'counts' => [
                'destinations' => $destinationCount,
                'users' => $userCount,
                'trip_moods' => $tripMoodCount,
                'community_topics' => $communityTopicCount,
            ],
            'message' => 'Database is accessible'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('/setup/clear-cache', function () {
    try {
        DB::purge('pgsql');
        DB::purge('pgsql_direct');
        
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        DB::reconnect('pgsql');
        
        return response()->json([
            'success' => true,
            'message' => 'All caches cleared and connections reset'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('/setup/migrate', function () {
    try {
        $dbUrl = env('DATABASE_URL');
        $directUrl = str_replace('-pooler.', '.', $dbUrl);
        $url = parse_url($directUrl);
        
        config(['database.connections.pgsql_direct' => [
            'driver' => 'pgsql',
            'host' => $url['host'],
            'port' => $url['port'] ?? 5432,
            'database' => ltrim($url['path'], '/'),
            'username' => $url['user'],
            'password' => $url['pass'],
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'require',
        ]]);
        
        DB::purge('pgsql');
        DB::purge('pgsql_direct');
        config(['database.default' => 'pgsql_direct']);
        
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();
        
        config(['database.default' => 'pgsql']);
        DB::purge('pgsql');
        
        return response()->json([
            'success' => true,
            'output' => $output,
            'message' => 'Migrations completed using direct connection'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile())
        ], 500);
    }
});

Route::post('/setup/fresh', function () {
    try {
        $dbUrl = env('DATABASE_URL');
        $directUrl = str_replace('-pooler.', '.', $dbUrl);
        $url = parse_url($directUrl);
        
        config(['database.connections.pgsql_direct' => [
            'driver' => 'pgsql',
            'host' => $url['host'],
            'port' => $url['port'] ?? 5432,
            'database' => ltrim($url['path'], '/'),
            'username' => $url['user'],
            'password' => $url['pass'],
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'require',
        ]]);
        
        DB::purge('pgsql');
        DB::purge('pgsql_direct');
        config(['database.default' => 'pgsql_direct']);
        
        $pdo = DB::connection('pgsql_direct')->getPdo();
        
        try {
            $pdo->exec('ROLLBACK');
        } catch (\Exception $e) {
            // Ignore
        }
        
        $pdo->exec('DROP SCHEMA IF EXISTS public CASCADE');
        $pdo->exec('CREATE SCHEMA public');
        $pdo->exec('GRANT ALL ON SCHEMA public TO ' . $url['user']);
        $pdo->exec('GRANT ALL ON SCHEMA public TO public');
        
        DB::purge('pgsql_direct');
        DB::reconnect('pgsql_direct');
        
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = Artisan::output();
        
        Artisan::call('db:seed', ['--force' => true]);
        $seedOutput = Artisan::output();
        
        $destinationCount = DB::connection('pgsql_direct')->table('destinations')->count();
        $userCount = DB::connection('pgsql_direct')->table('users')->count();
        
        config(['database.default' => 'pgsql']);
        DB::purge('pgsql');
        DB::purge('pgsql_direct');
        DB::reconnect('pgsql');
        
        $poolerDestinationCount = DB::table('destinations')->count();
        $poolerUserCount = DB::table('users')->count();
        
        return response()->json([
            'success' => true,
            'migrate_output' => $migrateOutput,
            'seed_output' => $seedOutput,
            'message' => 'Database reset and seeded successfully',
            'verification' => [
                'direct_connection' => [
                    'destinations' => $destinationCount,
                    'users' => $userCount,
                ],
                'pooler_connection' => [
                    'destinations' => $poolerDestinationCount,
                    'users' => $poolerUserCount,
                ]
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
            'trace' => explode("\n", $e->getTraceAsString())
        ], 500);
    }
});
