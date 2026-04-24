<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // AI suggestion metadata (only add if not exists)
            if (!Schema::hasColumn('trips', 'travel_tip')) {
                $table->text('travel_tip')->nullable()->after('description');
            }
            if (!Schema::hasColumn('trips', 'visa_info')) {
                $table->text('visa_info')->nullable()->after('travel_tip');
            }
            if (!Schema::hasColumn('trips', 'flight_info')) {
                $table->text('flight_info')->nullable()->after('visa_info');
            }
            if (!Schema::hasColumn('trips', 'best_time_to_visit')) {
                $table->string('best_time_to_visit')->nullable()->after('flight_info');
            }
            if (!Schema::hasColumn('trips', 'is_good_right_now')) {
                $table->boolean('is_good_right_now')->default(false)->after('best_time_to_visit');
            }
            
            // Validation data
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
            $columns = [
                'travel_tip',
                'visa_info',
                'flight_info',
                'best_time_to_visit',
                'is_good_right_now',
                'validation_data',
                'weather_data',
                'safety_data',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('trips', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
