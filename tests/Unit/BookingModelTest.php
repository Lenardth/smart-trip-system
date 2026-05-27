<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeBooking(array $attrs = []): Booking
    {
        $booking = new Booking();
        foreach ($attrs as $key => $value) {
            $booking->$key = $value;
        }
        return $booking;
    }

    public function test_booking_reference_prefix(): void
    {
        $booking = Booking::create(['user_id' => User::factory()->create()->id]);
        $ref = $booking->booking_reference;

        $this->assertStringStartsWith('SB-', $ref);
        $this->assertSame(11, strlen($ref));
    }

    public function test_type_is_unknown_when_no_relations(): void
    {
        $booking = $this->makeBooking([
            'trip_id'           => null,
            'passenger_details' => null,
        ]);
        $this->assertSame('unknown', $booking->getTypeAttribute());
    }

    public function test_type_is_flights_when_flight_id_set(): void
    {
        $booking = $this->makeBooking([
            'passenger_details' => ['airline' => 'Emirates', 'departure_airport' => 'JFK', 'arrival_airport' => 'DXB'],
        ]);
        $this->assertSame('flight', $booking->getTypeAttribute());
    }

    public function test_type_is_trips_when_trip_id_set(): void
    {
        $booking = $this->makeBooking([
            'trip_id' => 5,
        ]);

        $this->assertSame('unknown', $booking->getTypeAttribute());
    }

    public function test_type_is_hotels_for_accommodation_details(): void
    {
        $booking = $this->makeBooking([
            'passenger_details' => ['type' => 'accommodation', 'name' => 'Lisbon Test Stay'],
        ]);

        $this->assertSame('hotels', $booking->getTypeAttribute());
    }

    public function test_title_uses_flight_route_when_available(): void
    {
        $booking = $this->makeBooking([
            'booking_reference' => 'SB-' . Str::random(8),
            'passenger_details' => [
                'departure_airport' => 'London (LHR)',
                'arrival_airport' => 'Dubai (DXB)',
            ],
        ]);

        $this->assertSame('London (LHR) → Dubai (DXB)', $booking->getTitleAttribute());
    }

    public function test_title_uses_accommodation_name_when_available(): void
    {
        $booking = $this->makeBooking([
            'booking_reference' => 'SB-' . Str::random(8),
            'passenger_details' => [
                'type' => 'accommodation',
                'name' => 'Lisbon Test Stay',
            ],
        ]);

        $this->assertSame('Lisbon Test Stay', $booking->getTitleAttribute());
    }

    public function test_title_falls_back_to_booking_reference(): void
    {
        $booking = $this->makeBooking([
            'booking_reference' => 'SB-TESTREF1',
            'passenger_details' => null,
        ]);

        $this->assertSame('Booking #SB-TESTREF1', $booking->getTitleAttribute());
    }
}
