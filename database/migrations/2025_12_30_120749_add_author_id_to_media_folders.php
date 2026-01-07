<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media_folders', function (Blueprint $table) {
            if (! Schema::hasColumn('media_folders', 'author_id')) {
                $table->foreignId('author_id')->nullable()->after('sort_order')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_folders', function (Blueprint $table) {
            if (Schema::hasColumn('media_folders', 'author_id')) {
                $table->dropForeign(['media_folders_author_id_foreign']);
                $table->dropColumn('author_id');
            }
        });
    }
};
