<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService implements WhatsAppServiceInterface
{
    protected string $driver;

    protected string $apiUrl;

    protected string $apiKey;

    public function __construct()
    {
        /** @var string $driver */
        $driver = config('services.whatsapp.driver', 'log');
        /** @var string $apiUrl */
        $apiUrl = config('services.whatsapp.url', '');
        /** @var string $apiKey */
        $apiKey = config('services.whatsapp.key', '');

        $this->driver = $driver;
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * Send a text message to a specific number.
     */
    public function send(string $to, string $message): bool
    {
        // Normalize number (e.g. 081 -> 6281)
        $to = $this->normalizeNumber($to);

        if ($this->driver === 'log') {
            Log::info("WhatsApp (Log Driver) -> {$to}: {$message}");

            return true;
        }

        if ($this->driver === 'http') {
            return $this->sendViaHttp($to, $message);
        }

        return false;
    }

    /**
     * Send via Generic HTTP Provider (Wablas, Fonnte, etc - adjust payload as needed).
     */
    protected function sendViaHttp(string $to, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'phone' => $to,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp Sent -> {$to}");

                return true;
            }

            Log::error("WhatsApp Failed -> {$to}: ".$response->body());

            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp Exception -> {$to}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Normalize phone number to 62xxx format.
     */
    protected function normalizeNumber(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number) ?? '';

        if (str_starts_with($number, '08')) {
            return '62'.substr($number, 1);
        }

        if (str_starts_with($number, '8')) {
            return '62'.$number;
        }

        return $number;
    }
}
