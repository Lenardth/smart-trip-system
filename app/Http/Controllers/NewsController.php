<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    public function accommodationNews(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', 'travel hotels tourism'));

        // Try GNews first
        $articles = $this->fetchFromGNews($q);

        // Fall back to NewsAPI if GNews returned nothing
        if (empty($articles)) {
            $articles = $this->fetchFromNewsApi($q);
        }

        return response()->json(['articles' => $articles]);
    }

    public function travelWarning(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (!$q) return response()->json(['warnings' => []]);

        // Fetch news about safety/warnings for this location
        $safetyQuery = "{$q} travel warning safety crime protest";
        $articles = $this->fetchFromGNews($safetyQuery);
        if (empty($articles)) {
            $articles = $this->fetchFromNewsApi($safetyQuery);
        }

        // Filter to only articles that seem safety-relevant
        $keywords = ['warning', 'danger', 'crime', 'protest', 'strike', 'flood', 'storm',
                     'unrest', 'avoid', 'alert', 'unsafe', 'attack', 'riot', 'scam',
                     'theft', 'robbery', 'earthquake', 'hurricane', 'typhoon', 'fire'];

        $warnings = array_filter($articles, function($a) use ($keywords) {
            $text = strtolower(($a['title'] ?? '') . ' ' . ($a['description'] ?? ''));
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) return true;
            }
            return false;
        });

        return response()->json([
            'warnings' => array_values(array_slice($warnings, 0, 3)),
        ]);
    }

    public function destinationNews(Request $request): JsonResponse
    {
        $destination = trim((string) $request->input('destination', ''));
        $country = trim((string) $request->input('country', ''));
        
        if (!$destination && !$country) {
            return response()->json(['articles' => []]);
        }

        // Build search query for destination news
        $query = $destination ? "{$destination}" : "{$country}";
        if ($country && $destination !== $country) {
            $query .= " {$country}";
        }
        $query .= " travel tourism news";

        // Fetch from news APIs
        $articles = $this->fetchFromGNews($query);
        if (empty($articles)) {
            $articles = $this->fetchFromNewsApi($query);
        }

        return response()->json([
            'articles' => array_slice($articles, 0, 6),
        ]);
    }

    private function fetchFromGNews(string $q): array
    {
        $apiKey = config('services.gnews.key');
        if (!$apiKey) return [];

        try {
            $response = Http::timeout(20)->get('https://gnews.io/api/v4/search', [
                'q'      => $q,
                'lang'   => 'en',
                'max'    => 6,
                'sortby' => 'publishedAt',
                'apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                return $response->json('articles', []);
            }
        } catch (\Throwable $e) {
            Log::warning('GNews fetch failed: ' . $e->getMessage());
        }

        return [];
    }

    private function fetchFromNewsApi(string $q): array
    {
        $apiKey = config('services.newsapi.key');
        if (!$apiKey) return [];

        try {
            $response = Http::timeout(20)->get('https://newsapi.org/v2/everything', [
                'q'        => $q,
                'sortBy'   => 'publishedAt',
                'language' => 'en',
                'pageSize' => 6,
                'apiKey'   => $apiKey,
            ]);

            if (!$response->successful()) return [];

            // Normalize NewsAPI articles to match GNews format
            return collect($response->json('articles', []))
                ->filter(fn($a) => !empty($a['title']) && $a['title'] !== '[Removed]')
                ->map(fn($a) => [
                    'title'       => $a['title'],
                    'description' => $a['description'] ?? '',
                    'url'         => $a['url'],
                    'image'       => $a['urlToImage'] ?? null,
                    'publishedAt' => $a['publishedAt'],
                    'source'      => ['name' => $a['source']['name'] ?? ''],
                ])
                ->values()
                ->toArray();

        } catch (\Throwable $e) {
            Log::warning('NewsAPI fetch failed: ' . $e->getMessage());
        }

        return [];
    }
}
