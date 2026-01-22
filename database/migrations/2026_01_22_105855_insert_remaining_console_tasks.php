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
        // 1. Warm Cache (Daily 02:00)
        \App\Models\ScheduledTask::updateOrCreate(
            ['command' => 'cache:warm'],
            [
                'name' => 'Warm Cache',
                'schedule' => '0 2 * * *',
                'description' => 'Warm up application cache with frequently accessed data.',
                'is_active' => true,
                'status' => 'pending'
            ]
        );

        // 2. Cleanup Logs (Daily 03:00)
        \App\Models\ScheduledTask::updateOrCreate(
            ['command' => 'logs:cleanup'],
            [
                'name' => 'Cleanup Logs',
                'schedule' => '0 3 * * *',
                'description' => 'Clean up old activity, security, and login logs.',
                'is_active' => true,
                'status' => 'pending'
            ]
        );

        // 3. Cleanup Analytics (Weekly Sunday 03:30)
        \App\Models\ScheduledTask::updateOrCreate(
            ['command' => 'analytics:cleanup --days=90'],
            [
                'name' => 'Cleanup Analytics',
                'schedule' => '30 3 * * 0',
                'description' => 'Clean up analytics data older than 90 days.',
                'is_active' => true,
                'status' => 'pending'
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\ScheduledTask::whereIn('command', [
            'cache:warm',
            'logs:cleanup',
            'analytics:cleanup --days=90'
        ])->delete();
    }
};
