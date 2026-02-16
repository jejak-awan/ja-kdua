<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusService
{
    protected string $connection = 'radius';

    protected CoAService $coa;

    protected RadiusStatService $stats;

    public function __construct(CoAService $coa, RadiusStatService $stats)
    {
        $this->coa = $coa;
        $this->stats = $stats;
    }

    /**
     * Get the RADIUS database connection name.
     */
    public function getConnectionName(): string
    {
        /** @var string $conn */
        $conn = config('database.radius_connection', 'radius');

        return $conn;
    }

    /**
     * Sync user to Radius database.
     *
     * @param  array<string, string>  $replyAttributes
     * @param  array<string, string>  $checkAttributes
     */
    public function syncUser(string $username, string $password, array $replyAttributes = [], array $checkAttributes = []): void
    {
        try {
            Log::info("Radius Sync: Syncing user {$username}");

            // 1. Sync Check Items (radcheck)
            // Always set Password first
            DB::connection($this->connection)->table('radcheck')
                ->updateOrInsert(
                    ['username' => $username, 'attribute' => 'Cleartext-Password'],
                    ['op' => ':=', 'value' => $password]
                );

            // Sync other check attributes (e.g., Called-Station-Id)
            // We first delete existing check attributes that are NOT password
            DB::connection($this->connection)->table('radcheck')
                ->where('username', $username)
                ->where('attribute', '!=', 'Cleartext-Password')
                ->delete();

            foreach ($checkAttributes as $attr => $value) {
                DB::connection($this->connection)->table('radcheck')->insert([
                    'username' => $username,
                    'attribute' => $attr,
                    'op' => '==', // Check items usually use ==
                    'value' => $value,
                ]);
            }

            // 2. Sync Reply Items (radreply)
            foreach ($replyAttributes as $attr => $value) {
                DB::connection($this->connection)->table('radreply')
                    ->updateOrInsert(
                        ['username' => $username, 'attribute' => $attr],
                        ['op' => ':=', 'value' => $value]
                    );
            }

            Log::info("Radius Sync: Successfully synced user {$username}");
        } catch (\Exception $e) {
            Log::error("Radius Sync Error for {$username}: ".$e->getMessage());
        }
    }

    /**
     * Sync NAS (Network Access Server) to Radius database.
     */
    public function syncNas(\App\Models\Isp\Network\ServiceNode $router): void
    {
        if (! $router->ip_address) {
            return;
        }

        // If radius is disabled, or we want to remove it, we call removeNas
        if (! $router->radius_enabled) {
            $this->removeNas($router);

            return;
        }

        try {
            DB::connection($this->connection)->table('nas')
                ->updateOrInsert(
                    ['nasname' => $router->ip_address],
                    [
                        'shortname' => $router->name,
                        'type' => 'mikrotik',
                        'secret' => $router->radius_secret ?: ($router->secret ?: 'testing123'),
                        'description' => "JA-CMS Router: {$router->name}",
                    ]
                );

            Log::info("Radius Sync: Synced NAS {$router->name} ({$router->ip_address})");
        } catch (\Exception $e) {
            Log::error("Radius Sync Error (NAS) for {$router->name}: ".$e->getMessage());
        }
    }

    /**
     * Remove NAS from Radius database.
     */
    public function removeNas(\App\Models\Isp\Network\ServiceNode $router): void
    {
        if (! $router->ip_address) {
            return;
        }

        try {
            DB::connection($this->connection)->table('nas')->where('nasname', $router->ip_address)->delete();
            Log::info("Radius Sync: Removed NAS {$router->name} ({$router->ip_address})");
        } catch (\Exception $e) {
            Log::error("Radius Sync Error (Remove NAS) for {$router->name}: ".$e->getMessage());
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
    public function sendDisconnectRequest(\App\Models\Isp\Customer\Customer $customer): bool
    {
        if (! $customer->mikrotik_login) {
            return false;
        }

        /** @var \App\Models\Isp\Network\ServiceNode|null $router */
        $router = \App\Models\Isp\Network\ServiceNode::find($customer->router_id);
        if (! $router || ! $router->ip_address) {
            Log::warning("Radius CoA: Unable to find router/IP for customer #{$customer->id}");

            return false;
        }

        $secret = $router->radius_secret ?: ($router->secret ?: '');
        $ip = $router->ip_address;
        $username = $customer->mikrotik_login;

        $coaPortSetting = \App\Models\Core\Setting::get('radius_coa_port', 1700);
        $coaPort = is_numeric($coaPortSetting) ? (int) $coaPortSetting : 1700;

        return $this->coa->disconnect($username, $ip, $secret, $coaPort);
    }

    /**
     * Get active sessions from radacct.
     *
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    public function getActiveSessions(int $limit = 50): \Illuminate\Support\Collection
    {
        return $this->stats->getActiveSessions($limit);
    }

    /**
     * Get recent authentication logs from radpostauth.
     *
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    public function getAuthLogs(int $limit = 50): \Illuminate\Support\Collection
    {
        return $this->stats->getAuthLogs($limit);
    }

    /**
     * Sync customer usage data from radacct.
     */
    public function syncUsageData(\App\Models\Isp\Customer\Customer $customer): void
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
    public function applyFup(\App\Models\Isp\Customer\Customer $customer): bool
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
        return $this->stats->getCustomerUsageDaily($username);
    }

    /**
     * Get aggregate usage for last 30 days by day.
     *
     * @return array<int, array{date: string, up: float, down: float}>
     */
    public function getCustomerUsageMonthly(string $username): array
    {
        return $this->stats->getCustomerUsageMonthly($username);
    }

    /**
     * Get aggregate usage for the last 24 hours (global/all users).
     *
     * @return array<int, array{time: string, up: float, down: float}>
     */
    public function getGlobalUsageDaily(): array
    {
        return $this->stats->getGlobalUsageDaily();
    }

    /**
     * Get top users by bandwidth consumption.
     *
     * @return array<int, array{username: string, total_gb: float}>
     */
    public function getTopTalkers(int $limit = 10): array
    {
        return $this->stats->getTopTalkers($limit);
    }

    /**
     * Check if FreeRADIUS service is running.
     * Note: exec() is disabled in PHP-FPM. Use process file check instead.
     *
     * @return array{running: bool, since: string|null, version: string}
     */
    /**
     * Get status of RADIUS node and global stats.
     *
     * @return array{
     *    nodes: array<int, array{id: int, name: string, ip: string|null, role: string, is_active: bool, health: array{running: bool, message: string}}>,
     *    total_nas: int,
     *    total_users: int,
     *    active_sessions: int
     * }
     */
    public function getRadiusStatus(): array
    {
        $stats = $this->stats->getRadiusStatus();
        $nodes = \App\Models\Isp\Network\ServiceZone::where('is_active', true)->get();

        $nodeList = $nodes->map(function ($node) use ($stats) {
            $isLocal = $node->ip_address === '127.0.0.1' || $node->ip_address === 'localhost';
            
            return [
                'id' => $node->id,
                'name' => $node->name,
                'ip' => $node->ip_address,
                'role' => $node->role,
                'is_active' => $node->is_active,
                'health' => [
                    'running' => $isLocal ? $stats['process']['running'] : true, // Remote assumed true if in zone for now
                    'message' => $isLocal 
                        ? ($stats['process']['running'] ? 'Local process is running' : 'Local process not found')
                        : 'Remote node monitored via synchronization',
                ],
            ];
        });

        // Add default local node if none exists
        if ($nodeList->isEmpty()) {
            $nodeList->push([
                'id' => 1,
                'name' => 'Default Radius Node',
                'ip' => '127.0.0.1',
                'role' => 'main',
                'is_active' => true,
                'health' => [
                    'running' => $stats['process']['running'],
                    'message' => $stats['process']['running'] ? 'Local process is running' : 'Local process not found',
                ],
            ]);
        }

        return [
            'nodes' => $nodeList->all(),
            'total_nas' => $stats['total_nas'],
            'total_users' => $stats['total_users'],
            'active_sessions' => $stats['active_sessions'],
        ];
    }

    /**
     * Send Disconnect-Request (CoA) to NAS by raw parameters.
     */
    public function rawDisconnect(string $username, string $nasIp): bool
    {
        /** @var \App\Models\Isp\Network\ServiceNode|null $router */
        $router = \App\Models\Isp\Network\ServiceNode::where('ip_address', $nasIp)->first();
        if (! $router) {
            Log::warning("Radius CoA: Unable to find router for NAS IP {$nasIp}");

            return false;
        }

        $secret = $router->radius_secret ?: ($router->secret ?: 'testing123');
        $coaPortSetting = \App\Models\Core\Setting::get('radius_coa_port', 1700);
        $coaPort = is_numeric($coaPortSetting) ? (int) $coaPortSetting : 1700;

        return $this->coa->disconnect($username, $nasIp, $secret, $coaPort);
    }
}
