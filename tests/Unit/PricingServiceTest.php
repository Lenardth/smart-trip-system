<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;
    private PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PricingService();
    }

    private function makeUser(bool $isPremium = false): User
    {
        $user = new User();
        $user->id = 1;
        $user->is_premium = $isPremium;
        return $user;
    }

    public function test_service_fee_applied_for_regular_user(): void
    {
        $user   = $this->makeUser(false);
        $result = $this->service->calculate(100, $user);

        $this->assertSame(5.0, $result['service_fee']);
        $this->assertSame(105.0, $result['total']);
    }

    public function test_no_service_fee_for_premium_user(): void
    {
        $user   = $this->makeUser(true);
        $result = $this->service->calculate(100, $user);

        $this->assertSame(0, $result['service_fee']);
        $this->assertSame(100.0, $result['total']);
    }

    public function test_subtotal_is_returned_correctly(): void
    {
        $user   = $this->makeUser();
        $result = $this->service->calculate(250.50, $user);

        $this->assertSame(250.50, $result['subtotal']);
    }

    public function test_no_coupon_means_zero_discount(): void
    {
        $user   = $this->makeUser();
        $result = $this->service->calculate(100, $user);

        $this->assertSame(0.0, $result['discount']);
        $this->assertNull($result['coupon']);
    }

    public function test_invalid_coupon_code_sets_error(): void
    {
        $user   = $this->makeUser();
        $result = $this->service->calculate(100, $user, 'FAKECODE');

        $this->assertSame(0.0, $result['discount']);
        $this->assertSame('Coupon code not found.', $result['coupon_error']);
    }

    public function test_service_fee_rate_constant(): void
    {
        $this->assertSame(0.05, PricingService::SERVICE_FEE_RATE);
    }

    public function test_agency_commission_rate_constant(): void
    {
        $this->assertSame(0.10, PricingService::AGENCY_COMMISSION);
    }

    public function test_is_premium_flag_reflected_in_result(): void
    {
        $premium = $this->makeUser(true);
        $regular = $this->makeUser(false);

        $this->assertTrue($this->service->calculate(100, $premium)['is_premium']);
        $this->assertFalse($this->service->calculate(100, $regular)['is_premium']);
    }
}
