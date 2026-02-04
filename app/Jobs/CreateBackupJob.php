<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // Backup should only run once

    public int $timeout = 1800; // 30 minutes

    /** @var array<int, int> */
    public array $backoff = [300]; // Retry after 5 minutes if failed

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public string $type = 'database' // database, files, full
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $backup = null;
        try {
            $backupService = new BackupService;

            switch ($this->type) {
                case 'database':
                    $backup = $backupService->createDatabaseBackup($this->name);
                    break;
                case 'files':
                    $backup = $backupService->createFilesBackup($this->name);
                    break;
                case 'full':
                    $backup = $backupService->createFullBackup($this->name);
                    break;
                default:
                    throw new \Exception("Unknown backup type: {$this->type}");
            }

            if ($this->description) {
                $backup->update(['description' => $this->description]);
            }

            Log::channel('backup')->info('CreateBackupJob: Backup created successfully', [
                'backup_id' => $backup->id ?? null,
                'type' => $this->type,
                'name' => $this->name,
            ]);
        } catch (\Exception $e) {
            Log::channel('backup')->error('CreateBackupJob failed: '.$e->getMessage(), [
                'type' => $this->type,
                'name' => $this->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update backup status if backup record exists
            if ($backup !== null) {
                $backup->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('backup')->error('CreateBackupJob permanently failed', [
            'type' => $this->type,
            'name' => $this->name,
            'error' => $exception->getMessage(),
        ]);
    }
}
