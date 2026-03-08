<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('community_topics', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->string('title');
            $table->json('tags')->nullable();
            $table->text('body')->nullable();
            $table->unsignedInteger('replies')->default(0);
            $table->timestamps();
        });

        Schema::create('community_groups', function (Blueprint $table) {
            $table->id();
            $table->string('organizer');
            $table->string('name');
            $table->string('destination');
            $table->string('date');
            $table->unsignedInteger('spots_left')->default(0);
            $table->enum('status', ['open', 'full', 'closed'])->default('open');
            $table->timestamps();
        });

        Schema::create('community_stories', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_stories');
        Schema::dropIfExists('community_groups');
        Schema::dropIfExists('community_topics');
    }
};
