<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Coupons / promo codes
        if (Schema::hasTable('coupons')) {
            return;
        }

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

        // Track which user used which coupon
        Schema::create('coupon_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount_amount', 8, 2);
            $table->timestamps();
        });

        // Premium subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('plan', ['premium'])->default('premium');
            $table->decimal('amount_paid', 8, 2);
            $table->enum('status', ['active', 'cancelled', 'expired'])->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });

        // Revenue ledger — every booking generates a revenue record
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
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // Add monetization columns to bookings
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'subtotal'))         $table->decimal('subtotal', 10, 2)->nullable()->after('total_price');
            if (!Schema::hasColumn('bookings', 'discount_amount'))  $table->decimal('discount_amount', 8, 2)->default(0)->after('subtotal');
            if (!Schema::hasColumn('bookings', 'service_fee'))      $table->decimal('service_fee', 8, 2)->default(0)->after('discount_amount');
            if (!Schema::hasColumn('bookings', 'coupon_code'))      $table->string('coupon_code', 32)->nullable()->after('service_fee');
        });

        // Add subscription column to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_premium'))    $table->boolean('is_premium')->default(false)->after('user_type');
            if (!Schema::hasColumn('users', 'premium_until')) $table->timestamp('premium_until')->nullable()->after('is_premium');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn($t) => $t->dropColumn(['is_premium', 'premium_until']));
        Schema::table('bookings', fn($t) => $t->dropColumn(['subtotal', 'discount_amount', 'service_fee', 'coupon_code']));
        Schema::dropIfExists('revenue_records');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('coupon_uses');
        Schema::dropIfExists('coupons');
    }
};
