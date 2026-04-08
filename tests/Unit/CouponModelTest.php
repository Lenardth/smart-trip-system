<?php

namespace Tests\Unit;

use App\Models\Coupon;
use Tests\TestCase;

class CouponModelTest extends TestCase
{
    private function makeCoupon(array $attrs = []): Coupon
    {
        $defaults = [
            'is_active'    => true,
            'expires_at'   => null,
            'uses_limit'   => null,
            'uses_total'   => 0,
            'uses_per_user'=> 1,
            'min_order'    => 0,
            'max_discount' => null,
            'type'         => 'percent',
            'value'        => 10,
        ];

        $coupon = new Coupon();
        foreach (array_merge($defaults, $attrs) as $key => $value) {
            $coupon->$key = $value;
        }
        return $coupon;
    }

    public function test_inactive_coupon_is_invalid(): void
    {
        $coupon = $this->makeCoupon(['is_active' => false]);
        [$valid, $message] = $coupon->isValid(100, 1);
        $this->assertFalse($valid);
        $this->assertStringContainsString('inactive', $message);
    }

    public function test_expired_coupon_is_invalid(): void
    {
        $coupon = $this->makeCoupon(['expires_at' => now()->subDay()]);
        [$valid, $message] = $coupon->isValid(100, 1);
        $this->assertFalse($valid);
        $this->assertStringContainsString('expired', $message);
    }

    public function test_coupon_exceeding_usage_limit_is_invalid(): void
    {
        $coupon = $this->makeCoupon(['uses_limit' => 5, 'uses_total' => 5]);
        [$valid, $message] = $coupon->isValid(100, 1);
        $this->assertFalse($valid);
        $this->assertStringContainsString('usage limit', $message);
    }

    public function test_coupon_below_min_order_is_invalid(): void
    {
        $coupon = $this->makeCoupon(['min_order' => 200]);
        [$valid, $message] = $coupon->isValid(100, 1);
        $this->assertFalse($valid);
        $this->assertStringContainsString('Minimum order', $message);
    }

    public function test_percent_discount_calculation(): void
    {
        $coupon = $this->makeCoupon(['type' => 'percent', 'value' => 20]);
        $this->assertSame(20.0, $coupon->calculateDiscount(100));
    }

    public function test_fixed_discount_calculation(): void
    {
        $coupon = $this->makeCoupon(['type' => 'fixed', 'value' => 15]);
        $this->assertSame(15.0, $coupon->calculateDiscount(100));
    }

    public function test_discount_capped_by_max_discount(): void
    {
        $coupon = $this->makeCoupon(['type' => 'percent', 'value' => 50, 'max_discount' => 30]);
        $this->assertSame(30.0, $coupon->calculateDiscount(100));
    }

    public function test_discount_cannot_exceed_subtotal(): void
    {
        $coupon = $this->makeCoupon(['type' => 'fixed', 'value' => 200]);
        $this->assertSame(50.0, $coupon->calculateDiscount(50));
    }

    public function test_generate_code_has_correct_prefix(): void
    {
        $code = Coupon::generateCode('TEST');
        $this->assertStringStartsWith('TEST', $code);
        $this->assertSame(10, strlen($code)); // 4 prefix + 6 random
    }

    public function test_generate_code_default_prefix(): void
    {
        $code = Coupon::generateCode();
        $this->assertStringStartsWith('SB', $code);
    }
}
