<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\AiSuggestionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Api\TripMoodController;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', fn () => view('landing.index'))->name('home');

Route::post('/ai/suggest', [AiSuggestionController::class, 'suggest'])
    ->middleware('throttle:20,1')
    ->name('ai.suggest');

Route::get('/accommodations',           [AccommodationController::class, 'index'])->name('accommodations.index');
Route::get('/api/accommodations',       [AccommodationController::class, 'list'])->name('api.accommodations.list');
Route::get('/api/accommodation-news',   [NewsController::class, 'accommodationNews']);

Route::get('/community', [CommunityController::class, 'index'])->name('community');

Route::prefix('api/community')->group(function () {
    Route::get('/stats',                [CommunityController::class, 'stats']);
    Route::get('/topics',               [CommunityController::class, 'topics']);
    Route::post('/topics',              [CommunityController::class, 'storeTopic']);
    Route::get('/topics/{id}',          [CommunityController::class, 'showTopic']);
    Route::put('/topics/{id}',          [CommunityController::class, 'updateTopic']);
    Route::delete('/topics/{id}',       [CommunityController::class, 'destroyTopic']);
    Route::post('/topics/{id}/replies', [CommunityController::class, 'storeReply']);
    Route::delete('/replies/{id}',      [CommunityController::class, 'destroyReply']);
    Route::get('/groups',               [CommunityController::class, 'groups']);
    Route::post('/groups',              [CommunityController::class, 'storeGroup']);
    Route::delete('/groups/{id}',       [CommunityController::class, 'destroyGroup']);
    Route::get('/tags',                 [CommunityController::class, 'tags']);
    Route::get('/stories',              [CommunityController::class, 'stories']);
    Route::get('/travelers',            [CommunityController::class, 'travelers']);
});

Route::get('/discover', [DiscoverController::class, 'index'])->name('discover');

Route::prefix('api/discover')->group(function () {
    Route::get('/destinations',          [DiscoverController::class, 'destinations'])->name('api.discover.destinations');
    Route::get('/destinations/{id}',     [DiscoverController::class, 'destinationById'])->name('api.discover.destination')->where('id', '[0-9]+');
    Route::get('/hidden-gems',           [DiscoverController::class, 'hiddenGems'])->name('api.discover.hidden-gems');
});

Route::get('/destinations',                 [DestinationController::class, 'index'])->name('destinations');
Route::get('/destinations/compare',         [DestinationController::class, 'compare'])->name('destinations.compare');
Route::post('/destinations/compare/add',    [DestinationController::class, 'addToCompare'])->name('destinations.compare.add');
Route::post('/destinations/compare/remove', [DestinationController::class, 'removeFromCompare'])->name('destinations.compare.remove');
Route::get('/destinations/{id}',            [DestinationController::class, 'show'])->name('destinations.show')->where('id', '[0-9]+');

