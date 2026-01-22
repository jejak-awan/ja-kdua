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
        // Remove tasks that don't have underlying commands or packages installed
        \App\Models\ScheduledTask::whereIn('command', [
            'activitylog:clean', // Package spatie/laravel-activitylog not installed
            'media:cleanup-temp', // Command does not exist
        ])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse for deletion of invalid tasks
    }
};
