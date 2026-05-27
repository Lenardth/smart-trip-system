<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFlightBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'airline' => 'required|string|max:100',
            'flight_number' => 'required|string|max:20',
            'departure_airport' => 'nullable|string|max:100',
            'arrival_airport' => 'nullable|string|max:100',
            'departure_time' => 'nullable|string|max:30',
            'arrival_time' => 'nullable|string|max:30',
            'departure_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'duration' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'adults' => 'nullable|integer|min:1|max:9',
            'travel_class' => ['nullable', 'string', Rule::in(config('booking.travel_classes'))],
            'coupon_code' => 'nullable|string|max:32',
        ];
    }
}
