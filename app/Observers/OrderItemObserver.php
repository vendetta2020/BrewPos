<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Services\InventoryDeductionService;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     *
     * Only triggers stock deduction if the parent order is already 'completed'.
     * This is the standard path when checkout saves items after marking the order.
     *
     * Satisfies SRS: INV-05 — automatic stock deduction on each completed transaction.
     * Follows SDD: section 4.2 — OrderItemObserver pattern.
     */
    public function created(OrderItem $orderItem): void
    {
        if (in_array($orderItem->order->status, ['paid', 'completed'])) {
            app(InventoryDeductionService::class)->deductFromOrder($orderItem);
        }
    }
}
