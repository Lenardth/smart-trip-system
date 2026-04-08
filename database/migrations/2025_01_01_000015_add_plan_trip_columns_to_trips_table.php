<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'destination'))   $table->string('destination')->nullable()->after('user_id');
            if (!Schema::hasColumn('trips', 'country'))       $table->string('country')->nullable()->after('destination');
            if (!Schema::hasColumn('trips', 'mood'))          $table->string('mood')->nullable()->after('country');
            if (!Schema::hasColumn('trips', 'duration'))      $table->string('duration')->nullable()->after('mood');
            if (!Schema::hasColumn('trips', 'companion'))     $table->string('companion')->nullable()->after('duration');
            if (!Schema::hasColumn('trips', 'region'))        $table->string('region')->nullable()->after('companion');
            if (!Schema::hasColumn('trips', 'accommodation')) $table->string('accommodation')->nullable()->after('region');
            if (!Schema::hasColumn('trips', 'origin'))        $table->string('origin')->nullable()->after('accommodation');
            if (!Schema::hasColumn('trips', 'month'))         $table->string('month')->nullable()->after('origin');
            if (!Schema::hasColumn('trips', 'estimated_cost'))$table->unsignedInteger('estimated_cost')->nullable()->after('month');
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
