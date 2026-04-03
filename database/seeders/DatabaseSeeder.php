<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name'  => 'Test User', 'password' => bcrypt('password')]
        );

        $flight = Flight::inRandomOrder()->first();

        $statuses = ['confirmed', 'pending', 'completed', 'cancelled'];

        foreach ($statuses as $status) {
            Booking::create([
                'user_id'           => $user->id,
                'flight_id'         => $flight?->id,
                'hotel_id'          => null,
                'trip_id'           => null,
                'seats_booked'      => rand(1, 3),
                'total_price'       => rand(300, 2500),
                'status'            => $status,
                'passenger_details' => null,
            ]);
        }
    }
}
