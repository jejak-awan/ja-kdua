<?php

declare(strict_types=1);

namespace App\Services\Isp;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusIntegration
{
    protected string $connection = 'radius'; // Define this in config/database.php

    /**
     * Get the RADIUS database connection name.
     */
    public function getConnectionName(): string
    {
        return $this->connection;
    }

    /**
     * Sync user to Radius database.
     *
     * @param  array<string, string>  $attributes
     */
    public function syncUser(string $username, string $password, array $attributes = []): void
    {
        try {
            Log::info("Radius Sync: Syncing user {$username}");

            // Update Password in radcheck
            DB::connection($this->connection)->table('radcheck')
                ->updateOrInsert(
                    ['username' => $username, 'attribute' => 'Cleartext-Password'],
                    ['op' => ':=', 'value' => $password]
                );

            // Update attributes in radreply
            foreach ($attributes as $attr => $value) {
                DB::connection($this->connection)->table('radreply')
                    ->updateOrInsert(
                        ['username' => $username, 'attribute' => $attr],
                        ['op' => ':=', 'value' => $value]
                    );
            }

            Log::info("Radius Sync: Successfully synced user {$username}");
        } catch (\Exception $e) {
            Log::error("Radius Sync Error for {$username}: ".$e->getMessage());
            // We don't throw here to avoid breaking the whole provisioning flow
            // but we might want to flag this.
        }
    }

    /**
     * Remove user from Radius database.
     */
    public function removeUser(string $username): void
    {
        try {
            DB::connection($this->connection)->table('radcheck')->where('username', $username)->delete();
            DB::connection($this->connection)->table('radreply')->where('username', $username)->delete();
            DB::connection($this->connection)->table('radusergroup')->where('username', $username)->delete();

            Log::info("Radius Sync: Removed user {$username}");
        } catch (\Exception $e) {
            Log::error("Radius Sync Error (Remove) for {$username}: ".$e->getMessage());
        }
    }

