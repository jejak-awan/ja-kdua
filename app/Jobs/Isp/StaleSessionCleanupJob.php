<?php

declare(strict_types=1);

namespace App\Jobs\Isp;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaleSessionCleanupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $connectionRaw = config('database.radius_connection', 'radius');
        $connection = is_string($connectionRaw) ? $connectionRaw : 'radius';
        $staleThreshold = now()->subMinutes(15);

        try {
            Log::info("StaleSessionCleanup: Searching for sessions inactive since {$staleThreshold}");

            $staleCount = DB::connection($connection)
                ->table('radacct')
                ->whereNull('acctstoptime')
                ->where('acctupdatetime', '<', $staleThreshold)
                ->count();

            if ($staleCount > 0) {
                DB::connection($connection)
                    ->table('radacct')
                    ->whereNull('acctstoptime')
                    ->where('acctupdatetime', '<', $staleThreshold)
                    ->update([
                        'acctstoptime' => now(),
                        'acctterminatecause' => 'Admin-Reset-Stale',
                    ]);

                Log::info("StaleSessionCleanup: Successfully cleared {$staleCount} stale sessions.");
            } else {
                Log::info("StaleSessionCleanup: No stale sessions found.");
            }
        } catch (\Throwable $e) {
            Log::error("StaleSessionCleanup Error: " . $e->getMessage());
        }
    }
}
