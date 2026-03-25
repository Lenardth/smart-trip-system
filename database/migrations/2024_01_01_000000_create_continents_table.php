<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE TABLE continents (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(2) NOT NULL UNIQUE,
                emoji VARCHAR(10),
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            );
        ");

        DB::unprepared("
            INSERT INTO continents (name, code, emoji, created_at, updated_at) VALUES
            ('Africa','AF','🌍',NOW(),NOW()),
            ('Antarctica','AN','🧊',NOW(),NOW()),
            ('Asia','AS','🌏',NOW(),NOW()),
            ('Europe','EU','🌍',NOW(),NOW()),
            ('North America','NA','🌎',NOW(),NOW()),
            ('Oceania','OC','🌏',NOW(),NOW()),
            ('South America','SA','🌎',NOW(),NOW());
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('continents');
    }
};
