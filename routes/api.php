<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AiTripController;

Route::post('/ai/trip-plan', [AiTripController::class, 'generate']);

// Media routes - Changed to 'auth' for Blade dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media/upload', [MediaController::class, 'upload']);
    Route::delete('/media/delete', [MediaController::class, 'delete']);
    Route::post('/media/{media}/favorite', [MediaController::class, 'toggleFavorite']);
    Route::put('/media/{media}', [MediaController::class, 'update']);
    Route::get('/dashboard/stats', [MediaController::class, 'stats']);

    // Trip routes
    Route::get('/trips', [TripController::class, 'index']);
    Route::get('/trips/upcoming', [TripController::class, 'upcoming']);
});

// Profile routes
Route::middleware(['auth'])->group(function () {
    Route::post('/profile/update', [ProfileController::class, 'update']);
});
Route::middleware(['auth'])->post('/api/test', function() { return response()->json(['success' => true]); });

Route::get('/destinations', function () {
    return \App\Models\Destination::active()
        ->orderBy('sort_order')
        ->get();
});
