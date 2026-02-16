<?php

declare(strict_types=1);

namespace App\Models\Isp\Customer;

use App\Models\Core\User;
use App\Models\Isp\Billing\VoucherBatch;
use App\Traits\Isp\HasSaldo;
use App\Traits\Isp\HasAuditTrail;
use App\Traits\LogsActivity;
use App\Models\Isp\Billing\Transaction;
use App\Models\Isp\Billing\Voucher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string $category
 * @property string|null $phone
 * @property string|null $address
 * @property float $saldo
 * @property float $limit_hutang
 * @property float $commission_rate
 * @property string $status
 * @property array<string, mixed>|null $metadata
 * @property mixed $statistics
 */
class Partner extends Model
{
    use HasSaldo, HasAuditTrail, LogsActivity, SoftDeletes;

    protected $table = 'isp_partners';

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'phone',
        'address',
        'saldo',
        'limit_hutang',
        'commission_rate',
        'status',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'saldo' => 'float',
        'limit_hutang' => 'float',
        'commission_rate' => 'float',
        'metadata' => 'array',
    ];

    /**
     * Get the user associated with this partner.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all vouchers sold by this partner.
     *
     * @return HasMany<Voucher, $this>
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'partner_id');
    }

    /**
     * Get all transactions for this partner.
     *
     * @return MorphMany<Transaction, $this>
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'parent');
    }

    /**
     * Get all voucher batches for this partner.
     *
     * @return HasMany<VoucherBatch, $this>
     */
    public function voucherBatches(): HasMany
    {
        return $this->hasMany(VoucherBatch::class, 'partner_id');
    }

    /**
     * Get all customers referred by this partner.
     *
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'partner_id');
    }

    /**
     * Check if partner is a reseller.
     */
    public function isReseller(): bool
    {
        return $this->category === 'reseller';
    }

    /**
     * Check if partner is a biller.
     */
    public function isBiller(): bool
    {
        return $this->category === 'biller';
    }
}
