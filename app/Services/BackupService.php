<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    public function createDatabaseBackup($name = null)
    {
        $name = $name ?? 'backup_'.now()->format('Y-m-d_His');

        // Prepare path first
        $filename = $name.'.sql';
        $path = 'backups/'.date('Y/m').'/'.$filename;

        // Create backup record with temporary path
        $backup = Backup::create([
            'name' => $name,
            'type' => 'database',
            'status' => 'in_progress',
            'path' => $path, // Set path immediately to avoid NOT NULL constraint
            'disk' => 'local',
        ]);

        try {
            $database = config('database.connections.'.config('database.default').'.database');
            $username = config('database.connections.'.config('database.default').'.username');
            $password = config('database.connections.'.config('database.default').'.password');
            $host = config('database.connections.'.config('database.default').'.host');

            // For SQLite
            if (config('database.default') === 'sqlite') {
                // Check if database path is already absolute
                if (str_starts_with($database, '/')) {
                    $dbPath = $database;
                } else {
                    $dbPath = database_path($database);
                }

                // Try with .sqlite extension if not found
                if (! file_exists($dbPath)) {
                    if (str_starts_with($database, '/')) {
                        $dbPath = $database.'.sqlite';
                    } else {
                        $dbPath = database_path($database.'.sqlite');
                    }
                }

                if (file_exists($dbPath)) {
                    // Ensure backup directory exists
                    $backupDir = dirname(Storage::disk('local')->path($path));
                    if (! is_dir($backupDir)) {
                        mkdir($backupDir, 0755, true);
                    }

                    Storage::disk('local')->put($path, file_get_contents($dbPath));
                } else {
                    throw new \Exception('Database file not found: '.$dbPath);
                }
            } else {
                // For MySQL/PostgreSQL - would need mysqldump/pg_dump
                // This is a simplified version
                $backup->update([
                    'status' => 'failed',
                    'error_message' => 'MySQL/PostgreSQL backup requires mysqldump/pg_dump command',
                ]);

                return $backup;
            }

            $fullPath = Storage::disk('local')->path($path);
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;

            $backup->update([
                'path' => $path,
                'disk' => 'local',
                'size' => $size,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return $backup;
        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return $backup;
        }
    }

    public function restoreDatabaseBackup(Backup $backup)
    {
        if ($backup->type !== 'database' || ! $backup->isCompleted()) {
            throw new \Exception('Invalid backup or backup not completed');
        }

        try {
            $fullPath = Storage::disk($backup->disk)->path($backup->path);

            if (config('database.default') === 'sqlite') {
                $database = config('database.connections.'.config('database.default').'.database');
                $dbPath = database_path($database);

                if (file_exists($fullPath)) {
                    copy($fullPath, $dbPath);
                }
            } else {
                throw new \Exception('MySQL/PostgreSQL restore requires manual process');
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Restore failed: '.$e->getMessage());
        }
    }

    public function deleteBackup(Backup $backup)
    {
        // Delete file
        if (Storage::disk($backup->disk)->exists($backup->path)) {
            Storage::disk($backup->disk)->delete($backup->path);
        }

        // Delete record
        $backup->delete();
    }

    public function listBackups($type = null)
    {
        $query = Backup::query();

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getBackupStats()
    {
        return [
            'total' => Backup::count(),
            'completed' => Backup::where('status', 'completed')->count(),
            'failed' => Backup::where('status', 'failed')->count(),
            'total_size' => Backup::where('status', 'completed')->sum('size'),
            'latest' => Backup::where('status', 'completed')->latest('completed_at')->first(),
            'schedule' => $this->getScheduleSettings(),
        ];
    }

    /**
     * Get backup schedule settings from database
     */
    public function getScheduleSettings(): array
    {
        return [
            'enabled' => \App\Models\Setting::get('backup_schedule_enabled', false),
            'frequency' => \App\Models\Setting::get('backup_schedule_frequency', 'daily'), // daily, weekly, monthly
            'time' => \App\Models\Setting::get('backup_schedule_time', '02:00'),
            'retention_days' => (int) \App\Models\Setting::get('backup_retention_days', 30),
            'max_backups' => (int) \App\Models\Setting::get('backup_max_count', 10),
            'last_run' => \App\Models\Setting::get('backup_last_run'),
            'next_run' => $this->calculateNextRun(),
        ];
    }

    /**
     * Update backup schedule settings
     */
    public function updateScheduleSettings(array $settings): array
    {
        $validKeys = ['backup_schedule_enabled', 'backup_schedule_frequency', 'backup_schedule_time', 'backup_retention_days', 'backup_max_count'];
        
        foreach ($settings as $key => $value) {
            if (in_array($key, $validKeys)) {
                \App\Models\Setting::set($key, $value, 'string', 'backup');
            }
        }

        return $this->getScheduleSettings();
    }

    /**
     * Run scheduled backup (called by scheduler)
     */
    public function runScheduledBackup(): ?Backup
    {
        $settings = $this->getScheduleSettings();
        
        if (!$settings['enabled']) {
            return null;
        }

        // Create backup
        $backup = $this->createDatabaseBackup('scheduled_' . now()->format('Y-m-d_His'));

        // Update last run time
        \App\Models\Setting::set('backup_last_run', now()->toISOString(), 'string', 'backup');

        // Cleanup old backups
        $this->cleanupOldBackups($settings['retention_days'], $settings['max_backups']);

        return $backup;
    }

    /**
     * Cleanup old backups based on retention policy
     */
    public function cleanupOldBackups(int $retentionDays = 30, int $maxBackups = 10): int
    {
        $deleted = 0;

        // Delete backups older than retention days
        $oldBackups = Backup::where('status', 'completed')
            ->where('created_at', '<', now()->subDays($retentionDays))
            ->get();

        foreach ($oldBackups as $backup) {
            $this->deleteBackup($backup);
            $deleted++;
        }

        // Keep only max_backups (delete oldest if more)
        $totalBackups = Backup::where('status', 'completed')->count();
        if ($totalBackups > $maxBackups) {
            $excessCount = $totalBackups - $maxBackups;
            $excessBackups = Backup::where('status', 'completed')
                ->orderBy('created_at', 'asc')
                ->limit($excessCount)
                ->get();

            foreach ($excessBackups as $backup) {
                $this->deleteBackup($backup);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Calculate next scheduled run time
     */
    protected function calculateNextRun(): ?string
    {
        $settings = [
            'enabled' => \App\Models\Setting::get('backup_schedule_enabled', false),
            'frequency' => \App\Models\Setting::get('backup_schedule_frequency', 'daily'),
            'time' => \App\Models\Setting::get('backup_schedule_time', '02:00'),
        ];

        if (!$settings['enabled']) {
            return null;
        }

        $time = explode(':', $settings['time']);
        $hour = (int) ($time[0] ?? 2);
        $minute = (int) ($time[1] ?? 0);

        $next = now()->setTime($hour, $minute, 0);
        
        if ($next->isPast()) {
            switch ($settings['frequency']) {
                case 'weekly':
                    $next->addWeek();
                    break;
                case 'monthly':
                    $next->addMonth();
                    break;
                default:
                    $next->addDay();
            }
        }

        return $next->toISOString();
    }
}
