<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \DB::statement("ALTER TABLE contents MODIFY COLUMN status ENUM('draft', 'pending', 'published', 'archived') NOT NULL DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \DB::statement("ALTER TABLE contents MODIFY COLUMN status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft'");
        }
    }
};
