<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->json('tags')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('replies_count')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_pinned');
            $table->index('created_at');
        });

        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_topic_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('body');
            $table->timestamps();

            $table->index(['forum_topic_id', 'created_at']);
            $table->index('user_id');
        });

        Schema::create('travel_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->string('image_url')->nullable();
            $table->string('destination')->nullable();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_published');
            $table->index('created_at');
        });

        Schema::create('story_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_story_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['travel_story_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('story_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_story_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('body');
            $table->timestamps();

            $table->index(['travel_story_id', 'created_at']);
            $table->index('user_id');
        });

        Schema::create('group_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('destination');
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('icon')->nullable();
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->unsignedSmallInteger('spots_total');
            $table->unsignedSmallInteger('spots_taken')->default(0);
            $table->enum('status', ['open', 'full', 'cancelled', 'completed'])->default('open');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('departure_date');
        });

        Schema::create('group_trip_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->unique(['group_trip_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_trip_members');
        Schema::dropIfExists('group_trips');
        Schema::dropIfExists('story_comments');
        Schema::dropIfExists('story_likes');
        Schema::dropIfExists('travel_stories');
        Schema::dropIfExists('forum_replies');
        Schema::dropIfExists('forum_topics');
    }
};
