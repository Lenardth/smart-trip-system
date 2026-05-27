<?php

namespace App\Http\Requests\Api;

class UpdateTripRequest extends StoreTripRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'destination' => 'sometimes|string|max:255',
            'status' => 'nullable|in:planned,ongoing,completed,cancelled',
            'notes' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
