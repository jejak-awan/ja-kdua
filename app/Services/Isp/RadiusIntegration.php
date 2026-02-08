<?php

declare(strict_types=1);

namespace App\Services\Isp;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusIntegration
{
    protected string $connection = 'radius'; // Define this in config/database.php

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
}
