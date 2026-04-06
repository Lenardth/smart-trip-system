<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'itinerary_id',
        'mood',
        'destination',
        'companion',
        'travelers',
        'departure_date',
        'return_date',
        'budget',
        'requirements',
        'generated_at',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'generated_at' => 'datetime',
        'travelers' => 'integer',
        'budget' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute()
    {
        return $this->departure_date->diffInDays($this->return_date);
    }

    public function getFormattedDestinationAttribute()
    {
        $destinations = [
            'bali' => 'Bali, Indonesia',
            'kyoto' => 'Kyoto, Japan',
            'swiss' => 'Swiss Alps, Switzerland',
            'santorini' => 'Santorini, Greece',
            'paris' => 'Paris, France',
            'lisbon' => 'Lisbon, Portugal',
            'bangkok' => 'Bangkok, Thailand',
            'amalfi' => 'Amalfi Coast, Italy',
            'nz' => 'New Zealand',
            'morocco' => 'Morocco',
        ];

        return $destinations[$this->destination] ?? ucfirst($this->destination);
    }
}