<?php

declare(strict_types=1);

namespace App\Models\Isp;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\VoucherFactory> */
    use HasFactory;

    protected $table = 'isp_vouchers';

    protected $fillable = [
        'code',
        'profile',
        'price',
        'status',
        'used_at',
        'used_by',
        'batch_id',
    ];

    protected $casts = [
        'price' => 'float',
        'used_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
