<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('scheduled_tasks')) {
            Schema::create('scheduled_tasks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('command'); // Artisan command or custom task
                $table->string('schedule'); // Cron expression
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_run_at')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
                $table->text('output')->nullable();
                $table->json('options')->nullable();
                $table->timestamps();

                $table->index('is_active');
                $table->index('next_run_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_tasks');
    }
};
