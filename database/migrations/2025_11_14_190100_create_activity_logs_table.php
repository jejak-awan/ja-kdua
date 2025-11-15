<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('action'); // created, updated, deleted, viewed, etc.
                $table->string('model_type')->nullable(); // Content, Category, Media, etc.
                $table->unsignedBigInteger('model_id')->nullable();
                $table->text('description')->nullable();
                $table->json('changes')->nullable(); // Track what changed
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index(['model_type', 'model_id']);
                $table->index('action');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
