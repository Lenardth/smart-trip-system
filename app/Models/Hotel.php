<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'city',
        'country',
        'address',
        'stars',
        'room_type',
        'check_in',
        'check_out',
        'price_per_night',
        'total_price',
        'status',
        'image_url',
        'amenities',
        'notes',
    ];

    protected $casts = [
        'check_in'       => 'date',
        'check_out'      => 'date',
        'price_per_night'=> 'decimal:2',
        'total_price'    => 'decimal:2',
        'amenities'      => 'array',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getNightsAttribute(): int
    {
        if (!$this->check_in || !$this->check_out) return 0;
        return $this->check_in->diffInDays($this->check_out);
    }
}
