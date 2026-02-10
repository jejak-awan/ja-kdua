<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $sku
 * @property string $category
 * @property string $unit
 * @property int $stock
 * @property int $min_stock
 * @property float $unit_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Isp\InventoryTransaction> $transactions
 */
class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\InventoryFactory> */
    use HasFactory;

    protected $table = 'isp_inventories';

    protected $fillable = [
        'name',
        'sku',
        'category',
        'unit',
        'stock',
        'min_stock',
        'unit_price',
    ];

    protected $casts = [
        'stock' => 'integer',
        'min_stock' => 'integer',
        'unit_price' => 'float',
    ];

    /**
     * @return HasMany<InventoryTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'inventory_id');
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
