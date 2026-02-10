<?php

namespace App\Console\Commands\Core;

use App\Models\Core\Setting;
use App\Models\Core\SlowQuery;
use Illuminate\Console\Command;

class CleanupSlowQueryLogs extends Command
{
    protected $signature = 'logs:cleanup-slow-queries {--days=30 : Number of days to retain}';

    protected $description = 'Remove slow query logs older than specified days';

    public function handle(): int
    {
        $daysRaw = $this->option('days') ?? Setting::get('slow_query_retention_days', 7);
        /** @var int $days */
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 7;
        /** @var int $count */
        $count = SlowQuery::where('created_at', '<', now()->subDays($days))->delete();

        $this->info(sprintf('Deleted %d slow query log(s) older than %d days.', $count, $days));

        return Command::SUCCESS;
    }
}
