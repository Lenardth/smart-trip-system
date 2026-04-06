<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'flight_number',
        'airline',
        'departure_city',
        'arrival_city',
        'departure_time',
        'arrival_time',
        'price',
        'seats_available',
        'total_seats',
        'class',
        'description',
        'is_active',
        'aircraft_type',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'price' => 'decimal:2',
        'seats_available' => 'integer',
        'total_seats' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByAgency($query, $agencyId)
    {
        return $query->where('user_id', $agencyId);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function isAvailable()
    {
        return $this->is_active && $this->seats_available > 0;
    }

    public function hasAvailableSeats($seats = 1)
    {
        return $this->seats_available >= $seats;
    }

    public function formatDuration()
    {
        if ($this->departure_time && $this->arrival_time) {
            $diff = $this->departure_time->diff($this->arrival_time);
            return $diff->format('%hh %im');
        }
        return 'N/A';
    }
}