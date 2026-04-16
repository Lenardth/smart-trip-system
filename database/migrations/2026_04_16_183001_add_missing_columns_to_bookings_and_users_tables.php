<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('bookings', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('bookings', 'service_fee')) {
                $table->decimal('service_fee', 10, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('bookings', 'coupon_code')) {
                $table->string('coupon_code', 32)->nullable()->after('service_fee');
            }
        });

        // Add missing columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_premium')) {
                $table->boolean('is_premium')->default(false)->after('bio');
            }
            if (!Schema::hasColumn('users', 'premium_until')) {
                $table->timestamp('premium_until')->nullable()->after('is_premium');
            }
            if (!Schema::hasColumn('users', 'location')) {
                $table->string('location')->nullable()->after('bio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount_amount', 'service_fee', 'coupon_code']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_premium', 'premium_until', 'location']);
        });
    }
};
