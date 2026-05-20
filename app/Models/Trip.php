<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'destination',
        'country',
        'mood',
        'feeling_note',
        'budget',
        'duration',
        'companion',
        'region',
        'accommodation',
        'origin',
        'month',
        'estimated_cost',
        'status',
        'start_date',
        'end_date',
        'notes',
        'description',
        // Editable pricing
        'cost_breakdown',
        'flight_cost',
        'accommodation_cost',
        'activities_cost',
        'food_cost',
        'transport_cost',
        // Editable itinerary
        'daily_itinerary',
        'activities',
        'cities_to_visit',
        // AI metadata
        'travel_tip',
        'visa_info',
        'flight_info',
        'best_time_to_visit',
        'is_good_right_now',
        // Validation data
        'validation_data',
        'weather_data',
        'safety_data',
    ];

    protected $casts = [
        'cost_breakdown'    => 'array',
        'daily_itinerary'   => 'array',
        'activities'        => 'array',
        'cities_to_visit'   => 'array',
        'validation_data'   => 'array',
        'weather_data'      => 'array',
        'safety_data'       => 'array',
        'is_good_right_now' => 'boolean',
        'start_date'        => 'date',
        'end_date'          => 'date',
        'estimated_cost'    => 'float',
        'flight_cost'       => 'float',
        'accommodation_cost'=> 'float',
        'activities_cost'   => 'float',
        'food_cost'         => 'float',
        'transport_cost'    => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getDurationLabelAttribute(): string
    {
        $labels = config('trips.duration_labels', []);

        return $labels[$this->duration] ?? $this->duration ?? '-';
    }

    public function getBudgetLabelAttribute(): string
    {
        $labels = config('trips.budget_labels', []);

        return $labels[$this->budget] ?? $this->budget ?? '-';
    }
}
