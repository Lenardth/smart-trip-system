<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('source')->nullable()->after('image_url');
            $table->string('source_id')->nullable()->after('source');
            $table->string('country_code', 2)->nullable()->after('country');
            $table->decimal('lat', 10, 6)->nullable()->after('source_id');
            $table->decimal('lng', 10, 6)->nullable()->after('lat');
            $table->json('raw_data')->nullable()->after('tags');
            $table->index(['source', 'source_id']);
            $table->index(['country_code']);
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropIndex(['source', 'source_id']);
            $table->dropIndex(['country_code']);
            $table->dropColumn(['source', 'source_id', 'country_code', 'lat', 'lng', 'raw_data']);
        });
    }
};
