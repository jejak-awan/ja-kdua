<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\Isp\StaleSessionCleanupJob;

Schedule::job(new StaleSessionCleanupJob())->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('isp:sync-voucher-usage')->everyFiveMinutes()->withoutOverlapping();

// --- Phase 6 & 7: Network Operations ---
// Poll node health every minute for real-time dashboards and auto-incidents
Schedule::command('isp:health-poll')->everyMinute()->withoutOverlapping();
Schedule::command('isp:poll-traffic')->everyMinute()->withoutOverlapping();

// Run security & audit maintenance every 15 minutes
Schedule::command('isp:maintenance-poll')->everyFifteenMinutes()->withoutOverlapping();

// Daily full network drift audit at 3 AM
Schedule::command('isp:maintenance-poll --type=drift')->dailyAt('03:00')->withoutOverlapping();

// Daily router configuration backup at 4 AM
Schedule::command('isp:router-backup')->dailyAt('04:00')->withoutOverlapping();

// Hourly automated billing isolation
Schedule::command('isp:isolate-overdue')->hourly()->withoutOverlapping();

// Hourly proactive network healing
Schedule::job(new \App\Jobs\Isp\NetworkHealingJob())->hourly()->withoutOverlapping();

// --- Phase 10: Multi-Vendor OLT Automation ---
// Poll optical signal levels every 15 minutes
Schedule::command('isp:poll-olt-signals')->everyFifteenMinutes()->withoutOverlapping();

// Scan for unconfigured ONUs every 5 minutes
Schedule::command('isp:discover-onus')->everyFiveMinutes()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Load scheduled tasks from database
 */
try {
    if (\Illuminate\Support\Facades\Schema::hasTable('scheduled_tasks')) {
        $tasks = \App\Models\Core\ScheduledTask::where('is_active', true)->get();

        foreach ($tasks as $task) {
            $taskId = $task->id;

            $event = Schedule::command($task->command)
                ->cron($task->schedule)
                ->description($task->description ?? $task->name)
                ->withoutOverlapping()
                ->before(function () use ($taskId) {
                    // Mark task as running
                    \App\Models\Core\ScheduledTask::where('id', $taskId)->update([
                        'status' => 'running',
                        'last_run_at' => now(),
                    ]);
                })
                ->after(function () use ($taskId) {
                    // Mark task as completed
                    \App\Models\Core\ScheduledTask::where('id', $taskId)->update([
                        'status' => 'completed',
                    ]);
                })
                ->onFailure(function () use ($taskId) {
                    // Mark task as failed
                    \App\Models\Core\ScheduledTask::where('id', $taskId)->update([
                        'status' => 'failed',
                    ]);
                });
        }
    }
} catch (\Exception $e) {
    // Fail silently if DB is not ready or migration running
    \Illuminate\Support\Facades\Log::warning('Scheduler: Failed to load tasks from DB', ['error' => $e->getMessage()]);
}
