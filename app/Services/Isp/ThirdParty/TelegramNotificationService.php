<?php

declare(strict_types=1);

namespace App\Services\Isp\ThirdParty;

use App\Models\Core\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    protected ?string $token;

    protected ?string $adminChatId;

    public function __construct()
    {
        /** @var mixed $token */
        $token = Setting::get('telegram_bot_token', '');
        $this->token = is_string($token) ? $token : '';

        /** @var mixed $adminChatId */
        $adminChatId = Setting::get('telegram_admin_chat_id', '');
        $this->adminChatId = is_string($adminChatId) ? $adminChatId : '';
    }

    /**
     * Send a message to a specific chat ID.
     */
    public function sendMessage(string $chatId, string $text, string $parseMode = 'Markdown'): bool
    {
        if (empty($this->token)) {
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $parseMode,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('TelegramNotificationService: Failed to send message: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Send an alert to the admin chat.
     */
    public function sendAdminAlert(string $message): bool
    {
        if (empty($this->adminChatId)) {
            // Silently fail if not configured to avoid log spam
            return false;
        }

        return $this->sendMessage($this->adminChatId, "🚨 *ISP ALERT* 🚨\n\n".$message);
    }

    /**
     * Send a notification to a customer if they have a telegram_id.
     */
    public function sendCustomerNotification(\App\Models\Isp\Customer\Customer $customer, string $message): bool
    {
        if (empty($customer->telegram_id)) {
            return false;
        }

        return $this->sendMessage((string) $customer->telegram_id, $message);
    }
}
