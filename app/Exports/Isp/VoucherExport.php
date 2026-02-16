<?php

declare(strict_types=1);

namespace App\Exports\Isp;

use App\Models\Isp\Billing\Voucher;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements WithMapping<Voucher>
 */
class VoucherExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var array<string, mixed> */
    protected array $filters;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return Collection<int, Voucher>
     */
    public function collection(): Collection
    {
        $query = Voucher::with('partner');

        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['partner_id']) && $this->filters['partner_id'] !== 'all') {
            $query->where('partner_id', $this->filters['partner_id']);
        }

        return $query->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Profile',
            'Price',
            'Status',
            'Partner',
            'Duration',
            'Created At',
            'Used At',
        ];
    }

    /**
     * @param  Voucher  $voucher
     * @return array<int, mixed>
     */
    public function map($voucher): array
    {
        return [
            $voucher->id,
            $voucher->code,
            $voucher->profile,
            $voucher->price,
            $voucher->status,
            $voucher->partner->name ?? 'Server',
            $voucher->duration,
            $voucher->created_at?->format('Y-m-d H:i') ?? '-',
            $voucher->used_at ?? '-',
        ];
    }
}
