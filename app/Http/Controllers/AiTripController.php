<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for AI-powered trip planning.
 *
 * This stub prevents autoloading errors when routes reference
 * AiTripController. It currently returns a 501 Not Implemented response.
 * If you intend to implement AI trip planning in the future, replace
 * this method with actual logic that returns itinerary suggestions.
 */
class AiTripController extends Controller
{
    /**
     * Handle the incoming request as an invokable controller.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'AI trip planning is not implemented yet. Please try again later.',
        ], 501);
    }
}