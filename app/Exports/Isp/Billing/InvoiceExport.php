<?php

declare(strict_types=1);

namespace App\Exports\Isp\Billing;

use App\Models\Isp\Billing\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoiceExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Invoice::with('user')->latest()->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Invoice #',
            'Customer Name',
            'Amount (Rp)',
            'Status',
            'Due Date',
            'Created At',
        ];
    }

    /**
     * @param  \App\Models\Isp\Billing\Invoice  $invoice
     * @return array<int, string|null>
     */
    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->user?->name ?? 'N/A',
            number_format((float) $invoice->amount, 0, ',', '.'),
            strtoupper($invoice->status),
            $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-',
            $invoice->created_at ? $invoice->created_at->format('Y-m-d H:i') : '-',
        ];
    }
}