Route::get('/about',   fn() => view('about.index'))->name('about');
Route::get('/privacy', fn() => view('privacy.index'))->name('privacy');
Route::get('/terms',   fn() => view('terms.index'))->name('terms');
Route::get('/contact',  fn() => view('contact.index'))->name('contact');
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');

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

    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/confirm-password',  [AuthController::class, 'showConfirmPassword'])->name('password.confirm');
    Route::post('/confirm-password', [AuthController::class, 'confirmPassword']);
    Route::post('/logout',           [AuthController::class, 'logout'])->name('logout');
    Route::get('/check-activity',    [AuthController::class, 'checkActivity'])->name('check.activity');

    Route::get('/dashboard',     [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications', fn () => view('notifications.index'))->name('notifications.index');
    Route::get('/plan-trip',     fn () => view('plan-trip.index'))->name('plan-trip');
    Route::get('/settings',      fn () => view('settings'))->name('settings');

    Route::get('/chat', fn () => view('chat.index'))->name('chat.index');
    Route::get('/chat/{userId}', function ($userId) {
        return view('chat.index', ['other' => \App\Models\User::findOrFail($userId)]);
    })->where('userId', '[0-9]+')->name('chat.thread');

    // Agency
    Route::prefix('agency')->name('agency.')->group(function () {
        Route::get('/flights',  fn () => view('agency.flights'))->name('flights');
        Route::get('/bookings', [BookingController::class, 'agencyBookings'])->name('bookings');
    });

    // Itineraries
    Route::prefix('itineraries')->name('itineraries.')->group(function () {
        Route::get('/',        [ItineraryController::class, 'index'])->name('index');
        Route::get('/export',  [ItineraryController::class, 'export'])->name('export');
        Route::post('/export', [ItineraryController::class, 'export'])->name('export.post');
        Route::get('/{id}',    [ItineraryController::class, 'show'])->name('show');
        Route::delete('/{id}', [ItineraryController::class, 'destroy'])->name('destroy');
    });

    // Flights
    Route::get('/flights',                  [FlightController::class, 'index'])->name('flights.index');
    Route::post('/flights/search',          [FlightController::class, 'search'])->name('flights.search');
    Route::get('/flights/airports',         [FlightController::class, 'airports'])->name('flights.airports');

    // Bookings
    Route::get('/bookings',                        [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create',                 [BookingController::class, 'create'])->name('bookings.create');
    Route::get('/bookings/agency',                 [BookingController::class, 'agencyBookings'])->name('bookings.agency');
    Route::get('/bookings/{booking}',              [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel',      [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Wishlist
    Route::get('/wishlist',         [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist',        [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Profile
    Route::get('/profile',            [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',         [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password',           [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::post('/profile/picture',   [ProfileController::class, 'uploadProfilePicture'])->name('profile.picture.upload');
    Route::delete('/profile/picture', [ProfileController::class, 'deleteProfilePicture'])->name('profile.picture.delete');

    // API (auth required)
    Route::prefix('api')->group(function () {

        Route::get('/user/statistics',              [DashboardController::class, 'statistics']);
        Route::get('/user/recent-activity',         [DashboardController::class, 'recentActivity']);
        Route::get('/notifications',                [DashboardController::class, 'notifications']);
        Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead']);
        Route::post('/notifications/mark-read',     [DashboardController::class, 'markNotificationsRead']);
        Route::post('/chat/send',                   [DashboardController::class, 'sendChat']);

        Route::get('/users/search',          [MessageController::class, 'searchUsers']);
        Route::get('/conversations',         [MessageController::class, 'conversations']);
        Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
        Route::get('/messages/{userId}',     [MessageController::class, 'thread'])->where('userId', '[0-9]+');
        Route::post('/messages',             [MessageController::class, 'send']);

        Route::post('/itineraries', [ItineraryController::class, 'store']);

        Route::get('/media',                   [MediaController::class, 'index']);
        Route::post('/media/upload',           [MediaController::class, 'upload']);
        Route::delete('/media/delete',         [MediaController::class, 'delete']);
        Route::post('/media/{media}/favorite', [MediaController::class, 'toggleFavorite']);
        Route::put('/media/{media}',           [MediaController::class, 'update']);
        Route::get('/dashboard/stats',         [MediaController::class, 'stats']);

        Route::get('/trips',           [TripController::class, 'index']);
        Route::get('/trips/upcoming',  [TripController::class, 'upcoming']);
        Route::post('/trips',          [TripController::class, 'store']);
        Route::put('/trips/{id}',      [TripController::class, 'update']);
        Route::patch('/trips/{id}',    [TripController::class, 'update']);
        Route::delete('/trips/{id}',   [TripController::class, 'destroy']);

        Route::get('/wishlist/count', [WishlistController::class, 'count']);

        Route::post('/bookings/flight',         [BookingController::class, 'bookFlight']);
        Route::post('/bookings/accommodation',  [BookingController::class, 'storeAccommodation']);

        Route::middleware('throttle:60,1')->group(function () {
            Route::get('/trip-moods', [TripMoodController::class, 'index']);
        });

        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/trip-moods',            [TripMoodController::class, 'store']);
            Route::post('/trip-moods/{mood}/use', [TripMoodController::class, 'use']);
        });
    });
});
