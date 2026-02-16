<?php

namespace App\Jobs\Isp;

use App\Models\Isp\Customer\Customer;
use App\Services\Isp\Network\RouterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReactivateCustomerJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900, 3600]; // 1m, 5m, 15m, 1h
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Customer $customer
    ) {}

    /**
     * Execute the job.
     */
    public function handle(\App\Services\Isp\Network\IsolationService $isolationService): void
    {
        if ($this->customer->status !== 'active') {
            Log::info("ReactivateCustomerJob: Customer status is no longer active, skipping.", [
                'customer_id' => $this->customer->id
            ]);
            return;
        }

        $success = $isolationService->restore($this->customer);

        if (!$success) {
            throw new \Exception("Failed to restore customer on router. Retrying...");
        }

        Log::info("ReactivateCustomerJob: Successfully restored customer (removed isolation).", [
            'customer_id' => $this->customer->id,
            'mikrotik_login' => $this->customer->mikrotik_login
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ReactivateCustomerJob Failed: " . $exception->getMessage(), [
            'customer_id' => $this->customer->id,
            'mikrotik_login' => $this->customer->mikrotik_login
        ]);
    }
}
