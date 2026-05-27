<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use Illuminate\Database\Seeder;

class AccommodationSeeder extends Seeder
{
    public function run(): void
    {
        $accommodations = [
            ['name' => 'The Grand Palace Hotel', 'city' => 'Paris', 'country' => 'France', 'style' => 'hotel', 'budget_tier' => 'premium', 'nightly_rate' => 320, 'rating' => 5],
            ['name' => 'Montmartre Boutique', 'city' => 'Paris', 'country' => 'France', 'style' => 'hotel', 'budget_tier' => 'mid', 'nightly_rate' => 145, 'rating' => 4],
            ['name' => 'Paris Backpackers Hub', 'city' => 'Paris', 'country' => 'France', 'style' => 'hostel', 'budget_tier' => 'backpacker', 'nightly_rate' => 28, 'rating' => 4],
            ['name' => 'Dubai Marina Resort', 'city' => 'Dubai', 'country' => 'UAE', 'style' => 'resort', 'budget_tier' => 'luxury', 'nightly_rate' => 580, 'rating' => 5],
            ['name' => 'Downtown Dubai Apartments', 'city' => 'Dubai', 'country' => 'UAE', 'style' => 'apartment', 'budget_tier' => 'mid', 'nightly_rate' => 180, 'rating' => 4],
            ['name' => 'Burj View Hotel', 'city' => 'Dubai', 'country' => 'UAE', 'style' => 'hotel', 'budget_tier' => 'premium', 'nightly_rate' => 420, 'rating' => 5],
            ['name' => 'London City Hostel', 'city' => 'London', 'country' => 'UK', 'style' => 'hostel', 'budget_tier' => 'backpacker', 'nightly_rate' => 35, 'rating' => 4],
            ['name' => 'Covent Garden Hotel', 'city' => 'London', 'country' => 'UK', 'style' => 'hotel', 'budget_tier' => 'premium', 'nightly_rate' => 380, 'rating' => 5],
            ['name' => 'Shoreditch Apartments', 'city' => 'London', 'country' => 'UK', 'style' => 'apartment', 'budget_tier' => 'mid', 'nightly_rate' => 160, 'rating' => 4],
            ['name' => 'Bali Ubud Resort', 'city' => 'Bali', 'country' => 'Indonesia', 'style' => 'resort', 'budget_tier' => 'premium', 'nightly_rate' => 220, 'rating' => 5],
            ['name' => 'Seminyak Beach Villa', 'city' => 'Bali', 'country' => 'Indonesia', 'style' => 'resort', 'budget_tier' => 'luxury', 'nightly_rate' => 450, 'rating' => 5],
            ['name' => 'Kuta Budget Inn', 'city' => 'Bali', 'country' => 'Indonesia', 'style' => 'guest_house', 'budget_tier' => 'budget', 'nightly_rate' => 45, 'rating' => 3],
            ['name' => 'Tokyo Shinjuku Hotel', 'city' => 'Tokyo', 'country' => 'Japan', 'style' => 'hotel', 'budget_tier' => 'mid', 'nightly_rate' => 130, 'rating' => 4],
            ['name' => 'Shibuya Capsule Inn', 'city' => 'Tokyo', 'country' => 'Japan', 'style' => 'hostel', 'budget_tier' => 'backpacker', 'nightly_rate' => 40, 'rating' => 4],
            ['name' => 'Park Hyatt Tokyo', 'city' => 'Tokyo', 'country' => 'Japan', 'style' => 'hotel', 'budget_tier' => 'luxury', 'nightly_rate' => 620, 'rating' => 5],
            ['name' => 'New York Times Square Hotel', 'city' => 'New York', 'country' => 'USA', 'style' => 'hotel', 'budget_tier' => 'mid', 'nightly_rate' => 280, 'rating' => 4],
            ['name' => 'Brooklyn Boutique Stay', 'city' => 'New York', 'country' => 'USA', 'style' => 'hotel', 'budget_tier' => 'budget', 'nightly_rate' => 120, 'rating' => 4],
            ['name' => 'Manhattan Luxury Suites', 'city' => 'New York', 'country' => 'USA', 'style' => 'hotel', 'budget_tier' => 'luxury', 'nightly_rate' => 750, 'rating' => 5],
            ['name' => 'Barcelona Gothic Quarter Hotel', 'city' => 'Barcelona', 'country' => 'Spain', 'style' => 'hotel', 'budget_tier' => 'mid', 'nightly_rate' => 140, 'rating' => 4],
            ['name' => 'Barceloneta Beach Hostel', 'city' => 'Barcelona', 'country' => 'Spain', 'style' => 'hostel', 'budget_tier' => 'backpacker', 'nightly_rate' => 30, 'rating' => 4],
            ['name' => 'Singapore Marina Bay Hotel', 'city' => 'Singapore', 'country' => 'Singapore', 'style' => 'hotel', 'budget_tier' => 'premium', 'nightly_rate' => 380, 'rating' => 5],
            ['name' => 'Chinatown Budget Inn', 'city' => 'Singapore', 'country' => 'Singapore', 'style' => 'guest_house', 'budget_tier' => 'budget', 'nightly_rate' => 65, 'rating' => 3],
            ['name' => 'Cape Town Waterfront Hotel', 'city' => 'Cape Town', 'country' => 'South Africa', 'style' => 'hotel', 'budget_tier' => 'mid', 'nightly_rate' => 110, 'rating' => 4],
            ['name' => 'Camps Bay Beach Resort', 'city' => 'Cape Town', 'country' => 'South Africa', 'style' => 'resort', 'budget_tier' => 'premium', 'nightly_rate' => 280, 'rating' => 5],
            ['name' => 'Sydney Harbour Hotel', 'city' => 'Sydney', 'country' => 'Australia', 'style' => 'hotel', 'budget_tier' => 'premium', 'nightly_rate' => 340, 'rating' => 5],
            ['name' => 'Bondi Beach Hostel', 'city' => 'Sydney', 'country' => 'Australia', 'style' => 'hostel', 'budget_tier' => 'backpacker', 'nightly_rate' => 38, 'rating' => 4],
            ['name' => 'Bangkok Riverside Resort', 'city' => 'Bangkok', 'country' => 'Thailand', 'style' => 'resort', 'budget_tier' => 'mid', 'nightly_rate' => 95, 'rating' => 4],
            ['name' => 'Khao San Road Hostel', 'city' => 'Bangkok', 'country' => 'Thailand', 'style' => 'hostel', 'budget_tier' => 'backpacker', 'nightly_rate' => 18, 'rating' => 3],
            ['name' => 'Rome Colosseum View Hotel', 'city' => 'Rome', 'country' => 'Italy', 'style' => 'hotel', 'budget_tier' => 'mid', 'nightly_rate' => 160, 'rating' => 4],
            ['name' => 'Trastevere Boutique Inn', 'city' => 'Rome', 'country' => 'Italy', 'style' => 'hotel', 'budget_tier' => 'budget', 'nightly_rate' => 85, 'rating' => 4],
        ];

        foreach ($accommodations as $a) {
            $geoapifyId = 'seed_' . strtolower(str_replace(' ', '_', $a['name']));

            Accommodation::updateOrCreate(['geoapify_id' => $geoapifyId], array_merge($a, [
                'geoapify_id' => $geoapifyId,
                'image_url'   => 'https://picsum.photos/seed/' . urlencode($a['name']) . '/400/280',
                'is_active'   => true,
                'amenities'   => null,
                'description' => $a['name'] . ' in ' . $a['city'] . ', ' . $a['country'] . '. A great place to stay.',
            ]));
        }
    }
}
