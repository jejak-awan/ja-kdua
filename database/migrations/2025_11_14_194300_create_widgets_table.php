<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('widgets')) {
            Schema::create('widgets', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type'); // text, html, recent_posts, categories, custom
                $table->string('location')->nullable(); // sidebar, footer, etc.
                $table->text('content')->nullable();
                $table->json('settings')->nullable(); // Widget-specific settings
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['location', 'sort_order']);
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};

