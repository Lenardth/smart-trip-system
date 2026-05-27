<?php

namespace App\Services;

use App\Models\Trip;

class TripService
{
    public function allForUser(int $userId)
    {
        return Trip::where('user_id', $userId)->latest()->get();
    }

    public function upcomingForUser(int $userId)
    {
        return Trip::where('user_id', $userId)
            ->where('status', 'planned')
            ->latest()
            ->get();
    }

    public function createForUser(array $data, int $userId): array
    {
        $exists = Trip::where('user_id', $userId)
            ->where('destination', $data['destination'])
            ->where('budget', $data['budget'] ?? null)
            ->where('duration', $data['duration'] ?? null)
            ->where('status', 'planned')
            ->exists();

        if ($exists) {
            return [
                'success' => false,
                'status' => 409,
                'message' => 'This trip is already saved to your dashboard.',
            ];
        }

        $days = config('trips.duration_days.' . ($data['duration'] ?? ''), config('trips.default_duration_days', 7));
        $trip = Trip::create([
            ...$data,
            'user_id'    => $userId,
            'status'     => 'planned',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addDays($days)->toDateString(),
            'title'      => $data['destination'] . (!empty($data['country']) ? ', ' . $data['country'] : ''),
        ]);

        return [
            'success' => true,
            'status' => 201,
            'message' => 'Trip saved to your dashboard.',
            'trip' => $trip,
        ];
    }

    public function updateForUser(array $data, int $tripId, int $userId): Trip
    {
        $trip = Trip::where('user_id', $userId)->findOrFail($tripId);

        if ($this->hasCostChanges($data)) {
            $data['estimated_cost'] =
                ($data['flight_cost']        ?? $trip->flight_cost        ?? 0) +
                ($data['accommodation_cost'] ?? $trip->accommodation_cost ?? 0) +
                ($data['activities_cost']    ?? $trip->activities_cost    ?? 0) +
                ($data['food_cost']          ?? $trip->food_cost          ?? 0) +
                ($data['transport_cost']     ?? $trip->transport_cost     ?? 0);
        }

        $trip->update($data);

        return $trip->fresh();
    }

    public function deleteForUser(int $tripId, int $userId): void
    {
        Trip::where('user_id', $userId)
            ->where('id', $tripId)
            ->firstOrFail()
            ->delete();
    }

    private function hasCostChanges(array $data): bool
    {
        return isset($data['flight_cost'])
            || isset($data['accommodation_cost'])
            || isset($data['activities_cost'])
            || isset($data['food_cost'])
            || isset($data['transport_cost']);
    }
}
