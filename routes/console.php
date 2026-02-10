<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('isp:check-fup')->hourly()->withoutOverlapping();

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
