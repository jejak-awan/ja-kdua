<?php

declare(strict_types=1);

namespace App\Services\Isp\Billing;

use App\Models\Core\User;
use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Billing\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SaldoService
{
    /**
     * Add credit (top up) to customer saldo.
     */
    public function addCredit(
        Customer $customer,
        float $amount,
        string $category,
        string $description = '',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?User $createdBy = null
    ): Transaction {
        return $customer->addCredit(
            $amount,
            $category,
            $description,
            $referenceType,
            $referenceId,
            $createdBy?->id
        );
    }

    /**
     * Debit (charge) from customer saldo.
     */
    public function addDebit(
        Customer $customer,
        float $amount,
        string $category,
        string $description = '',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?User $createdBy = null
    ): Transaction {
        return $customer->addDebit(
            $amount,
            $category,
            $description,
            $referenceType,
            $referenceId,
            $createdBy?->id
        );
    }

    /**
     * Pay invoice using customer saldo.
     *
     * @return array{success: bool, message: string, transaction: Transaction|null}
     */
    public function payWithSaldo(Customer $customer, float $invoiceAmount, int $invoiceId, ?User $createdBy = null): array
    {
        if ((float) $customer->saldo < $invoiceAmount) {
            return [
                'success' => false,
                'message' => 'Insufficient saldo',
                'transaction' => null,
            ];
        }

        $transaction = $this->addDebit(
            $customer,
            $invoiceAmount,
            'invoice_payment',
            'Payment for invoice #'.$invoiceId,
            'invoice',
            $invoiceId,
            $createdBy
        );

        return [
            'success' => true,
            'message' => 'Invoice paid with saldo',
            'transaction' => $transaction,
        ];
    }

    /**
     * Get transaction history for a customer.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Transaction>
     */
    public function getTransactions(int $customerId, array $filters = []): LengthAwarePaginator
    {
        $query = Transaction::where('parent_type', Customer::class)
            ->where('parent_id', $customerId);

        if (isset($filters['type']) && is_string($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['category']) && is_string($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        $perPage = isset($filters['per_page']) && is_numeric($filters['per_page']) ? (int) $filters['per_page'] : 15;

        return $query->latest()->paginate($perPage);
    }
}
