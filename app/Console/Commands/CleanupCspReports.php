<?php

namespace App\Console\Commands;

use App\Models\CspReport;
use Illuminate\Console\Command;

class CleanupCspReports extends Command
{
    protected $signature = 'logs:cleanup-csp-reports {--days=90 : Number of days to retain}';
    protected $description = 'Remove CSP reports older than specified days';

    public function handle()
    {
        $days = $this->option('days');
        
        $deleted = CspReport::where('created_at', '<', now()->subDays($days))->delete();
        
        $this->info("Deleted {$deleted} CSP report(s) older than {$days} days.");
        
        return Command::SUCCESS;
    }
}
