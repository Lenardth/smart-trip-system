<?php

namespace App\Http\Controllers;

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
                                'destination','country','region','description',
                                'cost_min_usd','cost_max_usd','cost_includes',
                                'best_months','is_good_right_now','top_activities',
                                'travel_tip','visa_info','flight_info',
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

    public function suggest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mood'                       => 'required|string|max:100',
            'budget'                     => 'required|string|max:50',
            'duration'                   => 'required|string|max:50',
            'companion'                  => 'required|string|max:50',
            'month'                      => 'nullable|string|max:20',
            'region'                     => 'nullable|string|max:50',
            'accommodation'              => 'nullable|string|max:50',
            'origin'                     => 'nullable|string|max:100',
            'experience'                 => 'nullable|string|max:50',
            'feeling_note'               => 'nullable|string|max:500',
            'excluded_destinations'      => 'nullable|array|max:200',
            'excluded_destinations.*'    => 'string|max:100',
            'excluded_countries'         => 'nullable|array|max:200',
            'excluded_countries.*'       => 'string|max:100',
        ]);
        $validated['accommodation'] = $this->normaliseAccommodation($validated['accommodation'] ?? null);

        try {
            $apiKey = config('services.groq.key') ?: config('ai.groq.key') ?: env('GROQ_API_KEY');

            if (empty($apiKey)) {
                throw new \RuntimeException('GROQ_API_KEY is not set.');
            }

            $result = $this->callGroqWithRetry($validated, $apiKey);

            return response()->json(['success' => true, 'data' => $result]);

        } catch (\Throwable $e) {
            Log::error('AiSuggestionController failed', [
                'error'  => $e->getMessage(),
                'params' => $validated,
                'trace'  => config('app.debug') ? $e->getTraceAsString() : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Could not generate suggestions right now. Please try again.',
            ], 500);
        }
    }

    private function callGroqWithRetry(array $p, string $apiKey): array
    {
        $norm            = fn($s) => strtolower(trim(preg_replace('/\s+/', ' ', $s)));
        $seenDests       = array_map($norm, $p['excluded_destinations'] ?? []);
        $seenCountries   = array_map($norm, $p['excluded_countries']    ?? []);

        $accumulated     = [];
        $attemptExclDests     = $seenDests;
        $attemptExclCountries = $seenCountries;

        for ($try = 1; $try <= self::MAX_TRIES; $try++) {
            $params = array_merge($p, [
                'excluded_destinations' => $attemptExclDests,
                'excluded_countries'    => $attemptExclCountries,
            ]);

            $batch = $this->callGroq($params, $apiKey);

            foreach ($batch as $dest) {
                $dNorm = $norm($dest['destination'] ?? '');
                $cNorm = $norm($dest['country']     ?? '');

                if (in_array($dNorm, $attemptExclDests,     true)) continue;
                if (in_array($cNorm, $attemptExclCountries, true)) continue;

                $accumulated[]          = $dest;
                $attemptExclDests[]     = $dNorm;
                $attemptExclCountries[] = $cNorm;
            }

            if (count($accumulated) >= 5) {
                return array_slice($accumulated, 0, 5);
            }
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
        if ($status === 401) throw new \RuntimeException('Groq authentication failed — check GROQ_API_KEY.');
        if ($status >= 400) {
            Log::error('Groq API error', ['status' => $status, 'body' => $body]);
            throw new \RuntimeException("Groq API error {$status}: " . substr($body, 0, 300));
        }

        $decoded  = json_decode($body, true);
        $toolCall = $decoded['choices'][0]['message']['tool_calls'][0] ?? null;

        if (!$toolCall) {
            Log::error('Groq did not return tool_calls', ['response' => $decoded]);
            throw new \RuntimeException('Unexpected response from Groq.');
        }

        $input = json_decode($toolCall['function']['arguments'] ?? '{}', true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($input['destinations'])) {
            Log::error('Groq tool arguments invalid', ['raw' => $toolCall['function']['arguments'] ?? '', 'error' => json_last_error_msg()]);
            throw new \RuntimeException('Groq returned malformed tool arguments.');
        }

        return array_values(array_map([$this, 'normalise'], $input['destinations']));
    }

    private function buildPrompts(array $p): array
    {
        $month = now()->format('F');
        $year  = now()->year;

        $duration = match ($p['duration']) {
            'weekend'   => 'a long weekend (3–4 days)',
            'week'      => 'one week (7 days)',
            'two_weeks' => 'two weeks (10–14 days)',
            'month'     => 'one month or longer',
            'flexible'  => 'a flexible open-ended trip',
            default     => $p['duration'],
        };

        $budget = match ($p['budget']) {
            'backpacker' => 'backpacker (under $500 USD total)',
            'budget'     => 'budget-friendly ($500–$1,500 USD)',
            'mid'        => 'mid-range ($1,500–$4,000 USD)',
            'premium'    => 'premium ($4,000–$8,000 USD)',
            'luxury'     => 'luxury ($8,000+ USD)',
            default      => $p['budget'],
        };

        $companion = match ($p['companion']) {
            'solo'          => 'solo traveller',
            'couple'        => 'couple',
            'family_young'  => 'family with young children',
            'family_teens'  => 'family with teenagers',
            'friends_small' => 'small group of friends (2–4)',
            'friends_large' => 'large group of friends (5+)',
            'business'      => 'business traveller',
            default         => $p['companion'],
        };

        $extras = [];
        if (!empty($p['month']))                                           $extras[] = "departure month: {$p['month']}";
        if (!empty($p['region'])        && $p['region']        !== 'any') $extras[] = "preferred region: {$p['region']}";
        if (!empty($p['accommodation']) && $p['accommodation'] !== 'any') {
            $extras[] = 'accommodation preference: ' . $this->accommodationLabel($p['accommodation']);
        }
        if (!empty($p['origin']))  $extras[] = "flying from: {$p['origin']}";
        if (!empty($p['experience'])) $extras[] = "experience level: {$p['experience']}";
        if (!empty($p['feeling_note'])) $extras[] = "what they said: \"{$p['feeling_note']}\"";
        $extrasStr = $extras ? "\nAdditional context: " . implode(' | ', $extras) . '.' : '';

        $excludedStr = '';
        $exclDests     = array_filter($p['excluded_destinations'] ?? []);
        $exclCountries = array_filter($p['excluded_countries']    ?? []);

        if (!empty($exclDests) || !empty($exclCountries)) {
            $excludedStr = "\n\nDo not repeat any of these — they've already been shown:";
            if (!empty($exclDests)) {
                $safe = array_map(fn($d) => preg_replace('/[^a-zA-Z0-9\s,.\-()\'\x{00C0}-\x{024F}]/u', '', $d), $exclDests);
                $excludedStr .= "\nDestinations already shown: " . implode(', ', $safe) . '.';
            }
            if (!empty($exclCountries)) {
                $safe = array_map(fn($d) => preg_replace('/[^a-zA-Z0-9\s,.\-()\'\x{00C0}-\x{024F}]/u', '', $d), $exclCountries);
                $excludedStr .= "\nCountries already shown: " . implode(', ', $safe) . '.';
            }
            $excludedStr .= "\nAll 5 picks must be from different countries not listed above.";
        }

        $system = <<<SYSTEM
You are someone who has spent years travelling and now helps friends figure out where to go. You are not a travel agent and you do not write like one. You write the way a well-travelled person texts a friend — short sentences mixed with longer ones, the occasional aside, a mild opinion here and there. You never use words like "vibrant", "nestled", "boasts", "tapestry", "gem", "paradise", or "breathtaking". You never start a sentence with "Whether you're". You never write in bullet points inside the description field.

Today is {$month} {$year}. Use this to judge whether a destination is actually good to visit right now.

For each destination:
- description: 2–3 sentences. Write like you've been there. Name something specific — a street, a dish, a neighbourhood, a weird local habit. Keep it conversational, not promotional.
- travel_tip: one concrete thing most people don't know. Not "book early" or "respect the culture". Something like: "the old town floods with tour groups by 10am — get there at 8" or "skip the famous beach and go to the one 20 minutes south".
- visa_info: give the actual answer for the traveller's origin country if known. If not known, give the most common scenario. Don't say "check official sources".
- flight_info: rough flight time, whether direct routes exist, and the most common layover city if not direct.
- cost_min_usd / cost_max_usd: realistic total per-person cost including return flights, accommodation, food, and activities for the given budget tier and duration. Don't round to suspiciously clean numbers.
- best_months: the actual 3–4 best months. Not "spring" or "dry season" — actual month names.
- top_activities: 4–6 specific things to do. Not "explore the city" or "visit local markets". Real activities with names where possible.
- is_good_right_now: true only if {$month} is genuinely a decent time to go.

Pick 5 destinations from 5 different countries. Spread them across different parts of the world when possible.
SYSTEM;

        $user = "A {$companion} wants to travel. Mood: {$p['mood']}. Budget: {$budget}. Trip length: {$duration}."
            . $extrasStr
            . $excludedStr;

        return [$system, $user];
    }

    private function normalise(array $d): array
    {
        return [
            'destination'        => $d['destination']  ?? '',
            'country'            => $d['country']       ?? '',
            'region'             => $d['region']        ?? '',
            'description'        => $d['description']   ?? '',
            'estimated_cost'     => '$' . number_format($d['cost_min_usd'] ?? 0)
                                  . ' – $' . number_format($d['cost_max_usd'] ?? 0)
                                  . ' USD (' . ($d['cost_includes'] ?? 'flights, hotel, food') . ')',
            'best_time_to_visit' => implode(', ', $d['best_months']             ?? []),
            'is_good_right_now'  => (bool) ($d['is_good_right_now']             ?? false),
            'top_activities'     => implode(', ', (array) ($d['top_activities'] ?? [])),
            'travel_tip'         => $d['travel_tip']    ?? '',
            'visa_info'          => $d['visa_info']     ?? '',
            'flight_info'        => $d['flight_info']   ?? '',
            'match_reason'       => $d['match_reason']  ?? '',
        ];
    }

    private function normaliseAccommodation(?string $value): ?string
    {
        if (!$value) return null;
        return match ($value) {
            'hotel' => 'budget_hotel',
            'bnb' => 'boutique',
            default => $value,
        };
    }

    private function accommodationLabel(string $value): string
    {
        return match ($value) {
            'hostel' => 'hostel / shared accommodation',
            'budget_hotel' => 'budget hotel',
            'boutique' => 'boutique hotel or BnB',
            'resort' => 'resort',
            'villa' => 'private villa',
            'airbnb' => 'apartment or Airbnb',
            'glamping' => 'glamping or eco-lodge',
            default => $value,
        };
    }
}