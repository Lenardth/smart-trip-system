<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiSuggestionController extends Controller
{
    public function suggest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mood'          => 'required|string',
            'budget'        => 'required|string',
            'duration'      => 'required|string',
            'companion'     => 'required|string',
            'month'         => 'nullable|string',
            'region'        => 'nullable|string',
            'accommodation' => 'nullable|string',
            'origin'        => 'nullable|string',
            'experience'    => 'nullable|string',
        ]);

        try {
            $v = $validated;

            $durationLabel = match ($v['duration']) {
                'weekend'   => 'a long weekend (3-4 days)',
                'week'      => 'one week (7 days)',
                'two_weeks' => 'two weeks (10-14 days)',
                'month'     => 'one month or more',
                'flexible'  => 'a flexible open-ended trip',
                default     => $v['duration'],
            };

            $budgetLabel = match ($v['budget']) {
                'backpacker' => 'backpacker budget (under 500 USD total)',
                'budget'     => 'budget-friendly (500-1,500 USD)',
                'mid'        => 'mid-range (1,500-4,000 USD)',
                'premium'    => 'premium (4,000-8,000 USD)',
                'luxury'     => 'luxury (8,000 USD+)',
                default      => $v['budget'],
            };

            $companionLabel = match ($v['companion']) {
                'solo'          => 'solo traveller',
                'couple'        => 'couple',
                'family_young'  => 'family with young children',
                'family_teens'  => 'family with teenagers',
                'friends_small' => 'small group of friends (2-4)',
                'friends_large' => 'large group of friends (5+)',
                'business'      => 'business traveller',
                default         => $v['companion'],
            };

            $extras = [];
            if (!empty($v['month']))                                  $extras[] = "departing in {$v['month']}";
            if (!empty($v['region']) && $v['region'] !== 'any')       $extras[] = "preferred region: {$v['region']}";
            if (!empty($v['accommodation']) && $v['accommodation'] !== 'any') $extras[] = "accommodation style: {$v['accommodation']}";
            if (!empty($v['origin']))                                 $extras[] = "flying from {$v['origin']}";
            if (!empty($v['experience']))                             $extras[] = "traveller experience level: {$v['experience']}";
            $extraStr = count($extras) ? ' Additional details: ' . implode(', ', $extras) . '.' : '';

            $systemPrompt = 'You are an expert travel advisor with deep knowledge of global destinations, flights, visa rules, and costs. You MUST respond with a valid JSON array only — no markdown, no backticks, no explanation. The array must contain exactly 5 destination objects each from a DIFFERENT country. Each object must have exactly these keys: destination, country, description, estimated_cost, best_time_to_visit, top_activities, travel_tip, visa_info, flight_info. ALL values must be strings. estimated_cost must include currency e.g. "1,200 USD". top_activities must be a comma-separated string. visa_info should be short e.g. "Visa on arrival" or "No visa required". flight_info should mention approx flight time and major hub e.g. "~9h via Dubai (Emirates)".';

            $userPrompt = "Recommend 5 travel destinations from 5 different countries for a {$companionLabel} in the mood for {$v['mood']} travel, with a {$budgetLabel} budget, for {$durationLabel}.{$extraStr} Prioritise well-suited, realistic, and currently popular destinations. Return ONLY a JSON array of 5 objects.";

            $apiKey  = env('OPENAI_API_KEY');
            $baseUrl = rtrim(env('OPENAI_URL', 'https://integrate.api.nvidia.com/v1'), '/');

            if (empty($apiKey)) {
                throw new \RuntimeException('OPENAI_API_KEY is not set in .env');
            }

            $body = json_encode([
                'model'       => 'meta/llama3-70b-instruct',
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt],
                ],
                'temperature' => 0.9,
                'max_tokens'  => 2000,
            ]);

            $ch = curl_init("{$baseUrl}/chat/completions");

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    "Authorization: Bearer {$apiKey}",
                ],
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
                CURLOPT_TCP_KEEPALIVE  => 1,
                CURLOPT_TCP_KEEPIDLE   => 30,
                CURLOPT_TCP_KEEPINTVL  => 15,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $responseBody = curl_exec($ch);
            $curlError    = curl_error($ch);
            $httpStatus   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($responseBody === false) {
                throw new \RuntimeException("cURL error: {$curlError}");
            }

            if ($httpStatus >= 400) {
                Log::error('NVIDIA API error', ['status' => $httpStatus, 'body' => $responseBody]);
                throw new \RuntimeException("NVIDIA API error {$httpStatus}: {$responseBody}");
            }

            $decoded = json_decode($responseBody, true);
            $text    = $decoded['choices'][0]['message']['content'] ?? null;

            if (!$text) {
                throw new \RuntimeException('Empty response from AI');
            }

            $clean = preg_replace('/^```(?:json)?\s*|\s*```$/s', '', trim($text));
            $clean = preg_replace('/("estimated_cost"\s*:\s*)(\d{1,3}(?:,\d{3})+)/', '$1"$2"', $clean);

            $result = json_decode($clean, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                preg_match('/(\[.*\]|\{.*\})/s', $clean, $matches);
                if (!empty($matches[1])) {
                    $result = json_decode($matches[1], true);
                }

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('AI returned invalid JSON', [
                        'text'  => $text,
                        'clean' => $clean,
                        'error' => json_last_error_msg(),
                    ]);
                    throw new \RuntimeException('AI returned invalid JSON: ' . substr($text, 0, 200));
                }
            }

            if (!is_array($result) || isset($result['destination'])) {
                $result = [$result];
            }

            foreach ($result as $item) {
                $required = ['destination', 'country', 'description', 'estimated_cost', 'best_time_to_visit', 'top_activities', 'travel_tip', 'visa_info', 'flight_info'];
                foreach ($required as $field) {
                    if (!isset($item[$field])) {
                        Log::warning('Missing field in AI response', ['field' => $field, 'item' => $item]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);

        } catch (\Throwable $e) {
            Log::error('AiSuggestionController failed', [
                'error'  => $e->getMessage(),
                'params' => $validated,
                'trace'  => config('app.debug') ? $e->getTraceAsString() : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Could not generate a suggestion right now. Please try again.',
            ], 500);
        }
    }
}
