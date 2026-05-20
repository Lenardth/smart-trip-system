<?php

namespace App\Http\Controllers;

use App\Contracts\FlightPricingInterface;
use App\Contracts\AccommodationPricingInterface;
use App\Http\Concerns\NormalisesAccommodation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiSuggestionController extends Controller
{
    use NormalisesAccommodation;

    private const MAX_TRIES = 3;

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
        private FlightPricingInterface        $flightPricing,
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
            $apiKey = config('services.groq.api_key');

            if (empty($apiKey)) {
                throw new \RuntimeException('GROQ_API_KEY is not configured.');
            }

            $result = $this->callGroqWithRetry($validated, $apiKey);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            Log::error('AiSuggestionController failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Could not generate suggestions right now.',
            ], 500);
        }
    }

    private function callGroqWithRetry(array $p, string $apiKey): array
    {
        $norm          = fn($s) => strtolower(trim(preg_replace('/\s+/', ' ', $s)));
        $exclDests     = array_map($norm, $p['excluded_destinations'] ?? []);
        $exclCountries = array_map($norm, $p['excluded_countries'] ?? []);
        $accumulated   = [];

        for ($try = 1; $try <= self::MAX_TRIES; $try++) {
            $batch = $this->callGroq(array_merge($p, [
                'excluded_destinations' => $exclDests,
                'excluded_countries'    => $exclCountries,
            ]), $apiKey);

            foreach ($batch as $dest) {
                $dNorm = $norm($dest['destination'] ?? '');
                $cNorm = $norm($dest['country'] ?? '');

                if (in_array($dNorm, $exclDests, true) || in_array($cNorm, $exclCountries, true)) {
                    continue;
                }

                $accumulated[]   = $dest;
                $exclDests[]     = $dNorm;
                $exclCountries[] = $cNorm;
            }

            if (count($accumulated) >= 5) {
                return array_slice($accumulated, 0, 5);
            }
        }

        if (empty($accumulated)) {
            throw new \RuntimeException(
                'Could not generate non-duplicate destinations after ' . self::MAX_TRIES . ' attempts.'
            );
        }

        return array_slice($accumulated, 0, 5);
    }

    private function callGroq(array $p, string $apiKey): array
    {
        [$system, $user] = $this->buildPrompts($p);

        $payload = [
            'model'       => config('services.groq.model', 'llama-3.3-70b-versatile'),
            'max_tokens'  => config('services.groq.max_tokens', 2048),
            'temperature' => 1.1,
            'tools'       => [self::TOOL],
            'tool_choice' => ['type' => 'function', 'function' => ['name' => 'suggest_destinations']],
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
        ];

        $apiUrl   = config('services.groq.url', 'https://api.groq.com/openai/v1/chat/completions');
        $response = Http::withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->timeout(60)
            ->connectTimeout(15)
            ->post($apiUrl, $payload);

        if ($response->status() === 401) {
            throw new \RuntimeException('Groq authentication failed.');
        }

        if ($response->failed()) {
            throw new \RuntimeException(
                "Groq API error {$response->status()}: " . Str::limit($response->body(), 300)
            );
        }

        $data     = $response->json();
        $toolCall = $data['choices'][0]['message']['tool_calls'][0] ?? null;

        if (!$toolCall) {
            throw new \RuntimeException('Unexpected response from Groq.');
        }

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

        $durationLabels = config('trips.duration_prompt_labels', []);
        $duration = is_numeric($p['duration'])
            ? (int) $p['duration'] . ' days'
            : ($durationLabels[$p['duration']] ?? $p['duration']);

        $budgetLabels = config('trips.budget_prompt_labels', []);
        $budget = isset($budgetLabels[$p['budget']])
            ? sprintf($budgetLabels[$p['budget']], $currency)
            : $p['budget'];

        $companionLabels = config('trips.companion_prompt_labels', []);
        $companion = $companionLabels[$p['companion']] ?? $p['companion'];

        $extras = [];
        if (!empty($p['month']))                                           $extras[] = "departure month: {$p['month']}";
        if (!empty($p['region'])        && $p['region']        !== 'any') $extras[] = "preferred region: {$p['region']}";
        if (!empty($p['accommodation']) && $p['accommodation'] !== 'any') $extras[] = 'accommodation: ' . $this->accommodationLabel($p['accommodation']);
        if (!empty($p['origin']))                                          $extras[] = "flying from: {$p['origin']}";
        if (!empty($p['experience']))                                      $extras[] = "experience level: {$p['experience']}";
        if (!empty($p['feeling_note']))                                    $extras[] = "what they said: \"{$p['feeling_note']}\"";

        $extrasStr = $extras ? "\nAdditional context: " . implode(' | ', $extras) . '.' : '';

        $excludedStr   = '';
        $exclDests     = array_filter($p['excluded_destinations'] ?? []);
        $exclCountries = array_filter($p['excluded_countries']    ?? []);

        if (!empty($exclDests) || !empty($exclCountries)) {
            $sanitise    = fn($d) => preg_replace('/[^a-zA-Z0-9\s,.\-()\'\x{00C0}-\x{024F}]/u', '', $d);
            $excludedStr = "\n\nDo not repeat any of these:";

            if (!empty($exclDests)) {
                $excludedStr .= "\nDestinations: " . implode(', ', array_map($sanitise, $exclDests)) . '.';
            }

            if (!empty($exclCountries)) {
                $excludedStr .= "\nCountries: " . implode(', ', array_map($sanitise, $exclCountries)) . '.';
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

        if (empty($months) && is_array($d['weather_data'] ?? null)) {
            $avg    = (($d['weather_data']['avg_high'] ?? 20) + ($d['weather_data']['avg_low'] ?? 10)) / 2;
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

            if ($max - $min < 200) {
                $max = $min + 500;
            }

            return ['min' => $min, 'max' => $max];
        } catch (\Throwable $e) {
            return ['min' => $aiMin, 'max' => $aiMax];
        }
    }
}
