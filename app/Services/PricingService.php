<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUse;
use App\Models\RevenueRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PricingService
{
    const SERVICE_FEE_RATE   = 0.05;  // 5%
    const AGENCY_COMMISSION  = 0.10;  // 10% of booking goes to platform from agency bookings

    public function calculate(float $subtotal, User $user, ?string $couponCode = null): array
    {
        $discount   = 0;
        $coupon     = null;
        $couponError = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', strtoupper($couponCode))->first();
            if ($coupon) {
                [$valid, $couponError] = $coupon->isValid($subtotal, $user->id);
                if ($valid) {
                    $discount = $coupon->calculateDiscount($subtotal);
                } else {
                    $coupon = null;
                }
            } else {
                $couponError = 'Coupon code not found.';
            }
        }

        $afterDiscount = max(0, $subtotal - $discount);

        // Premium users pay no service fee
        $serviceFee = $user->is_premium ? 0 : round($afterDiscount * self::SERVICE_FEE_RATE, 2);

        $total = $afterDiscount + $serviceFee;

        return [
            'subtotal'     => round($subtotal, 2),
            'discount'     => round($discount, 2),
            'service_fee'  => $serviceFee,
            'total'        => round($total, 2),
            'coupon'       => $coupon,
            'coupon_error' => $couponError,
            'is_premium'   => $user->is_premium,
        ];
    }

    public function recordRevenue(\App\Models\Booking $booking, array $pricing): void
    {
        $agencyCommission = round($pricing['subtotal'] * self::AGENCY_COMMISSION, 2);
        $netRevenue       = $pricing['service_fee'] + $agencyCommission - $pricing['discount'];

        RevenueRecord::create([
            'booking_id'        => $booking->id,
            'user_id'           => $booking->user_id,
            'booking_subtotal'  => $pricing['subtotal'],
            'discount_amount'   => $pricing['discount'],
            'service_fee'       => $pricing['service_fee'],
            'agency_commission' => $agencyCommission,
            'net_revenue'       => max(0, $netRevenue),
            'coupon_code'       => $pricing['coupon']?->code,
        ]);

        if ($pricing['coupon']) {
            CouponUse::create([
                'coupon_id'       => $pricing['coupon']->id,
                'user_id'         => $booking->user_id,
                'booking_id'      => $booking->id,
                'discount_amount' => $pricing['discount'],
            ]);
            $pricing['coupon']->increment('uses_total');
        }
    }

    public function validateCoupon(string $code, float $subtotal, int $userId): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();
        if (!$coupon) return ['valid' => false, 'message' => 'Coupon not found.'];

        [$valid, $message] = $coupon->isValid($subtotal, $userId);
        if (!$valid) return ['valid' => false, 'message' => $message];

        $discount = $coupon->calculateDiscount($subtotal);
        return [
            'valid'       => true,
            'discount'    => $discount,
            'description' => $coupon->description ?? ($coupon->type === 'percent'
                ? $coupon->value . '% off'
                : '$' . number_format($coupon->value, 2) . ' off'),
        ];
    }
}
