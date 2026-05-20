<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueRecord extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'booking_subtotal',
        'discount_amount',
        'service_fee',
        'agency_commission',
        'net_revenue',
        'coupon_code',
        'meta',
    ];

    protected $casts = [
        'booking_subtotal'  => 'decimal:2',
        'discount_amount'   => 'decimal:2',
        'service_fee'       => 'decimal:2',
        'agency_commission' => 'decimal:2',
        'net_revenue'       => 'decimal:2',
        'meta'              => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
