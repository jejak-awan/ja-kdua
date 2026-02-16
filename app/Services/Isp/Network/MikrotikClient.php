<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

/**
 * Lightweight RouterOS API Client
 * Based on the official MikroTik PHP API class.
 */
class MikrotikClient
{
    public bool $debug = false;

    public bool $connected = false;

    public int $api_port = 8728;

    public int $timeout = 3;

    public int $attempts = 2;

    public int $delay = 1;

    /** @var resource|null */
    private $socket;

    public function connect(string $host, string $user, string $pass): bool
    {
        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            $socket = @fsockopen($host, $this->api_port, $errno, $errstr, (float) $this->timeout);
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
        $this->writeWord('/login');
        $this->writeWord('=name='.$user);
        $this->writeWord('=password='.$pass);
        $this->writeWord(''); // Terminator

        $response = $this->readResponse();

        $challenge = null;
        foreach ($response as $word) {
            if (str_starts_with($word, '=ret=')) {
                $challenge = substr($word, 5);
                break;
            }
        }

        if ($challenge && ctype_xdigit($challenge)) {
            $this->writeWord('/login');
            $this->writeWord('=name='.$user);
            $this->writeWord('=response=00'.md5(chr(0).$pass.pack('H*', $challenge)));
            $this->writeWord(''); // Terminator
            $response = $this->readResponse();
        }

        return isset($response[0]) && $response[0] === '!done';
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

        $this->writeWord($com);
        foreach ($args as $key => $value) {
            $prefix = str_starts_with((string) $key, '?') ? '' : '=';
            $this->writeWord($prefix.$key.'='.(string) $value);
        }
        $this->writeWord(''); // Terminator

        return $this->parseResponse($this->readResponse());
    }

    private function writeWord(string $word): void
    {
        if ($this->socket === null) {
            return;
        }

        $len = strlen($word);
        if ($len < 0x80) {
            fwrite($this->socket, chr($len));
        } elseif ($len < 0x4000) {
            $len |= 0x8000;
            fwrite($this->socket, chr(($len >> 8) & 0xFF).chr($len & 0xFF));
        } elseif ($len < 0x200000) {
            $len |= 0xC00000;
            fwrite($this->socket, chr(($len >> 16) & 0xFF).chr(($len >> 8) & 0xFF).chr($len & 0xFF));
        } elseif ($len < 0x10000000) {
            $len |= 0xE0000000;
            fwrite($this->socket, chr(($len >> 24) & 0xFF).chr(($len >> 16) & 0xFF).chr(($len >> 8) & 0xFF).chr($len & 0xFF));
        }

        fwrite($this->socket, $word);
    }

    /**
     * @return array<int, string>
     */
    private function readResponse(): array
    {
        $words = [];
        if ($this->socket === null) {
            return $words;
        }

        while (true) {
            $word = $this->readWord();
            if ($word === null) {
                break;
            }
            $words[] = $word;
            
            // Sentence terminator
            if ($word === '') {
                $lastWord = $words[count($words) - 2] ?? '';
                if ($lastWord === '!done' || $lastWord === '!fatal' || $lastWord === '!trap') {
                    break;
                }
            }
        }

        return $words;
    }

    private function readWord(): ?string
    {
        if ($this->socket === null) {
            return null;
        }

        $firstChar = fread($this->socket, 1);
        if ($firstChar === false || $firstChar === '') {
            return null;
        }
        $byte = ord($firstChar);
        $length = 0;

        if (($byte & 0x80) === 0x00) {
            $length = $byte;
        } elseif (($byte & 0xC0) === 0x80) {
            $char2 = fread($this->socket, 1);
            $length = (($byte & 0x3F) << 8) + ($char2 !== false ? ord($char2) : 0);
        } elseif (($byte & 0xE0) === 0xC0) {
            $char2 = fread($this->socket, 1);
            $char3 = fread($this->socket, 1);
            $length = (($byte & 0x1F) << 16) + (($char2 !== false ? ord($char2) : 0) << 8) + ($char3 !== false ? ord($char3) : 0);
        } elseif (($byte & 0xF0) === 0xE0) {
            $char2 = fread($this->socket, 1);
            $char3 = fread($this->socket, 1);
            $char4 = fread($this->socket, 1);
            $length = (($byte & 0x0F) << 24) + (($char2 !== false ? ord($char2) : 0) << 16) + (($char3 !== false ? ord($char3) : 0) << 8) + ($char4 !== false ? ord($char4) : 0);
        } elseif (($byte & 0xF8) === 0xF0) {
            $char2 = fread($this->socket, 1);
            $char3 = fread($this->socket, 1);
            $char4 = fread($this->socket, 1);
            $char5 = fread($this->socket, 1);
            $length = (($char2 !== false ? ord($char2) : 0) << 24) + (($char3 !== false ? ord($char3) : 0) << 16) + (($char4 !== false ? ord($char4) : 0) << 8) + ($char5 !== false ? ord($char5) : 0);
        }

        if ($length === 0) {
            return '';
        }

        $content = '';
        while (strlen($content) < $length) {
            $remaining = $length - strlen($content);
            /** @var int<1, max> $readLen */
            $readLen = $remaining > 0 ? $remaining : 1;
            $piece = fread($this->socket, $readLen);
            if ($piece === false || $piece === '') {
                break;
            }
            $content .= $piece;
        }

        return $content;
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
            } elseif ($line === '!trap' || $line === '!fatal') {
                $item['error'] = 'true';
            }
        }
        if (! empty($item)) {
            $result[] = $item;
        }

        return $result;
    }
}
