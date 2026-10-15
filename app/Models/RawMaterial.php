<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawMaterial extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'category',
        'unit',
        'cost_per_unit',
        'stock_qty',
        'min_stock_qty',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cost_per_unit' => 'decimal:2',
        'stock_qty' => 'decimal:2',
        'min_stock_qty' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Bootstrap the model and its events.
     */
    protected static function booted(): void
    {
        static::saving(function (RawMaterial $material) {
            if (isset($material->stock_qty) && (float) $material->stock_qty < 0) {
                throw new \InvalidArgumentException("Stok bahan baku '{$material->name}' tidak boleh bernilai negatif (diberikan: {$material->stock_qty}).");
            }
            if (isset($material->min_stock_qty) && (float) $material->min_stock_qty < 0) {
                throw new \InvalidArgumentException("Ambang batas minimum stok '{$material->name}' tidak boleh bernilai negatif (diberikan: {$material->min_stock_qty}).");
            }
        });
    }

    /**
     * Decrement a column's value with strict non-negative stock guard.
     *
     * @param string $column
     * @param float|int $amount
     * @param array $extra
     * @return int
     * @throws \RuntimeException
     */
    public function decrement($column, $amount = 1, array $extra = [])
    {
        if ($column === 'stock_qty') {
            $freshStock = (float) $this->fresh()->stock_qty;
            if ($freshStock < (float) $amount) {
                throw new \RuntimeException("Stok bahan baku '{$this->name}' tidak mencukupi untuk dikurangi {$amount} {$this->unit} (tersedia: {$freshStock} {$this->unit}).");
            }
        }

        return parent::decrement($column, $amount, $extra);
    }

    /**
     * Get the recipes using this raw material.
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(ProductRecipe::class);
    }

    /**
     * Get the stock movements for this raw material.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Determine the stock status of this material.
     *
     * Returns one of: 'Aman' (safe), 'Hampir Habis' (low), 'Kritis' (critical)
     * 
     * Satisfies SRS: INV-02 — stock status indicator.
     *
     * @return string
     */
    public function getStockStatus(): string
    {
        // Kritis: at or below minimum threshold
        if ($this->stock_qty <= $this->min_stock_qty) {
            return 'Kritis';
        }

        // Hampir Habis: between minimum and 150% of minimum
        $warningThreshold = $this->min_stock_qty * 1.5;
        if ($this->stock_qty <= $warningThreshold) {
            return 'Hampir Habis';
        }

        // Aman: above 150% of minimum
        return 'Aman';
    }
}
