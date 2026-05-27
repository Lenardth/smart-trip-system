<?php

namespace App\Contracts;

interface GeoapifyInterface
{
    public function geocodeCity(string $city): ?array;

    public function placesByCity(string $city, array $categories = [], int $limit = 100): array;

    public function searchDestinations(string $query, ?string $countryCode = null, ?string $mood = null, int $limit = 20): array;

    public function getCountries(): array;

    public function countryDetails(string $countryCode): array;

    public function searchWikipediaSummary(string $query): array;
}
