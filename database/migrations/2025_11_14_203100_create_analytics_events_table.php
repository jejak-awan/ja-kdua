<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('analytics_events')) {
            Schema::create('analytics_events', function (Blueprint $table) {
                $table->id();
                $table->string('session_id')->index();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('event_type'); // page_view, click, download, form_submit, etc.
                $table->string('event_name');
                $table->string('event_category')->nullable();
                $table->json('event_data')->nullable();
                $table->string('url')->nullable();
                $table->foreignId('content_id')->nullable()->constrained('contents')->onDelete('set null');
                $table->string('ip_address')->nullable();
                $table->timestamp('occurred_at');
                $table->timestamps();
                
                $table->index(['event_type', 'occurred_at']);
                $table->index(['content_id', 'occurred_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};

