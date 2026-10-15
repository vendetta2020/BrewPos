<?php

namespace App\Http\Controllers\Inventory;

use App\Models\RawMaterial;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RestockController extends Controller
{
    /**
     * Show the form for recording a restock transaction.
     *
     * Satisfies SRS: INV-08 — form to record restock with qty, harga beli, tanggal, supplier.
     */
    public function create()
    {
        $materials = RawMaterial::where('is_active', true)->get();

        return view('admin.inventory.restock-form', [
            'materials' => $materials,
        ]);
    }

    /**
     * Store a restock transaction in stock_movements table.
     *
     * Satisfies SRS: INV-08 — record restock dengan jumlah masuk, harga beli, tanggal, dan supplier.
     * Satisfies SRS: INV-06 — each stock movement logged with reference.
     * Follows SDD: section 3 — stock_movements schema with type='in'.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'raw_material_id'  => ['required', 'exists:raw_materials,id'],
            'qty_in'           => ['required', 'numeric', 'min:0.01'],
            'cost_per_unit'    => ['required', 'numeric', 'min:0'],
            'supplier'         => ['nullable', 'string', 'max:255'],
            'note'             => ['nullable', 'string', 'max:255'],
        ]);

        $qtyIn = (float) $validated['qty_in'];
        $costPerUnit = (float) $validated['cost_per_unit'];

        $material = DB::transaction(function () use ($validated, $qtyIn, $costPerUnit) {
            $material = RawMaterial::lockForUpdate()->findOrFail($validated['raw_material_id']);
            $totalCost = $qtyIn * $costPerUnit;

            // Update material stock and update cost_per_unit for accurate HPP
            $material->increment('stock_qty', $qtyIn);
            if ($costPerUnit > 0) {
                $material->update(['cost_per_unit' => $costPerUnit]);
            }

            $noteContent = trim(($validated['supplier'] ?? '') . (!empty($validated['note']) ? ' | ' . $validated['note'] : ''));

            // Log the movement
            StockMovement::create([
                'raw_material_id' => $material->id,
                'order_item_id'   => null,  // Restock is manual, not from order
                'type'            => 'in',
                'qty'             => $qtyIn,
                'cost_per_unit'   => $costPerUnit,
                'total_cost'      => $totalCost,
                'note'            => !empty($noteContent) ? $noteContent : 'Restock bahan baku',
                'created_by'      => Auth::id() ?? 1,
                'created_at'      => now(),
            ]);

            return $material;
        }, 3);

        return redirect()
            ->route('admin.restocks.create')
            ->with('success', "Restock {$material->name} sebanyak {$qtyIn} {$material->unit} berhasil dicatat.");
    }
}
