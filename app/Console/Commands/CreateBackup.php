<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class CreateBackup extends Command
{
    protected $signature = 'cms:backup {--type=database : Type of backup (database, files, full)}';

    protected $description = 'Create a backup of the CMS';

    public function handle(BackupService $backupService)
    {
        $type = $this->option('type');

        $this->info("Creating {$type} backup...");

        $backup = $backupService->createDatabaseBackup();

        if ($backup->isCompleted()) {
            $this->info("Backup created successfully: {$backup->name}");
            $this->info("Size: {$backup->size_human}");
            $this->info("Path: {$backup->path}");
        } else {
            $this->error("Backup failed: {$backup->error_message}");

            return 1;
        }

        return 0;
    }
}
