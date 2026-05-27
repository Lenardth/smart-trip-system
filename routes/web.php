<?php

use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\AgencyFlightController;
use App\Http\Controllers\Api\AccommodationController as ApiAccommodationController;
use App\Http\Controllers\Api\AiSuggestionController as ApiAiSuggestionController;
use App\Http\Controllers\Api\BookingController as ApiBookingController;
use App\Http\Controllers\Api\CouponController as ApiCouponController;
use App\Http\Controllers\Api\CurrencyController as ApiCurrencyController;
use App\Http\Controllers\Api\DashboardController as ApiDashboardController;
use App\Http\Controllers\Api\DiscoverController as ApiDiscoverController;
use App\Http\Controllers\Api\FlightController as ApiFlightController;
use App\Http\Controllers\Api\LandingController as ApiLandingController;
use App\Http\Controllers\Api\TripController as ApiTripController;
use App\Http\Controllers\Api\TripMoodController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ── Landing ───────────────────────────────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/api/landing/destinations', [ApiLandingController::class, 'destinations'])
    ->middleware('throttle:60,1')
    ->name('api.landing.destinations');

// ── Discover ──────────────────────────────────────────────────────────────────
Route::get('/discover', [DiscoverController::class, 'index'])->name('discover');
Route::get('/discover/place/{destination}', [DiscoverController::class, 'show'])
    ->name('discover.place.show');

// Primary JSON endpoint — mirrors /api/accommodations
Route::get('/api/discover', [ApiDiscoverController::class, 'list'])
    ->middleware('throttle:60,1')
    ->name('api.discover.list');

// Legacy alias kept for backward compat
Route::get('/api/discover/search', [ApiDiscoverController::class, 'search'])
    ->middleware('throttle:60,1')
    ->name('api.discover.search');

// ── AI Suggestions (public, throttled) ───────────────────────────────────────
Route::post('/ai/suggest', [ApiAiSuggestionController::class, 'suggest'])
    ->middleware('throttle:20,1')
    ->name('ai.suggest');

// ── Accommodations (public browse) ───────────────────────────────────────────
Route::get('/accommodations', [AccommodationController::class, 'index'])->name('accommodations.index');
Route::get('/api/accommodations', [ApiAccommodationController::class, 'list'])->name('api.accommodations.list');
Route::get('/api/accommodation-news', [ApiAccommodationController::class, 'news'])->middleware('throttle:30,1')->name('api.accommodation.news');
Route::get('/api/travel-warning', [ApiAccommodationController::class, 'travelWarning'])->middleware('throttle:30,1')->name('api.travel.warning');

// ── Static / Footer pages ─────────────────────────────────────────────────────
Route::get('/about', fn () => view('about.index'))->name('about');
Route::get('/privacy', fn () => view('privacy.index'))->name('privacy');
Route::get('/terms', fn () => view('terms.index'))->name('terms');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send')->middleware('throttle:5,1');

// ── Currency (public) ─────────────────────────────────────────────────────────
Route::get('/api/currency/rates', [ApiCurrencyController::class, 'rates'])->name('api.currency.rates');
Route::post('/api/currency/set', [ApiCurrencyController::class, 'setCurrency'])->name('api.currency.set');
Route::post('/api/currency/convert', [ApiCurrencyController::class, 'convert'])->name('api.currency.convert');

// ── Guest only ────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');
});

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/check-activity', [AuthController::class, 'checkActivity'])->name('check.activity');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/user/statistics', [ApiDashboardController::class, 'statistics']);
    Route::get('/api/user/recent-activity', [ApiDashboardController::class, 'recentActivity']);

    // Plan Trip (AI)
    Route::get('/plan-trip', fn () => view('plan-trip.index'))->name('plan-trip');
    Route::prefix('api/trips')->controller(ApiTripController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/upcoming', 'upcoming');
        Route::post('/', 'store')->middleware('throttle:30,1');
        Route::patch('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

    Route::prefix('api/trip-moods')->controller(TripMoodController::class)->group(function () {
        Route::get('/', 'index')->middleware('throttle:60,1');
        Route::post('/', 'store')->middleware('throttle:10,1');
        Route::post('/{mood}/use', 'use')->middleware('throttle:10,1');
    });

    // Flights
    Route::get('/flights', [FlightController::class, 'index'])->name('flights.index');
    Route::post('/flights/search', [ApiFlightController::class, 'search'])->middleware('throttle:30,1')->name('flights.search');
    Route::get('/flights/airports', [ApiFlightController::class, 'airports'])->name('flights.airports');

    // Accommodations (search history, auth required)
    Route::get('/api/accommodation-searches', [ApiAccommodationController::class, 'searches']);

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:10,1')->name('bookings.cancel');
    Route::post('/api/bookings/flight', [ApiBookingController::class, 'bookFlight'])->middleware('throttle:10,1');
    Route::post('/api/bookings/accommodation', [ApiBookingController::class, 'storeAccommodation'])->middleware('throttle:10,1');
    Route::post('/api/coupon/validate', [ApiCouponController::class, 'validate'])->middleware('throttle:30,1');

    // Agency
    Route::prefix('agency')->name('agency.')->group(function () {
        Route::get('/flights', [AgencyFlightController::class, 'index'])->name('flights.index');
        Route::post('/flights', [AgencyFlightController::class, 'store'])->middleware('throttle:20,1')->name('flights.store');
        Route::post('/flights/{flight}/publish', [AgencyFlightController::class, 'publish'])->middleware('throttle:20,1')->name('flights.publish');
        Route::post('/flights/{flight}/archive', [AgencyFlightController::class, 'archive'])->middleware('throttle:20,1')->name('flights.archive');
        Route::get('/bookings', [AgencyFlightController::class, 'incomingBookings'])->name('bookings.index');
    });

    // Profile
    Route::post('/profile/picture', [ProfileController::class, 'uploadPicture'])->middleware('throttle:5,1')->name('profile.picture.upload');
    Route::delete('/profile/picture', [ProfileController::class, 'deletePicture'])->middleware('throttle:10,1')->name('profile.picture.delete');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:5,1')->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('throttle:3,1')->name('profile.destroy');
});
