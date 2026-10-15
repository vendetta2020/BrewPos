<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cashier_session_id',
        'user_id',
        'order_number',
        'table_number',
        'subtotal',
        'discount',
        'tax',
        'total',
        'payment_method',
        'cash_received',
        'change_given',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'tax'           => 'decimal:2',
        'total'         => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_given'  => 'decimal:2',
    ];

    /**
     * Get the cashier session this order belongs to.
     */
    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class);
    }

    /**
     * Get the cashier (user) who processed the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items that belong to this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Alias for items relationship.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Void / cancel this order and restore its inventory.
     *
     * @param string $reason
     * @return void
     * @throws \DomainException
     */
    public function void(string $reason = ''): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($reason) {
            $order = Order::where('id', $this->id)->lockForUpdate()->firstOrFail();
            if ($order->status === 'cancelled') {
                throw new \DomainException("Order #{$order->order_number} sudah dibatalkan sebelumnya.");
            }

            // Restore inventory if order had deducted stock
            if (in_array($order->status, ['completed', 'processing', 'ready', 'paid'])) {
                app(\App\Services\InventoryDeductionService::class)->restoreOrderStock($order, $reason);
            }

            $order->update([
                'status' => 'cancelled',
            ]);

            $this->status = 'cancelled';
        }, 3);
    }
}
