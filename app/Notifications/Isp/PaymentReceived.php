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

    /**
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    /**
     * @param mixed $notifiable
     */
    public function toWhatsApp($notifiable): string
    {
        /** @var string|float|int $amountRaw */
        $amountRaw = $this->invoice->amount ?? 0;
        $amount = number_format((float) $amountRaw, 0, ',', '.');

        /** @var string $name */
        $name = is_object($notifiable) && property_exists($notifiable, 'name') ? (string) $notifiable->name : 'Pelanggan';

        return "Terima kasih {$name},\n\n".
               "Pembayaran untuk Invoice *" . (string) $this->invoice->invoice_number . "* sebesar *Rp {$amount}* telah berhasil kami terima.\n\n".
               "Layanan internet Anda aktif kembali (jika sebelumnya terisolir).\n\n".
               'Selamat menikmati layanan kami.';
    }
}
