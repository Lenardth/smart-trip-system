<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_stories', function (Blueprint $table) {
            if (!Schema::hasColumn('community_stories', 'media_type')) {
                $table->enum('media_type', ['image', 'video'])->default('image')->after('image_url');
            }
            if (!Schema::hasColumn('community_stories', 'video_url')) {
                $table->string('video_url')->nullable()->after('media_type');
            }
            if (!Schema::hasColumn('community_stories', 'thumbnail_url')) {
                $table->string('thumbnail_url')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('community_stories', 'duration')) {
                $table->integer('duration')->nullable()->comment('Video duration in seconds')->after('thumbnail_url');
            }
            if (!Schema::hasColumn('community_stories', 'views')) {
                $table->unsignedInteger('views')->default(0)->after('comments');
            }
        });
    }

    public function down(): void
    {
        Schema::table('community_stories', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'video_url', 'thumbnail_url', 'duration', 'views']);
        });
    }
};
