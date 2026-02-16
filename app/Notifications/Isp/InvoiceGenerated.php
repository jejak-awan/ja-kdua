<?php

declare(strict_types=1);

namespace App\Notifications\Isp;

use App\Models\Isp\Billing\Invoice;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceGenerated extends Notification
{
    use Queueable;

    protected Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toWhatsApp($notifiable): string
    {
        $month = $this->invoice->created_at ? $this->invoice->created_at->translatedFormat('F Y') : date('F Y');
        /** @var string|float|int $amountRaw */
        $amountRaw = $this->invoice->amount ?? 0;
        $amount = number_format((float) $amountRaw, 0, ',', '.');
        $dueDate = $this->invoice->due_date ? $this->invoice->due_date->format('d/m/Y') : '-';

        /** @var string $name */
        $name = is_object($notifiable) && property_exists($notifiable, 'name') ? (string) $notifiable->name : 'Pelanggan';

        return "Halo {$name},\n\n".
               "Tagihan internet Anda untuk bulan *{$month}* telah terbit.\n\n".
               'No. Invoice: '.(string) $this->invoice->invoice_number."\n".
               "Total: Rp {$amount}\n".
               "Jatuh Tempo: {$dueDate}\n\n".
               "Mohon segera lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari isolir otomatis.\n\n".
               'Terima kasih.';
    }
}
