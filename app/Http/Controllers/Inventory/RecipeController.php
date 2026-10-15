<?php

namespace App\Http\Controllers\Inventory;

use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RecipeController extends Controller
{
    /**
     * Display all products with their recipes listed.
     *
     * Satisfies SRS: INV-03 — show resep per produk.
     * Satisfies SRS: INV-05 — manage product recipes with ingredient selection.
     * Satisfies SRS: INV-06 — calculate cost of goods (HPP) per product.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'recipes.rawMaterial']);

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by recipe status
        if ($request->filled('filter')) {
            if ($request->filter === 'with-recipe') {
                $query->whereHas('recipes');
            } elseif ($request->filter === 'no-recipe') {
                $query->whereDoesntHave('recipes');
            }
        }

        $products = $query->get();

        // Calculate statistics
        $totalProducts = Product::count();
        $productsWithRecipe = Product::whereHas('recipes')->count();

        // Calculate average HPP
        $hppData = Product::with('recipes.rawMaterial')
            ->get()
            ->map(function ($product) {
                return $product->recipes()
                    ->join('raw_materials', 'product_recipes.raw_material_id', '=', 'raw_materials.id')
                    ->sum(\DB::raw('product_recipes.qty_used * raw_materials.cost_per_unit'));
            })
            ->filter(fn($hpp) => $hpp > 0);

        $averageHpp = $hppData->isNotEmpty() ? $hppData->avg() : 0;

        return view('inventory.recipes.index', [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'productsWithRecipe' => $productsWithRecipe,
            'averageHpp' => $averageHpp,
        ]);
    }

    /**
     * Show the form to create a new recipe.
     *
     * Satisfies SRS: INV-05 — add recipe for a product.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->get();
        $materials = RawMaterial::where('is_active', true)->get();

        return view('inventory.recipes.create', [
            'products' => $products,
            'materials' => $materials,
        ]);
    }

    /**
     * Show the form to edit/create recipes for a specific product.
     *
     * Satisfies SRS: INV-03 — define resep per produk: bahan + qty per sajian + varian ukuran.
     * Satisfies SRS: INV-04 — preview HPP dikalkulasi secara real-time.
     * Follows SDD: section 6 — Recipe Editor component design.
     */
    public function edit(Product $product)
    {
        $materials = RawMaterial::where('is_active', true)->get();
        $recipes = $product->recipes()->with('rawMaterial')->get();

        // Calculate HPP for each size variant
        $hpp = $this->calculateHpp($product);

        return view('inventory.recipes.edit', [
            'product'   => $product,
            'materials' => $materials,
            'recipes'   => $recipes,
            'hpp'       => $hpp,
        ]);
    }

    /**
     * Store recipes for a product.
     *
     * Satisfies SRS: INV-03 — define resep per produk with bahan dan qty.
     * Follows SDD: section 3 — product_recipes schema with unique constraint on
     * (product_id, raw_material_id, size_variant).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'recipes'   => ['required', 'array'],
            'recipes.*.raw_material_id'  => ['required', 'exists:raw_materials,id'],
            'recipes.*.qty_used'         => ['required', 'numeric', 'min:0.01'],
            'recipes.*.size_variant'     => ['nullable', 'string'],
            'recipes.*.notes'            => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Delete existing recipes for this product (will be recreated)
        $product->recipes()->delete();

        // Create new recipes
        foreach ($validated['recipes'] as $recipe) {
            ProductRecipe::create([
                'product_id'       => $product->id,
                'raw_material_id'  => $recipe['raw_material_id'],
                'qty_used'         => $recipe['qty_used'],
                'size_variant'     => $recipe['size_variant'] ?? null,
                'notes'            => $recipe['notes'] ?? null,
            ]);
        }

        // Mark product as having recipe
        $product->update(['has_recipe' => true]);

        return redirect()
            ->route('admin.recipes.index')
            ->with('success', 'Resep produk berhasil disimpan. HPP diperbarui.');
    }

    /**
     * Update recipes for a product.
     *
     * Satisfies SRS: INV-05 — edit recipe for a product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'recipes'   => ['required', 'array'],
            'recipes.*.raw_material_id'  => ['required', 'exists:raw_materials,id'],
            'recipes.*.qty_used'         => ['required', 'numeric', 'min:0.01'],
            'recipes.*.size_variant'     => ['nullable', 'string'],
            'recipes.*.notes'            => ['nullable', 'string', 'max:255'],
        ]);

        // Delete existing recipes for this product
        $product->recipes()->delete();

        // Create new recipes
        foreach ($validated['recipes'] as $recipe) {
            ProductRecipe::create([
                'product_id'       => $product->id,
                'raw_material_id'  => $recipe['raw_material_id'],
                'qty_used'         => $recipe['qty_used'],
                'size_variant'     => $recipe['size_variant'] ?? null,
                'notes'            => $recipe['notes'] ?? null,
            ]);
        }

        // Mark product as having recipe
        $product->update(['has_recipe' => true]);

        return redirect()
            ->route('admin.recipes.index')
            ->with('success', 'Resep produk berhasil diperbarui.');
    }

    /**
     * Remove recipes for a product.
     *
     * Satisfies SRS: INV-05 — delete recipe for a product.
     */
    public function destroy(Product $product)
    {
        $product->recipes()->delete();
        $product->update(['has_recipe' => false]);

        return redirect()
            ->route('admin.recipes.index')
            ->with('success', 'Resep produk berhasil dihapus.');
    }

    /**
     * Calculate HPP (Harga Pokok Produksi) for a product across all size variants.
     *
     * HPP = sum of (qty_used × cost_per_unit) for each raw material in recipe.
     *
     * @param Product $product
     * @return array  Keyed by size variant: ['S' => 1500, 'M' => 2000, 'L' => 2500]
     */
    private function calculateHpp(Product $product): array
    {
        $hpp = [];

        // Get all unique size variants for this product
        $sizes = $product->recipes()
            ->distinct()
            ->pluck('size_variant')
            ->unique();

        foreach ($sizes as $size) {
            $totalCost = $product->recipes()
                ->where(function ($q) use ($size) {
                    $q->where('size_variant', $size)
                      ->orWhere('size_variant', 'all');
                })
                ->with('rawMaterial')
                ->get()
                ->sum(function ($recipe) {
                    return $recipe->qty_used * $recipe->rawMaterial->cost_per_unit;
                });

            $hpp[$size] = $totalCost;
        }

        return $hpp;
    }
}
