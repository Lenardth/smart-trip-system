<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->string('type')->default('image'); // image or video
            $table->string('location')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('trip_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
