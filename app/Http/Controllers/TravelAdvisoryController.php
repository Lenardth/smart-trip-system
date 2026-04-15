<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TravelAdvisoryController extends Controller
{
    private const API_URL    = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL      = 'llama-3.3-70b-versatile';
    private const CACHE_TTL  = 3600; // 1 hour

    public function advisory(Request $request): JsonResponse
    {
        $request->validate([
            'destination' => 'required|string|max:100',
            'country'     => 'nullable|string|max:100',
        ]);

        $destination = trim($request->input('destination'));
        $country     = trim($request->input('country', $destination));
        $cacheKey    = 'travel_advisory_' . md5(strtolower($destination . $country));

        $cached = Cache::get($cacheKey);
        if ($cached) {
            return response()->json(['success' => true, 'data' => $cached, 'cached' => true]);
        }

        $apiKey = config('services.groq.key') ?: env('GROQ_API_KEY');
        if (empty($apiKey)) {
            return response()->json(['success' => false, 'message' => 'AI service not configured.'], 503);
        }

        try {
            $data = $this->fetchAdvisory($destination, $country, $apiKey);
            Cache::put($cacheKey, $data, self::CACHE_TTL);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('TravelAdvisoryController failed', ['error' => $e->getMessage(), 'destination' => $destination]);
            return response()->json(['success' => false, 'message' => 'Could not load advisory right now.'], 500);
        }
    }

    private function fetchAdvisory(string $destination, string $country, string $apiKey): array
    {
        $month = now()->format('F Y');

        $system = "You are a seasoned travel journalist and safety expert. Give honest, current, practical travel intelligence. Never be vague. Never say 'check official sources'. Give real answers based on what you know.";

        $user = <<<PROMPT
Give a travel advisory for {$destination}, {$country} as of {$month}.

Return a JSON object with exactly these fields:
{
  "safety_level": "safe" | "caution" | "avoid",
  "safety_summary": "2 sentences. Be direct about the actual situation.",
  "current_affairs": "2-3 sentences on anything relevant right now - political climate, events, strikes, weather, anything a traveller should know.",
  "best_areas": ["3-4 specific neighbourhood or area names that are best for tourists with one-line reason each, formatted as 'Area Name - reason'"],
  "areas_to_avoid": ["2-3 specific areas or situations to avoid with brief reason, formatted as 'Area/Situation - reason'"],
  "top_tips": ["4-5 practical tips specific to this destination - not generic advice like 'respect local culture'"],
  "best_accommodation_areas": ["2-3 specific areas/neighbourhoods best for staying with brief reason"],
  "local_transport": "2 sentences on how to get around like a local.",
  "money_tips": "1-2 sentences on cash vs card, tipping, scams to watch.",
  "best_time_now": true or false - is {$month} actually a good time to visit?
}

Only return valid JSON. No markdown, no explanation outside the JSON.
PROMPT;

        $payload = [
            'model'       => self::MODEL,
            'max_tokens'  => 1024,
            'temperature' => 0.7,
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
            'response_format' => ['type' => 'json_object'],
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

        $advisory = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) throw new \RuntimeException('Invalid JSON from Groq');

        return array_merge([
            'destination'             => $destination,
            'country'                 => $country,
            'generated_at'            => now()->toISOString(),
            'safety_level'            => 'caution',
            'safety_summary'          => '',
            'current_affairs'         => '',
            'best_areas'              => [],
            'areas_to_avoid'          => [],
            'top_tips'                => [],
            'best_accommodation_areas'=> [],
            'local_transport'         => '',
            'money_tips'              => '',
            'best_time_now'           => true,
        ], $advisory);
    }
}
