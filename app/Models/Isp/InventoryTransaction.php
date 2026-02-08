<?php

declare(strict_types=1);

namespace App\Models\Isp;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $table = 'isp_inventory_transactions';

    protected $fillable = [
        'inventory_id',
        'type',
        'quantity',
        'customer_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * @return BelongsTo<Inventory, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
