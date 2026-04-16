<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DestinationCostController extends Controller
{
    private const API_URL   = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL     = 'llama-3.3-70b-versatile';
    private const CACHE_TTL = 86400; // 24 hours

    public function breakdown(Request $request): JsonResponse
    {
        $request->validate([
            'destination' => 'required|string|max:100',
            'country'     => 'nullable|string|max:100',
            'duration'    => 'nullable|integer|min:1|max:30',
        ]);

        $destination = trim($request->input('destination'));
        $country     = trim($request->input('country', $destination));
        $duration    = (int) $request->input('duration', 7);
        $cacheKey    = 'dest_cost_' . md5(strtolower($destination . $country . $duration));

        $cached = Cache::get($cacheKey);
        if ($cached) {
            return response()->json(['success' => true, 'data' => $cached, 'cached' => true]);
        }

        $apiKey = config('services.groq.key') ?: env('GROQ_API_KEY');
        if (empty($apiKey)) {
            return response()->json(['success' => false, 'message' => 'AI service not configured.'], 503);
        }

        try {
            $data = $this->fetchCostBreakdown($destination, $country, $duration, $apiKey);
            Cache::put($cacheKey, $data, self::CACHE_TTL);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('DestinationCostController failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Could not load cost data.'], 500);
        }
    }

    private function fetchCostBreakdown(string $destination, string $country, int $duration, string $apiKey): array
    {
        $prompt = <<<PROMPT
Give a realistic daily cost breakdown for a traveller visiting {$destination}, {$country} for {$duration} days.

Return ONLY valid JSON with this exact structure:
{
  "destination": "{$destination}",
  "country": "{$country}",
  "duration_days": {$duration},
  "currency_note": "All prices in USD per person",
  "daily_costs": {
    "budget_hotel": 45,
    "mid_hotel": 120,
    "luxury_hotel": 280,
    "street_food": 8,
    "mid_restaurant": 25,
    "fine_dining": 80,
    "local_transport": 5,
    "taxi_rideshare": 15,
    "car_rental": 45
  },
  "activities": [
    {"name": "Activity name", "cost_usd": 25, "duration_hours": 3, "category": "culture", "included": true},
    {"name": "Activity name", "cost_usd": 0, "duration_hours": 2, "category": "nature", "included": true},
    {"name": "Activity name", "cost_usd": 60, "duration_hours": 5, "category": "adventure", "included": false},
    {"name": "Activity name", "cost_usd": 15, "duration_hours": 2, "category": "food", "included": true},
    {"name": "Activity name", "cost_usd": 35, "duration_hours": 4, "category": "culture", "included": true},
    {"name": "Activity name", "cost_usd": 20, "duration_hours": 3, "category": "relaxation", "included": false},
    {"name": "Activity name", "cost_usd": 45, "duration_hours": 6, "category": "adventure", "included": false},
    {"name": "Activity name", "cost_usd": 10, "duration_hours": 2, "category": "nature", "included": true}
  ],
  "visa_cost_usd": 0,
  "travel_insurance_usd": 35,
  "misc_daily_usd": 15,
  "budget_total_usd": 0,
  "mid_total_usd": 0,
  "luxury_total_usd": 0,
  "tips": ["Practical money-saving tip 1", "Practical money-saving tip 2", "Practical money-saving tip 3"]
}

Use REAL current prices for {$destination}. Activities must be specific to this destination with real names. Calculate budget/mid/luxury totals for {$duration} days including accommodation + food + transport + activities + misc.
PROMPT;

        $payload = [
            'model'           => self::MODEL,
            'max_tokens'      => 1500,
            'temperature'     => 0.3,
            'response_format' => ['type' => 'json_object'],
            'messages'        => [
                ['role' => 'system', 'content' => 'You are a travel cost expert. Return only valid JSON with accurate real-world prices.'],
                ['role' => 'user',   'content' => $prompt],
            ],
        ];

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: Bearer {$apiKey}",
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $body   = curl_exec($ch);
        $err    = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) throw new \RuntimeException("cURL error: {$err}");
        if ($status >= 400)  throw new \RuntimeException("Groq API error {$status}");

        $decoded = json_decode($body, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;
        if (!$content) throw new \RuntimeException('Empty response from Groq');

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) throw new \RuntimeException('Invalid JSON from Groq');

        return $data;
    }
}
