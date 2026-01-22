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
        // Insert if not exists
        \App\Models\ScheduledTask::updateOrCreate(
            ['command' => 'security:update-cf-ips'],
            [
                'name' => 'Update Cloudflare IPs',
                'schedule' => '0 4 * * *', // Daily at 4 AM
                'description' => 'Update Cloudflare trusted IP ranges to ensure TrustProxies middleware works correctly.',
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
        \App\Models\ScheduledTask::where('command', 'security:update-cf-ips')->delete();
    }
};
