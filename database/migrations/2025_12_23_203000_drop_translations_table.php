<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drops the deprecated translations table that was used for content translations.
     * UI translations are now handled via JSON files in resources/lang/.
     */
    public function up(): void
    {
        Schema::dropIfExists('translations');
    }

    /**
     * Reverse the migrations.
     * Note: This does not recreate the table as it is no longer used.
     */
    public function down(): void
    {
        // Table schema is no longer maintained.
        // If you need to recreate it, please refer to the git history.
    }
};
