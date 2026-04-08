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
        'flight_id',
        'trip_id',
        'booking_reference',
        'seats_booked',
        'total_price',
        'status',
        'passenger_details',
    ];

    protected $casts = [
        'total_price'       => 'decimal:2',
        'passenger_details' => AsArrayObject::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->booking_reference ??= 'SB-' . strtoupper(Str::random(8));
        });
    }

    public function user()   { return $this->belongsTo(User::class);   }
    public function flight() { return $this->belongsTo(Flight::class); }
    public function trip()   { return $this->belongsTo(Trip::class);   }

    public function getTypeAttribute(): string
    {
        if ($this->flight_id) return 'flights';
        if ($this->trip_id)   return 'trips';
        if ($this->passenger_details && ($this->passenger_details['type'] ?? '') === 'accommodation') return 'hotels';
        return 'unknown';
    }

    public function getTitleAttribute(): string
    {
        if ($this->flight) {
            $from = $this->flight->departure_city;
            $to   = $this->flight->arrival_city;
            return "{$from} (" . strtoupper(substr($from, 0, 3)) . ") ? "
                 . "{$to} ("   . strtoupper(substr($to,   0, 3)) . ")";
        }
        if ($this->trip) return $this->trip->name;
        if ($this->passenger_details?->name) return $this->passenger_details['name'];
        return 'Booking #' . $this->booking_reference;
    }

    public function scopeConfirmed($query)       { return $query->where('status', 'confirmed'); }
    public function scopePending($query)         { return $query->where('status', 'pending');   }
    public function scopeCancelled($query)       { return $query->where('status', 'cancelled'); }
    public function scopeCompleted($query)       { return $query->where('status', 'completed'); }
    public function scopeActive($query)          { return $query->whereIn('status', ['confirmed', 'pending']); }
    public function scopeByUser($query, $userId) { return $query->where('user_id', $userId); }
}
