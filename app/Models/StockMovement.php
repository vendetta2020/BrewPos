<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    /**
     * Disable auto-managed updated_at — table only has created_at.
     * Satisfies SDD section 3: stock_movements schema.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'raw_material_id',
        'order_item_id',
        'type',
        'qty',
        'cost_per_unit',
        'total_cost',
        'note',
        'created_by',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'qty'           => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'total_cost'    => 'decimal:2',
        'created_at'    => 'datetime',
    ];

    /**
     * Get the raw material associated with this movement.
     */
    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    /**
     * Get the order item that triggered this movement (nullable for manual/restock).
     * Satisfies SRS: INV-06 — references order_item_id.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the user who created this movement entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
