<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mood_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('slug', 60)->unique();
            $table->string('description', 120)->nullable();
            $table->string('icon', 60)->default('compass');
            $table->string('gradient_from', 20)->default('#e3f2fd');
            $table->string('gradient_to', 20)->default('#bbdefb');
            $table->string('color', 20)->default('#1976d2');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mood_categories');
    }
};
