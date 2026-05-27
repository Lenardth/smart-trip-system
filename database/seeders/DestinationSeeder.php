<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = [
            [
                'name' => 'Lisbon',
                'country' => 'Portugal',
                'country_code' => 'PT',
                'region' => 'Europe',
                'description' => 'Historic neighbourhoods, river views, and Atlantic breezes.',
                'image_url' => 'https://images.unsplash.com/photo-1555881400-74d7acaacd8b?w=800&q=80',
                'price_from' => 89.00,
                'tags' => ['Cultural', 'Foodie', 'Beach'],
                'is_featured' => true,
                'is_editors_choice' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Kyoto',
                'country' => 'Japan',
                'country_code' => 'JP',
                'region' => 'East Asia',
                'description' => 'Temples, gardens, and timeless traditions.',
                'image_url' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=800&q=80',
                'price_from' => 120.00,
                'tags' => ['Cultural', 'Nature', 'Photography'],
                'is_featured' => true,
                'is_editors_choice' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Santorini',
                'country' => 'Greece',
                'country_code' => 'GR',
                'region' => 'Europe',
                'description' => 'Iconic white-washed villages and stunning sunsets.',
                'image_url' => 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=800&q=80',
                'price_from' => 145.00,
                'tags' => ['Romantic', 'Beach'],
                'is_featured' => true,
                'is_editors_choice' => false,
                'display_order' => 3,
            ],
            [
                'name' => 'Bali',
                'country' => 'Indonesia',
                'country_code' => 'ID',
                'region' => 'Southeast Asia',
                'description' => 'Tropical paradise with rice terraces and spiritual vibes.',
                'image_url' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800&q=80',
                'price_from' => 75.00,
                'tags' => ['Relaxed', 'Eco-Travel'],
                'is_featured' => true,
                'is_editors_choice' => false,
                'display_order' => 4,
            ],
            [
                'name' => 'Marrakech',
                'country' => 'Morocco',
                'country_code' => 'MA',
                'region' => 'Africa',
                'description' => 'Vibrant souks, palaces, and desert adventures.',
                'image_url' => 'https://images.unsplash.com/photo-1597212618440-806262de4f6b?w=800&q=80',
                'price_from' => 68.00,
                'tags' => ['Adventurous', 'Foodie'],
                'is_featured' => true,
                'is_editors_choice' => false,
                'display_order' => 5,
            ],
            [
                'name' => 'Reykjavik',
                'country' => 'Iceland',
                'country_code' => 'IS',
                'region' => 'Europe',
                'description' => 'Northern lights, geothermal pools, and dramatic landscapes.',
                'image_url' => 'https://images.unsplash.com/photo-1504829857797-ddff29c27927?w=800&q=80',
                'price_from' => 165.00,
                'tags' => ['Adventurous', 'Nature'],
                'is_featured' => true,
                'is_editors_choice' => false,
                'display_order' => 6,
            ],
        ];

        foreach ($destinations as $destination) {
            Destination::updateOrCreate(
                ['name' => $destination['name'], 'country' => $destination['country']],
                $destination
            );
        }
    }
}
