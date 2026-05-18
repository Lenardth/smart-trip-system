<?php

namespace Database\Seeders;

use App\Models\MoodCategory;
use Illuminate\Database\Seeder;

class MoodCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'          => 'Cultural',
                'slug'          => 'cultural',
                'description'   => 'History, art & heritage',
                'icon'          => 'landmark',
                'gradient_from' => '#e3f2fd',
                'gradient_to'   => '#bbdefb',
                'color'         => '#1976d2',
                'display_order' => 1,
            ],
            [
                'name'          => 'Adventurous',
                'slug'          => 'adventurous',
                'description'   => 'Thrills & exploration',
                'icon'          => 'hiking',
                'gradient_from' => '#fff3e0',
                'gradient_to'   => '#ffe0b2',
                'color'         => '#f57c00',
                'display_order' => 2,
            ],
            [
                'name'          => 'Relaxed',
                'slug'          => 'relaxed',
                'description'   => 'Peace & tranquility',
                'icon'          => 'spa',
                'gradient_from' => '#e8f5e9',
                'gradient_to'   => '#c8e6c9',
                'color'         => '#388e3c',
                'display_order' => 3,
            ],
            [
                'name'          => 'Romantic',
                'slug'          => 'romantic',
                'description'   => 'Love & escape',
                'icon'          => 'heart',
                'gradient_from' => '#fce4ec',
                'gradient_to'   => '#f8bbd0',
                'color'         => '#c2185b',
                'display_order' => 4,
            ],
            [
                'name'          => 'Foodie',
                'slug'          => 'foodie',
                'description'   => 'Cuisine & flavor',
                'icon'          => 'utensils',
                'gradient_from' => '#fff8e1',
                'gradient_to'   => '#ffecb3',
                'color'         => '#f57f17',
                'display_order' => 5,
            ],
            [
                'name'          => 'Eco-Travel',
                'slug'          => 'eco-travel',
                'description'   => 'Nature & sustainability',
                'icon'          => 'leaf',
                'gradient_from' => '#e0f2f1',
                'gradient_to'   => '#b2dfdb',
                'color'         => '#00796b',
                'display_order' => 6,
            ],
            [
                'name'          => 'Beach',
                'slug'          => 'beach',
                'description'   => 'Sun, sand & sea',
                'icon'          => 'umbrella-beach',
                'gradient_from' => '#e1f5fe',
                'gradient_to'   => '#b3e5fc',
                'color'         => '#0288d1',
                'display_order' => 7,
            ],
            [
                'name'          => 'Nature',
                'slug'          => 'nature',
                'description'   => 'Wildlife & landscapes',
                'icon'          => 'tree',
                'gradient_from' => '#f1f8e9',
                'gradient_to'   => '#dcedc8',
                'color'         => '#558b2f',
                'display_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            MoodCategory::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
