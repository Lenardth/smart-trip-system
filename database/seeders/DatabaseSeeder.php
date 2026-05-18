<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\DestinationSeeder;
use Database\Seeders\MoodCategorySeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TripMoodSeeder::class,
            CouponSeeder::class,
            AccommodationSeeder::class,
            DestinationSeeder::class,
            MoodCategorySeeder::class,
        ]);
    }
}
