<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('redis_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('connection'); // connection, cache, session, queue
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });

        // Insert default Redis settings
        DB::table('redis_settings')->insert([
            [
                'key' => 'redis_host',
                'value' => '127.0.0.1',
                'type' => 'string',
                'group' => 'connection',
                'description' => 'Redis server host address',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'redis_port',
                'value' => '6379',
                'type' => 'integer',
                'group' => 'connection',
                'description' => 'Redis server port',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'redis_password',
                'value' => env('REDIS_PASSWORD', ''),
                'type' => 'string',
                'group' => 'connection',
                'description' => 'Redis server password',
                'is_encrypted' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'redis_database',
                'value' => '0',
                'type' => 'integer',
                'group' => 'connection',
                'description' => 'Redis database index (0-15)',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'cache_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'cache',
                'description' => 'Enable Redis for caching',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'cache_prefix',
                'value' => 'ja_cms_cache',
                'type' => 'string',
                'group' => 'cache',
                'description' => 'Cache key prefix',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'session_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'session',
                'description' => 'Use Redis for sessions',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'queue_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'queue',
                'description' => 'Use Redis for queue jobs',
                'is_encrypted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redis_settings');
    }
};
