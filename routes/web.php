<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public (Guest) Pages
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('public.landing');
})->name('home');

Route::get('/discover', function () {
    return view('discover');
});

Route::get('/destinations', function () {
    return view('destinations');
});

Route::get('/community', function () {
    return view('community');
});

/*
|--------------------------------------------------------------------------
| Authenticated Pages
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/plan-trip', function () {
        return view('plan-trip');
    });

});

/*
|--------------------------------------------------------------------------
| Breeze Auth Routes (LOGIN / REGISTER / LOGOUT)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
