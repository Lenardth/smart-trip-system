<?php

namespace App\Contracts;

interface FlightPricingInterface
{
    public function getPrice(string $from, string $to, string $duration, string $travelClass = 'ECONOMY'): array;
}
