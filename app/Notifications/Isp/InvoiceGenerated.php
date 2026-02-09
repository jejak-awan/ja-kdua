<?php

declare(strict_types=1);

namespace App\Notifications\Isp;

use App\Models\Isp\Invoice;
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

    public function via($notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable): string
    {
        $month = $this->invoice->created_at ? $this->invoice->created_at->translatedFormat('F Y') : date('F Y');
        $amount = number_format((float) $this->invoice->amount, 0, ',', '.');
        $dueDate = $this->invoice->due_date ? $this->invoice->due_date->format('d/m/Y') : '-';

        return "Halo {$notifiable->name},\n\n".
               "Tagihan internet Anda untuk bulan *{$month}* telah terbit.\n\n".
               "No. Invoice: {$this->invoice->invoice_number}\n".
               "Total: Rp {$amount}\n".
               "Jatuh Tempo: {$dueDate}\n\n".
               "Mohon segera lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari isolir otomatis.\n\n".
               'Terima kasih.';
    }
}
