<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('webhooks')) {
            Schema::create('webhooks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('url');
                $table->string('event'); // content.created, content.updated, user.created, etc.
                $table->string('method')->default('POST'); // POST, PUT, PATCH
                $table->json('headers')->nullable();
                $table->json('payload_template')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('timeout')->default(30); // seconds
                $table->integer('retry_count')->default(0);
                $table->integer('max_retries')->default(3);
                $table->timestamp('last_triggered_at')->nullable();
                $table->integer('success_count')->default(0);
                $table->integer('failure_count')->default(0);
                $table->timestamps();

                $table->index('event');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
