<?php

namespace App\Http\Controllers\Inventory;

use App\Events\LowStockAlert;
use App\Models\RawMaterial;
use App\Models\Category;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RawMaterialController extends Controller
{
    /**
     * Display a listing of all raw materials with stock status.
     *
     * Satisfies SRS: INV-01 — display bahan baku with nama, satuan, harga/unit, stok, stok minimum.
     * Satisfies SRS: INV-02 — show status: Aman, Hampir Habis, or Kritis.
     */
    public function index(Request $request)
    {
        $query = RawMaterial::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Paginate results
        $rawMaterials = $query->paginate(15);

        // Calculate summary counts
        $allMaterials = RawMaterial::all();
        $criticalCount = $allMaterials->filter(fn($m) => $m->getStockStatus() === 'Kritis')->count();
        $warningCount = $allMaterials->filter(fn($m) => $m->getStockStatus() === 'Hampir Habis')->count();

        // Calculate total inventory value
        $totalInventoryValue = $allMaterials->sum(fn($m) => $m->stock_qty * $m->cost_per_unit);

        // Get critical items for the card
        $criticalItems = $allMaterials
            ->filter(fn($m) => $m->getStockStatus() === 'Kritis')
            ->sortBy('stock_qty')
            ->take(5);

        // Get unique categories
        $categories = RawMaterial::distinct('category')->pluck('category');

        return view('inventory.raw-materials.index', [
            'rawMaterials' => $rawMaterials,
            'categories' => $categories,
            'criticalCount' => $criticalCount,
            'warningCount' => $warningCount,
            'totalInventoryValue' => $totalInventoryValue,
            'criticalItems' => $criticalItems,
        ]);
    }

    /**
     * Show the form for creating a new raw material.
     *
     * Satisfies SRS: INV-01 — form to add bahan baku.
     */
    public function create()
    {
        return view('admin.inventory.raw-materials-form');
    }

    /**
     * Store a newly created raw material in the database.
     *
     * Satisfies SRS: INV-01 — save bahan baku with all required fields.
     * Follows SDD: section 3 — raw_materials schema.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'category'       => ['required', 'string', 'max:255'],
            'unit'           => ['required', 'in:gram,ml,pcs,liter'],
            'cost_per_unit'  => ['required', 'numeric', 'min:0'],
            'stock_qty'      => ['required', 'numeric', 'min:0'],
            'min_stock_qty'  => ['required', 'numeric', 'min:0'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $material = RawMaterial::create($validated);

        if ($material->stock_qty > 0) {
            StockMovement::create([
                'raw_material_id' => $material->id,
                'order_item_id'   => null,
                'type'            => 'in',
                'qty'             => $material->stock_qty,
                'cost_per_unit'   => $material->cost_per_unit,
                'total_cost'      => $material->stock_qty * $material->cost_per_unit,
                'note'            => 'Stok awal bahan baku',
                'created_by'      => Auth::id() ?? 1,
                'created_at'      => now(),
            ]);
        }

        return redirect()
            ->route('admin.raw-materials.index')
            ->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a raw material.
     *
     * Satisfies SRS: INV-01 — edit bahan baku.
     */
    public function edit(RawMaterial $rawMaterial)
    {
        return view('admin.inventory.raw-materials-form', [
            'material' => $rawMaterial,
        ]);
    }

    /**
     * Update the specified raw material in the database.
     *
     * Satisfies SRS: INV-01 — update bahan baku fields.
     */
    public function update(Request $request, RawMaterial $rawMaterial)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'category'       => ['required', 'string', 'max:255'],
            'unit'           => ['required', 'in:gram,ml,pcs,liter'],
            'cost_per_unit'  => ['required', 'numeric', 'min:0'],
            'stock_qty'      => ['required', 'numeric', 'min:0'],
            'min_stock_qty'  => ['required', 'numeric', 'min:0'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $rawMaterial->update($validated);

        return redirect()
            ->route('admin.raw-materials.index')
            ->with('success', 'Bahan baku berhasil diperbarui.');
    }

    /**
     * Delete (soft delete) the specified raw material.
     *
     * Satisfies SRS: INV-01 — delete bahan baku (soft delete per SDD section 3).
     */
    public function destroy(RawMaterial $rawMaterial)
    {
        $rawMaterial->delete();

        return redirect()
            ->route('admin.raw-materials.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }

    /**
     * Adjust stock quantity manually with reason.
     *
     * Satisfies SRS: INV-09 — Koreksi stok manual.
     */
    public function adjust(Request $request, RawMaterial $rawMaterial)
    {
        $validated = $request->validate([
            'adjusted_qty' => ['required', 'numeric', 'min:0'],
            'reason'       => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($rawMaterial, $validated) {
            $material = RawMaterial::lockForUpdate()->findOrFail($rawMaterial->id);
            $newQty = (float) $validated['adjusted_qty'];
            $qtyChange = $newQty - (float) $material->stock_qty;

            $material->update(['stock_qty' => $newQty]);

            StockMovement::create([
                'raw_material_id' => $material->id,
                'order_item_id'   => null,
                'type'            => 'adjust',
                'qty'             => abs($qtyChange),
                'cost_per_unit'   => $material->cost_per_unit,
                'total_cost'      => abs($qtyChange) * $material->cost_per_unit,
                'note'            => 'Koreksi stok: ' . $validated['reason'] . ' (' . ($qtyChange >= 0 ? '+' : '') . number_format($qtyChange, 2) . ' ' . $material->unit . ')',
                'created_by'      => Auth::id() ?? 1,
                'created_at'      => now(),
            ]);

            // Dispatch LowStockAlert after commit if stock dropped below or at threshold
            $freshMaterial = $material->fresh();
            if ($freshMaterial->stock_qty <= $freshMaterial->min_stock_qty) {
                DB::afterCommit(function () use ($freshMaterial) {
                    event(new LowStockAlert($freshMaterial));
                });
            }
        }, 3);

        return redirect()
            ->back()
            ->with('success', 'Koreksi stok manual berhasil disimpan.');
    }

    /**
     * Display stock movement audit logs.
     *
     * Satisfies SRS: INV-06 — stock movements auditable.
     */
    public function movements(Request $request)
    {
        $query = StockMovement::with(['rawMaterial', 'creator', 'orderItem.order'])->latest('id');

        if ($request->filled('raw_material_id')) {
            $query->where('raw_material_id', $request->raw_material_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhereHas('rawMaterial', fn($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }

        $movements = $query->paginate(20);
        $materials = RawMaterial::orderBy('name')->get();

        return view('admin.inventory.movements', [
            'movements' => $movements,
            'materials' => $materials,
        ]);
    }
}
