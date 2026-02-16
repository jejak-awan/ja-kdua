<?php

declare(strict_types=1);

namespace App\Services\Isp\Customer;

use App\Models\Core\User;
use App\Models\Isp\Customer\Partner;
use App\Models\Isp\Billing\Transaction;
use Illuminate\Database\Eloquent\Collection;

class PartnerService
{
    /**
     * Get all partners with optional filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Partner>
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Partner::query();

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search']) && is_string($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Create a new partner.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Partner
    {
        return Partner::create($data);
    }

    /**
     * Update a partner.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Partner $partner, array $data): Partner
    {
        $partner->update($data);

        return $partner->refresh();
    }

    /**
     * Delete a partner.
     */
    public function delete(Partner $partner): bool
    {
        return (bool) $partner->delete();
    }

    /**
     * Add credit to partner saldo.
     */
    public function addCredit(
        Partner $partner,
        float $amount,
        string $category,
        string $description,
        ?User $createdBy = null
    ): Transaction {
        return $partner->addCredit(
            $amount,
            $category,
            $description,
            null,
            null,
            $createdBy?->id
        );
    }

    /**
     * Deduct from partner saldo.
     */
    public function addDebit(
        Partner $partner,
        float $amount,
        string $category,
        string $description,
        ?User $createdBy = null
    ): Transaction {
        return $partner->addDebit(
            $amount,
            $category,
            $description,
            null,
            null,
            $createdBy?->id
        );
    }

    /**
     * Get partner transaction history.
     *
     * @return Collection<int, Transaction>
     */
    public function getTransactions(Partner $partner, ?string $fromDate = null, ?string $toDate = null): Collection
    {
        $query = $partner->transactions();

        if ($fromDate !== null) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate !== null) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Get partner statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(Partner $partner): array
    {
        $vouchers = $partner->vouchers();
        $customers = $partner->customers();

        return [
            'saldo' => $partner->saldo,
            'limit_hutang' => $partner->limit_hutang,
            'total_vouchers' => $vouchers->count(),
            'sold_vouchers' => $vouchers->whereNotNull('sold_at')->count(),
            'used_vouchers' => $vouchers->whereNotNull('used_at')->count(),
            'total_leads' => $customers->count(),
            'active_customers' => $customers->where('status', 'active')->count(),
            'total_commission' => $partner->transactions()->where('category', 'commission')->sum('amount'),
            'total_transactions' => $partner->transactions()->count(),
        ];
    }
}
