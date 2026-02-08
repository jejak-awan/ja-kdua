<?php

declare(strict_types=1);

namespace App\Services\Isp;

/**
 * Lightweight RouterOS API Client
 * Based on the official MikroTik PHP API class.
 */
class RouterOSAPI
{
    public bool $debug = false;

    public bool $connected = false;

    public int $port = 8728;

    public int $timeout = 3;

    public int $attempts = 2;

    public int $delay = 1;

    /** @var resource|null */
    private $socket;

    public function connect(string $host, string $user, string $pass): bool
    {
        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            $socket = @fsockopen($host, $this->port, $errno, $errstr, (float) $this->timeout);
            if ($socket !== false) {
                $this->socket = $socket;
                stream_set_timeout($this->socket, $this->timeout);
                if ($this->login($user, $pass)) {
                    $this->connected = true;

                    return true;
                }
                fclose($this->socket);
                $this->socket = null;
            }
            if ($attempt < $this->attempts) {
                sleep($this->delay);
            }
        }

        return false;
    }

    public function disconnect(): void
    {
        if ($this->socket !== null) {
            fclose($this->socket);
            $this->socket = null;
        }
        $this->connected = false;
    }

    private function login(string $user, string $pass): bool
    {
        $this->write('/login');
        $response = $this->read();
        if (isset($response[0]) && $response[0] === '!done') {
            $hash = isset($response[1]) ? explode('=', $response[1]) : [];
            if (isset($hash[1])) {
                $this->write('/login', false);
                $this->write('=name='.$user, false);
                $this->write('=response=00'.md5(chr(0).$pass.pack('H*', $hash[1])));
                /** @var array<int, string> $response */
                $response = $this->read();
                if (isset($response[0]) && $response[0] === '!done') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, string|int>  $args
     * @return array<int, array<string, string>>
     */
    public function comm(string $com, array $args = []): array
    {
        if (! $this->connected) {
            return [];
        }
        $this->write($com, false);
        foreach ($args as $key => $value) {
            $this->write('='.$key.'='.(string) $value, false);
        }
        $this->write($com, true);

        return $this->parseResponse($this->read());
    }

    private function write(string $command, bool $terminal = true): void
    {
        if ($this->socket === null) {
            return;
        }

        $len = strlen($command);
        if ($len < 0x80) {
            fwrite($this->socket, chr($len));
        } elseif ($len < 0x4000) {
            $len |= 0x8000;
            fwrite($this->socket, chr(($len >> 8) & 0xFF).chr($len & 0xFF));
        } elseif ($len < 0x200000) {
            $len |= 0xC00000;
            fwrite($this->socket, chr(($len >> 16) & 0xFF).chr(($len >> 8) & 0xFF).chr($len & 0xFF));
        }
        fwrite($this->socket, $command);
        if ($terminal) {
            fwrite($this->socket, chr(0));
        }
    }

    /**
     * @return array<int, string>
     */
    private function read(): array
    {
        if ($this->socket === null) {
            return [];
        }

        $responses = [];
        while (true) {
            $char = fread($this->socket, 1);
            if ($char === false || $char === '') {
                break;
            }
            $byte = ord($char);
            $length = 0;
            if ($byte & 0x80) {
                if (($byte & 0xC0) === 0x80) {
                    $nextChar = fread($this->socket, 1);
                    if ($nextChar !== false && $nextChar !== '') {
                        $length = (($byte & 0x3F) << 8) + ord($nextChar);
                    }
                } elseif (($byte & 0xE0) === 0xC0) {
                    $c1 = fread($this->socket, 1);
                    $c2 = fread($this->socket, 1);
                    if ($c1 !== false && $c1 !== '' && $c2 !== false && $c2 !== '') {
                        $length = (($byte & 0x1F) << 16) + (ord($c1) << 8) + ord($c2);
                    }
                }
            } else {
                $length = $byte;
            }

            if ($length === 0) {
                break;
            }

            $content = fread($this->socket, $length);
            if ($content !== false && $content !== '') {
                $responses[] = $content;
            }
        }

        return $responses;
    }

    /**
     * @param  array<int, string>  $response
     * @return array<int, array<string, string>>
     */
    private function parseResponse(array $response): array
    {
        $result = [];
        $item = [];
        foreach ($response as $line) {
            if ($line === '!re') {
                if (! empty($item)) {
                    $result[] = $item;
                }
                $item = [];
            } elseif (str_starts_with($line, '=')) {
                $parts = explode('=', substr($line, 1), 2);
                $item[$parts[0]] = $parts[1] ?? '';
            }
        }
        if (! empty($item)) {
            $result[] = $item;
        }

        return $result;
    }
}
