<?php

declare(strict_types=1);

namespace App\Notifications\Isp;

use App\Models\Isp\Invoice;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    protected Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable): string
    {
        $amount = number_format((float) $this->invoice->amount, 0, ',', '.');
        
        return "Terima kasih {$notifiable->name},\n\n" .
               "Pembayaran untuk Invoice *{$this->invoice->invoice_number}* sebesar *Rp {$amount}* telah berhasil kami terima.\n\n" .
               "Layanan internet Anda aktif kembali (jika sebelumnya terisolir).\n\n" .
               "Selamat menikmati layanan kami.";
    }
}
