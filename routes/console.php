<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\WarmCacheJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule cache warming - run every hour
Schedule::job(new WarmCacheJob())
    ->hourly()
    ->description('Warm up application cache')
    ->withoutOverlapping();

// Schedule cache warming after deployment (can be triggered manually)
Schedule::command('cache:warm')
    ->dailyAt('02:00')
    ->description('Daily cache warming at 2 AM')
    ->withoutOverlapping();

// Schedule log cleanup - run daily at 3 AM
Schedule::command('logs:cleanup')
    ->dailyAt('03:00')
    ->description('Clean up old activity, security, and login logs')
    ->withoutOverlapping();

// Schedule analytics cleanup - run weekly on Sunday at 3:30 AM
Schedule::command('analytics:cleanup --days=90')
    ->weeklyOn(0, '03:30')
    ->description('Clean up analytics data older than 90 days')
    ->withoutOverlapping();
