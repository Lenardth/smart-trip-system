<?php

namespace App\Models;

use App\Models\Traits\HasStatusScopes;
use App\Models\Traits\HasUserScope;
use App\Services\BookingTypeResolver;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, HasStatusScopes, HasUserScope;

    protected $fillable = [
        'user_id',
        'trip_id',
        'flight_listing_id',
        'booking_reference',
        'seats_booked',
        'total_price',
        'subtotal',
        'discount_amount',
        'service_fee',
        'coupon_code',
        'status',
        'passenger_details',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'seats_booked' => 'integer',
        'passenger_details' => AsArrayObject::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->booking_reference ??= 'SB-'.strtoupper(Str::random(8));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function flightListing(): BelongsTo
    {
        return $this->belongsTo(FlightListing::class);
    }

    public function getTypeAttribute(): string
    {
        return BookingTypeResolver::resolve($this->passenger_details, $this->trip_id);
    }

    public function getTitleAttribute(): string
    {
        return BookingTypeResolver::resolveTitle($this->passenger_details, $this->booking_reference);
    }
}
