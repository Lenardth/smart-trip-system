<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PersistCountrySelection
{
    /**
     * Handle an incoming request and persist country selection across pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if a country is being set via query parameter
        if ($request->has('country') && $request->filled('country')) {
            $country = $request->input('country');
            $destination = $request->input('destination', '');
            
            // Store in session
            session([
                'locked_country' => $country,
                'locked_destination' => $destination,
                'country_locked_at' => now()->toIso8601String(),
            ]);
        }
        
        // Check if user wants to unlock/clear the country
        if ($request->has('unlock_country') && $request->input('unlock_country') === 'true') {
            session()->forget(['locked_country', 'locked_destination', 'country_locked_at']);
        }

        return $next($request);
    }
}
