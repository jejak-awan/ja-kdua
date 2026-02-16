<?php

declare(strict_types=1);

namespace App\Traits\Isp;

use App\Models\Isp\Billing\Transaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

/**
 * @property float $saldo
 * @property float $limit_hutang
 */
trait HasSaldo
{
    /**
     * Get all transactions for this entity.
     *
     * @return MorphMany<Transaction, $this>
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'parent');
    }

    /**
     * Add credit to the balance.
     */
    public function addCredit(float $amount, string $category, ?string $description = null, ?string $referenceType = null, ?int $referenceId = null, ?int $createdBy = null): Transaction
    {
        return $this->processTransaction('credit', $amount, $category, $description, $referenceType, $referenceId, $createdBy);
    }

    /**
     * Deduct debit from the balance.
     */
    public function addDebit(float $amount, string $category, ?string $description = null, ?string $referenceType = null, ?int $referenceId = null, ?int $createdBy = null): Transaction
    {
        return $this->processTransaction('debit', $amount, $category, $description, $referenceType, $referenceId, $createdBy);
    }

    /**
     * Process a transaction and update balance.
     */
    protected function processTransaction(string $type, float $amount, string $category, ?string $description = null, ?string $referenceType = null, ?int $referenceId = null, ?int $createdBy = null): Transaction
    {
        return DB::transaction(function () use ($type, $amount, $category, $description, $referenceType, $referenceId, $createdBy) {
            // Get fresh data to ensure accurate saldo_before
            $this->refresh();
            $saldoBefore = (float) $this->saldo;

            if ($type === 'credit') {
                $this->increment('saldo', $amount);
            } else {
                $this->decrement('saldo', $amount);
            }

            // Refresh again to get the updated saldo after increment/decrement
            $this->refresh();

            return $this->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'saldo_before' => $saldoBefore,
                'saldo_after' => (float) $this->saldo,
                'category' => $category,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $createdBy ?? auth()->id(),
            ]);
        });
    }

    /**
     * Check if sufficient balance exists.
     */
    public function hasSufficientSaldo(float $amount): bool
    {
        return ($this->saldo + $this->limit_hutang) >= $amount;
    }
}
