<?php

declare(strict_types=1);

namespace App\Models\Isp;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $parent_type
 * @property int $parent_id
 * @property string $type
 * @property float $amount
 * @property float $saldo_before
 * @property float $saldo_after
 * @property string $category
 * @property string|null $description
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property int|null $created_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Model $parent
 * @property-read User|null $creator
 */
class Transaction extends Model
{
    protected $table = 'isp_transactions';

    protected $fillable = [
        'parent_type',
        'parent_id',
        'type',
        'amount',
        'saldo_before',
        'saldo_after',
        'category',
        'description',
        'reference_type',
        'reference_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'saldo_before' => 'float',
        'saldo_after' => 'float',
        'parent_id' => 'integer',
        'reference_id' => 'integer',
        'created_by' => 'integer',
    ];

    /**
     * Get the parent model (Customer or Mitra).
     *
     * @return MorphTo<Model, $this>
     */
    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this transaction.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this is a credit transaction.
     */
    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    /**
     * Check if this is a debit transaction.
     */
    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }
}
