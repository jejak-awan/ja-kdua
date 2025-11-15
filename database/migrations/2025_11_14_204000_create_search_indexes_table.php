<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('search_indexes')) {
            Schema::create('search_indexes', function (Blueprint $table) {
                $table->id();
                $table->string('searchable_type'); // Content, Category, Tag, etc.
                $table->unsignedBigInteger('searchable_id');
                $table->text('title');
                $table->text('content'); // Full text content for search
                $table->text('excerpt')->nullable();
                $table->json('meta')->nullable(); // Additional searchable metadata
                $table->string('url')->nullable();
                $table->string('type')->nullable(); // post, page, category, tag
                $table->integer('relevance_score')->default(0);
                $table->timestamps();

                $table->index(['searchable_type', 'searchable_id']);
                $table->index('type');
                $table->index(['title', 'content']); // Regular index for search
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('search_indexes');
    }
};
