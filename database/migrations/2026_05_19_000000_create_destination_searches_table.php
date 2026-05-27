<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('query', 120);
            $table->string('resolved_query', 120)->nullable();
            $table->string('region_code', 10)->nullable();
            $table->string('mood', 60)->nullable();
            $table->integer('results_count')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->string('source', 20)->default('web');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['query', 'created_at']);
            $table->index(['region_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_searches');
    }
};
