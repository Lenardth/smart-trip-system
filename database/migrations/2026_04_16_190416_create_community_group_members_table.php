<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('community_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('accepted');
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
            $table->index('group_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_group_members');
    }
};
