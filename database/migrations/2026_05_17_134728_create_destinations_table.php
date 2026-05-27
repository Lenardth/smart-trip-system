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
            $table->string('country');
            $table->string('region');
            $table->text('description');
            $table->string('image_url');
            $table->decimal('price_from', 10, 2);
            $table->json('tags')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_editors_choice')->default(false);
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'is_featured', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
