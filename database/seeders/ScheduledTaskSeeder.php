<?php

namespace Database\Seeders;

use App\Models\ScheduledTask;
use Illuminate\Database\Seeder;

class ScheduledTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            [
                'name' => 'Run Daily Backup',
                'command' => 'cms:backup',
                'schedule' => '0 1 * * *', // Daily at 1 AM
                'description' => 'Creates a full backup of the database and files.',
                'is_active' => true,
            ],
            [
                'name' => 'Clear Caches',
                'command' => 'optimize:clear',
                'schedule' => '0 4 * * *', // Daily at 4 AM
                'description' => 'Clears application cache to ensure fresh data.',
                'is_active' => false, // Inactive by default to prevent accidental purging
            ],
            [
                'name' => 'Cleanup Activity Logs',
                'command' => 'activitylog:clean',
                'schedule' => '0 0 * * 0', // Weekly on Sunday
                'description' => 'Removes old activity logs.',
                'is_active' => true,
            ],
            [
                'name' => 'Prune Failed Jobs',
                'command' => 'queue:prune-failed',
                'schedule' => '0 2 * * *', // Daily at 2 AM
                'description' => 'Removes failed queue jobs older than 24 hours.',
                'is_active' => true,
            ],
             [
                'name' => 'Cleanup Temp Files',
                'command' => 'media:cleanup-temp', 
                'schedule' => '0 3 * * *', // Daily at 3 AM
                'description' => 'Removes temporary files older than 24 hours.',
                'is_active' => true,
            ]
        ];

        foreach ($tasks as $task) {
            ScheduledTask::updateOrCreate(
                ['command' => $task['command']],
                $task
            );
        }
    }
}
