<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerary_destinations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();   // e.g. 'bali', 'paris'
            $table->string('label');                // e.g. 'Bali, Indonesia'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('itinerary_day_plans', function (Blueprint $table) {
            $table->id();
            $table->string('destination_code', 32)->index();
            $table->unsignedTinyInteger('day');
            $table->string('title');
            $table->text('description');
            $table->timestamps();

            $table->index(['destination_code', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_day_plans');
        Schema::dropIfExists('itinerary_destinations');
    }
};
