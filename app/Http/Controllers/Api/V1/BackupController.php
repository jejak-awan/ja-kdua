<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
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

        return response()->json($backups);
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
                return response()->json([
                    'message' => $backup->error_message ?? 'Backup creation failed',
                    'backup' => $backup,
                ], 500);
            }

            return response()->json($backup, 201);
        } catch (\Exception $e) {
            \Log::error('Backup creation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Backup $backup)
    {
        return response()->json($backup);
    }

    public function restore(Request $request, Backup $backup)
    {
        if (!$backup->isCompleted()) {
            return response()->json(['message' => 'Cannot restore incomplete backup'], 422);
        }

        try {
            $this->backupService->restoreDatabaseBackup($backup);
            return response()->json(['message' => 'Backup restored successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Backup $backup)
    {
        $this->backupService->deleteBackup($backup);
        return response()->json(['message' => 'Backup deleted successfully']);
    }

    public function download(Backup $backup)
    {
        if (!$backup->isCompleted()) {
            return response()->json(['message' => 'Backup not available'], 404);
        }

        $path = \Storage::disk($backup->disk)->path($backup->path);
        
        if (!file_exists($path)) {
            return response()->json(['message' => 'Backup file not found'], 404);
        }

        return response()->download($path, $backup->name . '.sql');
    }

    public function stats()
    {
        $stats = $this->backupService->getBackupStats();
        return response()->json($stats);
    }
}
