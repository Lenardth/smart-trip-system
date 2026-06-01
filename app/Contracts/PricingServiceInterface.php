<?php

namespace App\Contracts;

use App\Models\Booking;
use App\Models\User;

interface PricingServiceInterface
{
    public function calculate(float $subtotal, User $user, ?string $couponCode = null): array;

    public function recordRevenue(Booking $booking, array $pricing): void;

    public function updateRevenue(Booking $booking, array $pricing): void;

    public function validateCoupon(string $code, float $subtotal, int $userId): array;
}
