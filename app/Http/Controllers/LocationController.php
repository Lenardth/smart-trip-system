<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(private LocationService $locationService) {}

    /**
     * Detect user's location from IP (opt-in only, privacy-protected)
     */
    public function detect(Request $request): JsonResponse
    {
        // Check if user has consented to location detection
        if (!$request->input('consent', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Location detection requires user consent',
                'consent_required' => true,
            ], 403);
        }

        $ipAddress = $request->ip();
        
        // Don't detect for local/private IPs
        if ($this->isPrivateIP($ipAddress)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot detect location for local IP addresses',
                'location' => null,
            ]);
        }

        $location = $this->locationService->getLocationFromIP($ipAddress);
        
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Could not detect location',
                'location' => null,
            ]);
        }

        // Find nearest airport if coordinates available
        $nearestAirport = null;
        if ($location['latitude'] && $location['longitude']) {
            $nearestAirport = $this->locationService->findNearestAirport(
                $location['latitude'],
                $location['longitude']
            );
        }

        return response()->json([
            'success' => true,
            'location' => [
                'city' => $location['city'],
                'country' => $location['country'],
                'country_code' => $location['country_code'],
            ],
            'nearest_airport' => $nearestAirport,
        ]);
    }

    /**
     * Get list of all major airports
     */
    public function airports(): JsonResponse
    {
        $airports = $this->locationService->getAllAirports();
        
        return response()->json([
            'success' => true,
            'airports' => $airports,
        ]);
    }

    /**
     * Save user's preferred departure airport
     */
    public function setDepartureAirport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'airport_code' => 'required|string|max:10',
            'airport_name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
        ]);

        session([
            'departure_airport_code' => $validated['airport_code'],
            'departure_airport_name' => $validated['airport_name'],
            'departure_city' => $validated['city'] ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Departure airport saved',
            'airport' => $validated,
        ]);
    }

    /**
     * Get user's saved departure airport
     */
    public function getDepartureAirport(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'airport' => [
                'code' => session('departure_airport_code'),
                'name' => session('departure_airport_name'),
                'city' => session('departure_city'),
            ],
        ]);
    }

    /**
     * Check if IP is private/local
     */
    private function isPrivateIP(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
