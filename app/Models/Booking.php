<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'flight_id',
        'trip_id',
        'booking_reference',
        'seats_booked',
        'total_price',
        'status',
        'passenger_details',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'passenger_details' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (! $booking->booking_reference) {
                $booking->booking_reference = 'SB-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
