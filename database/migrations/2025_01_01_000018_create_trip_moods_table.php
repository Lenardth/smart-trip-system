<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_moods', function (Blueprint $table) {
            $table->id();
            $table->string('label', 80);
            $table->string('label_normalized', 80)->unique();
            $table->unsignedInteger('use_count')->default(1);
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['use_count', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_moods');
    }
};
