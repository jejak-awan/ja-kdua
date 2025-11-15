<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('themes')) {
            Schema::create('themes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('version')->default('1.0.0');
                $table->text('description')->nullable();
                $table->string('author')->nullable();
                $table->string('author_url')->nullable();
                $table->string('preview_image')->nullable();
                $table->json('settings')->nullable(); // Theme-specific settings
                $table->text('custom_css')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
                
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};

