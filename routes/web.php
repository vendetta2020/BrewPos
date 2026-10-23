<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Cashier\CashierSessionController;
use App\Http\Controllers\Inventory\RawMaterialController;
use App\Http\Controllers\Inventory\RecipeController;
use App\Http\Controllers\Inventory\RestockController;
use App\Http\Controllers\Reports\InventoryReportController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Reports\SalesController;

// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', function (\App\Services\ReportService $reportService) {
            $todaySales = \App\Models\Order::whereDate('created_at', today())->whereIn('status', ['paid', 'processing', 'ready', 'completed'])->sum('total');
            $todayCount = \App\Models\Order::whereDate('created_at', today())->whereIn('status', ['paid', 'processing', 'ready', 'completed'])->count();
            $avgOrder = $todayCount > 0 ? $todaySales / $todayCount : 0;

            $criticalMaterials = \App\Models\RawMaterial::whereRaw('stock_qty <= min_stock_qty')->get();
            $activeSessions = \App\Models\CashierSession::where('status', 'open')->with('user')->get();

            $topProducts = \App\Models\OrderItem::selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
                ->whereHas('order', fn($q) => $q->whereDate('created_at', today())->whereIn('status', ['paid', 'processing', 'ready', 'completed']))
                ->groupBy('product_id')
                ->orderByDesc('total_qty')
                ->take(5)
                ->with('product')
                ->get();

            return view('admin.dashboard', [
                'todaySales'        => $todaySales,
                'todayCount'        => $todayCount,
                'avgOrder'          => $avgOrder,
                'criticalMaterials' => $criticalMaterials,
                'activeSessions'    => $activeSessions,
                'topProducts'       => $topProducts,
            ]);
        })->name('dashboard');

        Route::prefix('admin')->name('admin.')->group(function () {
            // Raw Materials Management
            Route::resource('raw-materials', RawMaterialController::class, [
                'parameters' => ['raw_material' => 'rawMaterial']
            ]);
            Route::post('raw-materials/{rawMaterial}/adjust', [RawMaterialController::class, 'adjust'])->name('raw-materials.adjust');
            Route::get('inventory/movements', [RawMaterialController::class, 'movements'])->name('inventory.movements');

            // Recipe Management
            Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
            Route::get('/recipes/create', [RecipeController::class, 'create'])->name('recipes.create');
            Route::get('/recipes/{product}/edit', [RecipeController::class, 'edit'])->name('recipes.edit');
            Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');
            Route::post('/recipes/{product}', [RecipeController::class, 'store']);
            Route::put('/recipes/{product}', [RecipeController::class, 'update'])->name('recipes.update');
            Route::delete('/recipes/{product}', [RecipeController::class, 'destroy'])->name('recipes.destroy');

            // Restock Management
            Route::get('/restocks/create', [RestockController::class, 'create'])->name('restocks.create');
            Route::post('/restocks', [RestockController::class, 'store'])->name('restocks.store');

            // Resource routes for admin modules
            Route::resource('products', ProductController::class, ['names' => 'products']);
            Route::resource('categories', CategoryController::class, ['names' => 'categories']);
            Route::resource('users', UserController::class, ['names' => 'users']);
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.sales');
            Route::get('/reports/inventory', [ReportController::class, 'index'])->name('reports.inventory')->defaults('tab', 'raw_material');
            Route::get('/reports/export/sales', [SalesController::class, 'export'])->name('reports.sales.export');
            Route::get('/reports/export/inventory', [InventoryReportController::class, 'export'])->name('reports.inventory.export');
            Route::get('/orders', [PosController::class, 'adminOrders'])->name('orders.index');
        });
    });

    // Cashier Routes
    Route::middleware('role:cashier')->group(function () {
        Route::get('/cashier/session/open', [CashierSessionController::class, 'openForm'])->name('cashier.session.open');
        Route::post('/cashier/session/open', [CashierSessionController::class, 'storeSession'])->name('cashier.session.store');
        Route::get('/cashier/session/close', [CashierSessionController::class, 'closeForm'])->name('cashier.session.close');
        Route::post('/cashier/session/close', [CashierSessionController::class, 'updateSession'])->name('cashier.session.update');
        Route::get('/pos', [PosController::class, 'index'])->name('pos');
        Route::get('/cashier/orders', [PosController::class, 'orders'])->name('cashier.orders.index');
    });

    // Order Actions (Cashier & Admin)
    Route::middleware('role:cashier,admin')->group(function () {
        Route::get('/cashier/orders/{order}/receipt', [PosController::class, 'receipt'])->name('cashier.receipt');
        Route::post('/cashier/orders/{order}/void', [PosController::class, 'void'])->name('cashier.orders.void');
    });

    // Barista & Admin KDS Routes
    Route::middleware('role:barista,admin')->group(function () {
        Route::get('/kds', function () {
            return view('barista.kds');
        })->name('kds');
    });
});

