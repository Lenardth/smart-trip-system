<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('api_responses')) {
            Schema::create('api_responses', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 60);
                $table->string('endpoint', 160);
                $table->string('cache_key', 64)->unique();
                $table->json('params')->nullable();
                $table->longText('payload')->nullable();
                $table->string('status', 30)->default('ok');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->index(['provider', 'endpoint']);
                $table->index('expires_at');
            });
        }

        $this->addSearchColumns('flight_searches');
        $this->addSearchColumns('accommodation_searches');
        $this->addSearchColumns('destination_searches');

        if (Schema::hasTable('accommodation_searches') && Schema::hasColumn('accommodation_searches', 'user_id')) {
            Schema::table('accommodation_searches', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['flight_searches', 'accommodation_searches', 'destination_searches'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['search_hash', 'request_payload', 'response_payload', 'cache_hit'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('api_responses');
    }

    private function addSearchColumns(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'search_hash')) {
                $table->string('search_hash', 64)->nullable()->after('id');
                $table->index(['search_hash', 'created_at']);
            }

            if (!Schema::hasColumn($tableName, 'request_payload')) {
                $table->json('request_payload')->nullable()->after('search_hash');
            }

            if (!Schema::hasColumn($tableName, 'response_payload')) {
                $table->longText('response_payload')->nullable()->after('request_payload');
            }

            if (!Schema::hasColumn($tableName, 'cache_hit')) {
                $table->boolean('cache_hit')->default(false)->after('results_count');
            }
        });
    }
};
