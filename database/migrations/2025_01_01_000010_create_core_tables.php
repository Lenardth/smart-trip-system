<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Trip Moods
        if (!Schema::hasTable('trip_moods')) {
            Schema::create('trip_moods', function (Blueprint $table) {
                $table->id();
                $table->string('label', 80)->unique();
                $table->string('label_normalized', 80)->unique();
                $table->unsignedInteger('use_count')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // Trips
        if (!Schema::hasTable('trips')) {
            Schema::create('trips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title')->nullable();
                $table->string('destination');
                $table->string('country')->nullable();
                $table->string('mood')->nullable();
                $table->text('feeling_note')->nullable();
                $table->string('budget')->nullable();
                $table->string('duration')->nullable();
                $table->string('companion')->nullable();
                $table->string('region')->nullable();
                $table->string('accommodation')->nullable();
                $table->string('origin')->nullable();
                $table->string('month')->nullable();
                $table->decimal('estimated_cost', 10, 2)->nullable();
                $table->string('status')->default('planned');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->text('notes')->nullable();
                $table->string('travel_tip')->nullable();
                $table->string('visa_info')->nullable();
                $table->string('flight_info')->nullable();
                $table->string('best_time_to_visit')->nullable();
                $table->boolean('is_good_right_now')->default(false);
                $table->json('top_activities')->nullable();
                $table->json('daily_itinerary')->nullable();
                $table->json('cost_breakdown')->nullable();
                $table->timestamps();
            });
        }

        // Accommodations
        if (!Schema::hasTable('accommodations')) {
            Schema::create('accommodations', function (Blueprint $table) {
                $table->id();
                $table->string('geoapify_id')->unique()->nullable();
                $table->string('name');
                $table->string('city');
                $table->string('country')->nullable();
                $table->string('style')->default('hotel');
                $table->string('budget_tier')->default('mid');
                $table->decimal('nightly_rate', 8, 2)->default(0);
                $table->decimal('rating', 3, 1)->nullable();
                $table->integer('review_count')->nullable();
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->text('description')->nullable();
                $table->string('image_url')->nullable();
                $table->json('amenities')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Accommodation Searches
        if (!Schema::hasTable('accommodation_searches')) {
            Schema::create('accommodation_searches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('query');
                $table->string('style')->nullable();
                $table->string('budget_tier')->nullable();
                $table->integer('results_count')->default(0);
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        // Coupons
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code', 32)->unique();
                $table->enum('type', ['percent', 'fixed'])->default('percent');
                $table->decimal('value', 8, 2);
                $table->decimal('min_order', 8, 2)->default(0);
                $table->decimal('max_discount', 8, 2)->nullable();
                $table->unsignedInteger('uses_total')->default(0);
                $table->unsignedInteger('uses_limit')->nullable();
                $table->unsignedInteger('uses_per_user')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // Coupon Uses
        if (!Schema::hasTable('coupon_uses')) {
            Schema::create('coupon_uses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->decimal('discount_amount', 8, 2);
                $table->timestamps();
            });
        }

        // Bookings
        if (!Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('trip_id')->nullable()->constrained()->nullOnDelete();
                $table->string('booking_reference')->unique();
                $table->integer('seats_booked')->default(1);
                $table->decimal('subtotal', 10, 2)->nullable();
                $table->decimal('discount_amount', 8, 2)->default(0);
                $table->decimal('service_fee', 8, 2)->default(0);
                $table->decimal('total_price', 10, 2)->default(0);
                $table->string('coupon_code', 32)->nullable();
                $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('confirmed');
                $table->json('passenger_details')->nullable();
                $table->timestamps();
            });
        }

        // Revenue Records
        if (!Schema::hasTable('revenue_records')) {
            Schema::create('revenue_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('booking_subtotal', 10, 2);
                $table->decimal('discount_amount', 8, 2)->default(0);
                $table->decimal('service_fee', 8, 2)->default(0);
                $table->decimal('agency_commission', 8, 2)->default(0);
                $table->decimal('net_revenue', 10, 2);
                $table->string('coupon_code')->nullable();
                $table->timestamps();
            });
        }

        // Add premium columns to users if missing
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_premium')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_premium')->default(false)->after('remember_token');
                $table->timestamp('premium_until')->nullable()->after('is_premium');
                $table->string('agency_name')->nullable()->after('name');
                $table->string('user_type')->default('user')->after('agency_name');
                $table->string('profile_picture')->nullable()->after('user_type');
                $table->text('bio')->nullable()->after('profile_picture');
                $table->timestamp('last_login_at')->nullable()->after('bio');
                $table->string('last_login_ip')->nullable()->after('last_login_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_records');
        Schema::dropIfExists('coupon_uses');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('accommodation_searches');
        Schema::dropIfExists('accommodations');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('trip_moods');
    }
};
