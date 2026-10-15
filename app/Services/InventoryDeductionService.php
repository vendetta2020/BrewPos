<?php

namespace App\Services;

use App\Events\LowStockAlert;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

class InventoryDeductionService
{
    /**
     * Calculate aggregated raw material requirements for the cart, sorted ascending by raw material ID.
     *
     * @param array $cart
     * @return array<int, array{id: int, name: string, unit: string, needed: float}>
     */
    public function getRawMaterialRequirementsForCart(array $cart): array
    {
        $requirements = [];
        foreach ($cart as $item) {
            $product = \App\Models\Product::with('recipes.rawMaterial')->find($item['product_id']);
            if (!$product) {
                continue;
            }

            $size = $item['size'] ?? 'M';
            $recipes = $product->recipes->filter(function ($r) use ($size) {
                return $r->size_variant === $size || $r->size_variant === 'all';
            });

            foreach ($recipes as $recipe) {
                $matId = (int) $recipe->raw_material_id;
                $totalQty = (float) $recipe->qty_used * (int) $item['quantity'];
                if (!isset($requirements[$matId])) {
                    $requirements[$matId] = [
                        'id'     => $matId,
                        'name'   => $recipe->rawMaterial->name ?? 'Bahan Baku',
                        'unit'   => $recipe->rawMaterial->unit ?? '',
                        'needed' => 0.0,
                    ];
                }
                $requirements[$matId]['needed'] += $totalQty;
            }
        }

        ksort($requirements); // Strictly ordered ascending by ID to prevent deadlocks
        return $requirements;
    }

    /**
     * Validate that sufficient raw material stock is available for the given cart items.
     * Throws an exception if any raw material stock is insufficient.
     *
     * @param array $cart
     * @return void
     * @throws \RuntimeException
     */
    public function validateStockForCart(array $cart): void
    {
        $requirements = $this->getRawMaterialRequirementsForCart($cart);

        foreach ($requirements as $matId => $req) {
            $material = RawMaterial::find($matId);
            if ($material && (float) $material->stock_qty < $req['needed']) {
                throw new \RuntimeException("Stok bahan baku '{$req['name']}' tidak mencukupi (tersedia: {$material->stock_qty} {$req['unit']}, dibutuhkan: {$req['needed']} {$req['unit']}).");
            }
        }
    }

    /**
     * Deterministically acquire pessimistic locks on all required raw materials (ordered ascending by ID)
     * and validate that sufficient stock is available.
     *
     * By locking resources in strict ascending ID order, this eliminates deadlocks (circular waits)
     * across concurrent multi-item checkout transactions.
     *
     * @param array $cart
     * @return void
     * @throws \RuntimeException
     */
    public function validateAndLockStockForCart(array $cart): void
    {
        $requirements = $this->getRawMaterialRequirementsForCart($cart);
        if (empty($requirements)) {
            return;
        }

        $materialIds = array_keys($requirements);
        sort($materialIds); // Ensure strict ascending order for deadlock avoidance

        $lockedMaterials = RawMaterial::whereIn('id', $materialIds)
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($requirements as $matId => $req) {
            $material = $lockedMaterials->get($matId);
            if (! $material || (float) $material->stock_qty < $req['needed']) {
                $available = $material ? $material->stock_qty : 0;
                throw new \RuntimeException("Stok bahan baku '{$req['name']}' tidak mencukupi (tersedia: {$available} {$req['unit']}, dibutuhkan: {$req['needed']} {$req['unit']}).");
            }
        }
    }

