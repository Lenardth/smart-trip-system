<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthCheck extends Command
{
    protected $signature = 'health:check';
    protected $description = 'Check application health';

    public function handle()
    {
        $this->info('🏥 Application Health Check');
        $this->line('========================');

        // Check database
        $this->checkDatabase();

        // Check storage
        $this->checkStorage();

        // Check cache
        $this->checkCache();

        // Check environment
        $this->checkEnvironment();

        $this->newLine();
        $this->info('✅ Health check completed');

        return 0;
    }

    private function checkDatabase()
    {
        $this->info('1. Database Check');
        try {
            DB::connection()->getPdo();
            $this->line('   ✅ Database connection: OK');
            $this->line('   📊 Driver: ' . DB::connection()->getDriverName());
        } catch (\Exception $e) {
            $this->error('   ❌ Database connection failed: ' . $e->getMessage());
        }
    }

    private function checkStorage()
    {
        $this->info('2. Storage Check');
        try {
            Storage::disk('local')->put('health-check.txt', 'test');
            Storage::disk('local')->delete('health-check.txt');
            $this->line('   ✅ Storage write/delete: OK');
        } catch (\Exception $e) {
            $this->error('   ❌ Storage check failed: ' . $e->getMessage());
        }
    }

    private function checkCache()
    {
        $this->info('3. Cache Check');
        try {
            cache()->put('health-check', 'test', 1);
            $value = cache()->get('health-check');
            $this->line('   ✅ Cache read/write: OK');
        } catch (\Exception $e) {
            $this->error('   ❌ Cache check failed: ' . $e->getMessage());
        }
    }

    private function checkEnvironment()
    {
        $this->info('4. Environment Check');
        $this->line('   🌱 App Environment: ' . app()->environment());
        $this->line('   🚀 App Debug: ' . (config('app.debug') ? 'Enabled' : 'Disabled'));
        $this->line('   🔗 App URL: ' . config('app.url'));
    }
}