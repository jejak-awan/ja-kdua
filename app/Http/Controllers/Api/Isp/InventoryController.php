<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\Inventory;
use App\Models\Isp\InventoryTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends BaseApiController
{
    /**
     * Display a listing of inventory items.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Inventory::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            /** @var string $search */
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        }

        return $this->success($query->latest()->get(), 'Inventory items retrieved successfully');
    }

    /**
     * Store a newly created inventory item.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:isp_inventories,sku',
            'category' => 'required|in:ONU,Router,Cable,SFP,Splitter,Other',
            'unit' => 'required|string|max:20',
            'stock' => 'integer|min:0',
            'min_stock' => 'integer|min:0',
            'unit_price' => 'numeric|min:0',
        ]);

        $item = Inventory::create($validated);

        return $this->success($item, 'Inventory item created successfully', 201);
    }

    /**
     * Record a stock transaction.
     */
    public function adjustStock(Request $request, Inventory $inventory): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:In,Out,Adjustment,Return',
            'quantity' => 'required|integer',
            'notes' => 'nullable|string',
            'customer_id' => 'nullable|exists:users,id',
        ]);

        return DB::transaction(function () use ($inventory, $validated) {
            $transaction = InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'customer_id' => $validated['customer_id'] ?? null,
                'user_id' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update stock level
            if ($validated['type'] === 'In' || $validated['type'] === 'Return') {
                $inventory->increment('stock', $validated['quantity']);
            } else {
                $inventory->decrement('stock', $validated['quantity']);
            }

            return $this->success($inventory->fresh(), 'Stock adjusted successfully');
        });
    }

    /**
     * Get transaction history for an item.
     */
    public function transactions(Inventory $inventory): JsonResponse
    {
        return $this->success(
            $inventory->transactions()->with(['user', 'customer'])->latest()->get(),
            'Transaction history retrieved successfully'
        );
    }

    /**
     * Remove the specified inventory item.
     */
    public function destroy(Inventory $inventory): JsonResponse
    {
        $inventory->delete();

        return $this->success(null, 'Inventory item deleted successfully');
    }
}
