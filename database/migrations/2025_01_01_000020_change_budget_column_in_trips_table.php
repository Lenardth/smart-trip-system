<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support modifyColumn, so we recreate via raw SQL
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');
            DB::statement('ALTER TABLE trips RENAME TO trips_old;');
            DB::statement('
                CREATE TABLE trips (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    destination_id INTEGER,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    start_date DATE NOT NULL,
                    end_date DATE NOT NULL,
                    status VARCHAR(255) NOT NULL DEFAULT \'planned\',
                    budget VARCHAR(100),
                    travelers_count INTEGER NOT NULL DEFAULT 1,
                    notes TEXT,
                    destination VARCHAR(255),
                    country VARCHAR(255),
                    mood VARCHAR(100),
                    duration VARCHAR(100),
                    companion VARCHAR(100),
                    region VARCHAR(100),
                    accommodation VARCHAR(100),
                    origin VARCHAR(255),
                    month VARCHAR(50),
                    estimated_cost INTEGER UNSIGNED,
                    feeling_note VARCHAR(500),
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL
                )
            ');
            DB::statement('INSERT INTO trips SELECT * FROM trips_old;');
            DB::statement('DROP TABLE trips_old;');
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // PostgreSQL: use raw ALTER TABLE to avoid doctrine/dbal dependency
            DB::statement('ALTER TABLE trips ALTER COLUMN budget TYPE VARCHAR(100) USING budget::VARCHAR(100)');
        }
    }

    public function down(): void
    {
        // No safe rollback for SQLite column type change
    }
};
