<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE TABLE notifications (
                id UUID PRIMARY KEY,
                type VARCHAR(255) NOT NULL,
                notifiable_type VARCHAR(255) NOT NULL,
                notifiable_id BIGINT NOT NULL,
                data TEXT NOT NULL,
                read_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            );

            CREATE INDEX notifications_notifiable_index
            ON notifications (notifiable_type, notifiable_id);
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
