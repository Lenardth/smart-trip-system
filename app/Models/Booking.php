<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trip_id',
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
        'total_price'       => 'decimal:2',
        'subtotal'          => 'decimal:2',
        'discount_amount'   => 'decimal:2',
        'service_fee'       => 'decimal:2',
        'passenger_details' => AsArrayObject::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->booking_reference ??= 'SB-' . strtoupper(Str::random(8));
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function trip() { return $this->belongsTo(Trip::class); }

    public function getTypeAttribute(): string
    {
        if ($this->passenger_details && ($this->passenger_details['type'] ?? '') === 'accommodation') return 'accommodation';
        if ($this->passenger_details && isset($this->passenger_details['airline'])) return 'flight';
        return 'unknown';
    }

    public function getTitleAttribute(): string
    {
        $pd = $this->passenger_details;
        if ($pd) {
            $dep = $pd['departure_airport'] ?? null;
            $arr = $pd['arrival_airport']   ?? null;
            if ($dep && $arr) return "{$dep} → {$arr}";

            $name = $pd['name'] ?? null;
            if ($name) return $name;

            $city = $pd['city'] ?? null;
            if ($city) return "Stay in {$city}";
        }

        return 'Booking #' . $this->booking_reference;
    }

    public function scopeConfirmed($query)       { return $query->where('status', 'confirmed'); }
    public function scopePending($query)         { return $query->where('status', 'pending');   }
    public function scopeCancelled($query)       { return $query->where('status', 'cancelled'); }
    public function scopeCompleted($query)       { return $query->where('status', 'completed'); }
    public function scopeActive($query)          { return $query->whereIn('status', ['confirmed', 'pending']); }
    public function scopeByUser($query, $userId) { return $query->where('user_id', $userId); }
}
