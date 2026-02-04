<?php

namespace App\Console\Commands;

use App\Models\SlowQuery;
use Illuminate\Console\Command;

class CleanupSlowQueryLogs extends Command
{
    protected $signature = 'logs:cleanup-slow-queries {--days=30 : Number of days to retain}';

    protected $description = 'Remove slow query logs older than specified days';

    public function handle()
    {
        $days = (int) $this->option('days');

        $deleted = SlowQuery::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Deleted {$deleted} slow query log(s) older than {$days} days.");

        return Command::SUCCESS;
    }
}
