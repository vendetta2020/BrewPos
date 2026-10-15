<?php

namespace App\Services;

use App\Models\CashierSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function makePeriod(array $filters): array
    {
        $period = $filters['period'] ?? 'today';
        $start = Carbon::now();
        $end = Carbon::now();

        switch ($period) {
            case 'this_week':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                break;
            case 'this_month':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
            case 'last_30_days':
                $start = Carbon::now()->subDays(30)->startOfDay();
                $end = Carbon::now()->endOfDay();
                break;
            case 'custom':
                $start = isset($filters['start']) ? Carbon::parse($filters['start'])->startOfDay() : Carbon::now()->startOfDay();
                $end = isset($filters['end']) ? Carbon::parse($filters['end'])->endOfDay() : Carbon::now()->endOfDay();
                break;
            default:
                $start = Carbon::now()->startOfDay();
                $end = Carbon::now()->endOfDay();
                break;
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        return ['start' => $start, 'end' => $end, 'period' => $period];
    }

    public function getSalesSummary(array $range): array
    {
        $orders = Order::whereIn('status', ['paid', 'processing', 'ready', 'completed'])
            ->whereBetween('created_at', [$range['start'], $range['end']]);

        $totalRevenue = (float) $orders->sum('total');
        $transactionCount = $orders->count();
        $averageOrderValue = $transactionCount ? $totalRevenue / $transactionCount : 0.0;

        return [
            'total_revenue' => $totalRevenue,
            'transaction_count' => $transactionCount,
            'average_order_value' => $averageOrderValue,
            'critical_stock_count' => RawMaterial::whereColumn('stock_qty', '<=', 'min_stock_qty')->count(),
        ];
    }

    public function getTopProducts(array $range, int $limit = 10): Collection
    {
        return OrderItem::query()
            ->select([
                'product_id',
                DB::raw('SUM(quantity) as quantity_sold'),
                DB::raw('SUM(subtotal) as revenue'),
            ])
            ->whereHas('order', function ($query) use ($range) {
                $query->whereIn('status', ['paid', 'processing', 'ready', 'completed'])
                    ->whereBetween('created_at', [$range['start'], $range['end']]);
            })
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('quantity_sold')
            ->limit($limit)
            ->get();
    }

    public function getRawMaterialUsage(array $range): Collection
    {
        $usage = StockMovement::query()
            ->select([
                'raw_material_id',
                DB::raw('SUM(qty) as qty_used'),
                DB::raw('SUM(total_cost) as total_cost'),
            ])
            ->where('type', 'out')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('raw_material_id')
            ->get();

        $materials = RawMaterial::whereIn('id', $usage->pluck('raw_material_id'))->get()->keyBy('id');

        return $usage->map(function ($item) use ($materials) {
            return (object) [
                'raw_material' => $materials->get($item->raw_material_id),
                'qty_used' => (float) $item->qty_used,
                'total_cost' => (float) $item->total_cost,
                'unit_cost' => $item->qty_used > 0 ? (float) $item->total_cost / (float) $item->qty_used : 0.0,
            ];
        });
    }

    public function getHppAndMargin(): Collection
    {
        $materialCosts = $this->getMaterialCostMap();
        $products = Product::with(['recipes.rawMaterial', 'variants'])->whereHas('recipes')->get();

        $rows = collect();

        foreach ($products as $product) {
            if ($product->has_variants && $product->variants->isNotEmpty()) {
                // For variant products, compute HPP & margin for each variant size
                foreach ($product->variants as $variant) {
                    $size = $variant->size;
                    $salePrice = (float) $variant->price;

                    // HPP includes size-specific ingredients PLUS ingredients applicable to 'all'
                    $relevantRecipes = $product->recipes->filter(function ($recipe) use ($size) {
                        return $recipe->size_variant === $size || $recipe->size_variant === 'all';
                    });

                    $hpp = $relevantRecipes->sum(function ($recipe) use ($materialCosts) {
                        $cost = $materialCosts->get($recipe->raw_material_id);
                        $unitCost = $cost !== null ? (float) $cost : (float) (optional($recipe->rawMaterial)->cost_per_unit ?? 0);
                        return (float) $recipe->qty_used * $unitCost;
                    });

                    $marginRp = $salePrice - $hpp;
                    $marginPct = $salePrice > 0 ? ($marginRp / $salePrice) * 100 : 0.0;

                    $rows->push((object) [
                        'product_name' => $product->name,
                        'size_variant' => $size,
                        'sale_price'   => $salePrice,
                        'hpp'          => (float) $hpp,
                        'margin_rp'    => (float) $marginRp,
                        'margin_pct'   => (float) $marginPct,
                    ]);
                }
            } else {
                // For non-variant products, compute HPP for base product
                $salePrice = (float) $product->price;

                $relevantRecipes = $product->recipes->filter(function ($recipe) {
                    return $recipe->size_variant === 'all' || empty($recipe->size_variant);
                });

                if ($relevantRecipes->isEmpty()) {
                    $relevantRecipes = $product->recipes;
                }

                $hpp = $relevantRecipes->sum(function ($recipe) use ($materialCosts) {
                    $cost = $materialCosts->get($recipe->raw_material_id);
                    $unitCost = $cost !== null ? (float) $cost : (float) (optional($recipe->rawMaterial)->cost_per_unit ?? 0);
                    return (float) $recipe->qty_used * $unitCost;
                });

                $marginRp = $salePrice - $hpp;
                $marginPct = $salePrice > 0 ? ($marginRp / $salePrice) * 100 : 0.0;

                $rows->push((object) [
                    'product_name' => $product->name,
                    'size_variant' => 'all',
                    'sale_price'   => $salePrice,
                    'hpp'          => (float) $hpp,
                    'margin_rp'    => (float) $marginRp,
                    'margin_pct'   => (float) $marginPct,
                ]);
            }
        }

        return $rows;
    }

    public function getCashierSessionSummary(array $range): Collection
    {
        return CashierSession::query()
            ->with('user')
            ->whereBetween('opened_at', [$range['start'], $range['end']])
            ->withCount(['orders as orders_count' => function ($query) use ($range) {
                $query->whereIn('status', ['paid', 'processing', 'ready', 'completed'])
                    ->whereBetween('created_at', [$range['start'], $range['end']]);
            }])
            ->withSum(['orders as total_sales' => function ($query) use ($range) {
                $query->whereIn('status', ['paid', 'processing', 'ready', 'completed'])
                    ->whereBetween('created_at', [$range['start'], $range['end']]);
            }], 'total')
            ->orderByDesc('opened_at')
            ->get()
            ->map(function ($session) {
                return (object) [
                    'id' => $session->id,
                    'user_name' => $session->user->name,
                    'opened_at' => $session->opened_at,
                    'closed_at' => $session->closed_at,
                    'status' => $session->status,
                    'opening_balance' => (float) $session->opening_balance,
                    'closing_balance' => (float) $session->closing_balance,
                    'orders_count' => $session->orders_count,
                    'total_sales' => (float) $session->total_sales,
                    'difference' => (float) $session->difference,
                ];
            });
    }

    private function getMaterialCostMap(): Collection
    {
        return StockMovement::query()
            ->where('type', 'in')
            ->orderByDesc('created_at')
            ->get()
            ->unique('raw_material_id')
            ->pluck('cost_per_unit', 'raw_material_id');
    }
}
