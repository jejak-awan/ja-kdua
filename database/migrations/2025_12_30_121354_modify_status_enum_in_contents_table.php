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
        Schema::table('contents', function (Blueprint $table) {
            // Modify the enum column to include 'pending'
            // DB::statement is safer for enums in MySQL usually, but change() might work if doctrine/dbal is set up correctly.
            // Let's try native Schema approach first, or raw SQL if needed.
            // Given potential issues with enum changes, raw SQL is often preferred for MySQL.
            // ALTER TABLE contents MODIFY COLUMN status ENUM('draft', 'pending', 'published', 'archived') NOT NULL DEFAULT 'draft';
        });

        // Use raw statement to ensure it works on MySQL
        \DB::statement("ALTER TABLE contents MODIFY COLUMN status ENUM('draft', 'pending', 'published', 'archived') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original
        \DB::statement("ALTER TABLE contents MODIFY COLUMN status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft'");
    }
};
