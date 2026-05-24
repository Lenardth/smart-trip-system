<?php

namespace App\Http\Controllers;

use App\Contracts\GeoapifyInterface;
use App\Models\Destination;
use App\Services\PexelsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LandingController extends Controller
{
    public function __construct(
        private readonly GeoapifyInterface $geoapify,
        private readonly PexelsService $pexels
    ) {}

    public function index()
    {
        return view('landing.index');
    }

    public function destinations(): JsonResponse
    {
        if (Destination::active()->count() < config('api.pagination.per_page', 16)) {
            $this->fetchAndStoreFeaturedDestinations();
        }

        $destinations = Destination::active()
            ->ordered()
            ->limit(config('api.pagination.per_page', 16))
            ->get()
            ->map(fn (Destination $destination) => $this->format($destination))
            ->values()
            ->all();

        return response()->json(['destinations' => $destinations]);
    }

    private function fetchAndStoreFeaturedDestinations(): void
    {
        try {
            $limit = config('api.pagination.per_page', 16);
            $places = $this->geoapify->searchDestinations('travel destination', null, null, $limit);

            foreach ($places as $index => $place) {
                $sourceId = $place['id'] ?? null;
                $name = $place['name'] ?? null;

                if (!$sourceId || !$name || $name === 'Unknown place') {
                    continue;
                }

                $destination = Destination::firstOrNew([
                    'source' => $place['source'] ?? 'openstreetmap',
                    'source_id' => $sourceId,
                ]);

                $imageQuery = trim($name . ' ' . ($place['country'] ?? '') . ' travel');

                $destination->fill([
                    'name' => $name,
                    'country' => $place['country'] ?? '',
                    'country_code' => $place['country_code'] ?? null,
                    'region' => $place['region'] ?? ($place['country'] ?? ''),
                    'description' => $place['description'] ?? '',
                    'image_url' => $destination->image_url ?: ($this->pexels->getRandomPhoto($imageQuery) ?? ''),
                    'price_from' => $destination->price_from ?? 0,
                    'tags' => $destination->tags ?: [],
                    'lat' => $place['lat'] ?? null,
                    'lng' => $place['lon'] ?? null,
                    'raw_data' => $place,
                    'is_featured' => true,
                    'is_active' => true,
                ]);

                if (!$destination->display_order) {
                    $destination->display_order = $index;
                }

                $destination->save();
            }
        } catch (\Throwable $e) {
            Log::warning('Landing destination API fetch failed', ['error' => $e->getMessage()]);
        }
    }

    private function format(Destination $destination): array
    {
        $tags = $destination->tags ?: [];
        $primaryTag = strtolower((string) ($tags[0] ?? 'general'));

        return [
            'id' => $destination->id,
            'name' => $destination->name,
            'country' => $destination->country,
            'country_code' => $destination->country_code,
            'region' => $destination->region,
            'mood' => $primaryTag,
            'category' => $primaryTag,
            'price_from' => $destination->price_from,
            'description' => $destination->description,
            'image_url' => $destination->image_url,
            'tags' => $tags,
            'is_featured' => $destination->is_featured,
            'is_editors_choice' => $destination->is_editors_choice,
            'detail_url' => route('discover.place.show', ['destination' => $destination->id]),
            'plan_url' => route('plan-trip') . '?destination=' . urlencode($destination->name),
        ];
    }
}
