<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlightListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'airline',
        'flight_number',
        'departure_airport',
        'arrival_airport',
        'departure_iata',
        'arrival_iata',
        'departure_date',
        'departure_time',
        'arrival_time',
        'duration',
        'travel_class',
        'price',
        'seats_total',
        'seats_available',
        'status',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'price' => 'decimal:2',
        'seats_total' => 'integer',
        'seats_available' => 'integer',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agency_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
