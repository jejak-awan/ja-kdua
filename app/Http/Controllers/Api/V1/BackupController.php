<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends BaseApiController
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index(Request $request)
    {
        $type = $request->input('type');
        $backups = $this->backupService->listBackups($type);

        return $this->success($backups, 'Backups retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|in:database,files,full',
        ]);

        try {
            $name = $request->input('name');
            $backup = $this->backupService->createDatabaseBackup($name);

            if ($backup->status === 'failed') {
                return $this->error(
                    $backup->error_message ?? 'Backup creation failed',
                    500,
                    [],
                    'BACKUP_FAILED',
                    ['backup' => $backup]
                );
            }

            return $this->success($backup, 'Backup created successfully', 201);
        } catch (\Throwable $e) {
            \Log::error('Backup creation error: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error(
                'Failed to create backup: '.$e->getMessage(),
                500,
                [],
                'BACKUP_ERROR'
            );
        }
    }

    public function show(Backup $backup)
    {
        return $this->success($backup, 'Backup retrieved successfully');
    }

    public function restore(Request $request, Backup $backup)
    {
        if (! $backup->isCompleted()) {
            return $this->validationError(['backup' => ['Cannot restore incomplete backup']], 'Cannot restore incomplete backup');
        }

        try {
            $this->backupService->restoreDatabaseBackup($backup);

            return $this->success(null, 'Backup restored successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to restore backup: '.$e->getMessage(), 500, [], 'RESTORE_ERROR');
        }
    }

    public function destroy(Backup $backup)
    {
        $this->backupService->deleteBackup($backup);

        return $this->success(null, 'Backup deleted successfully');
    }

    public function download(Backup $backup)
    {
        if (! $backup->isCompleted()) {
            return $this->notFound('Backup');
        }

        $path = \Storage::disk($backup->disk)->path($backup->path);

        if (! file_exists($path)) {
            return $this->notFound('Backup file');
        }

        return response()->download($path, $backup->name.'.sql');
    }

    public function stats()
    {
        $stats = $this->backupService->getBackupStats();

        return $this->success($stats, 'Backup statistics retrieved successfully');
    }

    public function schedule(Request $request)
    {
        if ($request->isMethod('GET')) {
            $schedule = $this->backupService->getScheduleSettings();

            return $this->success($schedule, 'Backup schedule retrieved successfully');
        }

        // POST - update schedule
        $validated = $request->validate([
            'backup_schedule_enabled' => 'sometimes|boolean',
            'backup_schedule_frequency' => 'sometimes|in:daily,weekly,monthly',
            'backup_schedule_time' => 'sometimes|date_format:H:i',
            'backup_retention_days' => 'sometimes|integer|min:1|max:365',
            'backup_max_count' => 'sometimes|integer|min:1|max:100',
        ]);

        $schedule = $this->backupService->updateScheduleSettings($validated);

        return $this->success($schedule, 'Backup schedule updated successfully');
    }

    public function cleanup(Request $request)
    {
        $retentionDays = $request->input('retention_days', 30);
        $maxBackups = $request->input('max_backups', 10);

        $deleted = $this->backupService->cleanupOldBackups($retentionDays, $maxBackups);

        return $this->success([
            'deleted' => $deleted,
        ], "{$deleted} old backups cleaned up");
    }
}
