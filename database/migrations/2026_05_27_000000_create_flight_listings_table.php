<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('users')->cascadeOnDelete();
            $table->string('airline');
            $table->string('flight_number', 20);
            $table->string('departure_airport');
            $table->string('arrival_airport');
            $table->string('departure_iata', 3)->nullable();
            $table->string('arrival_iata', 3)->nullable();
            $table->date('departure_date');
            $table->string('departure_time', 30)->nullable();
            $table->string('arrival_time', 30)->nullable();
            $table->string('duration')->nullable();
            $table->string('travel_class', 40)->default('ECONOMY');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('seats_total');
            $table->unsignedInteger('seats_available');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();

            $table->index(['departure_iata', 'arrival_iata', 'departure_date', 'status']);
            $table->index(['agency_id', 'status']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('flight_listing_id')->nullable()->after('trip_id')->constrained('flight_listings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('flight_listing_id');
        });

        Schema::dropIfExists('flight_listings');
    }
};
