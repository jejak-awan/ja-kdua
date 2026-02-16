<?php

declare(strict_types=1);

namespace App\Services\Isp\ThirdParty;

interface WhatsAppServiceInterface
{
    /**
     * Send a text message to a specific number.
     *
     * @param  string  $to  The recipient phone number (e.g., 628123456789)
     * @param  string  $message  The text message content
     * @return bool True if sent successfully (or queued), false otherwise
     */
    public function send(string $to, string $message): bool;
}
