<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;

// Public routes
Route::get('/', function () {
    return view('public.landing');
})->name('home');

Route::get('/discover', function () {
    return view('discover');
})->name('discover');

Route::get('/destinations', function () {
    return view('destinations');
})->name('destinations');

Route::get('/community', function () {
    return view('community');
})->name('community');

// Guest only routes (login/register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Trip Planning
    Route::get('/plan-trip', function () {
        return view('plan-trip');
    })->name('plan-trip');

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

    // Profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/picture', [ProfileController::class, 'uploadProfilePicture'])->name('profile.picture.upload');
    Route::delete('/profile/picture', [ProfileController::class, 'deleteProfilePicture'])->name('profile.picture.delete');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
