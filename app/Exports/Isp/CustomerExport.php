<?php

namespace App\Exports\Isp;

use App\Models\Isp\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements WithMapping<Customer>
 */
class CustomerExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return Collection<int, Customer>
     */
    public function collection(): Collection
    {
        return Customer::with(['user', 'plan'])->get();
    }

    /**
     * @return array<int, string>
     */
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

    /**
     * @param  Customer  $customer
     * @return array<int, mixed>
     */
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
            $customer->created_at ? $customer->created_at->format('Y-m-d') : '-',
        ];
    }
}