    /**
     * Deduct raw materials from inventory based on the order item and its product's recipe.
     *
     * Validates that stock is not decremented below zero.
     *
     * Satisfies SRS: INV-05 — automatic stock deduction on completed transactions.
     * Satisfies SRS: INV-06 — each deduction is logged as a stock movement.
     * Satisfies SRS: INV-07 — low-stock alerts are broadcast to admins.
     *
     * @param OrderItem $orderItem  The completed order item containing product & quantity.
     * @return void
     * @throws \RuntimeException
     */
    public function deductFromOrder(OrderItem $orderItem): void
    {
        // Retrieve the size variant used for this item, default to 'all'
        $size = $orderItem->size ?? 'all';

        // Fetch all recipes for this product matching the size variant or 'all'
        $recipes = $orderItem->product->recipes()
            ->where(function ($query) use ($size) {
                $query->where('size_variant', $size)
                      ->orWhere('size_variant', 'all');
            })
            ->get();

        // Process each recipe ingredient
        foreach ($recipes as $recipe) {
            $material = RawMaterial::lockForUpdate()->find($recipe->raw_material_id);
            if (!$material) {
                continue;
            }

            // Calculate total quantity used: recipe qty * order item quantity
            $totalUsed = (float) $recipe->qty_used * (int) $orderItem->quantity;

            // Validate that stock does not become negative
            if ((float) $material->stock_qty < $totalUsed) {
                throw new \RuntimeException("Stok bahan baku '{$material->name}' tidak mencukupi (tersedia: {$material->stock_qty} {$material->unit}, dibutuhkan: {$totalUsed} {$material->unit}).");
            }

            // Decrement material stock
            $material->decrement('stock_qty', $totalUsed);

            // Create a stock movement log entry
            StockMovement::create([
                'raw_material_id' => $material->id,
                'order_item_id'   => $orderItem->id,
                'type'            => 'out',
                'qty'             => $totalUsed,
                'cost_per_unit'   => $material->cost_per_unit,
                'total_cost'      => $totalUsed * $material->cost_per_unit,
                'note'            => 'Penjualan #' . optional($orderItem->order)->order_number,
                'created_by'      => Auth::id() ?? optional($orderItem->order)->user_id ?? 1,
                'created_at'      => now(),
            ]);

            // Check if stock has fallen below minimum threshold (broadcast after commit to prevent phantom alerts on rollback)
            $freshMaterial = $material->fresh();
            if ($freshMaterial->stock_qty <= $freshMaterial->min_stock_qty) {
                \Illuminate\Support\Facades\DB::afterCommit(function () use ($freshMaterial) {
                    event(new LowStockAlert($freshMaterial));
                });
            }
        }
    }

    /**
     * Restore raw materials to inventory when an order is voided/cancelled.
     *
     * Locks materials in strict ascending ID order to prevent deadlocks with concurrent transactions.
     *
     * @param Order $order
     * @param string $reason
     * @return void
     */
    public function restoreOrderStock(Order $order, string $reason = ''): void
    {
        $order->loadMissing(['orderItems.product.recipes.rawMaterial']);

        // Aggregate required restorations by raw_material_id
        $restorations = [];
        foreach ($order->orderItems as $orderItem) {
            $size = $orderItem->size ?? 'all';

            $recipes = $orderItem->product->recipes()
                ->where(function ($query) use ($size) {
                    $query->where('size_variant', $size)
                          ->orWhere('size_variant', 'all');
                })
                ->get();

            foreach ($recipes as $recipe) {
                $matId = (int) $recipe->raw_material_id;
                $qty = (float) $recipe->qty_used * (int) $orderItem->quantity;
                if (!isset($restorations[$matId])) {
                    $restorations[$matId] = [
                        'qty'           => 0.0,
                        'order_item_id' => $orderItem->id,
                    ];
                }
                $restorations[$matId]['qty'] += $qty;
            }
        }

        if (empty($restorations)) {
            return;
        }

        // Deterministic lock ordering: ascending by raw material ID
        ksort($restorations);
        $materialIds = array_keys($restorations);
        $lockedMaterials = RawMaterial::whereIn('id', $materialIds)
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $note = 'Void/Batal order #' . $order->order_number . ($reason !== '' ? " ({$reason})" : '');

        foreach ($restorations as $matId => $data) {
            $material = $lockedMaterials->get($matId);
            if (!$material) {
                continue;
            }

            $totalUsed = $data['qty'];

            // Restore stock
            $material->increment('stock_qty', $totalUsed);

            // Log movement
            StockMovement::create([
                'raw_material_id' => $material->id,
                'order_item_id'   => $data['order_item_id'],
                'type'            => 'in',
                'qty'             => $totalUsed,
                'cost_per_unit'   => $material->cost_per_unit,
                'total_cost'      => $totalUsed * $material->cost_per_unit,
                'note'            => $note,
                'created_by'      => Auth::id() ?? $order->user_id ?? 1,
                'created_at'      => now(),
            ]);
        }
    }
}
