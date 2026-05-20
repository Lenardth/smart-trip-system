<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-api', function () {
    try {
        $count = \App\Models\Destination::count();
        $destinations = \App\Models\Destination::limit(3)->get();
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'destinations' => $destinations,
            'env' => [
                'geoapify_key' => env('GEOAPIFY_KEY') ? 'set' : 'not set',
                'pexels_key' => env('PEXELS_API_KEY') ? 'set' : 'not set',
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});