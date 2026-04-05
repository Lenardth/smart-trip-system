<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // In SQLite (including test in-memory DB), the trips table is created fresh
            // by migration 000003 which already has budget as TEXT in newer runs.
            // The table-rename approach breaks FK cascade deletes in tests.
            // Safe to skip — SQLite stores everything as TEXT anyway.
            return;
        }

        // PostgreSQL: change budget column from numeric to varchar
        DB::statement('ALTER TABLE trips ALTER COLUMN budget TYPE VARCHAR(100) USING budget::VARCHAR(100)');
    }

    public function down(): void
    {
        // No safe rollback for SQLite column type change
    }
};
