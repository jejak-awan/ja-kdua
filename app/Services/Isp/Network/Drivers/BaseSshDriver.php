<?php

declare(strict_types=1);

namespace App\Services\Isp\Network\Drivers;

use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;

abstract class BaseSshDriver
{
    protected ?SSH2 $ssh = null;

    protected string $host;

    protected string $api_username;

    protected string $api_password;

    protected int $api_port;

    protected int $timeout;

    protected ?int $nodeId;

    public function __construct(string $host, string $username, string $password, int $port = 22, int $timeout = 10, ?int $nodeId = null)
    {
        $this->host = $host;
        $this->api_username = $username;
        $this->api_password = $password;
        $this->api_port = $port;
        $this->timeout = $timeout;
        $this->nodeId = $nodeId;
    }

    /**
     * Connect and authenticate.
     */
    public function connect(): bool
    {
        try {
            $this->ssh = new SSH2($this->host, $this->api_port, $this->timeout);
            if (! $this->ssh->login($this->api_username, $this->api_password)) {
                Log::error("SSH Driver: Authentication failed for {$this->api_username}@{$this->host}");
                $this->ssh = null;

                return false;
            }

            // Set some defaults for interactive shells
            $this->ssh->setTimeout(5);

            // Initialize the session (paging disable, etc)
            $this->initializeSession();

            return true;
        } catch (\Exception $e) {
            Log::error("SSH Driver: Connection error to {$this->host}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Optional initialization for specific vendors (e.g. terminal length 0).
     */
    protected function initializeSession(): void
    {
        // To be overridden by children if needed
    }

    /**
     * Execute a single command and return output.
     */
    protected function execute(string $command): string
    {
        if (! $this->ssh && ! $this->connect()) {
            return '';
        }

        /** @var SSH2 $ssh */
        $ssh = $this->ssh;
        $start = (int) (microtime(true) * 1000);
        $output = (string) $ssh->exec($command);
        $end = (int) (microtime(true) * 1000);

        $this->logCommand($command, $output, true, $end - $start);

        return $output;
    }

    /**
     * Interactive command execution (useful for nested CLIs like OLTs).
     */
    protected function writeAndRead(string $command, string $expect = '#'): string
    {
        if (! $this->ssh && ! $this->connect()) {
            return '';
        }

        /** @var SSH2 $ssh */
        $ssh = $this->ssh;
        $start = (int) (microtime(true) * 1000);
        $ssh->write($command."\n");
        $output = (string) $ssh->read($expect);
        $end = (int) (microtime(true) * 1000);

        $this->logCommand($command, $output, true, $end - $start);

        return $output;
    }

    public function disconnect(): void
    {
        if ($this->ssh) {
            $this->ssh->disconnect();
            $this->ssh = null;
        }
    }

    /**
     * Log command execution to database.
     */
    protected function logCommand(string $command, string $response, bool $success, int $executionTime): void
    {
        if (! $this->nodeId) {
            return;
        }

        try {
            \App\Models\Isp\Network\OltCommandLog::create([
                'olt_id' => $this->nodeId,
                'user_id' => auth()->id(),
                'command' => $command,
                'response' => $response,
                'is_success' => $success,
                'execution_time_ms' => $executionTime,
            ]);
        } catch (\Exception $e) {
            Log::error('SSH Driver: Failed to log command: '.$e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
