<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use Illuminate\Support\Facades\Log;

/**
 * CoA Service (Change of Authorization)
 *
 * Manages RFC 3576 compliant RADIUS CoA requests using radclient.
 */
class CoAService
{
    /**
     * Send a Disconnect-Request to a NAS.
     */
    public function disconnect(string $username, string $nasIp, string $secret, int $port = 1700): bool
    {
        // Format: echo "User-Name=user" | radclient -x <nas_ip>:1700 disconnect <secret>
        $command = sprintf(
            'echo "User-Name=%s" | radclient -t 2 -r 2 %s:%d disconnect %s 2>&1',
            escapeshellarg($username),
            escapeshellarg($nasIp),
            $port,
            escapeshellarg($secret)
        );

        try {
            Log::info("CoA [Disconnect]: Sending to {$nasIp} for user {$username}");

            $output = [];
            $resultCode = 0;
            \exec($command, $output, $resultCode);

            if ($resultCode === 0) {
                Log::info("CoA [Disconnect]: Success for {$username} at {$nasIp}");

                return true;
            }

            $errorMsg = implode("\n", $output);
            Log::error("CoA [Disconnect]: Failed for {$username} at {$nasIp}. Code: {$resultCode}. Output: {$errorMsg}");

            return false;
        } catch (\Exception $e) {
            Log::error("CoA [Disconnect]: Exception for {$username} at {$nasIp}: ".$e->getMessage());

            return false;
        }
    }
}
