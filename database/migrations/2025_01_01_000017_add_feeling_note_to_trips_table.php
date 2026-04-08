<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('trips', 'feeling_note')) return;

        Schema::table('trips', function (Blueprint $table) {
            $table->text('feeling_note')->nullable()->after('mood');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('feeling_note');
        });
    }
};
