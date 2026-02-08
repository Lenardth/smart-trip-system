<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'generated_at' => 'datetime',
        'travelers' => 'integer',
        'budget' => 'integer',
    ];

    /**
     * Get the user that owns the itinerary.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the duration of the trip in days.
     */
    public function getDurationAttribute()
    {
        return $this->departure_date->diffInDays($this->return_date);
    }

    /**
     * Get the formatted destination name.
     */
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
