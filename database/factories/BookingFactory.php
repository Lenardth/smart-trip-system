<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $flight = Flight::inRandomOrder()->first();

        return [
            'user_id'            => User::inRandomOrder()->first()?->id ?? User::factory(),
            'flight_id'          => $flight?->id,
            'hotel_id'           => null,
            'trip_id'            => null,
            'booking_reference'  => 'SB-' . strtoupper(Str::random(8)),
            'seats_booked'       => $this->faker->numberBetween(1, 4),
            'total_price'        => $this->faker->randomFloat(2, 200, 3000),
            'status'             => $this->faker->randomElement(['confirmed', 'pending', 'completed', 'cancelled']),
            'passenger_details'  => null,
        ];
    }
}
