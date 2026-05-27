<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Destination;
use App\Models\FlightListing;
use App\Models\TripMood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_and_currency_apis_return_json(): void
    {
        $this->seedDestinations(20);
        Destination::create([
            'name' => 'Porto Test',
            'country' => 'Portugal',
            'country_code' => 'PT',
            'region' => 'Test Region',
            'description' => 'Porto Test description.',
            'image_url' => 'https://example.com/porto-test.jpg',
            'price_from' => 140,
            'tags' => ['Cultural'],
            'is_featured' => true,
            'display_order' => 21,
            'is_active' => true,
        ]);
        $this->seedAccommodation();

        $this->getJson('/api/landing/destinations')
            ->assertOk()
            ->assertJsonStructure(['destinations']);

        $this->getJson('/api/landing/destinations?mood=Cultural&budget=backpacker&companion=solo')
            ->assertOk()
            ->assertJsonStructure(['destinations' => [['match_score']]])
            ->assertJsonPath('destinations.0.match_score', fn ($score) => is_int($score) && $score > 0);

        $this->getJson('/api/discover')
            ->assertOk()
            ->assertJsonStructure(['destinations']);

        $this->getJson('/api/discover?mood=Cultural&budget=backpacker&companion=solo')
            ->assertOk()
            ->assertJsonStructure(['destinations' => [['match_score']]])
            ->assertJsonPath('destinations.0.match_score', fn ($score) => is_int($score) && $score > 0);

        $countryResponse = $this->getJson('/api/discover?region=PT')
            ->assertOk()
            ->assertJsonCount(2, 'destinations')
            ->assertJsonPath('source', 'database-local');

        $this->assertSame(
            ['Portugal'],
            collect($countryResponse->json('destinations'))->pluck('country')->unique()->values()->all()
        );

        $countryQueryResponse = $this->getJson('/api/discover?q=Portugal')
            ->assertOk()
            ->assertJsonCount(2, 'destinations')
            ->assertJsonPath('source', 'database-local');

        $this->assertSame(
            ['Portugal'],
            collect($countryQueryResponse->json('destinations'))->pluck('country')->unique()->values()->all()
        );

        $this->getJson('/api/accommodations')
            ->assertOk()
            ->assertJsonStructure(['accommodations', 'cached']);

        $this->getJson('/api/accommodation-news?q=Paris')
            ->assertOk()
            ->assertJsonStructure(['articles', 'query']);

        $this->getJson('/api/travel-warning?q=Paris')
            ->assertOk()
            ->assertJsonStructure(['warnings']);

        $this->getJson('/api/currency/rates?base=USD')
            ->assertOk()
            ->assertJsonStructure(['success', 'base', 'rates', 'updated_at', 'currencies']);

        $this->postJson('/api/currency/set', ['currency' => 'EUR'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('currency', 'EUR');

        $this->postJson('/api/currency/convert', [
            'amount' => 100,
            'from' => 'USD',
            'to' => 'EUR',
        ])
            ->assertOk()
            ->assertJsonStructure(['success', 'amount', 'formatted', 'symbol', 'currency']);

        $this->postJson('/api/currency/convert', [
            'amount' => 100,
            'from' => 'USD',
            'to' => 'XXX',
        ])
            ->assertUnprocessable();
    }

    public function test_discover_page_renders_default_moods_without_seed_data(): void
    {
        $this->get('/discover')
            ->assertOk()
            ->assertSee('All Moods')
            ->assertSee('Cultural')
            ->assertSee('Adventurous')
            ->assertSee('Beach');
    }

    public function test_discover_country_search_returns_country_scoped_fallback_when_empty(): void
    {
        $response = $this->getJson('/api/discover?region=PT')
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('destinations.0.name', 'Portugal')
            ->assertJsonPath('destinations.0.country', 'Portugal')
            ->assertJsonPath('destinations.0.country_code', 'PT');

        $this->assertSame(
            ['Portugal'],
            collect($response->json('destinations'))->pluck('country')->unique()->values()->all()
        );
    }

    public function test_discover_country_search_is_limited_to_sixteen_tiles(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Destination::create([
                'name' => "Portugal Tile {$i}",
                'country' => 'Portugal',
                'country_code' => 'PT',
                'region' => 'Portugal',
                'description' => "Portugal Tile {$i} description.",
                'image_url' => "https://example.com/portugal-tile-{$i}.jpg",
                'price_from' => 100 + $i,
                'tags' => ['Cultural'],
                'display_order' => $i,
                'is_active' => true,
            ]);
        }

        $response = $this->getJson('/api/discover?region=PT')
            ->assertOk()
            ->assertJsonPath('count', 16)
            ->assertJsonCount(16, 'destinations');

        $this->assertSame(
            ['Portugal'],
            collect($response->json('destinations'))->pluck('country')->unique()->values()->all()
        );
    }

    public function test_ai_suggestions_api_fails_gracefully_without_api_key(): void
    {
        config(['services.groq.api_key' => null]);

        $this->postJson('/ai/suggest', [
            'mood' => 'adventurous',
            'budget' => 'budget',
            'duration' => 'week',
            'companion' => 'solo',
        ])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message']);
    }

    public function test_news_api_uses_newsapi_fallback_and_filters_articles(): void
    {
        config([
            'services.gnews.api_key' => null,
            'services.newsapi.key' => 'test-newsapi-key',
        ]);

        Http::fake([
            'newsapi.org/v2/everything*' => Http::response([
                'articles' => [
                    [
                        'title' => 'Paris travel safety update',
                        'description' => 'Transport updates for visitors.',
                        'url' => 'https://example.com/paris-travel',
                        'publishedAt' => now()->toIso8601String(),
                        'source' => ['name' => 'Example News'],
                    ],
                    [
                        'title' => '[Removed]',
                        'description' => '[Removed]',
                        'url' => 'https://example.com/removed',
                        'source' => ['name' => 'Removed'],
                    ],
                    [
                        'title' => 'Unsafe URL article',
                        'description' => 'This should keep text but neutralize the URL.',
                        'url' => 'javascript:alert(1)',
                        'source' => ['name' => 'Bad Source'],
                    ],
                ],
            ]),
        ]);

        $response = $this->getJson('/api/accommodation-news?q=Paris')
            ->assertOk()
            ->assertJsonPath('articles.0.title', 'Paris travel safety update')
            ->assertJsonPath('articles.1.url', '#');

        $this->assertCount(2, $response->json('articles'));
    }

    public function test_travel_warning_uses_newsapi_when_gnews_is_missing(): void
    {
        config([
            'services.gnews.api_key' => null,
            'services.newsapi.key' => 'test-newsapi-key',
        ]);

        Http::fake([
            'newsapi.org/v2/everything*' => Http::response([
                'articles' => [
                    [
                        'title' => 'Paris travel safety warning issued',
                        'description' => 'Visitors should watch for transport strike alerts.',
                        'url' => 'https://example.com/paris-warning',
                        'publishedAt' => now()->toIso8601String(),
                        'source' => ['name' => 'Example News'],
                    ],
                ],
            ]),
        ]);

        $this->getJson('/api/travel-warning?q=Paris')
            ->assertOk()
            ->assertJsonPath('warnings.0.title', 'Paris travel safety warning issued')
            ->assertJsonPath('warnings.0.source.name', 'Example News');
    }

    public function test_authenticated_trip_mood_dashboard_and_search_apis_return_json(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        TripMood::create([
            'label' => 'Calm',
            'label_normalized' => 'calm',
            'use_count' => 2,
            'created_by' => $user->id,
        ]);

        $this->getJson('/api/user/statistics')
            ->assertOk()
            ->assertJsonStructure(['trips', 'bookings', 'stay_searches', 'flights', 'hotels', 'spent']);

        $this->getJson('/api/user/recent-activity')
            ->assertOk()
            ->assertJsonStructure(['activities']);

        $this->getJson('/api/trips')
            ->assertOk()
            ->assertJsonStructure(['trips']);

        $this->getJson('/api/trips/upcoming')
            ->assertOk()
            ->assertJsonStructure(['trips']);

        $this->getJson('/api/trip-moods')
            ->assertOk()
            ->assertJsonStructure(['moods']);

        $this->postJson('/api/trip-moods', ['label' => 'Focused'])
            ->assertCreated()
            ->assertJsonStructure(['mood']);

        $this->getJson('/api/accommodation-searches')
            ->assertOk()
            ->assertJsonStructure(['searches']);
    }

    public function test_trip_crud_api_works_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $create = $this->postJson('/api/trips', [
            'destination' => 'Lisbon',
            'country' => 'Portugal',
            'budget' => 'mid',
            'duration' => 'week',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'trip']);

        $tripId = $create->json('trip.id');

        $this->patchJson("/api/trips/{$tripId}", [
            'notes' => 'Pack light.',
            'status' => 'planned',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('trip.notes', 'Pack light.');

        $this->deleteJson("/api/trips/{$tripId}")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_flight_airport_booking_and_coupon_apis_work_with_local_fallbacks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => 10,
            'min_order' => 0,
            'uses_per_user' => 1,
            'is_active' => true,
        ]);

        $this->getJson('/flights/airports?keyword=London')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'results']);

        $flightSearch = $this->postJson('/flights/search', [
            'from' => 'London',
            'to' => 'Dubai',
            'departure_date' => now()->addDays(3)->format('Y-m-d'),
            'adults' => 1,
            'travel_class' => 'ECONOMY',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'from_code', 'to_code', 'flights', 'count']);

        $this->assertNotEmpty($flightSearch->json('flights'));

        $this->postJson('/api/coupon/validate', [
            'code' => 'SAVE10',
            'subtotal' => 200,
        ])
            ->assertOk()
            ->assertJsonPath('valid', true);

        $this->postJson('/api/bookings/flight', [
            'airline' => 'SmartJet Airways',
            'flight_number' => 'SJ220',
            'departure_date' => now()->addDays(3)->format('Y-m-d'),
            'price' => 200,
            'adults' => 1,
            'travel_class' => 'PREMIUM_ECONOMY',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['booking_reference', 'total_price']);

        $this->postJson('/api/bookings/flight', [
            'airline' => 'SmartJet Airways',
            'flight_number' => 'SJ220',
            'departure_date' => now()->subDay()->format('Y-m-d'),
            'price' => 200,
            'adults' => 1,
            'travel_class' => 'ECONOMY',
        ])
            ->assertUnprocessable();
    }

    public function test_agency_flight_listing_booking_reserves_last_seat_safely(): void
    {
        $agency = User::factory()->create(['user_type' => 'agency', 'agency_name' => 'Sky Test Agency']);
        $traveler = User::factory()->create();

        $listing = FlightListing::create([
            'agency_id' => $agency->id,
            'airline' => 'Agency Air',
            'flight_number' => 'AG101',
            'departure_airport' => 'London Heathrow (LHR)',
            'arrival_airport' => 'Dubai (DXB)',
            'departure_iata' => 'LHR',
            'arrival_iata' => 'DXB',
            'departure_date' => now()->addDays(3)->format('Y-m-d'),
            'departure_time' => '09:00',
            'arrival_time' => '19:00',
            'duration' => '7h 0m',
            'travel_class' => 'ECONOMY',
            'price' => 250,
            'seats_total' => 1,
            'seats_available' => 1,
            'status' => 'published',
        ]);

        $this->actingAs($traveler);

        $this->postJson('/flights/search', [
            'from' => 'London',
            'to' => 'Dubai',
            'departure_date' => $listing->departure_date->format('Y-m-d'),
            'adults' => 1,
            'travel_class' => 'ECONOMY',
        ])
            ->assertOk()
            ->assertJsonFragment(['flight_listing_id' => $listing->id]);

        $payload = [
            'flight_listing_id' => $listing->id,
            'airline' => 'Agency Air',
            'flight_number' => 'AG101',
            'departure_airport' => 'London Heathrow (LHR)',
            'arrival_airport' => 'Dubai (DXB)',
            'departure_date' => $listing->departure_date->format('Y-m-d'),
            'price' => 250,
            'adults' => 1,
            'travel_class' => 'ECONOMY',
        ];

        $this->postJson('/api/bookings/flight', $payload)
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertSame(0, $listing->fresh()->seats_available);

        $this->postJson('/api/bookings/flight', $payload)
            ->assertStatus(409);

        $this->actingAs($agency)
            ->get('/agency/bookings')
            ->assertOk()
            ->assertSee('AG101');
    }

    public function test_accommodation_booking_and_cancel_api_work(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $accommodation = $this->seedAccommodation();

        $bookingResponse = $this->postJson('/api/bookings/accommodation', [
            'accommodation_id' => $accommodation->id,
            'check_in' => now()->addDays(5)->format('Y-m-d'),
            'check_out' => now()->addDays(8)->format('Y-m-d'),
            'guests' => 2,
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['booking_reference', 'total_price']);

        $booking = Booking::where('booking_reference', $bookingResponse->json('booking_reference'))->firstOrFail();

        $this->postJson("/bookings/{$booking->id}/cancel")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    private function seedAccommodation(): Accommodation
    {
        return Accommodation::updateOrCreate(
            ['geoapify_id' => 'test_lisbon_stay'],
            [
                'name' => 'Lisbon Test Stay',
                'city' => 'Lisbon',
                'country' => 'Portugal',
                'style' => 'hotel',
                'budget_tier' => 'mid',
                'nightly_rate' => 120,
                'rating' => 4.5,
                'is_active' => true,
            ]
        );
    }

    private function seedDestinations(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $country = $i === 1 ? 'Portugal' : "Country {$i}";
            $code = $i === 1 ? 'PT' : null;

            Destination::updateOrCreate(
                ['name' => "Destination {$i}", 'country' => $country],
                [
                    'country_code' => $code,
                    'region' => 'Test Region',
                    'description' => "Destination {$i} description.",
                    'image_url' => "https://example.com/destination-{$i}.jpg",
                    'price_from' => 100 + $i,
                    'tags' => ['Cultural'],
                    'is_featured' => true,
                    'display_order' => $i,
                    'is_active' => true,
                ]
            );
        }
    }
}
