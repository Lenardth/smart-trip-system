<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Track likes for topics and stories
        Schema::create('community_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('likeable_type'); // CommunityTopic or CommunityStory
            $table->unsignedBigInteger('likeable_id');
            $table->timestamps();

            $table->unique(['user_id', 'likeable_type', 'likeable_id']);
            $table->index(['likeable_type', 'likeable_id']);
        });

        // Comments for community stories
        Schema::create('community_story_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained('community_stories')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('author')->nullable();
            $table->text('body');
            $table->timestamps();

            $table->index('story_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_story_comments');
        Schema::dropIfExists('community_likes');
    }
};
