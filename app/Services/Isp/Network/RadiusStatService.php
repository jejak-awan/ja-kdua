<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusStatService
{
    /**
     * Get the RADIUS database connection name.
     */
    protected function getConnectionName(): string
    {
        /** @var string $conn */
        $conn = config('database.radius_connection', 'radius');

        return $conn;
    }

    /**
     * Get active sessions from radacct.
     *
     * @return Collection<int, \stdClass>
     */
    public function getActiveSessions(int $limit = 50): Collection
    {
        // PHPStan hint for DB session
        /** @var Collection<int, \stdClass> $sessions */
        $sessions = DB::connection($this->getConnectionName())
            ->table('radacct')
            ->whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->limit($limit)
            ->get();

        return $sessions;
    }

    /**
     * Get recent authentication logs from radpostauth.
     *
     * @return Collection<int, \stdClass>
     */
    public function getAuthLogs(int $limit = 50): Collection
    {
        /** @var Collection<int, \stdClass> $logs */
        $logs = DB::connection($this->getConnectionName())
            ->table('radpostauth')
            ->orderBy('authdate', 'desc')
            ->limit($limit)
            ->get();

        return $logs;
    }

    /**
     * Get aggregate usage for last 24 hours by hour.
     *
     * @return array<int, array{time: string, up: float, down: float}>
     */
    public function getCustomerUsageDaily(string $username): array
    {
        /** @var array<int, \stdClass> $results */
        $results = DB::connection($this->getConnectionName())
            ->table('radacct')
            ->selectRaw("to_char(acctstarttime, 'HH24:00') as hour, SUM(acctinputoctets) as upload, SUM(acctoutputoctets) as download")
            ->where('username', $username)
            ->where('acctstarttime', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get()
            ->toArray();

        return array_map(fn ($row) => [
            'time' => (string) ($row->hour ?? ''),
            'up' => round(($row->upload ?? 0) / 1024 / 1024 / 1024, 2), // GB
            'down' => round(($row->download ?? 0) / 1024 / 1024 / 1024, 2),
        ], $results);
    }

    /**
     * Get top users by bandwidth consumption.
     *
     * @return array<int, array{username: string, total_gb: float}>
     */
    public function getTopTalkers(int $limit = 10): array
    {
        /** @var array<int, \stdClass> $results */
        $results = DB::connection($this->getConnectionName())
            ->table('radacct')
            ->selectRaw('username, SUM(acctinputoctets + acctoutputoctets) as total_octets')
            ->where('acctstarttime', '>=', now()->subMonth())
            ->groupBy('username')
            ->orderBy('total_octets', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();

        return array_map(fn ($row) => [
            'username' => (string) ($row->api_username ?? 'unknown'),
            'total_gb' => round(($row->total_octets ?? 0) / 1024 / 1024 / 1024, 2),
        ], $results);
    }

    /**
     * Get aggregate usage for last 30 days by day.
     *
     * @return array<int, array{date: string, up: float, down: float}>
     */
    public function getCustomerUsageMonthly(string $username): array
    {
        /** @var array<int, \stdClass> $results */
        $results = DB::connection($this->getConnectionName())
            ->table('radacct')
            ->selectRaw("to_char(acctstarttime, 'Mon DD') as date_label, SUM(acctinputoctets) as upload, SUM(acctoutputoctets) as download")
            ->where('username', $username)
            ->where('acctstarttime', '>=', now()->subDays(30))
            ->groupBy('date_label')
            ->orderBy(DB::raw('MIN(acctstarttime)'), 'asc')
            ->get()
            ->toArray();

        return array_map(fn ($row) => [
            'date' => (string) ($row->date_label ?? ''),
            'up' => round(($row->upload ?? 0) / 1024 / 1024 / 1024, 2),
            'down' => round(($row->download ?? 0) / 1024 / 1024 / 1024, 2),
        ], $results);
    }

    /**
     * Get aggregate usage for the last 24 hours (global).
     *
     * @return array<int, array{time: string, up: float, down: float}>
     */
    public function getGlobalUsageDaily(): array
    {
        /** @var array<int, \stdClass> $results */
        $results = DB::connection($this->getConnectionName())
            ->table('radacct')
            ->selectRaw("to_char(acctstarttime, 'HH24:00') as time_label, SUM(acctinputoctets) as upload, SUM(acctoutputoctets) as download")
            ->where('acctstarttime', '>=', now()->subDay())
            ->groupBy('time_label')
            ->orderBy('time_label', 'asc')
            ->get()
            ->toArray();

        return array_map(fn ($row) => [
            'time' => (string) ($row->time_label ?? ''),
            'up' => round(($row->upload ?? 0) / 1024 / 1024, 2), // MB for global daily
            'down' => round(($row->download ?? 0) / 1024 / 1024, 2),
        ], $results);
    }

    /**
     * Get consolidated status of RADIUS infrastructure.
     *
     * @return array{total_nas: int, total_users: int, active_sessions: int, process: array{running: bool, version: string}}
     */
    public function getRadiusStatus(): array
    {
        try {
            return [
                'total_nas' => DB::connection($this->getConnectionName())->table('nas')->count(),
                'total_users' => DB::connection($this->getConnectionName())->table('radcheck')->distinct('username')->count('username'),
                'active_sessions' => DB::connection($this->getConnectionName())->table('radacct')->whereNull('acctstoptime')->count(),
                'process' => $this->getLocalProcessStatus(),
            ];
        } catch (\Throwable $e) {
            Log::error('RadiusStatService: Failed to get RADIUS status: '.$e->getMessage());

            return [
                'total_nas' => 0,
                'total_users' => 0,
                'active_sessions' => 0,
                'process' => [
                    'running' => false,
                    'since' => null,
                    'version' => 'Unknown (Error)',
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Check local FreeRADIUS process status.
     *
     * @return array{running: bool, since: string|null, version: string}
     */
    public function getLocalProcessStatus(): array
    {
        $status = [
            'running' => false,
            'since' => null,
            'version' => 'Unknown',
        ];

        try {
            // Check process using multiple methods
            if (function_exists('exec')) {
                /** @var array<int, string> $pids */
                $pids = [];

                // Method 1: pgrep
                exec('pgrep -f "freeradius"', $pids);

                // Method 2: pidof (fallback)
                if (empty($pids)) {
                    exec('pidof freeradius', $pids);
                }

                // Method 3: ps aux (last resort)
                if (empty($pids)) {
                    $psOut = [];
                    exec('ps aux | grep "freeradius" | grep -v "grep"', $psOut);
                    if (! empty($psOut)) {
                        $pids = ['1']; // Mock PID to indicate running
                    }
                }

                if (! empty($pids)) {
                    $status['running'] = true;
                }

                // Check version
                /** @var array<int, string> $versionOut */
                $versionOut = [];
                exec('freeradius -v | head -n 1', $versionOut);
                if (! empty($versionOut)) {
                    $status['version'] = (string) $versionOut[0];
                }
            } else {
                // PHP-FPM Fallback: Search /proc filesystem directly
                $status['running'] = $this->isProcessRunning('freeradius');
                $status['version'] = 'Unknown (exec disabled)';
            }
        } catch (\Throwable $e) {
            Log::warning('RadiusStatService: Failed to get local process status: '.$e->getMessage());
        }

        return $status;
    }

    /**
     * Check if a process is running by searching /proc filesystem.
     * Useful when exec() is disabled.
     */
    protected function isProcessRunning(string $name): bool
    {
        $procDir = '/proc';
        if (! is_dir($procDir)) {
            return false;
        }

        $items = scandir($procDir);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if (! is_numeric($item)) {
                continue;
            }

            $commPath = "{$procDir}/{$item}/comm";
            if (is_readable($commPath)) {
                $comm = trim((string) file_get_contents($commPath));
                if ($comm === $name || str_starts_with($name, $comm)) {
                    return true;
                }
            }
        }

        return false;
    }
}
