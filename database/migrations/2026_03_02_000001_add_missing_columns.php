<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->integer('total_seats')->nullable()->after('seats_available');
            $table->string('aircraft_type', 50)->nullable()->after('total_seats');
        });

        Schema::table('destinations', function (Blueprint $table) {
            $table->string('category')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropColumn(['total_seats', 'aircraft_type']);
        });

        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
