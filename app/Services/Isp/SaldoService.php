<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Core\User;
use App\Models\Isp\Customer;
use App\Models\Isp\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($customer, $amount, $category, $description, $referenceType, $referenceId, $createdBy) {
            $saldoBefore = (float) $customer->saldo;
            $saldoAfter = $saldoBefore + $amount;

            $customer->update(['saldo' => $saldoAfter]);

            return Transaction::create([
                'parent_type' => Customer::class,
                'parent_id' => $customer->id,
                'type' => 'credit',
                'amount' => $amount,
                'saldo_before' => $saldoBefore,
                'saldo_after' => $saldoAfter,
                'category' => $category,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $createdBy?->id,
            ]);
        });
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
        return DB::transaction(function () use ($customer, $amount, $category, $description, $referenceType, $referenceId, $createdBy) {
            $saldoBefore = (float) $customer->saldo;
            $saldoAfter = $saldoBefore - $amount;

            $customer->update(['saldo' => $saldoAfter]);

            return Transaction::create([
                'parent_type' => Customer::class,
                'parent_id' => $customer->id,
                'type' => 'debit',
                'amount' => $amount,
                'saldo_before' => $saldoBefore,
                'saldo_after' => $saldoAfter,
                'category' => $category,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $createdBy?->id,
            ]);
        });
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
