<?php

declare(strict_types=1);

namespace App\Models\Isp\Hardware;

use App\Models\Isp\Customer\Customer;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $inventory_id
 * @property string $type
 * @property int $quantity
 * @property int|null $customer_id
 * @property int $user_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Isp\Hardware\Inventory $item
 * @property-read \App\Models\Isp\Customer\Customer|null $customer
 * @property-read \App\Models\Core\User $user
 */
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
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
