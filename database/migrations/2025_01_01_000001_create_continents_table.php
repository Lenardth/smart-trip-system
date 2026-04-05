<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('continents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2)->unique();
            $table->string('emoji')->nullable();
            $table->timestamps();
        });

        DB::table('continents')->insert([
            ['name' => 'Africa',        'code' => 'AF', 'emoji' => '🌍', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Antarctica',    'code' => 'AN', 'emoji' => '🧊', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Asia',          'code' => 'AS', 'emoji' => '🌏', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Europe',        'code' => 'EU', 'emoji' => '🌍', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'North America', 'code' => 'NA', 'emoji' => '🌎', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Oceania',       'code' => 'OC', 'emoji' => '🌏', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'South America', 'code' => 'SA', 'emoji' => '🌎', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('continents');
    }
};
