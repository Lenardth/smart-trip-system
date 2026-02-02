<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Guest-only routes (login & register)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/plan-trip', function () {
        return view('plan-trip');
    })->name('plan-trip');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
