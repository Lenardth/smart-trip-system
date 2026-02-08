<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('itinerary_id')->unique();
            $table->string('mood');
            $table->string('destination');
            $table->string('companion');
            $table->integer('travelers')->default(1);
            $table->date('departure_date');
            $table->date('return_date');
            $table->integer('budget');
            $table->text('requirements')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            // Add index for faster queries
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itineraries');
    }
};