    /**
     * Send Disconnect-Request (CoA) to NAS.
     */
    public function sendDisconnectRequest(\App\Models\Isp\Customer $customer): bool
    {
        if (! $customer->mikrotik_login) {
            return false;
        }

        /** @var \App\Models\Isp\ServiceNode|null $router */
        $router = \App\Models\Isp\ServiceNode::find($customer->router_id);
        if (! $router || ! $router->ip_address) {
            Log::warning("Radius CoA: Unable to find router/IP for customer #{$customer->id}");

            return false;
        }

        // We use radclient to send the disconnect request.
        // Format: echo "User-Name=user" | radclient -x <nas_ip>:1700 disconnect <secret>
        $secret = $router->secret ?: 'testing123';
        $ip = $router->ip_address;
        $username = $customer->mikrotik_login;

        // Note: Mikrotik listens for CoA on port 1700 by default.
        $coaPortSetting = \App\Models\Core\Setting::get('radius_coa_port', 1700);
        $coaPort = is_numeric($coaPortSetting) ? (int) $coaPortSetting : 1700;
        $command = sprintf(
            'echo "User-Name=%s" | radclient -t 1 -r 1 %s:%d disconnect %s 2>&1',
            escapeshellarg($username),
            escapeshellarg($ip),
            $coaPort,
            escapeshellarg($secret)
        );

        try {
            Log::info("Radius CoA: Sending disconnect to {$ip} for {$username}");
            $output = [];
            $resultCode = 0;
            \exec($command, $output, $resultCode);

            if ($resultCode === 0) {
                Log::info("Radius CoA: Successfully sent disconnect to {$ip} for {$username}");

                return true;
            }

            Log::error("Radius CoA: Failed to send disconnect. Exit code: {$resultCode}. Output: ".implode("\n", $output));

            return false;
        } catch (\Exception $e) {
            Log::error("Radius CoA Error for {$username}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Get active sessions from radacct.
     *
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    public function getActiveSessions(int $limit = 50): \Illuminate\Support\Collection
    {
        return DB::connection($this->connection)
            ->table('radacct')
            ->whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent authentication logs from radpostauth.
     *
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    public function getAuthLogs(int $limit = 50): \Illuminate\Support\Collection
    {
        return DB::connection($this->connection)
            ->table('radpostauth')
            ->orderBy('authdate', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Sync customer usage data from radacct.
     */
    public function syncUsageData(\App\Models\Isp\Customer $customer): void
    {
        if (! $customer->mikrotik_login) {
            return;
        }

        try {
            // Default to start of current month if not set
            $since = $customer->last_usage_reset_at ?: now()->startOfMonth();

            $usage = DB::connection($this->connection)
                ->table('radacct')
                ->where('username', $customer->mikrotik_login)
                ->where('acctstarttime', '>=', $since)
                ->select(DB::raw('SUM(acctinputoctets + acctoutputoctets) as total_bytes'))
                ->first();

            $usageArr = (array) $usage;
            $totalBytes = is_numeric($usageArr['total_bytes'] ?? null) ? (int) $usageArr['total_bytes'] : 0;

            $customer->update([
                'current_usage_bytes' => $totalBytes,
                'last_usage_reset_at' => $since,
            ]);

            Log::info("Radius Sync: Usage synced for {$customer->mikrotik_login}: ".number_format($totalBytes / (1024 * 1024), 2)." MB since {$since}");
        } catch (\Exception $e) {
            Log::error("Radius Sync: Failed to sync usage for {$customer->mikrotik_login}: ".$e->getMessage());
        }
    }

    /**
     * Check and apply FUP for a customer.
     */
    public function applyFup(\App\Models\Isp\Customer $customer): bool
    {
        $plan = $customer->plan;
        if (! $plan || ! $plan->fup_enabled || ! $plan->fup_limit_gb || ! $plan->fup_speed) {
            return false;
        }

        $usageGb = $customer->current_usage_bytes / (1024 * 1024 * 1024);
        $isOverLimit = $usageGb >= $plan->fup_limit_gb;

        // If status changed or needs enforcement
        if ($isOverLimit && ! $customer->is_fup_active) {
            Log::info("Radius FUP: Enabling throttling for {$customer->mikrotik_login} ({$usageGb} GB >= {$plan->fup_limit_gb} GB)");

            // Update radreply
            DB::connection($this->connection)->table('radreply')
                ->updateOrInsert(
                    ['username' => $customer->mikrotik_login, 'attribute' => 'Mikrotik-Rate-Limit'],
                    ['op' => ':=', 'value' => $plan->fup_speed]
                );

            $customer->update(['is_fup_active' => true]);
            $this->sendDisconnectRequest($customer);

            return true;
        }

        if (! $isOverLimit && $customer->is_fup_active) {
            Log::info("Radius FUP: Disabling throttling for {$customer->mikrotik_login} (Usage reset or under limit)");

            $defaultSpeedSetting = \App\Models\Core\Setting::get('radius_default_speed', '10M/10M');
            $defaultSpeed = is_string($defaultSpeedSetting) ? $defaultSpeedSetting : '10M/10M';

            // Restore original plan speed
            DB::connection($this->connection)->table('radreply')
                ->updateOrInsert(
                    ['username' => $customer->mikrotik_login, 'attribute' => 'Mikrotik-Rate-Limit'],
                    ['op' => ':=', 'value' => $plan->mikrotik_rate_limit ?: $defaultSpeed]
                );

            $customer->update(['is_fup_active' => false]);
            $this->sendDisconnectRequest($customer);

            return true;
        }

        return false;
    }

    /**
     * Get aggregate usage for last 24 hours by hour.
     *
     * @return array<int, array{time: string, up: float, down: float}>
     */
    public function getCustomerUsageDaily(string $username): array
    {
        $since = now()->subHours(24);

        $results = DB::connection($this->connection)
            ->table('radacct')
            ->where('username', $username)
            ->where('acctstarttime', '>=', $since)
            ->select(
                DB::raw("DATE_FORMAT(acctstarttime, '%H:00') as time_label"),
                DB::raw('SUM(acctinputoctets) as up_bytes'),
                DB::raw('SUM(acctoutputoctets) as down_bytes')
            )
            ->groupBy('time_label')
            ->orderBy('time_label', 'asc')
            ->get();

        /** @var array<int, array{time: string, up: float, down: float}> $usage */
        $usage = $results->map(function ($row) {
            $rowArr = (array) $row;
            $time = is_string($rowArr['time_label'] ?? null) ? (string) $rowArr['time_label'] : '';
            $up = is_numeric($rowArr['up_bytes'] ?? null) ? (float) $rowArr['up_bytes'] : 0.0;
            $down = is_numeric($rowArr['down_bytes'] ?? null) ? (float) $rowArr['down_bytes'] : 0.0;

            return [
                'time' => $time,
                'up' => round($up / (1024 * 1024), 2), // MB
                'down' => round($down / (1024 * 1024), 2), // MB
            ];
        })->toArray();

        return $usage;
    }

    /**
     * Get aggregate usage for last 30 days by day.
     *
     * @return array<int, array{date: string, up: float, down: float}>
     */
    public function getCustomerUsageMonthly(string $username): array
    {
        $since = now()->subDays(30);

        $results = DB::connection($this->connection)
            ->table('radacct')
            ->where('username', $username)
            ->where('acctstarttime', '>=', $since)
            ->select(
                DB::raw("DATE_FORMAT(acctstarttime, '%b %d') as date_label"),
                DB::raw('SUM(acctinputoctets) as up_bytes'),
                DB::raw('SUM(acctoutputoctets) as down_bytes')
            )
            ->groupBy('date_label')
            ->orderBy(DB::raw('MIN(acctstarttime)'), 'asc')
            ->get();

        /** @var array<int, array{date: string, up: float, down: float}> $usage */
        $usage = $results->map(function ($row) {
            $rowArr = (array) $row;
            $date = is_string($rowArr['date_label'] ?? null) ? (string) $rowArr['date_label'] : '';
            $up = is_numeric($rowArr['up_bytes'] ?? null) ? (float) $rowArr['up_bytes'] : 0.0;
            $down = is_numeric($rowArr['down_bytes'] ?? null) ? (float) $rowArr['down_bytes'] : 0.0;

            return [
                'date' => $date,
                'up' => round($up / (1024 * 1024 * 1024), 2), // GB
                'down' => round($down / (1024 * 1024 * 1024), 2), // GB
            ];
        })->toArray();

        return $usage;
    }

    /**
     * Get aggregate usage for the last 24 hours (global/all users).
     *
     * @return array<int, array{time: string, up: float, down: float}>
     */
    public function getGlobalUsageDaily(): array
    {
        $since = now()->subHours(24);

        $results = DB::connection($this->connection)
            ->table('radacct')
            ->where('acctstarttime', '>=', $since)
            ->select(
                DB::raw("DATE_FORMAT(acctstarttime, '%H:00') as time_label"),
                DB::raw('SUM(acctinputoctets) as up_bytes'),
                DB::raw('SUM(acctoutputoctets) as down_bytes')
            )
            ->groupBy('time_label')
            ->orderBy('time_label', 'asc')
            ->get();

        /** @var array<int, array{time: string, up: float, down: float}> $usage */
        $usage = $results->map(function ($row) {
            $rowArr = (array) $row;
            $time = is_string($rowArr['time_label'] ?? null) ? (string) $rowArr['time_label'] : '';
            $up = is_numeric($rowArr['up_bytes'] ?? null) ? (float) $rowArr['up_bytes'] : 0.0;
            $down = is_numeric($rowArr['down_bytes'] ?? null) ? (float) $rowArr['down_bytes'] : 0.0;

            return [
                'time' => $time,
                'up' => round($up / (1024 * 1024), 2), // MB
                'down' => round($down / (1024 * 1024), 2), // MB
            ];
        })->toArray();

        return $usage;
    }

    /**
     * Get top users by bandwidth consumption.
     *
     * @return array<int, array{username: string, total_gb: float}>
     */
    public function getTopTalkers(int $limit = 10): array
    {
        $since = now()->subDays(30);

        $results = DB::connection($this->connection)
            ->table('radacct')
            ->where('acctstarttime', '>=', $since)
            ->select(
                'username',
                DB::raw('SUM(acctinputoctets + acctoutputoctets) as total_bytes')
            )
            ->groupBy('username')
            ->orderBy('total_bytes', 'desc')
            ->limit($limit)
            ->get();

        /** @var array<int, array{username: string, total_gb: float}> $topTalkers */
        $topTalkers = $results->map(function ($row) {
            $rowArr = (array) $row;
            $username = is_string($rowArr['username'] ?? null) ? (string) $rowArr['username'] : 'Unknown';
            $bytes = is_numeric($rowArr['total_bytes'] ?? null) ? (float) $rowArr['total_bytes'] : 0.0;

            return [
                'username' => $username,
                'total_gb' => round($bytes / (1024 * 1024 * 1024), 2),
            ];
        })->toArray();

        return $topTalkers;
    }

    /**
     * Check if FreeRADIUS service is running.
     * Note: exec() is disabled in PHP-FPM. Use process file check instead.
     *
     * @return array{running: bool, since: string|null, version: string}
     */
    public function getRadiusStatus(): array
    {
        $status = [
            'running' => false,
            'since' => null,
            'version' => '3.0',
        ];

        try {
            // Scan /proc for freeradius process
            $procDirs = glob('/proc/[0-9]*', GLOB_ONLYDIR);
            foreach ($procDirs ?: [] as $procDir) {
                $commFile = $procDir.'/comm';
                if (file_exists($commFile)) {
                    $comm = trim((string) @file_get_contents($commFile));
                    if ($comm === 'freeradius') {
                        $status['running'] = true;
                        $statFile = $procDir.'/stat';
                        if (file_exists($statFile)) {
                            $status['since'] = date('Y-m-d H:i:s', (int) filectime($statFile));
                        }
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Radius Status Error: '.$e->getMessage());
        }

        return $status;
    }
}
