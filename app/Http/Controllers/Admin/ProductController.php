<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants'])->orderBy('name')->get();

        return view('admin.products.index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        return view('admin.products.create', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:64', 'unique:products,barcode'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'has_variants' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'variants.S' => ['required_if:has_variants,1', 'numeric', 'min:0'],
            'variants.M' => ['required_if:has_variants,1', 'numeric', 'min:0'],
            'variants.L' => ['required_if:has_variants,1', 'numeric', 'min:0'],
        ]);

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'barcode' => $validated['barcode'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'has_variants' => $request->boolean('has_variants'),
            'has_recipe' => false,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->boolean('has_variants')) {
            foreach (['S', 'M', 'L'] as $size) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'price' => $validated['variants'][$size],
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', [
            'product' => $product->load('variants'),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:64', 'unique:products,barcode,' . $product->id],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'has_variants' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'variants.S' => ['required_if:has_variants,1', 'numeric', 'min:0'],
            'variants.M' => ['required_if:has_variants,1', 'numeric', 'min:0'],
            'variants.L' => ['required_if:has_variants,1', 'numeric', 'min:0'],
        ]);

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'barcode' => $validated['barcode'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'has_variants' => $request->boolean('has_variants'),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->boolean('has_variants')) {
            foreach (['S', 'M', 'L'] as $size) {
                ProductVariant::updateOrCreate(
                    ['product_id' => $product->id, 'size' => $size],
                    [
                        'price' => $validated['variants'][$size],
                        'is_active' => true,
                    ]
                );
            }
        } else {
            $product->variants()->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
