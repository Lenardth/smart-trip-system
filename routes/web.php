<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\AiSuggestionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\TravelAdvisoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\Api\TripMoodController;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', fn () => view('landing.index'))->name('home');

// ── Setup (temporary for initial deployment) ──────────────────────────────────
Route::get('/setup', fn () => view('setup'));

Route::get('/setup/debug', function () {
    try {
        DB::purge('pgsql');
        DB::reconnect('pgsql');
        
        $total = DB::table('destinations')->count();
        $active = DB::table('destinations')->where('is_active', 1)->count();
        $notHidden = DB::table('destinations')->where('is_hidden_gem', 0)->count();
        $activeNotHidden = DB::table('destinations')
            ->where('is_active', 1)
            ->where('is_hidden_gem', 0)
            ->count();
        
        $sample = DB::table('destinations')
            ->select('id', 'name', 'country', 'is_active', 'is_hidden_gem', 'category', 'region')
            ->limit(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'counts' => [
                'total' => $total,
                'is_active_1' => $active,
                'is_hidden_gem_0' => $notHidden,
                'active_and_not_hidden' => $activeNotHidden,
            ],
            'sample_destinations' => $sample,
            'query_that_discover_uses' => 'SELECT * FROM destinations WHERE is_active = 1 AND is_hidden_gem = 0'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/setup/status', function () {
    try {
        // Purge all connections to get fresh data
        DB::purge('pgsql');
        DB::reconnect('pgsql');
        
        $destinationCount = DB::table('destinations')->count();
        $userCount = DB::table('users')->count();
        $tripMoodCount = DB::table('trip_moods')->count();
        $communityTopicCount = DB::table('community_topics')->count();
        
        return response()->json([
            'success' => true,
            'counts' => [
                'destinations' => $destinationCount,
                'users' => $userCount,
                'trip_moods' => $tripMoodCount,
                'community_topics' => $communityTopicCount,
            ],
            'message' => 'Database is accessible'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('/setup/clear-cache', function () {
    try {
        // Purge all database connections
        DB::purge('pgsql');
        DB::purge('pgsql_direct');
        
        // Clear Laravel caches
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        // Reconnect
        DB::reconnect('pgsql');
        
        return response()->json([
            'success' => true,
            'message' => 'All caches cleared and connections reset'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('/setup/migrate', function () {
    try {
        // Get the current DATABASE_URL
        $dbUrl = env('DATABASE_URL');
        
        // Create a direct connection (bypass pooler)
        $directUrl = str_replace('-pooler.', '.', $dbUrl);
        
        // Parse the direct URL
        $url = parse_url($directUrl);
        
        // Create a new direct connection config
        config(['database.connections.pgsql_direct' => [
            'driver' => 'pgsql',
            'host' => $url['host'],
            'port' => $url['port'] ?? 5432,
            'database' => ltrim($url['path'], '/'),
            'username' => $url['user'],
            'password' => $url['pass'],
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'require',
        ]]);
        
        // Purge existing connections
        DB::purge('pgsql');
        DB::purge('pgsql_direct');
        
        // Set direct connection as default
        config(['database.default' => 'pgsql_direct']);
        
        // Run migrations with direct connection
        Artisan::call('migrate', ['--force' => true]);
        
        $output = Artisan::output();
        
        // Switch back to pooler
        config(['database.default' => 'pgsql']);
        DB::purge('pgsql');
        
        return response()->json([
            'success' => true,
            'output' => $output,
            'message' => 'Migrations completed using direct connection'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile())
        ], 500);
    }
});

Route::post('/setup/fresh', function () {
    try {
        // Get the current DATABASE_URL
        $dbUrl = env('DATABASE_URL');
        
        // Create a direct connection (bypass pooler)
        $directUrl = str_replace('-pooler.', '.', $dbUrl);
        
        // Parse the direct URL
        $url = parse_url($directUrl);
        
        // Create a new direct connection config
        config(['database.connections.pgsql_direct' => [
            'driver' => 'pgsql',
            'host' => $url['host'],
            'port' => $url['port'] ?? 5432,
            'database' => ltrim($url['path'], '/'),
            'username' => $url['user'],
            'password' => $url['pass'],
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'require',
        ]]);
        
        // Purge ALL existing connections
        DB::purge('pgsql');
        DB::purge('pgsql_direct');
        
        // Set direct connection as default
        config(['database.default' => 'pgsql_direct']);
        
        // Get a fresh PDO connection
        $pdo = DB::connection('pgsql_direct')->getPdo();
        
        // Rollback any existing transaction
        try {
            $pdo->exec('ROLLBACK');
        } catch (\Exception $e) {
            // Ignore if no transaction
        }
        
        // Drop the entire public schema and recreate it (cleanest approach)
        $pdo->exec('DROP SCHEMA IF EXISTS public CASCADE');
        $pdo->exec('CREATE SCHEMA public');
        $pdo->exec('GRANT ALL ON SCHEMA public TO ' . $url['user']);
        $pdo->exec('GRANT ALL ON SCHEMA public TO public');
        
        // Purge and reconnect after schema recreation
        DB::purge('pgsql_direct');
        DB::reconnect('pgsql_direct');
        
        // Run migrations with the direct connection
        Artisan::call('migrate', ['--force' => true]);
        
        $migrateOutput = Artisan::output();
        
        // Run seeders
        Artisan::call('db:seed', ['--force' => true]);
        
        $seedOutput = Artisan::output();
        
        // Verify data was seeded
        $destinationCount = DB::connection('pgsql_direct')->table('destinations')->count();
        $userCount = DB::connection('pgsql_direct')->table('users')->count();
        
        // Switch back to pooler for normal operations
        config(['database.default' => 'pgsql']);
        DB::purge('pgsql');
        DB::purge('pgsql_direct');
        
        // Force reconnect to pooler to clear any cached state
        DB::reconnect('pgsql');
        
        // Verify pooler can see the data
        $poolerDestinationCount = DB::table('destinations')->count();
        $poolerUserCount = DB::table('users')->count();
        
        return response()->json([
            'success' => true,
            'migrate_output' => $migrateOutput,
            'seed_output' => $seedOutput,
            'message' => 'Database reset and seeded successfully using direct connection',
            'verification' => [
                'direct_connection' => [
                    'destinations' => $destinationCount,
                    'users' => $userCount,
                ],
                'pooler_connection' => [
                    'destinations' => $poolerDestinationCount,
                    'users' => $poolerUserCount,
                ]
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
            'trace' => explode("\n", $e->getTraceAsString())
        ], 500);
    }
});

// ── Database Management API (Protected by secret key) ────────────────────────
Route::prefix('api/database')->group(function () {
    Route::post('/migrate',      [\App\Http\Controllers\DatabaseController::class, 'migrate']);
    Route::post('/seed',         [\App\Http\Controllers\DatabaseController::class, 'seed']);
    Route::post('/setup',        [\App\Http\Controllers\DatabaseController::class, 'setup']);
    Route::post('/rollback',     [\App\Http\Controllers\DatabaseController::class, 'rollback']);
    Route::post('/fresh',        [\App\Http\Controllers\DatabaseController::class, 'fresh']);
    Route::get('/status',        [\App\Http\Controllers\DatabaseController::class, 'status']);
    Route::post('/clear-cache',  [\App\Http\Controllers\DatabaseController::class, 'clearCache']);
    Route::post('/optimize',     [\App\Http\Controllers\DatabaseController::class, 'optimize']);
});

Route::post('/ai/suggest', [AiSuggestionController::class, 'suggest'])
    ->middleware('throttle:20,1')
    ->name('ai.suggest');

Route::get('/api/travel-advisory', [TravelAdvisoryController::class, 'advisory'])
    ->middleware('throttle:30,1')
    ->name('api.travel-advisory');

Route::get('/api/destination-cost', [App\Http\Controllers\DestinationCostController::class, 'breakdown'])
    ->middleware('throttle:20,1')
    ->name('api.destination-cost');

Route::get('/api/travel-warning', [App\Http\Controllers\NewsController::class, 'travelWarning'])
    ->middleware('throttle:30,1')
    ->name('api.travel-warning');

Route::get('/api/destination-news', [App\Http\Controllers\NewsController::class, 'destinationNews'])
    ->middleware('throttle:30,1')
    ->name('api.destination-news');

// Currency
Route::get('/api/currency/rates',       [CurrencyController::class, 'rates'])->name('api.currency.rates');
Route::post('/api/currency/set',        [CurrencyController::class, 'setCurrency'])->name('api.currency.set');
Route::post('/api/currency/convert',    [CurrencyController::class, 'convert'])->name('api.currency.convert');

// Country Lock (for persisting country selection across pages)
Route::prefix('api/country-lock')->group(function () {
    Route::get('/',         [\App\Http\Controllers\CountryLockController::class, 'get'])->name('api.country-lock.get');
    Route::post('/lock',    [\App\Http\Controllers\CountryLockController::class, 'lock'])->name('api.country-lock.lock');
    Route::post('/unlock',  [\App\Http\Controllers\CountryLockController::class, 'unlock'])->name('api.country-lock.unlock');
});

// Location & Airport Detection (privacy-protected, opt-in only)
Route::prefix('api/location')->group(function () {
    Route::post('/detect',              [\App\Http\Controllers\LocationController::class, 'detect'])->name('api.location.detect');
    Route::get('/airports',             [\App\Http\Controllers\LocationController::class, 'airports'])->name('api.location.airports');
    Route::post('/departure-airport',   [\App\Http\Controllers\LocationController::class, 'setDepartureAirport'])->name('api.location.set-departure');
    Route::get('/departure-airport',    [\App\Http\Controllers\LocationController::class, 'getDepartureAirport'])->name('api.location.get-departure');
});

Route::get('/accommodations',           [AccommodationController::class, 'index'])->name('accommodations.index');
Route::get('/api/accommodations',       [AccommodationController::class, 'list'])->name('api.accommodations.list');
Route::get('/api/accommodation-news',   [NewsController::class, 'accommodationNews'])->name('api.accommodation-news');

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
    Route::post('/topics/{id}/like',    [CommunityController::class, 'likeTopic']);
    Route::post('/stories/{id}/like',   [CommunityController::class, 'likeStory']);
    Route::get('/stories/{id}/comments', [CommunityController::class, 'storyComments']);
    Route::post('/stories/{id}/comments', [CommunityController::class, 'storeStoryComment']);
    Route::get('/groups',               [CommunityController::class, 'groups']);
    Route::post('/groups',              [CommunityController::class, 'storeGroup']);
    Route::delete('/groups/{id}',       [CommunityController::class, 'destroyGroup']);
    Route::post('/groups/{id}/join',    [CommunityController::class, 'joinGroup']);
    Route::post('/groups/{id}/leave',   [CommunityController::class, 'leaveGroup']);
    Route::get('/groups/{id}/members',  [CommunityController::class, 'groupMembers']);
    Route::get('/tags',                 [CommunityController::class, 'tags']);
    Route::get('/stories',              [CommunityController::class, 'stories']);
    Route::get('/travelers',            [CommunityController::class, 'travelers']);
});

Route::get('/discover', [DiscoverController::class, 'index'])->name('discover');

Route::prefix('api/discover')->group(function () {
    Route::get('/destinations',          [DiscoverController::class, 'destinations'])->name('api.discover.destinations');
    Route::get('/destinations/{id}',     [DiscoverController::class, 'destinationById'])->name('api.discover.destination')->where('id', '[0-9]+');
    Route::get('/hidden-gems',           [DiscoverController::class, 'hiddenGems'])->name('api.discover.hidden-gems');
    Route::get('/search',                [DiscoverController::class, 'search'])->name('api.discover.search');
});

Route::get('/destinations',      [DestinationController::class, 'index'])->name('destinations');
Route::get('/destinations/{id}', [DestinationController::class, 'show'])->name('destinations.show')->where('id', '[0-9]+');
Route::get('/destination-info/{id}', [App\Http\Controllers\DestinationInfoController::class, 'show'])->name('destination-info.show');

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
    Route::get('/premium',       [SubscriptionController::class, 'page'])->name('premium');

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
    Route::get('/flights/create',           fn() => redirect()->route('flights.index'))->name('flights.create');
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
        Route::get('/itineraries/list', [ItineraryController::class, 'apiIndex']);

        Route::get('/media',                   [MediaController::class, 'index']);
        Route::get('/media/{media}',           [MediaController::class, 'show']);
        Route::post('/media/upload',           [MediaController::class, 'upload']);
        Route::delete('/media/delete',         [MediaController::class, 'delete']);
        Route::post('/media/{media}/favorite', [MediaController::class, 'toggleFavorite']);
        Route::patch('/media/{media}',         [MediaController::class, 'update']);
        Route::put('/media/{media}',           [MediaController::class, 'update']);
        Route::get('/dashboard/stats',         [MediaController::class, 'stats']);

        Route::get('/trips',           [TripController::class, 'index']);
        Route::get('/trips/upcoming',  [TripController::class, 'upcoming']);
        Route::post('/trips',          [TripController::class, 'store']);
        Route::patch('/trips/{id}',    [TripController::class, 'update']);
        Route::delete('/trips/{id}',   [TripController::class, 'destroy']);

        Route::get('/wishlist/count', [WishlistController::class, 'count']);

        Route::post('/bookings/flight',         [BookingController::class, 'bookFlight']);
        Route::post('/bookings/accommodation',  [BookingController::class, 'storeAccommodation']);

        Route::post('/coupon/validate',         [CouponController::class, 'validate']);

        Route::get('/subscription/status',      [SubscriptionController::class, 'status']);
        Route::post('/subscription/subscribe',  [SubscriptionController::class, 'subscribe']);
        Route::post('/subscription/cancel',     [SubscriptionController::class, 'cancel']);

        Route::middleware('throttle:60,1')->group(function () {
            Route::get('/trip-moods', [TripMoodController::class, 'index']);
        });

        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/trip-moods',            [TripMoodController::class, 'store']);
            Route::post('/trip-moods/{mood}/use', [TripMoodController::class, 'use']);
        });
    });
});
