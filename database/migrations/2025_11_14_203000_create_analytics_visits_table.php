<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('analytics_visits')) {
            Schema::create('analytics_visits', function (Blueprint $table) {
                $table->id();
                $table->string('session_id');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('ip_address');
                $table->string('user_agent')->nullable();
                $table->string('referer')->nullable();
                $table->string('url');
                $table->string('method')->default('GET');
                $table->integer('status_code')->default(200);
                $table->string('device_type')->nullable(); // desktop, mobile, tablet
                $table->string('browser')->nullable();
                $table->string('os')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->integer('duration')->nullable(); // seconds
                $table->timestamp('visited_at');
                $table->timestamps();

                $table->index(['visited_at', 'url']);
                $table->index('session_id');
                $table->index('ip_address');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_visits');
    }
};
