<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodations', 'geoapify_id')) {
                $table->string('geoapify_id')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('accommodations', 'budget_tier')) {
                $table->string('budget_tier', 50)->nullable()->after('style');
            }
            if (!Schema::hasColumn('accommodations', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('image_url');
            }
            if (!Schema::hasColumn('accommodations', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn(['geoapify_id', 'budget_tier', 'lat', 'lng']);
        });
    }
};
