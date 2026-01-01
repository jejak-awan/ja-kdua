<?php

namespace Database\Seeders;

use App\Models\ScheduledTask;
use Illuminate\Database\Seeder;

class SecurityTasksSeeder extends Seeder
{
    public function run(): void
    {
        // CSP Report Cleanup
        ScheduledTask::updateOrCreate(
            ['command' => 'logs:cleanup-csp-reports --days=90'],
            [
                'name' => 'Cleanup Old CSP Reports',
                'schedule' => '0 2 * * 0', // Weekly, Sunday 2 AM
                'description' => 'Remove CSP violation reports older than 90 days to save storage',
                'is_active' => true,
            ]
        );

        // Slow Query Cleanup
        ScheduledTask::updateOrCreate(
            ['command' => 'logs:cleanup-slow-queries --days=30'],
            [
                'name' => 'Cleanup Slow Query Logs',
                'schedule' => '0 3 * * 0', // Weekly, Sunday 3 AM
                'description' => 'Remove slow query logs older than 30 days',
                'is_active' => true,
            ]
        );

        // Dependency Security Audit
        ScheduledTask::updateOrCreate(
            ['command' => 'security:audit-dependencies'],
            [
                'name' => 'Security Dependency Audit',
                'schedule' => '0 4 * * 1', // Weekly, Monday 4 AM
                'description' => 'Scan Composer and NPM dependencies for known security vulnerabilities',
                'is_active' => true,
            ]
        );
    }
}
