<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoapifyService
{
    private array $overpassUrls = [
        'https://overpass.kumi.systems/api/interpreter',
        'https://maps.mail.ru/osm/tools/overpass/api/interpreter',
        'https://overpass-api.de/api/interpreter',
    ];

    private string $nominatimUrl = 'https://nominatim.openstreetmap.org';

    public function geocodeCity(string $city): ?array
    {
        $response = Http::timeout(10)
            ->withHeaders(['User-Agent' => 'SmartTripPlanner/1.0'])
            ->get("{$this->nominatimUrl}/search", [
                'q'              => $city,
                'format'         => 'json',
                'limit'          => 1,
                'addressdetails' => 1,
            ]);

        if (! $response->successful() || empty($response->json())) {
            return null;
        }

        $r   = $response->json()[0];
        $lat = (float) $r['lat'];
        $lon = (float) $r['lon'];

        $radius = 5000;
        if (isset($r['boundingbox'])) {
            [$minLat, $maxLat, $minLon, $maxLon] = $r['boundingbox'];
            $latKm    = abs($maxLat - $minLat) * 111;
            $lonKm    = abs($maxLon - $minLon) * 111 * cos(deg2rad($lat));
            $radiusKm = min(max($latKm, $lonKm) / 2, 50);
            $radius   = (int) ($radiusKm * 1000);
        }

        return ['lat' => $lat, 'lon' => $lon, 'radius' => $radius];
    }

    public function placesByCity(
        string $city,
        array $categories = [],
        int $limit = 100
    ): array {
        $geo = $this->geocodeCity($city);

        if (! $geo) {
            Log::warning('Overpass: geocode failed', ['city' => $city]);
            return [];
        }

        $lat    = $geo['lat'];
        $lon    = $geo['lon'];
        $radius = $geo['radius'];

        $query = "[out:json][timeout:20];(node[\"tourism\"~\"hotel|hostel|guest_house|motel|apartment|resort\"](around:{$radius},{$lat},{$lon});way[\"tourism\"~\"hotel|hostel|guest_house|motel|resort\"](around:{$radius},{$lat},{$lon}););out center {$limit};";

        foreach ($this->overpassUrls as $url) {
            try {
                $response = Http::timeout(25)
                    ->withHeaders(['User-Agent' => 'SmartTripPlanner/1.0'])
                    ->asForm()
                    ->post($url, ['data' => $query]);

                if ($response->successful()) {
                    $elements = $response->json()['elements'] ?? [];
                    return array_map(fn($el) => $this->mapElement($el, $city), $elements);
                }

                Log::warning('Overpass mirror failed', ['url' => $url, 'status' => $response->status()]);
            } catch (\Throwable $e) {
                Log::warning('Overpass mirror exception', ['url' => $url, 'error' => $e->getMessage()]);
            }
        }

        Log::error('All Overpass mirrors failed', ['city' => $city]);
        return [];
    }

    private function mapElement(array $el, string $city): array
    {
        $tags    = $el['tags'] ?? [];
        $elLat   = $el['lat'] ?? $el['center']['lat'] ?? null;
        $elLon   = $el['lon'] ?? $el['center']['lon'] ?? null;
        $tourism = $tags['tourism'] ?? 'hotel';

        return [
            'properties' => [
                'place_id'   => 'osm_' . $el['type'] . '_' . $el['id'],
                'name'       => $tags['name'] ?? $tags['brand'] ?? null,
                'city'       => $tags['addr:city'] ?? $city,
                'country'    => $tags['addr:country'] ?? '',
                'categories' => [$tourism],
                'stars'      => isset($tags['stars']) ? (int) $tags['stars'] : null,
                'website'    => $tags['website'] ?? null,
                'phone'      => $tags['phone'] ?? null,
            ],
            'geometry' => [
                'coordinates' => [$elLon, $elLat],
            ],
        ];
    }
}
