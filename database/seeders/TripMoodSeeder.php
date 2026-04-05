<?php

namespace Database\Seeders;

use App\Models\TripMood;
use Illuminate\Database\Seeder;

class TripMoodSeeder extends Seeder
{
    public function run(): void
    {
        $moods = [
            'Adventurous',
            'Relaxed & Peaceful',
            'Romantic Escape',
            'Cultural Immersion',
            'Foodie Paradise',
            'Eco & Nature',
            'City Break',
            'Beach & Sun',
            'Spiritual Journey',
            'Off the Beaten Path',
            'Wellness Retreat',
            'Family Fun',
            'Backpacking',
            'Luxury Escape',
            'Photography Trip',
        ];

        foreach ($moods as $label) {
            TripMood::firstOrCreate(
                ['label_normalized' => TripMood::normalize($label)],
                ['label' => $label, 'use_count' => rand(3, 15)]
            );
        }
    }
}
