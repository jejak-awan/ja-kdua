<?php

declare(strict_types=1);

namespace App\Services\Isp\Hardware;

use App\Models\Isp\Hardware\Inventory;
use App\Models\Isp\Hardware\InventoryTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Deduct items from inventory (e.g. during provisioning).
     */
    public function deductItems(string $category, int $quantity, int $customerId, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($category, $quantity, $customerId, $notes) {
            /** @var Inventory|null $item */
            $item = Inventory::where('category', $category)
                ->where('stock', '>=', $quantity)
                ->orderBy('stock', 'desc')
                ->first();

            if (! $item) {
                Log::error("InventoryService: Insufficient stock for category: {$category}");

                return false;
            }

            $item->decrement('stock', $quantity);

            InventoryTransaction::create([
                'inventory_id' => $item->id,
                'type' => 'Out',
                'quantity' => $quantity,
                'customer_id' => $customerId,
                'user_id' => Auth::id() ?? 0, // Fallback for background jobs
                'notes' => $notes ?? "Automated deduction for customer #{$customerId}",
            ]);

            // Check if stock dropped below minimum
            if ($item->stock <= $item->min_stock) {
                Log::warning("InventoryService: Low stock alert for {$item->name} (SKU: {$item->sku}). Current: {$item->stock}");
                // Future: Trigger WhatsApp alert to NOC admin
            }

            return true;
        });
    }

    /**
     * Add items to inventory.
     */
    public function addItems(int $itemId, int $quantity, ?string $notes = null): bool
    {
        $item = Inventory::findOrFail($itemId);

        return DB::transaction(function () use ($item, $quantity, $notes) {
            $item->increment('stock', $quantity);

            InventoryTransaction::create([
                'inventory_id' => $item->id,
                'type' => 'In',
                'quantity' => $quantity,
                'user_id' => Auth::id() ?? 0,
                'notes' => $notes ?? 'Manual stock addition',
            ]);

            return true;
        });
    }
}
