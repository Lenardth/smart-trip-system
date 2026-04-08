<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Lenard Hlabangwana', 'email' => 'admin@smartbooking.app',  'user_type' => 'agency'],
            ['name' => 'Sarah Mitchell',      'email' => 'sarah@example.com',       'user_type' => 'user'],
            ['name' => 'James Okafor',        'email' => 'james@example.com',       'user_type' => 'user'],
            ['name' => 'Priya Sharma',        'email' => 'priya@example.com',       'user_type' => 'user'],
            ['name' => 'Marco Rossi',         'email' => 'marco@example.com',       'user_type' => 'user'],
            ['name' => 'Yuki Tanaka',         'email' => 'yuki@example.com',        'user_type' => 'user'],
            ['name' => 'Emma Laurent',        'email' => 'emma@example.com',        'user_type' => 'user'],
            ['name' => 'Test User',           'email' => 'test@example.com',        'user_type' => 'user'],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                array_merge($u, ['password' => Hash::make('password')])
            );
        }

        $this->command->info('✓ Users seeded');

        $this->call(DestinationSeeder::class);
        $this->command->info('✓ Destinations seeded');

        try {
            $this->call(CommunitySeeder::class);
            $this->command->info('✓ Community seeded');
        } catch (\Throwable $e) {
            $this->command->warn('⚠ Community seeded skipped: ' . $e->getMessage());
        }

        try {
            $this->call(TripMoodSeeder::class);
            $this->command->info('✓ Trip moods seeded');
        } catch (\Throwable $e) {
            $this->command->warn('⚠ Trip moods skipped: ' . $e->getMessage());
        }

        try {
            $this->call(CouponSeeder::class);
            $this->command->info('✓ Coupons seeded');
        } catch (\Throwable $e) {
            $this->command->warn('⚠ Coupons skipped: ' . $e->getMessage());
        }

        try {
            $this->call(AccommodationSeeder::class);
            $this->command->info('✓ Accommodations seeded');
        } catch (\Throwable $e) {
            $this->command->warn('⚠ Accommodations skipped: ' . $e->getMessage());
        }

        try {
            $this->call(ItineraryDestinationSeeder::class);
            $this->command->info('✓ Itinerary destinations seeded');
        } catch (\Throwable $e) {
            $this->command->warn('⚠ Itinerary destinations skipped: ' . $e->getMessage());
        }
    }
}
