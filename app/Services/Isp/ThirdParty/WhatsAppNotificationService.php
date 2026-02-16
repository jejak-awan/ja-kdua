<?php

declare(strict_types=1);

namespace App\Services\Isp\ThirdParty;

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
     * Format and send an overdue reminder.
     */
    public function sendOverdueReminder(string $to, string $name, string $amount, string $dueDate): bool
    {
        $template = Setting::get('isp_wa_overdue_template',
            "PENTING: Tagihan internet atas nama *{$name}* sebesar *Rp {$amount}* sudah melewati jatuh tempo (*{$dueDate}*).\n\nLayanan Anda akan di-*isolir* otomatis dalam 24 jam jika pembayaran belum diterima.\n\nSegera lakukan pembayaran via Aplikasi atau Transfer."
        );
        $message = is_string($template) ? $template : '';

        return $this->sendMessage($to, $message);
    }

    /**
     * Format and send a suspension notice.
     */
    public function sendSuspensionNotice(string $to, string $name): bool
    {
        $template = Setting::get('isp_wa_suspension_template',
            "Halo *{$name}*,\n\nLayanan internet Anda sementara di-nonaktifkan karena adanya tagihan yang belum terbayar.\n\nLayanan akan otomatis aktif kembali setelah pembayaran dikonfirmasi.\n\nHubungi Support jika butuh bantuan."
        );
        $message = is_string($template) ? $template : '';

        return $this->sendMessage($to, $message);
    }

    /**
     * Format and send an outage alert.
     */
    public function sendOutageAlert(string $to, string $nodeName, string $status, string $description): bool
    {
        $template = Setting::get('isp_wa_outage_template',
            "🛑 *NOTIFIKASI GANGGUAN JARINGAN* 🛑\n\nNode: *{$nodeName}*\nStatus: *{$status}*\nKeterangan: {$description}\n\nTim kami sedang menangani masalah ini secara prioritas. Mohon maaf atas ketidaknyamanannya."
        );
        $message = is_string($template) ? $template : '';

        return $this->sendMessage($to, $message);
    }
}
