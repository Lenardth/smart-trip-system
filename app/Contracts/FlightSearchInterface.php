<?php

namespace App\Contracts;

interface FlightSearchInterface
{
    public function resolveIataCode(string $input): ?string;

    public function searchFlights(
        string $from,
        string $to,
        string $departureDate,
        int $adults = 1,
        string $travelClass = 'ECONOMY',
        ?string $returnDate = null
    ): array;

    public function searchAirports(string $keyword): array;
}
