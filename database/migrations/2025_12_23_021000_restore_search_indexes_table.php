<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

                // Add fulltext index only for MySQL/MariaDB (SQLite doesn't support it)
                if (DB::getDriverName() === 'mysql') {
                    $table->fullText(['title', 'content']);
                }
            });
        }
    }

    public function down(): void
    {
        // Don't drop it in down() since it's a restore migration,
        // or actually yes drop it if we roll this back as it implies we want to undo the restoration?
        // But the original migration also drops it.
        // Let's leave down() empty or use dropIfExists to be safe against double-drops if necessary.
        Schema::dropIfExists('search_indexes');
    }
};
