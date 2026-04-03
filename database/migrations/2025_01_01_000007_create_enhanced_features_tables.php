<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('flights')) {
            Schema::create('flights', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('flight_number');
                $table->string('airline');
                $table->string('departure_city');
                $table->string('arrival_city');
                $table->dateTime('departure_time');
                $table->dateTime('arrival_time');
                $table->decimal('price', 10, 2);
                $table->integer('seats_available');
                $table->enum('class', ['economy', 'business', 'first']);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('flight_id')->nullable();
                $table->unsignedBigInteger('hotel_id')->nullable();
                $table->unsignedBigInteger('trip_id')->nullable();
                $table->integer('seats_booked')->default(1);
                $table->integer('passenger_count')->default(1);
                $table->decimal('total_price', 10, 2);
                $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
                $table->string('booking_reference')->unique()->nullable();
                $table->json('passenger_details')->nullable();
                $table->text('special_requests')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('memories')) {
            Schema::create('memories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('type', ['photo', 'video']);
                $table->string('file_path');
                $table->string('thumbnail_path')->nullable();
                $table->json('metadata')->nullable();
                $table->boolean('is_public')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('memory_frames')) {
            Schema::create('memory_frames', function (Blueprint $table) {
                $table->id();
                $table->foreignId('memory_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('frame_type');
                $table->json('frame_settings')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_preferences')) {
            Schema::create('user_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('preference_key');
                $table->text('preference_value')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'preference_key']);
            });
        }

        if (!Schema::hasTable('agency_profiles')) {
            Schema::create('agency_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('agency_name');
                $table->string('business_registration')->nullable();
                $table->string('website')->nullable();
                $table->string('phone')->nullable();
                $table->text('description')->nullable();
                $table->json('social_links')->nullable();
                $table->decimal('rating', 3, 2)->default(0);
                $table->integer('total_reviews')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_profiles');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('memory_frames');
        Schema::dropIfExists('memories');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('flights');
    }
};
