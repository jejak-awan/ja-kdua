<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('plugins')) {
            Schema::create('plugins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('version')->default('1.0.0');
                $table->text('description')->nullable();
                $table->string('author')->nullable();
                $table->string('author_url')->nullable();
                $table->string('plugin_url')->nullable();
                $table->string('main_file')->nullable(); // Path to main plugin file
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(false);
                $table->integer('priority')->default(10); // Execution priority
                $table->timestamps();
                
                $table->index('is_active');
                $table->index('priority');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};

