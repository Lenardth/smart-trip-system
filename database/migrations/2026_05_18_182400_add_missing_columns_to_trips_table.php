<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Add description column
            if (!Schema::hasColumn('trips', 'description')) {
                $table->text('description')->nullable()->after('estimated_cost');
            }
            
            // Add individual cost columns
            if (!Schema::hasColumn('trips', 'flight_cost')) {
                $table->decimal('flight_cost', 10, 2)->nullable()->after('description');
            }
            if (!Schema::hasColumn('trips', 'accommodation_cost')) {
                $table->decimal('accommodation_cost', 10, 2)->nullable()->after('flight_cost');
            }
            if (!Schema::hasColumn('trips', 'activities_cost')) {
                $table->decimal('activities_cost', 10, 2)->nullable()->after('accommodation_cost');
            }
            if (!Schema::hasColumn('trips', 'food_cost')) {
                $table->decimal('food_cost', 10, 2)->nullable()->after('activities_cost');
            }
            if (!Schema::hasColumn('trips', 'transport_cost')) {
                $table->decimal('transport_cost', 10, 2)->nullable()->after('food_cost');
            }
            
            // Add activities and cities columns
            if (!Schema::hasColumn('trips', 'activities')) {
                $table->json('activities')->nullable()->after('cost_breakdown');
            }
            if (!Schema::hasColumn('trips', 'cities_to_visit')) {
                $table->json('cities_to_visit')->nullable()->after('activities');
            }
            
            // Add validation data columns
            if (!Schema::hasColumn('trips', 'validation_data')) {
                $table->json('validation_data')->nullable()->after('is_good_right_now');
            }
            if (!Schema::hasColumn('trips', 'weather_data')) {
                $table->json('weather_data')->nullable()->after('validation_data');
            }
            if (!Schema::hasColumn('trips', 'safety_data')) {
                $table->json('safety_data')->nullable()->after('weather_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'flight_cost',
                'accommodation_cost',
                'activities_cost',
                'food_cost',
                'transport_cost',
                'activities',
                'cities_to_visit',
                'validation_data',
                'weather_data',
                'safety_data',
            ]);
        });
    }
};
