<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NewsController extends Controller
{
    public function accommodationNews(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', 'travel hotels tourism'));
        $apiKey = config('services.gnews.key');

        if (! $apiKey) {
            return response()->json([
                'articles' => [],
                'message' => 'Missing GNews API key.',
            ], 500);
        }

        $response = Http::timeout(20)->get('https://gnews.io/api/v4/search', [
            'q' => $q,
            'lang' => 'en',
            'max' => 6,
            'sortby' => 'publishedAt',
            'apikey' => $apiKey,
        ]);

        if (! $response->successful()) {
            return response()->json([
                'articles' => [],
                'message' => 'Failed to load news.',
                'error' => $response->json(),
            ], $response->status());
        }

        return response()->json([
            'articles' => $response->json('articles', []),
        ]);
    }
}