<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'barcode',
        'description',
        'image',
        'price',
        'has_variants',
        'has_recipe',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'has_variants' => 'boolean',
        'has_recipe' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the recipes for the product.
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(ProductRecipe::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the price for a specific size.
     */
    public function priceForSize(string $size): float
    {
        if ($this->has_variants) {
            $variant = $this->variants()->where('size', $size)->first();
            if ($variant) {
                return (float) $variant->price;
            }
        }

        return (float) $this->price;
    }

    /**
     * Check if product ingredients are available for ordering.
     *
     * @param string $size
     * @param int $quantity
     * @return bool
     */
    public function isAvailable(string $size = 'all', int $quantity = 1): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->has_recipe) {
            return true;
        }

        $relevantRecipes = $this->recipes->filter(function ($recipe) use ($size) {
            return $recipe->size_variant === $size || $recipe->size_variant === 'all' || $size === 'all';
        });

        if ($relevantRecipes->isEmpty()) {
            return true;
        }

        foreach ($relevantRecipes as $recipe) {
            $material = $recipe->rawMaterial;
            if (! $material || ! $material->is_active) {
                return false;
            }

            $needed = (float) $recipe->qty_used * $quantity;
            if ((float) $material->stock_qty < $needed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine dynamic stock status for the product based on raw materials.
     *
     * @param string $size
     * @return string 'Tersedia' | 'Stok Menipis' | 'Habis'
     */
    public function getStockStatus(string $size = 'all'): string
    {
        if (! $this->is_active) {
            return 'Habis';
        }

        if (! $this->has_recipe) {
            return 'Tersedia';
        }

        $relevantRecipes = $this->recipes->filter(function ($recipe) use ($size) {
            return $recipe->size_variant === $size || $recipe->size_variant === 'all' || $size === 'all';
        });

        if ($relevantRecipes->isEmpty()) {
            return 'Tersedia';
        }

        $isLowStock = false;
        foreach ($relevantRecipes as $recipe) {
            $material = $recipe->rawMaterial;
            if (! $material || ! $material->is_active || (float) $material->stock_qty < (float) $recipe->qty_used) {
                return 'Habis';
            }

            if ((float) $material->stock_qty <= (float) $material->min_stock_qty) {
                $isLowStock = true;
            }
        }

        return $isLowStock ? 'Stok Menipis' : 'Tersedia';
    }
}
