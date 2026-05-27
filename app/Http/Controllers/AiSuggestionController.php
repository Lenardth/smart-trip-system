<?php

namespace App\Http\Controllers;

use App\Services\AiSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiSuggestionController extends Controller
{
    public function __construct(private readonly AiSuggestionService $suggestions) {}

    public function suggest(Request $request): JsonResponse
    {
        return $this->suggestions->suggest($request);
    }
}
