<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:cleanup 
        {--days=90 : Number of days to retain analytics data}
        {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old analytics data to manage database size';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $retentionDays = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoffDate = now()->subDays($retentionDays);

        $this->info("Analytics Cleanup - Retention: {$retentionDays} days");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
        }

        $this->newLine();

        // Count records to delete
        $visitsCount = AnalyticsVisit::where('visited_at', '<', $cutoffDate)->count();
        $eventsCount = AnalyticsEvent::where('occurred_at', '<', $cutoffDate)->count();
        $sessionsCount = AnalyticsSession::where('started_at', '<', $cutoffDate)->count();

        $this->table(
            ['Table', 'Records to Delete'],
            [
                ['analytics_visits', $visitsCount],
                ['analytics_events', $eventsCount],
                ['analytics_sessions', $sessionsCount],
            ]
        );

        $totalRecords = $visitsCount + $eventsCount + $sessionsCount;

        if ($totalRecords === 0) {
            $this->info('No records to delete.');

            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Would delete {$totalRecords} total records.");

            return Command::SUCCESS;
        }

        if (! $this->confirm("Delete {$totalRecords} records older than {$retentionDays} days?", true)) {
            $this->info('Cleanup cancelled.');

            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info('Deleting records...');

        // Delete in batches to avoid memory issues
        $deleted = [
            'visits' => 0,
            'events' => 0,
            'sessions' => 0,
        ];

        // Delete visits
        $this->output->write('Deleting visits... ');
        $deleted['visits'] = AnalyticsVisit::where('visited_at', '<', $cutoffDate)->delete();
        $this->info("Done ({$deleted['visits']} records)");

        // Delete events
        $this->output->write('Deleting events... ');
        $deleted['events'] = AnalyticsEvent::where('occurred_at', '<', $cutoffDate)->delete();
        $this->info("Done ({$deleted['events']} records)");

        // Delete sessions
        $this->output->write('Deleting sessions... ');
        $deleted['sessions'] = AnalyticsSession::where('started_at', '<', $cutoffDate)->delete();
        $this->info("Done ({$deleted['sessions']} records)");

        $this->newLine();
        $totalDeleted = array_sum($deleted);
        $this->info("Cleanup complete! Deleted {$totalDeleted} total records.");

        Log::info('Analytics cleanup completed', [
            'retention_days' => $retentionDays,
            'cutoff_date' => $cutoffDate->toDateTimeString(),
            'deleted' => $deleted,
        ]);

        return Command::SUCCESS;
    }
}
