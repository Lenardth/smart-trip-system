<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\AiSuggestionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlightController;

// ── Landing ───────────────────────────────────────────────────────────────────
Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('home');
Route::get('/api/landing/destinations', [\App\Http\Controllers\LandingController::class, 'destinations'])
    ->middleware('throttle:60,1')
    ->name('api.landing.destinations');

// ── Discover ──────────────────────────────────────────────────────────────────
Route::get('/discover', [\App\Http\Controllers\DiscoverController::class, 'index'])->name('discover');
Route::get('/discover/place/{destination}', [\App\Http\Controllers\DiscoverController::class, 'show'])
    ->name('discover.place.show');
// Primary JSON endpoint — mirrors /api/accommodations
Route::get('/api/discover', [\App\Http\Controllers\DiscoverController::class, 'list'])
    ->middleware('throttle:60,1')
    ->name('api.discover.list');
// Legacy alias kept for backward compat
Route::get('/api/discover/search', [\App\Http\Controllers\DiscoverController::class, 'search'])
    ->middleware('throttle:60,1')
    ->name('api.discover.search');

// ── AI Suggestions (public, throttled) ───────────────────────────────────────
Route::post('/ai/suggest', [AiSuggestionController::class, 'suggest'])
    ->middleware('throttle:20,1')
    ->name('ai.suggest');

// ── Accommodations (public browse) ───────────────────────────────────────────
Route::get('/accommodations',     [AccommodationController::class, 'index'])->name('accommodations.index');
Route::get('/api/accommodations', [AccommodationController::class, 'list'])->name('api.accommodations.list');

// ── Static / Footer pages ─────────────────────────────────────────────────────
Route::get('/about',   fn () => view('about.index'))->name('about');
Route::get('/privacy', fn () => view('privacy.index'))->name('privacy');
Route::get('/terms',   fn () => view('terms.index'))->name('terms');
Route::get('/contact',  [\App\Http\Controllers\ContactController::class, 'show'])->name('contact');
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'send'])->name('contact.send')->middleware('throttle:5,1');

// ── Currency (public) ─────────────────────────────────────────────────────────
Route::get('/api/currency/rates',    [CurrencyController::class, 'rates'])->name('api.currency.rates');
Route::post('/api/currency/set',     [CurrencyController::class, 'setCurrency'])->name('api.currency.set');
Route::post('/api/currency/convert', [CurrencyController::class, 'convert'])->name('api.currency.convert');

// ── Guest only ────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',                  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',                 [AuthController::class, 'login']);
    Route::get('/register',               [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',              [AuthController::class, 'register']);
    Route::get('/forgot-password',        [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password',       [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password',        [AuthController::class, 'resetPassword'])->name('password.store');
});

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::post('/logout',        [AuthController::class, 'logout'])->name('logout');
    Route::get('/check-activity', [AuthController::class, 'checkActivity'])->name('check.activity');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/user/statistics',    [DashboardController::class, 'statistics']);
    Route::get('/api/user/recent-activity', [DashboardController::class, 'recentActivity']);

    // Plan Trip (AI)
    Route::get('/plan-trip', fn () => view('plan-trip.index'))->name('plan-trip');
    Route::get('/api/trips',          [\App\Http\Controllers\TripController::class, 'index']);
    Route::get('/api/trips/upcoming', [\App\Http\Controllers\TripController::class, 'upcoming']);
    Route::post('/api/trips',         [\App\Http\Controllers\TripController::class, 'store'])->middleware('throttle:30,1');
    Route::patch('/api/trips/{id}',   [\App\Http\Controllers\TripController::class, 'update']);
    Route::delete('/api/trips/{id}',  [\App\Http\Controllers\TripController::class, 'destroy']);

    Route::get('/api/trip-moods', [\App\Http\Controllers\Api\TripMoodController::class, 'index'])->middleware('throttle:60,1');
    Route::post('/api/trip-moods', [\App\Http\Controllers\Api\TripMoodController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/api/trip-moods/{mood}/use', [\App\Http\Controllers\Api\TripMoodController::class, 'use'])->middleware('throttle:10,1');

    // Flights
    Route::get('/flights',           [FlightController::class, 'index'])->name('flights.index');
    Route::post('/flights/search',   [FlightController::class, 'search'])->middleware('throttle:30,1')->name('flights.search');
    Route::get('/flights/airports',  [FlightController::class, 'airports'])->name('flights.airports');

    // Accommodations (search history, auth required)
    Route::get('/api/accommodation-searches', [AccommodationController::class, 'searches']);

    // Bookings
    Route::get('/bookings',                   [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}',         [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/api/bookings/flight',        [BookingController::class, 'bookFlight'])->middleware('throttle:10,1');
    Route::post('/api/bookings/accommodation', [BookingController::class, 'storeAccommodation'])->middleware('throttle:10,1');
    Route::post('/api/coupon/validate',        [\App\Http\Controllers\CouponController::class, 'validate']);

    // Profile
    Route::post('/profile/picture',   [\App\Http\Controllers\ProfileController::class, 'uploadPicture'])->name('profile.picture.upload');
    Route::delete('/profile/picture', [\App\Http\Controllers\ProfileController::class, 'deletePicture'])->name('profile.picture.delete');
    Route::put('/profile/password',   [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile',         [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});
