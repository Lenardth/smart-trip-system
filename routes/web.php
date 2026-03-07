<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ItineraryController;

Route::get('/', function () {
    return view('public.landing');
})->name('home');

Route::get('/discover', function () {
    return view('discover');
})->name('discover');

Route::get('/community', function () {
    return view('community');
})->name('community');

Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations');
Route::get('/destinations/compare', [DestinationController::class, 'compare'])->name('destinations.compare');
Route::get('/destinations/{slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::post('/destinations/compare/add', [DestinationController::class, 'addToCompare'])->name('destinations.compare.add');
Route::post('/destinations/compare/remove', [DestinationController::class, 'removeFromCompare'])->name('destinations.compare.remove');

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

Route::middleware('auth')->group(function () {

    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/confirm-password', [AuthController::class, 'showConfirmPassword'])->name('password.confirm');
    Route::post('/confirm-password', [AuthController::class, 'confirmPassword']);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/check-activity', [AuthController::class, 'checkActivity'])->name('check.activity');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/plan-trip', function () {
        return view('plan-trip');
    })->name('plan-trip');

    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');

    Route::prefix('agency')->name('agency.')->group(function () {
        Route::get('/flights', function () {
            return view('agency.flights');
        })->name('flights');
        Route::get('/bookings', [BookingController::class, 'agencyBookings'])->name('bookings');
    });

    Route::prefix('itineraries')->name('itineraries.')->group(function () {
        Route::get('/', [ItineraryController::class, 'index'])->name('index');
        Route::post('/api/store', [ItineraryController::class, 'store'])->name('store');
        Route::get('/export', [ItineraryController::class, 'export'])->name('export');
        Route::get('/{id}', [ItineraryController::class, 'show'])->name('show');
        Route::delete('/{id}', [ItineraryController::class, 'destroy'])->name('destroy');
    });

    Route::get('/flights', [FlightController::class, 'index'])->name('flights.index');
    Route::post('/flights/search', [FlightController::class, 'search'])->name('flights.search');
    Route::get('/flights/create', [FlightController::class, 'create'])->name('flights.create');
    Route::post('/flights', [FlightController::class, 'store'])->name('flights.store');
    Route::get('/flights/my-flights', [FlightController::class, 'myFlights'])->name('flights.my');
    Route::get('/flights/{flight}', [FlightController::class, 'show'])->name('flights.show');
    Route::post('/flights/{flight}/book', [FlightController::class, 'book'])->name('flights.book');
    Route::post('/flights/{flight}/cancel', [FlightController::class, 'cancel'])->name('flights.cancel');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/agency', [BookingController::class, 'agencyBookings'])->name('bookings.agency');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::post('/profile/picture', [ProfileController::class, 'uploadProfilePicture'])->name('profile.picture.upload');
    Route::delete('/profile/picture', [ProfileController::class, 'deleteProfilePicture'])->name('profile.picture.delete');

    Route::prefix('api')->group(function () {
        Route::get('/user/statistics', [DashboardController::class, 'statistics']);
        Route::get('/notifications', [DashboardController::class, 'notifications']);
        Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead']);
        Route::post('/notifications/mark-read', [DashboardController::class, 'markNotificationsRead']);
        Route::get('/users/search', [DashboardController::class, 'searchUsers']);
        Route::post('/chat/send', [DashboardController::class, 'sendChat']);

        Route::post('/itineraries', [ItineraryController::class, 'store']);

        Route::get('/media', [MediaController::class, 'index']);
        Route::post('/media/upload', [MediaController::class, 'upload']);
        Route::delete('/media/delete', [MediaController::class, 'delete']);
        Route::post('/media/{media}/favorite', [MediaController::class, 'toggleFavorite']);
        Route::put('/media/{media}', [MediaController::class, 'update']);
        Route::get('/dashboard/stats', [MediaController::class, 'stats']);

        Route::get('/trips', [TripController::class, 'index']);
        Route::get('/trips/upcoming', [TripController::class, 'upcoming']);

        Route::post('/profile/update', [ProfileController::class, 'update']);
    });

});
