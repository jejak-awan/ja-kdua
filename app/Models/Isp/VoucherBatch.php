<?php

declare(strict_types=1);

namespace App\Models\Isp;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $batch_code
 * @property int $profile_id
 * @property int|null $partner_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $total_value
 * @property string $status
 * @property int|null $created_by
 */
class VoucherBatch extends Model
{
    protected $table = 'isp_voucher_batches';

    protected $fillable = [
        'batch_code',
        'profile_id',
        'partner_id',
        'quantity',
        'unit_price',
        'total_value',
        'status',
        'created_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'total_value' => 'float',
    ];

    /**
     * Get the service profile for this batch.
     *
     * @return BelongsTo<IspPlan, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(IspPlan::class, 'profile_id');
    }

    /**
     * Get the partner for this batch.
     *
     * @return BelongsTo<Partner, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the user who created this batch.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all vouchers in this batch.
     *
     * @return HasMany<Voucher, $this>
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'batch_code', 'batch_code');
    }

    /**
     * Generate a unique batch code.
     */
    public static function generateBatchCode(): string
    {
        return 'BATCH-'.strtoupper(substr(md5((string) microtime(true)), 0, 8));
    }
}
