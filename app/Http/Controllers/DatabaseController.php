<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseController extends Controller
{
    private const SECRET_KEY = 'your-secret-key-here'; // Change this!

    /**
     * Verify secret key for security
     */
    private function verifySecret(Request $request): bool
    {
        $secret = $request->header('X-Database-Secret') ?? $request->input('secret');
        return $secret === self::SECRET_KEY;
    }

    /**
     * Run database migrations
     * POST /api/database/migrate
     */
    public function migrate(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid secret key.',
            ], 401);
        }

        try {
            $force = $request->input('force', false);
            
            // Run migrations
            $exitCode = Artisan::call('migrate', [
                '--force' => $force,
            ]);

            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'message' => 'Migrations executed successfully',
                'output' => $output,
                'exit_code' => $exitCode,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Migration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run database seeders
     * POST /api/database/seed
     */
    public function seed(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid secret key.',
            ], 401);
        }

        try {
            $class = $request->input('class', 'DatabaseSeeder');
            $force = $request->input('force', false);

            // Run seeder
            $exitCode = Artisan::call('db:seed', [
                '--class' => $class,
                '--force' => $force,
            ]);

            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'message' => 'Seeder executed successfully',
                'output' => $output,
                'seeder' => $class,
                'exit_code' => $exitCode,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seeding failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Migrate and seed in one call
     * POST /api/database/setup
     */
    public function setup(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid secret key.',
            ], 401);
        }

        try {
            $results = [];

            // Run migrations
            $migrateCode = Artisan::call('migrate', ['--force' => true]);
            $results['migrate'] = [
                'success' => $migrateCode === 0,
                'output' => Artisan::output(),
            ];

            // Run seeders
            $seedCode = Artisan::call('db:seed', ['--force' => true]);
            $results['seed'] = [
                'success' => $seedCode === 0,
                'output' => Artisan::output(),
            ];

            return response()->json([
                'success' => $migrateCode === 0 && $seedCode === 0,
                'message' => 'Database setup completed',
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Setup failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rollback migrations
     * POST /api/database/rollback
     */
    public function rollback(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid secret key.',
            ], 401);
        }

        try {
            $steps = $request->input('steps', 1);

            $exitCode = Artisan::call('migrate:rollback', [
                '--step' => $steps,
                '--force' => true,
            ]);

            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'message' => 'Rollback executed successfully',
                'output' => $output,
                'steps' => $steps,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rollback failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fresh migration (drop all tables and re-migrate)
     * POST /api/database/fresh
     */
    public function fresh(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid secret key.',
            ], 401);
        }

        try {
            $seed = $request->input('seed', false);

            $options = ['--force' => true];
            if ($seed) {
                $options['--seed'] = true;
            }

            $exitCode = Artisan::call('migrate:fresh', $options);
            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'message' => 'Fresh migration completed',
                'output' => $output,
                'seeded' => $seed,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fresh migration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get database status
     * GET /api/database/status
     */
    public function status(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid secret key.',
            ], 401);
        }

        try {
            // Get migration status
            Artisan::call('migrate:status');
            $migrationStatus = Artisan::output();

            // Get table counts
            $tables = DB::select("
                SELECT table_name 
                FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_type = 'BASE TABLE'
                ORDER BY table_name
            ");

            $tableCounts = [];
            foreach ($tables as $table) {
                $tableName = $table->table_name;
                if ($tableName !== 'migrations') {
                    $count = DB::table($tableName)->count();
                    $tableCounts[$tableName] = $count;
                }
            }

            return response()->json([
                'success' => true,
                'database' => [
                    'connection' => config('database.default'),
                    'driver' => config('database.connections.' . config('database.default') . '.driver'),
                    'host' => config('database.connections.' . config('database.default') . '.host'),
                    'database' => config('database.connections.' . config('database.default') . '.database'),
                ],
                'tables' => count($tables),
                'table_counts' => $tableCounts,
                'migration_status' => $migrationStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all caches
     * POST /api/database/clear-cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid secret key.',
            ], 401);
        }

        try {
            $results = [];

            // Clear various caches
            Artisan::call('config:clear');
            $results['config'] = Artisan::output();

            Artisan::call('cache:clear');
            $results['cache'] = Artisan::output();

            Artisan::call('route:clear');
            $results['route'] = Artisan::output();

            Artisan::call('view:clear');
            $results['view'] = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'All caches cleared successfully',
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cache clearing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optimize application
     * POST /api/database/optimize
     */
    public function optimize(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid secret key.',
            ], 401);
        }

        try {
            Artisan::call('optimize');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Application optimized successfully',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Optimization failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
