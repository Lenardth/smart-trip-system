<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'mood' => 'nullable|string|max:100',
            'feeling_note' => 'nullable|string|max:500',
            'budget' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:100',
            'companion' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'accommodation' => 'nullable|string|max:100',
            'origin' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:50',
            'estimated_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'flight_cost' => 'nullable|numeric|min:0',
            'accommodation_cost' => 'nullable|numeric|min:0',
            'activities_cost' => 'nullable|numeric|min:0',
            'food_cost' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'cost_breakdown' => 'nullable|array',
            'daily_itinerary' => 'nullable|array',
            'activities' => 'nullable|array',
            'cities_to_visit' => 'nullable|array',
            'travel_tip' => 'nullable|string|max:1000',
            'visa_info' => 'nullable|string|max:1000',
            'flight_info' => 'nullable|string|max:500',
            'best_time_to_visit' => 'nullable|string|max:255',
            'is_good_right_now' => 'nullable|boolean',
            'validation_data' => 'nullable|array',
            'weather_data' => 'nullable|array',
            'safety_data' => 'nullable|array',
        ];
    }
}
