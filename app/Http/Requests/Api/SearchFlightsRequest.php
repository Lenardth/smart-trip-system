<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchFlightsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $minDaysAhead = config('flights.min_departure_days_ahead', 2);
        $maxAdults = config('flights.max_adults', 9);

        return [
            'from' => ['required', 'string', 'max:100'],
            'to' => ['required', 'string', 'max:100'],
            'departure_date' => ['required', 'date_format:Y-m-d', 'after:'.now()->addDays($minDaysAhead - 1)->format('Y-m-d')],
            'return_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:departure_date'],
            'adults' => ['nullable', 'integer', 'min:1', 'max:'.$maxAdults],
            'travel_class' => ['nullable', 'string', Rule::in(config('booking.travel_classes'))],
        ];
    }
}
