<?php

namespace App\Exports\Isp;

use App\Models\IspCustomer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return IspCustomer::with(['user', 'plan'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Phone',
            'Email',
            'Identity Number',
            'Address',
            'Plan',
            'Status',
            'Joined Date',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->user->name ?? '',
            $customer->user->phone ?? '',
            $customer->user->email ?? '',
            $customer->identity_number,
            $customer->address_street,
            $customer->plan->name ?? '-',
            $customer->status,
            $customer->created_at->format('Y-m-d'),
        ];
    }
}
