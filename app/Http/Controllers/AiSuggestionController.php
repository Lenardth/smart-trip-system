<?php

namespace App\Http\Controllers;

use App\Contracts\FlightPricingInterface;
use App\Contracts\AccommodationPricingInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiSuggestionController extends Controller
{
    private const API_URL    = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL      = 'llama-3.3-70b-versatile';
    private const MAX_TOKENS = 2048;
    private const MAX_TRIES  = 3;

    private const TOOL = [
        'type'     => 'function',
        'function' => [
            'name'        => 'suggest_destinations',
            'description' => 'Return exactly 5 travel destination recommendations, each from a different country.',
            'parameters'  => [
                'type'       => 'object',
                'required'   => ['destinations'],
                'properties' => [
                    'destinations' => [
                        'type'  => 'array',
                        'items' => [
                            'type'       => 'object',
                            'required'   => [
                                'destination', 'country', 'region', 'description',
                                'cost_min_usd', 'cost_max_usd',
                                'best_months', 'is_good_right_now', 'top_activities',
                                'travel_tip', 'visa_info', 'flight_info',
                            ],
                            'properties' => [
                                'destination'       => ['type' => 'string'],
                                'country'           => ['type' => 'string'],
                                'region'            => ['type' => 'string'],
                                'description'       => ['type' => 'string'],
                                'cost_min_usd'      => ['type' => 'integer'],
                                'cost_max_usd'      => ['type' => 'integer'],
                                'cost_includes'     => ['type' => 'string'],
                                'best_months'       => ['type' => 'array', 'items' => ['type' => 'string']],
                                'is_good_right_now' => ['type' => 'boolean'],
                                'top_activities'    => ['type' => 'array', 'items' => ['type' => 'string']],
                                'travel_tip'        => ['type' => 'string'],
                                'visa_info'         => ['type' => 'string'],
                                'flight_info'       => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    public function __construct(
        private FlightPricingInterface $flightPricing,
        private AccommodationPricingInterface $accommodationPricing
    ) {}

    public function suggest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mood'                    => 'required|string|max:100',
            'budget'                  => 'required|string|max:50',
            'duration'                => 'required|string|max:50',
            'companion'               => 'required|string|max:50',
            'month'                   => 'nullable|string|max:20',
            'region'                  => 'nullable|string|max:50',
            'accommodation'           => 'nullable|string|max:50',
            'origin'                  => 'nullable|string|max:100',
            'experience'              => 'nullable|string|max:50',
            'feeling_note'            => 'nullable|string|max:500',
            'excluded_destinations'   => 'nullable|array|max:200',
            'excluded_destinations.*' => 'string|max:100',
            'excluded_countries'      => 'nullable|array|max:200',
            'excluded_countries.*'    => 'string|max:100',
        ]);

        $validated['accommodation'] = $this->normaliseAccommodation($validated['accommodation'] ?? null);
        $validated['currency']      = session('preferred_currency', 'USD');

        try {
            $apiKey = config('services.groq.key');
            if (empty($apiKey)) {
                throw new \RuntimeException('GROQ_API_KEY is not configured.');
            }

            $result = $this->callGroqWithRetry($validated, $apiKey);
            return response()->json(['success' => true, 'data' => $result]);

        } catch (\Throwable $e) {
            Log::error('AiSuggestionController failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Could not generate suggestions right now.',
            ], 500);
        }
    }

    private function callGroqWithRetry(array $p, string $apiKey): array
    {
        $norm             = fn($s) => strtolower(trim(preg_replace('/\s+/', ' ', $s)));
        $exclDests        = array_map($norm, $p['excluded_destinations'] ?? []);
        $exclCountries    = array_map($norm, $p['excluded_countries'] ?? []);
        $accumulated      = [];

        for ($try = 1; $try <= self::MAX_TRIES; $try++) {
            $batch = $this->callGroq(array_merge($p, [
                'excluded_destinations' => $exclDests,
                'excluded_countries'    => $exclCountries,
            ]), $apiKey);

            foreach ($batch as $dest) {
                $dNorm = $norm($dest['destination'] ?? '');
                $cNorm = $norm($dest['country'] ?? '');
                if (in_array($dNorm, $exclDests, true) || in_array($cNorm, $exclCountries, true)) continue;
                $accumulated[]   = $dest;
                $exclDests[]     = $dNorm;
                $exclCountries[] = $cNorm;
            }

            if (count($accumulated) >= 5) return array_slice($accumulated, 0, 5);
        }

        if (empty($accumulated)) {
            throw new \RuntimeException('Could not generate non-duplicate destinations after ' . self::MAX_TRIES . ' attempts.');
        }

        return array_slice($accumulated, 0, 5);
    }

    private function callGroq(array $p, string $apiKey): array
    {
        [$system, $user] = $this->buildPrompts($p);

        $payload = [
            'model'       => self::MODEL,
            'max_tokens'  => self::MAX_TOKENS,
            'temperature' => 1.1,
            'tools'       => [self::TOOL],
            'tool_choice' => ['type' => 'function', 'function' => ['name' => 'suggest_destinations']],
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
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
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $body   = curl_exec($ch);
        $err    = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) throw new \RuntimeException("cURL error: {$err}");
        if ($status === 401) throw new \RuntimeException('Groq authentication failed.');
        if ($status >= 400) throw new \RuntimeException("Groq API error {$status}: " . substr($body, 0, 300));

        $decoded  = json_decode($body, true);
        $toolCall = $decoded['choices'][0]['message']['tool_calls'][0] ?? null;

        if (!$toolCall) throw new \RuntimeException('Unexpected response from Groq.');

        $input = json_decode($toolCall['function']['arguments'] ?? '{}', true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($input['destinations'])) {
            throw new \RuntimeException('Groq returned malformed tool arguments.');
        }

        return array_values(array_map([$this, 'normalise'], $input['destinations']));
    }

    private function buildPrompts(array $p): array
    {
        $month    = now()->format('F');
        $year     = now()->year;
        $currency = $p['currency'] ?? 'USD';

        $duration = match (true) {
            is_numeric($p['duration'])     => (int) $p['duration'] . ' days',
            $p['duration'] === 'weekend'   => 'a long weekend (3-4 days)',
            $p['duration'] === 'week'      => 'one week (7 days)',
            $p['duration'] === 'two_weeks' => 'two weeks (10-14 days)',
            $p['duration'] === 'month'     => 'one month or longer',
            $p['duration'] === 'flexible'  => 'a flexible open-ended trip',
            default                        => $p['duration'],
        };

        $budget = match ($p['budget']) {
            'backpacker' => "backpacker (under 500 {$currency} total)",
            'budget'     => "budget-friendly (500-1,500 {$currency})",
            'mid'        => "mid-range (1,500-4,000 {$currency})",
            'premium'    => "premium (4,000-8,000 {$currency})",
            'luxury'     => "luxury (8,000+ {$currency})",
            default      => $p['budget'],
        };

        $companion = match ($p['companion']) {
            'solo'          => 'solo traveller',
            'couple'        => 'couple',
            'family_young'  => 'family with young children',
            'family_teens'  => 'family with teenagers',
            'friends_small' => 'small group of friends (2-4)',
            'friends_large' => 'large group of friends (5+)',
            'business'      => 'business traveller',
            default         => $p['companion'],
        };

        $extras = [];
        if (!empty($p['month']))                                           $extras[] = "departure month: {$p['month']}";
        if (!empty($p['region'])        && $p['region']        !== 'any') $extras[] = "preferred region: {$p['region']}";
        if (!empty($p['accommodation']) && $p['accommodation'] !== 'any') $extras[] = 'accommodation: ' . $this->accommodationLabel($p['accommodation']);
        if (!empty($p['origin']))                                          $extras[] = "flying from: {$p['origin']}";
        if (!empty($p['experience']))                                      $extras[] = "experience level: {$p['experience']}";
        if (!empty($p['feeling_note']))                                    $extras[] = "what they said: \"{$p['feeling_note']}\"";
        $extrasStr = $extras ? "\nAdditional context: " . implode(' | ', $extras) . '.' : '';

        $excludedStr = '';
        $exclDests     = array_filter($p['excluded_destinations'] ?? []);
        $exclCountries = array_filter($p['excluded_countries'] ?? []);
        if (!empty($exclDests) || !empty($exclCountries)) {
            $excludedStr = "\n\nDo not repeat any of these:";
            if (!empty($exclDests)) {
                $safe = array_map(fn($d) => preg_replace('/[^a-zA-Z0-9\s,.\-()\'\x{00C0}-\x{024F}]/u', '', $d), $exclDests);
                $excludedStr .= "\nDestinations: " . implode(', ', $safe) . '.';
            }
            if (!empty($exclCountries)) {
                $safe = array_map(fn($d) => preg_replace('/[^a-zA-Z0-9\s,.\-()\'\x{00C0}-\x{024F}]/u', '', $d), $exclCountries);
                $excludedStr .= "\nCountries: " . implode(', ', $safe) . '.';
            }
            $excludedStr .= "\nAll 5 picks must be from different countries not listed above.";
        }

        $system = <<<SYSTEM
You are someone who has spent years travelling and now helps friends figure out where to go. Write like a well-travelled person texting a friend. Never use words like "vibrant", "nestled", "boasts", "tapestry", "gem", "paradise", or "breathtaking". Never start with "Whether you're". No bullet points in description.

Today is {$month} {$year}.

Provide REALISTIC pricing based on actual current travel costs including flights, accommodation, food, and activities for the given duration and budget tier. All costs in {$currency}.

For each destination:
- description: 2-3 conversational sentences. Name something specific.
- travel_tip: one concrete, non-obvious tip.
- visa_info: actual answer, not "check official sources".
- flight_info: rough flight time and layover info.
- cost_min_usd / cost_max_usd: realistic total per-person cost.
- best_months: 3-4 actual month names.
- top_activities: 4-6 specific named activities.
- is_good_right_now: true only if {$month} is genuinely good.

Pick 5 destinations from 5 different countries.
SYSTEM;

        $user = "A {$companion} wants to travel. Mood: {$p['mood']}. Budget: {$budget}. Trip length: {$duration}.{$extrasStr}{$excludedStr}";

        return [$system, $user];
    }

    private function normalise(array $d): array
    {
        $currency = session('preferred_currency', 'USD');
        $dest     = $d['destination'] ?? '';
        $country  = $d['country']     ?? '';
        $costs    = $this->validateCosts($dest, $d['cost_min_usd'] ?? 0, $d['cost_max_usd'] ?? 0);
        $months   = $d['best_months'] ?? [];
        $weather  = $d['weather_data'] ?? null;
        if (empty($months) && is_array($weather) && $weather) {
            $avg = (($weather['avg_high'] ?? 20) + ($weather['avg_low'] ?? 10)) / 2;
            $months = $avg > 25
                ? ['November', 'December', 'January', 'February']
                : ($avg < 10 ? ['June', 'July', 'August', 'September'] : ['April', 'May', 'September', 'October']);
        }

        return [
            'destination'        => $dest,
            'country'            => $country,
            'region'             => $d['region']      ?? '',
            'description'        => $d['description'] ?? '',
            'cost_min_usd'       => $costs['min'],
            'cost_max_usd'       => $costs['max'],
            'estimated_cost'     => number_format($costs['min']) . ' - ' . number_format($costs['max']) . ' ' . $currency,
            'best_time_to_visit' => implode(', ', $months),
            'is_good_right_now'  => (bool) ($d['is_good_right_now'] ?? false),
            'top_activities'     => implode(', ', (array) ($d['top_activities'] ?? [])),
            'travel_tip'         => $d['travel_tip']  ?? '',
            'visa_info'          => $d['visa_info']   ?? '',
            'flight_info'        => $d['flight_info'] ?? '',
        ];
    }

    private function validateCosts(string $destination, int $aiMin, int $aiMax): array
    {
        try {
            $accomNightly = $this->accommodationPricing->getPrice($destination, 'hotel', 'mid')['price'];
            $flightData   = $this->flightPricing->getPrice('JFK', 'LHR', '8h 0m');
            $ourMin       = (int) round(($flightData['price'] * 2) + ($accomNightly * 7) + (75 * 7));
            $ourMax       = (int) round($ourMin * 1.5);
            $aiAvg        = ($aiMin + $aiMax) / 2;
            $ourAvg       = ($ourMin + $ourMax) / 2;

            if ($aiAvg <= 0 || $ourAvg <= 0 || abs($aiAvg - $ourAvg) / $ourAvg > 0.5) {
                return ['min' => $ourMin, 'max' => $ourMax];
            }

            $min = (int) round($aiMin * 0.6 + $ourMin * 0.4);
            $max = (int) round($aiMax * 0.6 + $ourMax * 0.4);
            if ($max - $min < 200) $max = $min + 500;

            return ['min' => $min, 'max' => $max];
        } catch (\Throwable $e) {
            return ['min' => $aiMin, 'max' => $aiMax];
        }
    }

    private function normaliseAccommodation(?string $value): ?string
    {
        if (!$value) return null;
        return match ($value) {
            'hotel'  => 'budget_hotel',
            'bnb'    => 'boutique',
            default  => $value,
        };
    }

    private function accommodationLabel(string $value): string
    {
        return match ($value) {
            'budget_hotel' => 'budget hotel',
            'boutique'     => 'boutique / B&B',
            'hostel'       => 'hostel',
            'resort'       => 'resort',
            'villa'        => 'villa / private rental',
            'apartment'    => 'apartment',
            default        => $value,
        };
    }
}
