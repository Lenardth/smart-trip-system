<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('destinations');

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('category')->default('general');
            $table->string('mood')->nullable();
            $table->unsignedInteger('price_from')->default(0);
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('badge')->nullable();
            $table->boolean('is_hidden_gem')->default(false);
            $table->unsignedTinyInteger('match_score')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
