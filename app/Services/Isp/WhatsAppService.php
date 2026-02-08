<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiKey;

    protected string $apiUrl;

    public function __construct()
    {
        // Example with Fonnte (common in Indonesia/South-East Asia for ISP)
        $key = config('services.whatsapp.key', '');
        $this->apiKey = is_string($key) ? $key : '';

        $url = config('services.whatsapp.url', 'https://api.fonnte.com/send');
        $this->apiUrl = is_string($url) ? $url : 'https://api.fonnte.com/send';
    }

    /**
     * Send a WhatsApp message.
     */
    public function sendMessage(string $to, string $message): bool
    {
        try {
            Log::info("WhatsApp: Sending message to {$to}");

            if (empty($this->apiKey)) {
                Log::warning("WhatsApp API Key not configured. Logging message instead: {$message}");

                return true;
            }

            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->post($this->apiUrl, [
                'target' => $to,
                'message' => $message,
                'countryCode' => '62', // Default Indonesia
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp: Message sent successfully to {$to}");

                return true;
            }

            throw new \Exception('WhatsApp Send Failed: '.$response->body());
        } catch (\Exception $e) {
            Log::error('WhatsApp Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Format and send a billing reminder.
     */
    public function sendBillingReminder(string $to, string $name, string $amount, string $dueDate): bool
    {
        $template = Setting::get('isp_wa_billing_template',
            "Halo *{$name}*,\n\nTagihan internet Anda sebesar *Rp {$amount}* akan jatuh tempo pada *{$dueDate}*.\n\nSilahkan lakukan pembayaran sebelum jatuh tempo untuk menghindari isolir layanan.\n\nTerima kasih."
        );
        $message = is_string($template) ? $template : '';

        return $this->sendMessage($to, $message);
    }

    /**
     * Format and send an outage alert.
     */
    public function sendOutageAlert(string $to, string $name, string $area): bool
    {
        $template = Setting::get('isp_wa_outage_template',
            "Halo *{$name}*,\n\nKami menginformasikan bahwa saat ini sedang terjadi gangguan layanan di area *{$area}*.\n\nTim teknis kami sedang dalam proses penanganan. Mohon maaf atas ketidaknyamanan ini."
        );
        $message = is_string($template) ? $template : '';

        return $this->sendMessage($to, $message);
    }
}
