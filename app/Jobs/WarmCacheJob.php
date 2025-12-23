<?php

namespace App\Jobs;

use App\Services\CacheWarmingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WarmCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $type = null,
        public int $limit = 50
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(CacheWarmingService $warmingService): void
    {
        Log::info('Starting scheduled cache warming', [
            'type' => $this->type,
            'limit' => $this->limit,
        ]);

        try {
            if ($this->type) {
                $count = $warmingService->warmByType($this->type, $this->limit);
                Log::info("Cache warming completed for {$this->type}: {$count} items");
            } else {
                $results = $warmingService->warmAll();
                $total = array_sum($results);
                Log::info("Cache warming completed: {$total} total items", $results);
            }
        } catch (\Exception $e) {
            Log::error('Cache warming job failed: ' . $e->getMessage(), [
                'type' => $this->type,
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}

