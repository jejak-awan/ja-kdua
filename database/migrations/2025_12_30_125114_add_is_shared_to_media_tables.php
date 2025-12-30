<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('author_id')->index();
        });

        Schema::table('media_folders', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('author_id')->index();
        });

        // Data migration: items with NULL author_id are considered shared/global
        DB::table('media')->whereNull('author_id')->update(['is_shared' => true]);
        DB::table('media_folders')->whereNull('author_id')->update(['is_shared' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('is_shared');
        });

        Schema::table('media_folders', function (Blueprint $table) {
            $table->dropColumn('is_shared');
        });
    }
};
