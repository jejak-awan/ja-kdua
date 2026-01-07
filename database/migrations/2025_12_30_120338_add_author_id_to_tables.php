<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['categories', 'tags', 'media', 'content_templates'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (! Schema::hasColumn($tableName, 'author_id')) {
                        $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['categories', 'tags', 'media', 'content_templates'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'author_id')) {
                        $table->dropForeign([$tableName.'_author_id_foreign']); // Or explicit foreign key name if needed
                        $table->dropColumn('author_id');
                    }
                });
            }
        }
    }
};
