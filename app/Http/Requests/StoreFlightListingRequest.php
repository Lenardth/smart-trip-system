<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFlightListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->user_type === 'agency';
    }

    public function rules(): array
    {
        return [
            'airline' => 'required|string|max:100',
            'flight_number' => 'required|string|max:20',
            'departure_airport' => 'required|string|max:100',
            'arrival_airport' => 'required|string|max:100',
            'departure_iata' => 'nullable|string|size:3',
            'arrival_iata' => 'nullable|string|size:3',
            'departure_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'departure_time' => 'nullable|string|max:30',
            'arrival_time' => 'nullable|string|max:30',
            'duration' => 'nullable|string|max:50',
            'travel_class' => ['required', 'string', Rule::in(config('booking.travel_classes'))],
            'price' => 'required|numeric|min:0',
            'seats_total' => 'required|integer|min:1|max:999',
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}
