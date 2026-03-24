<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('country');
            $table->string('style', 50);
            $table->decimal('nightly_rate', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->decimal('rating', 2, 1)->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('accommodations')->insert([
            [
                'name' => 'Coastal Breeze Hostel',
                'city' => 'Lisbon',
                'country' => 'Portugal',
                'style' => 'hostel',
                'nightly_rate' => 38,
                'currency' => 'USD',
                'rating' => 4.3,
                'description' => 'Budget-friendly shared accommodation close to downtown and transit.',
                'image_url' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maple Court Boutique',
                'city' => 'Cape Town',
                'country' => 'South Africa',
                'style' => 'boutique',
                'nightly_rate' => 122,
                'currency' => 'USD',
                'rating' => 4.6,
                'description' => 'Charming boutique stay with local design and curated experiences.',
                'image_url' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Azure Bay Resort',
                'city' => 'Phuket',
                'country' => 'Thailand',
                'style' => 'resort',
                'nightly_rate' => 205,
                'currency' => 'USD',
                'rating' => 4.8,
                'description' => 'Full-service beachfront resort with activities and family amenities.',
                'image_url' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
