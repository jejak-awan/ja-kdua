<?php

use App\Jobs\WarmCacheJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Load scheduled tasks from database
 */
try {
    if (\Illuminate\Support\Facades\Schema::hasTable('scheduled_tasks')) {
        // Use a simpler query or catch exceptions to avoid migration issues
        // We defer to the model for logic
        $tasks = \App\Models\ScheduledTask::where('is_active', true)->get();
        
        foreach ($tasks as $task) {
            // Apply schedule
            $event = Schedule::command($task->command)
                ->cron($task->schedule)
                ->description($task->description ?? '')
                ->withoutOverlapping();
                
            // Append output if needed, though usually better handled by logging config
            // $event->appendOutputTo(storage_path('logs/scheduler.log'));
        }
    }
} catch (\Exception $e) {
    // Fail silently if DB is not ready or migration running
    // This prevents "php artisan migrate" from breaking if table doesn't exist yet
}
