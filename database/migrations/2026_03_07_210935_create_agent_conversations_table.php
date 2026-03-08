<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('agent_conversations');
        Schema::create('agent_conversations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('user_id')->nullable();
            $table->string('title');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('agent_conversations');
    }
};
