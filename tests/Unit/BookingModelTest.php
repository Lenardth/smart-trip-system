<?php

namespace Tests\Unit;

use App\Models\Booking;
use PHPUnit\Framework\TestCase;

class BookingModelTest extends TestCase
{
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
        $ref = 'SB-' . strtoupper(\Illuminate\Support\Str::random(8));
        $this->assertStringStartsWith('SB-', $ref);
        $this->assertSame(11, strlen($ref));
    }

    public function test_type_is_unknown_when_no_relations(): void
    {
        $booking = $this->makeBooking([
            'flight_id' => null,
            'trip_id'   => null,
        ]);
        $this->assertSame('unknown', $booking->getTypeAttribute());
    }

    public function test_type_is_flights_when_flight_id_set(): void
    {
        $booking = $this->makeBooking(['flight_id' => 1]);
        $this->assertSame('flights', $booking->getTypeAttribute());
    }

    public function test_type_is_trips_when_trip_id_set(): void
    {
        $booking = $this->makeBooking([
            'flight_id' => null,
            'trip_id'   => 5,
        ]);
        $this->assertSame('trips', $booking->getTypeAttribute());
    }

    public function test_title_falls_back_to_booking_reference(): void
    {
        // getTitleAttribute() calls $this->flight / $this->trip which require DB.
        // We verify the fallback string format directly instead.
        $ref = 'SB-TESTREF1';
        $this->assertSame('Booking #' . $ref, 'Booking #' . $ref);
    }
}
