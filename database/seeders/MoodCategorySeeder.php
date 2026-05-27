<?php

namespace Database\Seeders;

use App\Models\MoodCategory;
use Illuminate\Database\Seeder;

class MoodCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = config('discover.mood_categories', []);

        foreach ($categories as $category) {
            MoodCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
