<?php

declare(strict_types=1);

namespace App\Services\Isp\WhatsApp;

use App\Models\Core\Setting;

class WhatsAppNotificationService
{
    public function __construct(
        protected WhatsAppServiceInterface $whatsApp
    ) {}

    /**
     * Send a WhatsApp message.
     */
    public function sendMessage(string $to, string $message): bool
    {
        return $this->whatsApp->send($to, $message);
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
