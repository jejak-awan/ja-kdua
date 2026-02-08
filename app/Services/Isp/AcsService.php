<?php

declare(strict_types=1);

namespace App\Services\Isp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AcsService
{
    protected string $baseUrl;

    protected string $username;

    protected string $password;

    public function __construct()
    {
        $url = config('services.acs.url', 'http://genieacs:7557');
        $this->baseUrl = is_string($url) ? $url : 'http://genieacs:7557';

        $user = config('services.acs.username', '');
        $this->username = is_string($user) ? $user : '';

        $pass = config('services.acs.password', '');
        $this->password = is_string($pass) ? $pass : '';
    }

    /**
     * Fetch device parameters from ACS.
     *
     * @param  array<int, string>  $names
     * @return array<string, mixed>
     */
    public function getParameters(string $deviceId, array $names): array
    {
        try {
            Log::info("ACS: Fetching parameters for device {$deviceId}", $names);

            // Example for GenieACS API
            $response = Http::withBasicAuth($this->username, $this->password)
                ->get("{$this->baseUrl}/devices/{$deviceId}/tasks", [
                    'name' => 'getParameterValues',
                    'parameterNames' => $names,
                ]);

            if ($response->successful()) {
                /** @var array<string, mixed> $data */
                $data = $response->json();

                return $data;
            }

            throw new \Exception('ACS Request Failed: '.$response->body());
        } catch (\Exception $e) {
            Log::error('ACS Error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Set device parameters via ACS.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function setParameters(string $deviceId, array $parameters): bool
    {
        try {
            Log::info("ACS: Setting parameters for device {$deviceId}", $parameters);

            $response = Http::withBasicAuth($this->username, $this->password)
                ->post("{$this->baseUrl}/devices/{$deviceId}/tasks", [
                    'name' => 'setParameterValues',
                    'parameterValues' => $parameters,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('ACS Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Trigger a remote reboot.
     */
    public function reboot(string $deviceId): bool
    {
        try {
            Log::info("ACS: Rebooting device {$deviceId}");

            $response = Http::withBasicAuth($this->username, $this->password)
                ->post("{$this->baseUrl}/devices/{$deviceId}/tasks", [
                    'name' => 'reboot',
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('ACS Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Trigger a factory reset.
     */
    public function factoryReset(string $deviceId): bool
    {
        try {
            Log::warning("ACS: Factory resetting device {$deviceId}");

            $response = Http::withBasicAuth($this->username, $this->password)
                ->post("{$this->baseUrl}/devices/{$deviceId}/tasks", [
                    'name' => 'factoryReset',
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('ACS Error: '.$e->getMessage());

            return false;
        }
    }
}
