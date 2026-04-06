<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueRecord extends Model
{
    protected $fillable = [
        'booking_id', 'user_id', 'booking_subtotal',
        'discount_amount', 'service_fee', 'agency_commission',
        'net_revenue', 'coupon_code', 'meta',
    ];

    protected $casts = [
        'booking_subtotal'  => 'decimal:2',
        'discount_amount'   => 'decimal:2',
        'service_fee'       => 'decimal:2',
        'agency_commission' => 'decimal:2',
        'net_revenue'       => 'decimal:2',
        'meta'              => 'array',
    ];

    public function booking() { return $this->belongsTo(Booking::class); }
    public function user()    { return $this->belongsTo(User::class); }
}