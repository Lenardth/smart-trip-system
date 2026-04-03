<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->string('destination')->nullable()->after('user_id');
            $table->string('country')->nullable()->after('destination');
            $table->string('mood')->nullable()->after('country');
            $table->string('duration')->nullable()->after('mood');
            $table->string('companion')->nullable()->after('duration');
            $table->string('region')->nullable()->after('companion');
            $table->string('accommodation')->nullable()->after('region');
            $table->string('origin')->nullable()->after('accommodation');
            $table->string('month')->nullable()->after('origin');
            $table->unsignedInteger('estimated_cost')->nullable()->after('month');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'destination', 'country', 'mood', 'duration', 'companion',
                'region', 'accommodation', 'origin', 'month', 'estimated_cost',
            ]);
        });
    }
};
