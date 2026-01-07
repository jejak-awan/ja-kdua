<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('content_templates')) {
            Schema::create('content_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('type')->default('post'); // post, page, custom
                $table->text('title_template')->nullable(); // Template for title
                $table->text('body_template'); // Template for body
                $table->text('excerpt_template')->nullable();
                $table->json('default_fields')->nullable(); // Default custom fields
                $table->json('meta')->nullable(); // Additional metadata
                $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
                $table->boolean('is_active')->default(true);
                $table->integer('usage_count')->default(0);
                $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();

                $table->index('type');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_templates');
    }
};
