<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\FlightListing;
use App\Models\User;

class AgencyFlightService
{
    public function dashboardData(User $agency): array
    {
        $listings = FlightListing::where('agency_id', $agency->id)
            ->latest()
            ->paginate(12);

        return [
            'listings' => $listings,
            'publishedCount' => FlightListing::where('agency_id', $agency->id)->where('status', 'published')->count(),
            'draftCount' => FlightListing::where('agency_id', $agency->id)->where('status', 'draft')->count(),
            'incomingCount' => $this->incomingBookings($agency)->total(),
        ];
    }

    public function create(User $agency, array $data): FlightListing
    {
        $data['agency_id'] = $agency->id;
        $data['departure_iata'] = $this->normaliseIata($data['departure_iata'] ?? null);
        $data['arrival_iata'] = $this->normaliseIata($data['arrival_iata'] ?? null);
        $data['seats_available'] = (int) $data['seats_total'];

        return FlightListing::create($data);
    }

    public function incomingBookings(User $agency)
    {
        return Booking::with(['user', 'flightListing'])
            ->whereHas('flightListing', fn ($query) => $query->where('agency_id', $agency->id))
            ->latest()
            ->paginate(15);
    }

    private function normaliseIata(?string $code): ?string
    {
        return $code ? strtoupper($code) : null;
    }
}
