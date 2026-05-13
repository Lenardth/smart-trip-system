<?php

namespace App\Contracts;

interface GeoapifyInterface
{
    public function geocodeCity(string $city): ?array;

    public function placesByCity(string $city, array $categories = [], int $limit = 100): array;
}
