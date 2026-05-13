<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TripMoodSeeder::class,
            CouponSeeder::class,
            AccommodationSeeder::class,
        ]);
    }
}
