<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ItineraryController; // ✨ ADDED THIS

// Public routes
Route::get('/', function () {
    return view('public.landing');
})->name('home');

Route::get('/discover', function () {
    return view('discover');
})->name('discover');

Route::get('/community', function () {
    return view('community');
})->name('community');

// Destinations Routes (Public)
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations');
Route::get('/destinations/{slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::post('/destinations/compare/add', [DestinationController::class, 'addToCompare'])->name('destinations.compare.add');
Route::post('/destinations/compare/remove', [DestinationController::class, 'removeFromCompare'])->name('destinations.compare.remove');
Route::get('/destinations/compare', [DestinationController::class, 'compare'])->name('destinations.compare');

// Guest only routes (login/register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Password Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');
});

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    // Email Verification Routes
    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Password Confirmation Routes
    Route::get('/confirm-password', [AuthController::class, 'showConfirmPassword'])->name('password.confirm');
    Route::post('/confirm-password', [AuthController::class, 'confirmPassword']);

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ✨ FIX 1: Added Agency Routes
    Route::prefix('agency')->name('agency.')->group(function () {
        Route::get('/flights', function () {
            return view('agency.flights');
        })->name('flights');

        Route::get('/bookings', [BookingController::class, 'agencyBookings'])->name('bookings');
    });

    // ✨ FIX 2: Added Settings Route
    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');

    // Trip Planning
    Route::get('/plan-trip', function () {
        return view('plan-trip');
    })->name('plan-trip');

    // ✨ NEW: Itinerary Routes
    Route::prefix('itineraries')->name('itineraries.')->group(function () {
        Route::get('/', [ItineraryController::class, 'index'])->name('index');
        Route::post('/api/store', [ItineraryController::class, 'store'])->name('store');
        Route::get('/export', [ItineraryController::class, 'export'])->name('export');
        Route::get('/{id}', [ItineraryController::class, 'show'])->name('show');
        Route::delete('/{id}', [ItineraryController::class, 'destroy'])->name('destroy');
    });

    // Alternative API route (keep both for compatibility)
    Route::post('/api/itineraries', [ItineraryController::class, 'store']);

    // Logout & Session
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/check-activity', [AuthController::class, 'checkActivity'])->name('check.activity');

    // Flights
    Route::get('/flights', [FlightController::class, 'index'])->name('flights.index');
    Route::get('/flights/create', [FlightController::class, 'create'])->name('flights.create');
    Route::post('/flights', [FlightController::class, 'store'])->name('flights.store');
    Route::get('/flights/my-flights', [FlightController::class, 'myFlights'])->name('flights.my');
    Route::get('/flights/{flight}', [FlightController::class, 'show'])->name('flights.show');
    Route::post('/flights/{flight}/book', [FlightController::class, 'book'])->name('flights.book');
    Route::post('/flights/{flight}/cancel', [FlightController::class, 'cancel'])->name('flights.cancel');

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/agency', [BookingController::class, 'agencyBookings'])->name('bookings.agency');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Password Update Route
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

    // Additional profile routes
    Route::post('/profile/picture', [ProfileController::class, 'uploadProfilePicture'])->name('profile.picture.upload');
    Route::delete('/profile/picture', [ProfileController::class, 'deleteProfilePicture'])->name('profile.picture.delete');



});

// ====== API Routes (moved from api.php) ======
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Media routes
    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media/upload', [MediaController::class, 'upload']);
    Route::delete('/media/delete', [MediaController::class, 'delete']);
    Route::post('/media/{media}/favorite', [MediaController::class, 'toggleFavorite']);
    Route::put('/media/{media}', [MediaController::class, 'update']);
    Route::get('/dashboard/stats', [MediaController::class, 'stats']);

    // Trip routes
    Route::get('/trips', [TripController::class, 'index']);
    Route::get('/trips/upcoming', [TripController::class, 'upcoming']);

    // Profile routes
    Route::post('/profile/update', [ProfileController::class, 'update']);
});
