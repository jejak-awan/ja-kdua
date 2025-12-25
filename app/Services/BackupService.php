<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupService
{
    /**
     * Execute a shell command using Symfony Process with fallback to native exec
     */
    protected function executeCommand(string $command, ?int $timeout = 300): array
    {
        $output = [];
        $returnCode = 0;

        // Try Symfony Process first (works even when exec is disabled)
        if (class_exists(Process::class)) {
            try {
                $process = Process::fromShellCommandline($command);
                $process->setTimeout($timeout);
                $process->run();
                
                $output = explode("\n", $process->getOutput() . $process->getErrorOutput());
                $returnCode = $process->getExitCode();
                
                return ['output' => $output, 'returnCode' => $returnCode];
            } catch (\Exception $e) {
                // Fall through to native exec
            }
        }

        // Fallback to native exec if Symfony Process fails or exec function is available
        if (function_exists('exec')) {
            \exec($command, $output, $returnCode);
            return ['output' => $output, 'returnCode' => $returnCode];
        }

        throw new \Exception('No shell execution method available. Please enable exec() or install symfony/process.');
    }
    public function createDatabaseBackup($name = null)
    {
        $name = $name ?? 'backup_'.now()->format('Y-m-d_His');

        // Prepare paths
        // We'll first create a temporary .sql file, then zip and encrypt it
        $sqlFilename = $name.'.sql';
        $zipFilename = $name.'.zip';
        $targetPath = 'backups/'.date('Y/m').'/'.$zipFilename;
        $relativePath = 'backups/'.date('Y/m').'/'.$sqlFilename; // Temp path for SQL

        // Create backup record
        $backup = Backup::create([
            'name' => $name,
            'type' => 'database',
            'status' => 'in_progress',
            'path' => $targetPath,
            'disk' => 'local',
        ]);

        try {
            $database = config('database.connections.'.config('database.default').'.database');
            $username = config('database.connections.'.config('database.default').'.username');
            $password = config('database.connections.'.config('database.default').'.password');
            $host = config('database.connections.'.config('database.default').'.host');

            // Determine temporary path for the SQL file
            // We use the same directory structure but temporary filename
            $backupDir = dirname(Storage::disk('local')->path($targetPath));
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // We need absolute path for the SQL dump to work
            $tempSqlFile = $backupDir . '/' . $sqlFilename;

            // 1. Generate SQL Dump
            if (config('database.default') === 'sqlite') {
                 // Check database path logic (existing)
                if (str_starts_with($database, '/')) {
                    $dbPath = $database;
                } else {
                    $dbPath = database_path($database);
                }

                if (! file_exists($dbPath)) {
                    // Try with .sqlite extension
                     if (str_starts_with($database, '/')) {
                        $dbPath = $database.'.sqlite';
                    } else {
                        $dbPath = database_path($database.'.sqlite');
                    }
                }

                if (file_exists($dbPath)) {
                   copy($dbPath, $tempSqlFile);
                } else {
                    throw new \Exception('Database file not found: '.$dbPath);
                }
            } elseif (config('database.default') === 'mysql') {
                $port = config('database.connections.mysql.port', 3306);
                
                $command = sprintf(
                    'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s 2>&1',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    escapeshellarg($tempSqlFile)
                );
                
                $result = $this->executeCommand($command);
                if ($result['returnCode'] !== 0) {
                     throw new \Exception('mysqldump failed: ' . implode("\n", $result['output']));
                }
            } elseif (config('database.default') === 'pgsql') {
                 $port = config('database.connections.pgsql.port', 5432);
                \putenv("PGPASSWORD={$password}");
                
                $command = sprintf(
                    'pg_dump --host=%s --port=%s --username=%s --format=plain %s > %s 2>&1',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($database),
                    escapeshellarg($tempSqlFile)
                );
                
                $result = $this->executeCommand($command);
                \putenv("PGPASSWORD");
                
                if ($result['returnCode'] !== 0) {
                    throw new \Exception('pg_dump failed: ' . implode("\n", $result['output']));
                }
            } else {
                 throw new \Exception('Unsupported database driver: ' . config('database.default'));
            }

            // Verify SQL file creation
            if (!file_exists($tempSqlFile) || filesize($tempSqlFile) === 0) {
                throw new \Exception('Backup SQL file was not created or is empty');
            }

            // 2. Compress and Encrypt
            $zipPath = Storage::disk('local')->path($targetPath);
            $zip = new \ZipArchive();
            
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                // Add SQL file
                $zip->addFile($tempSqlFile, $sqlFilename);
                
                // Encrypt if password provided (default to APP_KEY)
                $encryptionPassword = env('BACKUP_ARCHIVE_PASSWORD') ?: config('app.key');
                
                if ($encryptionPassword && method_exists($zip, 'setEncryptionName')) {
                    if (!$zip->setEncryptionName($sqlFilename, \ZipArchive::EM_AES_256, $encryptionPassword)) {
                        throw new \Exception('Failed to set encryption for backup file');
                    }
                }
                
                $zip->close();
            } else {
                throw new \Exception('Failed to create Zip archive');
            }

            // 3. Cleanup
            // Delete the raw SQL file
            if (file_exists($tempSqlFile)) {
                unlink($tempSqlFile);
            }

            $size = file_exists($zipPath) ? filesize($zipPath) : 0;

            $backup->update([
                'path' => $targetPath,
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

            // Attempt cleanup on failure
            if (isset($tempSqlFile) && file_exists($tempSqlFile)) {
                @unlink($tempSqlFile);
            }

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

            if (!file_exists($fullPath)) {
                throw new \Exception('Backup file not found');
            }

            // Determine if it is a zip file
            $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
            $isZip = strtolower($extension) === 'zip';
            $sqlPath = $fullPath; // Default to full path if not zip
            $tempExtractDir = null;

            if ($isZip) {
                // Extract zip file
                $zip = new \ZipArchive();
                if ($zip->open($fullPath) === TRUE) {
                    // Create temp directory for extraction
                    $tempExtractDir = dirname($fullPath) . '/restore_' . uniqid();
                    if (!is_dir($tempExtractDir)) {
                        mkdir($tempExtractDir, 0755, true);
                    }

                    // Set password if encrypted
                    $encryptionPassword = env('BACKUP_ARCHIVE_PASSWORD') ?: config('app.key');
                    if ($encryptionPassword) {
                        $zip->setPassword($encryptionPassword);
                    }

                    // Extract all files
                    if (!$zip->extractTo($tempExtractDir)) {
                        $zip->close();
                        throw new \Exception('Failed to extract backup archive. Incorrect password?');
                    }
                    $zip->close();

                    // Find the SQL file inside
                    $files = glob($tempExtractDir . '/*.sql');
                    if (empty($files)) {
                        // Try .sqlite
                        $files = glob($tempExtractDir . '/*.sqlite');
                        if (empty($files)) {
                            throw new \Exception('No SQL file found in archive');
                        }
                    }
                    
                    $sqlPath = $files[0];
                } else {
                    throw new \Exception('Failed to open zip archive');
                }
            }

            try {
                if (config('database.default') === 'sqlite') {
                    $database = config('database.connections.'.config('database.default').'.database');
                    
                    // Logic to find target DB path
                    if (str_starts_with($database, '/')) {
                        $dbPath = $database;
                    } else {
                        $dbPath = database_path($database);
                    }
                    
                     // Try with .sqlite extension if not found (legacy fallback)
                    if (! file_exists($dbPath) && !str_ends_with($dbPath, '.sqlite')) {
                         $dbPath .= '.sqlite';
                    }

                    if (file_exists($sqlPath)) {
                        copy($sqlPath, $dbPath);
                    }
                } elseif (config('database.default') === 'mysql') {
                    $database = config('database.connections.mysql.database');
                    $username = config('database.connections.mysql.username');
                    $password = config('database.connections.mysql.password');
                    $host = config('database.connections.mysql.host');
                    $port = config('database.connections.mysql.port', 3306);
                    
                    $command = sprintf(
                        'mysql --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($password),
                        escapeshellarg($database),
                        escapeshellarg($sqlPath)
                    );
                    
                    $result = $this->executeCommand($command);
                    if ($result['returnCode'] !== 0) {
                        throw new \Exception('MySQL restore failed: ' . implode("\n", $result['output']));
                    }
                } elseif (config('database.default') === 'pgsql') {
                    $database = config('database.connections.pgsql.database');
                    $username = config('database.connections.pgsql.username');
                    $password = config('database.connections.pgsql.password');
                    $host = config('database.connections.pgsql.host');
                    $port = config('database.connections.pgsql.port', 5432);
                    
                    \putenv("PGPASSWORD={$password}");
                    
                    $command = sprintf(
                        'psql --host=%s --port=%s --username=%s %s < %s 2>&1',
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($database),
                        escapeshellarg($sqlPath)
                    );
                    
                    $result = $this->executeCommand($command);
                    \putenv("PGPASSWORD");
                    
                    if ($result['returnCode'] !== 0) {
                        throw new \Exception('PostgreSQL restore failed: ' . implode("\n", $result['output']));
                    }
                } else {
                    throw new \Exception('Unsupported database driver for restore');
                }
            } finally {
                // Cleanup temporary extracted files
                if ($tempExtractDir && is_dir($tempExtractDir)) {
                    array_map('unlink', glob("$tempExtractDir/*.*"));
                    rmdir($tempExtractDir);
                }
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
