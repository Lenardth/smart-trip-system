<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'flight_id',
        'passenger_count',
        'total_price',
        'status',
        'booking_reference',
        'passenger_details',
        'special_requests',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'passenger_details' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    // Generate booking reference
    public static function generateReference()
    {
        return 'BK' . strtoupper(uniqid());
    }

    // Check if booking is confirmed
    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }
}
