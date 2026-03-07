<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->foreignId('continent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->decimal('price_per_person', 10, 2)->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->integer('popularity_score')->default(0);
            $table->string('best_season')->nullable();
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('continent_id');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('popularity_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
