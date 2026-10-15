<?php

namespace App\Events;

use App\Models\RawMaterial;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * Fired by InventoryDeductionService when a raw material's stock_qty
     * falls at or below its min_stock_qty threshold.
     *
     * Satisfies SRS: INV-07 — system sends notification to admin on low stock.
     * Broadcast channel: 'admin.notifications' (SDD section 5.2).
     *
     * @param  RawMaterial $rawMaterial  The material that triggered the alert.
     */
    public function __construct(
        public readonly RawMaterial $rawMaterial
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin.notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'low-stock-alert';
    }
}
