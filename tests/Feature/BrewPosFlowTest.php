<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RawMaterial;
use App\Models\User;
use App\Models\CashierSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BrewPosFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_must_open_session_before_accessing_pos()
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->get('/pos');
        $response->assertRedirect(route('cashier.session.open'));
    }

    public function test_cashier_can_open_session_and_access_pos()
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->post(route('cashier.session.store'), [
            'opening_balance' => 500000,
            'notes' => 'Shift Pagi',
        ]);

        $response->assertRedirect(route('pos'));
        $this->assertDatabaseHas('cashier_sessions', [
            'user_id' => $cashier->id,
            'opening_balance' => 500000,
            'status' => 'open',
        ]);

        $posResponse = $this->actingAs($cashier)->get('/pos');
        $posResponse->assertStatus(200);
    }

    public function test_pos_checkout_creates_order_and_updates_kds()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Espresso Hot',
            'price' => 20000,
            'is_active' => true,
        ]);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 300000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 20000,
                    'quantity' => 2,
                    'size' => 'M',
                ]
            ])
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 50000)
            ->call('createOrder');

        $this->assertDatabaseHas('orders', [
            'cashier_session_id' => $session->id,
            'total' => 40000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);
    }

    public function test_barista_can_update_kds_status()
    {
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $order = \App\Models\Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-TEST1234',
            'subtotal' => 30000,
            'discount' => 0,
            'tax' => 0,
            'total' => 30000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $component = Livewire::actingAs($barista)
            ->test(\App\Http\Livewire\Kds\KdsBoard::class);
            
        $component->call('updateStatus', $order->id, 'processing');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);

        $component->call('updateStatus', $order->id, 'ready');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'ready',
        ]);

        $component->call('updateStatus', $order->id, 'completed');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    public function test_cash_payment_fails_if_cash_paid_is_zero_or_insufficient()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Americano',
            'price' => 25000,
            'is_active' => true,
        ]);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 200000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        // Attempt checkout with cashPaid = 0 when total is 25000
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 25000,
                    'quantity' => 1,
                    'size' => 'M',
                ]
            ])
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 0)
            ->call('createOrder')
            ->assertHasErrors(['cashPaid']);

        $this->assertDatabaseMissing('orders', [
            'cashier_session_id' => $session->id,
            'total' => 25000,
        ]);

        // Attempt checkout with insufficient cash: cashPaid = 15000 when total is 25000
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 25000,
                    'quantity' => 1,
                    'size' => 'M',
                ]
            ])
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 15000)
            ->call('createOrder')
            ->assertHasErrors(['cashPaid']);

        $this->assertDatabaseMissing('orders', [
            'cashier_session_id' => $session->id,
            'total' => 25000,
        ]);
    }

    public function test_checkout_fails_and_does_not_deduct_when_stock_is_insufficient()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Caramel Latte',
            'price' => 30000,
            'is_active' => true,
            'has_recipe' => true,
        ]);

        $material = RawMaterial::create([
            'name' => 'Biji Kopi Arabica',
            'category' => 'Biji Kopi',
            'unit' => 'gram',
            'cost_per_unit' => 200,
            'stock_qty' => 10, // only 10 grams in stock
            'min_stock_qty' => 50,
            'is_active' => true,
        ]);

        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $material->id,
            'qty_used' => 20, // requires 20 grams per cup
            'size_variant' => 'all',
        ]);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 200000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        // Attempt checkout when 20g required but only 10g available
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 30000,
                    'quantity' => 1,
                    'size' => 'M',
                ]
            ])
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 50000)
            ->call('createOrder')
            ->assertHasErrors(['cart']);

        // Assert order was NOT created and stock was NOT decremented into negative
        $this->assertDatabaseMissing('orders', [
            'cashier_session_id' => $session->id,
        ]);
        $this->assertEquals(10, $material->fresh()->stock_qty);
    }

    public function test_order_can_be_voided_and_restores_inventory()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cappuccino',
            'price' => 28000,
            'is_active' => true,
            'has_recipe' => true,
        ]);

        $material = RawMaterial::create([
            'name' => 'Susu Segar',
            'category' => 'Dairy',
            'unit' => 'ml',
            'cost_per_unit' => 20,
            'stock_qty' => 500,
            'min_stock_qty' => 100,
            'is_active' => true,
        ]);

        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $material->id,
            'qty_used' => 150,
            'size_variant' => 'all',
        ]);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        // Checkout 2 cups -> uses 300ml -> stock becomes 200ml
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 28000,
                    'quantity' => 2,
                    'size' => 'M',
                ]
            ])
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 60000)
            ->call('createOrder');

        $this->assertEquals(200, $material->fresh()->stock_qty);

        $order = \App\Models\Order::where('cashier_session_id', $session->id)->firstOrFail();
        $this->assertEquals('paid', $order->status);

        // Void the order
        $order->void('Pelanggan membatalkan pesanan');

        // Verify status cancelled and inventory restored back to 500ml
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(500, $material->fresh()->stock_qty);

        // Verify stock movement log has type 'in' with reason
        $this->assertDatabaseHas('stock_movements', [
            'raw_material_id' => $material->id,
            'type' => 'in',
            'qty' => 300,
            'note' => 'Void/Batal order #' . $order->order_number . ' (Pelanggan membatalkan pesanan)',
        ]);
    }

    public function test_kds_status_update_requires_authentication_and_authorization()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $order = \App\Models\Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-KDS111',
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 0,
            'total' => 20000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        // 1. Unauthenticated request must fail with 401
        Livewire::test(\App\Http\Livewire\Kds\KdsBoard::class)
            ->call('updateStatus', $order->id, 'processing')
            ->assertStatus(401);

        // 2. Cashier role must fail with 403 (only barista or admin allowed)
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Kds\KdsBoard::class)
            ->call('updateStatus', $order->id, 'processing')
            ->assertStatus(403);

        // 3. Invalid status input must fail validation
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);
        Livewire::actingAs($barista)
            ->test(\App\Http\Livewire\Kds\KdsBoard::class)
            ->call('updateStatus', $order->id, 'invalid_status_name')
            ->assertHasErrors(['status']);
    }

    public function test_deactivated_employees_cannot_login()
    {
        $deactivatedCashier = User::factory()->create([
            'email' => 'inactive@brewpos.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'cashier',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@brewpos.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $activeCashier = User::factory()->create([
            'email' => 'active@brewpos.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $successResponse = $this->post('/login', [
            'email' => 'active@brewpos.com',
            'password' => 'password123',
        ]);

        $successResponse->assertRedirect('/pos');
        $this->assertAuthenticatedAs($activeCashier);
    }

    public function test_hpp_and_profit_margin_reporting_formulas_are_accurate()
    {
        $category = Category::create(['name' => 'Signature', 'is_active' => true]);

        // Create product with variants: S = 20.000, M = 25.000, L = 30.000
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Signature Latte',
            'price' => 20000,
            'has_variants' => true,
            'has_recipe' => true,
            'is_active' => true,
        ]);

        \App\Models\ProductVariant::create(['product_id' => $product->id, 'size' => 'S', 'price' => 20000, 'is_active' => true]);
        \App\Models\ProductVariant::create(['product_id' => $product->id, 'size' => 'M', 'price' => 25000, 'is_active' => true]);
        \App\Models\ProductVariant::create(['product_id' => $product->id, 'size' => 'L', 'price' => 30000, 'is_active' => true]);

        // Base ingredient ('all'): Coffee beans = 18g @ Rp 200/g = Rp 3.600
        $beans = RawMaterial::create([
            'name' => 'Espresso Beans',
            'category' => 'Coffee',
            'unit' => 'gram',
            'cost_per_unit' => 200,
            'stock_qty' => 1000,
            'min_stock_qty' => 100,
            'is_active' => true,
        ]);
        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $beans->id,
            'qty_used' => 18,
            'size_variant' => 'all',
        ]);

        // Size-specific ingredient: Milk for M = 150ml @ Rp 20/ml = Rp 3.000
        $milk = RawMaterial::create([
            'name' => 'Fresh Milk',
            'category' => 'Dairy',
            'unit' => 'ml',
            'cost_per_unit' => 20,
            'stock_qty' => 5000,
            'min_stock_qty' => 500,
            'is_active' => true,
        ]);
        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $milk->id,
            'qty_used' => 150,
            'size_variant' => 'M',
        ]);

        $reportService = app(\App\Services\ReportService::class);
        $hppMarginRows = $reportService->getHppAndMargin();

        // For M: HPP = 3.600 (beans) + 3.000 (milk) = 6.600
        // Sale Price = 25.000
        // Margin Rp = 25.000 - 6.600 = 18.400
        // Margin % = (18.400 / 25.000) * 100 = 73.6%
        $rowM = $hppMarginRows->firstWhere('size_variant', 'M');
        $this->assertNotNull($rowM);
        $this->assertEquals(25000, $rowM->sale_price);
        $this->assertEquals(6600, $rowM->hpp);
        $this->assertEquals(18400, $rowM->margin_rp);
        $this->assertEquals(73.6, $rowM->margin_pct);

        // For S: HPP = 3.600 (beans only)
        // Sale Price = 20.000
        // Margin Rp = 20.000 - 3.600 = 16.400
        // Margin % = (16.400 / 20.000) * 100 = 82%
        $rowS = $hppMarginRows->firstWhere('size_variant', 'S');
        $this->assertNotNull($rowS);
        $this->assertEquals(20000, $rowS->sale_price);
        $this->assertEquals(3600, $rowS->hpp);
        $this->assertEquals(16400, $rowS->margin_rp);
        $this->assertEquals(82.0, $rowS->margin_pct);
    }

    public function test_checkout_transaction_is_atomic_and_rolls_back_on_failure()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Atomic Test Latte',
            'price' => 30000,
            'is_active' => true,
            'has_recipe' => true,
        ]);

        $material = RawMaterial::create([
            'name' => 'Atomic Test Milk',
            'category' => 'Dairy',
            'unit' => 'ml',
            'cost_per_unit' => 25,
            'stock_qty' => 500,
            'min_stock_qty' => 50,
            'is_active' => true,
        ]);

        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $material->id,
            'qty_used' => 150,
            'size_variant' => 'all',
        ]);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $initialOrdersCount = \App\Models\Order::count();
        $initialOrderItemsCount = \App\Models\OrderItem::count();
        $initialMovementsCount = \App\Models\StockMovement::count();

        // Cart with item 1 valid, but item 2 non-existent product_id -> foreign key constraint violation
        $cart = [
            [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 30000,
                'quantity' => 1,
                'size' => 'M',
            ],
            [
                'product_id' => 999999, // Non-existent foreign key
                'name' => 'Invalid Product',
                'price' => 30000,
                'quantity' => 1,
                'size' => 'M',
            ],
        ];

        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', $cart)
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 100000)
            ->call('createOrder')
            ->assertHasErrors(['cart']);

        // Assert transaction rolled back completely:
        // 1. Zero orders persisted
        $this->assertEquals($initialOrdersCount, \App\Models\Order::count());
        // 2. Zero order items persisted
        $this->assertEquals($initialOrderItemsCount, \App\Models\OrderItem::count());
        // 3. Raw material stock deduction rolled back (remains 500ml)
        $this->assertEquals(500, $material->fresh()->stock_qty);
        // 4. Stock movement log entries rolled back
        $this->assertEquals($initialMovementsCount, \App\Models\StockMovement::count());
    }

    public function test_cashier_can_view_active_session_order_history()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);

        // 1. Without active session, visiting /cashier/orders redirects to open session
        $this->actingAs($cashier)
            ->get(route('cashier.orders.index'))
            ->assertRedirect(route('cashier.session.open'));

        // 2. Open session and create an order
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 50000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $order = \App\Models\Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-HIST001',
            'subtotal' => 35000,
            'discount' => 0,
            'tax' => 0,
            'total' => 35000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        // 3. Visiting /cashier/orders displays order history
        $response = $this->actingAs($cashier)->get(route('cashier.orders.index'));
        $response->assertStatus(200);
        $response->assertSee('TRX-HIST001');
        $response->assertSee('Rp 35.000');
    }

    public function test_cashier_cannot_access_receipt_or_void_another_session_order_idor_protection()
    {
        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Caramel Macchiato',
            'price' => 20000,
            'is_active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'price' => 25000,
            'is_active' => true,
        ]);

        $cashier1 = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session1 = CashierSession::create([
            'user_id' => $cashier1->id,
            'opening_balance' => 50000,
            'opened_at' => now(),
            'status' => 'open',
        ]);
        $order1 = \App\Models\Order::create([
            'cashier_session_id' => $session1->id,
            'user_id' => $cashier1->id,
            'order_number' => 'TRX-OWNER01',
            'subtotal' => 25000,
            'discount' => 0,
            'tax' => 0,
            'total' => 25000,
            'payment_method' => 'cash',
            'cash_received' => 30000,
            'change_given' => 5000,
            'status' => 'paid',
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'size' => 'L',
            'price' => 25000,
            'subtotal' => 25000,
            'notes' => 'Extra caramel sauce',
        ]);

        $cashier2 = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session2 = CashierSession::create([
            'user_id' => $cashier2->id,
            'opening_balance' => 50000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Cashier 1 can access own receipt (with items & variant attached - prevents F-REC-01 regression)
        $this->actingAs($cashier1)
            ->get(route('cashier.receipt', $order1))
            ->assertStatus(200)
            ->assertSee('TRX-OWNER01')
            ->assertSee('Caramel Macchiato')
            ->assertSee('Extra caramel sauce');

        // Cashier 2 CANNOT access Cashier 1's receipt (IDOR protection) -> 403 Forbidden
        $this->actingAs($cashier2)
            ->get(route('cashier.receipt', $order1))
            ->assertStatus(403);

        // Cashier 2 CANNOT void Cashier 1's order -> 403 Forbidden
        $this->actingAs($cashier2)
            ->post(route('cashier.orders.void', $order1), ['reason' => 'Unauthorized void attempt'])
            ->assertStatus(403);

        // Order remains paid
        $this->assertEquals('paid', $order1->fresh()->status);

        // Admin CAN access receipt
        $this->actingAs($admin)
            ->get(route('cashier.receipt', $order1))
            ->assertStatus(200)
            ->assertSee('Caramel Macchiato');
    }

    public function test_kds_retains_completed_orders_within_two_minutes()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 50000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        // Order 1: Completed 30 seconds ago (< 2 minutes) -> Should be visible with fading badge
        $orderRecentCompleted = \App\Models\Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-KDS-RECENT',
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 0,
            'total' => 20000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);
        $orderRecentCompleted->timestamps = false;
        $orderRecentCompleted->updated_at = now()->subSeconds(30);
        $orderRecentCompleted->save();

        // Order 2: Completed 5 minutes ago (> 2 minutes) -> Should be purged from KDS
        $orderOldCompleted = \App\Models\Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-KDS-OLD',
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 0,
            'total' => 20000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);
        $orderOldCompleted->timestamps = false;
        $orderOldCompleted->updated_at = now()->subMinutes(5);
        $orderOldCompleted->save();

        // Order 3: Active order (processing) -> Should be visible
        $orderActive = \App\Models\Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-KDS-ACTIVE',
            'subtotal' => 25000,
            'discount' => 0,
            'tax' => 0,
            'total' => 25000,
            'payment_method' => 'cash',
            'status' => 'processing',
        ]);

        Livewire::actingAs($barista)
            ->test(\App\Http\Livewire\Kds\KdsBoard::class)
            ->assertSee('TRX-KDS-RECENT')
            ->assertSee('Pesanan Selesai (Fading 2m)')
            ->assertSee('TRX-KDS-ACTIVE')
            ->assertDontSee('TRX-KDS-OLD');
    }

    public function test_checkout_is_protected_against_double_submit_and_events_dispatch_after_commit()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Double Click Latte',
            'price' => 20000,
            'is_active' => true,
        ]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 50000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $material = RawMaterial::create([
            'name' => 'Test Coffee',
            'category' => 'Coffee',
            'unit' => 'gram',
            'cost_per_unit' => 200,
            'stock_qty' => 100,
            'min_stock_qty' => 50,
            'is_active' => true,
        ]);
        $order = \App\Models\Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-EVT01',
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 0,
            'total' => 20000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        // 1. Verify LowStockAlert and OrderPlaced implement ShouldDispatchAfterCommit (F-CHK-02)
        $this->assertInstanceOf(\Illuminate\Contracts\Events\ShouldDispatchAfterCommit::class, new \App\Events\LowStockAlert($material));
        $this->assertInstanceOf(\Illuminate\Contracts\Events\ShouldDispatchAfterCommit::class, new \App\Events\OrderPlaced($order));

        // 2. Verify in-flight double submit protection (F-CHK-01)
        $cart = [
            [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 20000,
                'quantity' => 1,
                'size' => 'M',
            ]
        ];

        $initialOrdersCount = \App\Models\Order::count();

        // When isProcessing is true, createOrder() drops the duplicate request immediately
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', $cart)
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 20000)
            ->set('isProcessing', true)
            ->call('createOrder');

        // Order count must not change
        $this->assertEquals($initialOrdersCount, \App\Models\Order::count());
    }

    public function test_raw_material_cannot_be_saved_with_negative_stock()
    {
        $this->expectException(\InvalidArgumentException::class);

        RawMaterial::create([
            'name' => 'Kopi Robusta',
            'category' => 'Biji Kopi',
            'unit' => 'gram',
            'cost_per_unit' => 150,
            'stock_qty' => -10, // Invalid negative stock
            'min_stock_qty' => 50,
            'is_active' => true,
        ]);
    }

    public function test_raw_material_decrement_cannot_reduce_stock_below_zero()
    {
        $material = RawMaterial::create([
            'name' => 'Susu Segar',
            'category' => 'Susu',
            'unit' => 'ml',
            'cost_per_unit' => 20,
            'stock_qty' => 50,
            'min_stock_qty' => 10,
            'is_active' => true,
        ]);

        $this->expectException(\RuntimeException::class);

        // Attempting to decrement 100 when only 50 available
        $material->decrement('stock_qty', 100);
    }

    public function test_pos_catalog_displays_accurate_stock_status_for_recipes()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);

        $productAvailable = Product::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu Mantap',
            'price' => 25000,
            'has_recipe' => true,
            'is_active' => true,
        ]);

        $productSoldOut = Product::create([
            'category_id' => $category->id,
            'name' => 'Matcha Latte',
            'price' => 28000,
            'has_recipe' => true,
            'is_active' => true,
        ]);

        $materialAmple = RawMaterial::create([
            'name' => 'Espresso Roast',
            'category' => 'Biji Kopi',
            'unit' => 'gram',
            'cost_per_unit' => 150,
            'stock_qty' => 500,
            'min_stock_qty' => 50,
            'is_active' => true,
        ]);

        $materialEmpty = RawMaterial::create([
            'name' => 'Matcha Powder',
            'category' => 'Bubuk',
            'unit' => 'gram',
            'cost_per_unit' => 500,
            'stock_qty' => 0, // 0 stock
            'min_stock_qty' => 50,
            'is_active' => true,
        ]);

        \App\Models\ProductRecipe::create([
            'product_id' => $productAvailable->id,
            'raw_material_id' => $materialAmple->id,
            'qty_used' => 20,
            'size_variant' => 'all',
        ]);

        \App\Models\ProductRecipe::create([
            'product_id' => $productSoldOut->id,
            'raw_material_id' => $materialEmpty->id,
            'qty_used' => 15,
            'size_variant' => 'all',
        ]);

        // Model helper checks
        $this->assertTrue($productAvailable->isAvailable('M'));
        $this->assertEquals('Tersedia', $productAvailable->getStockStatus('M'));

        $this->assertFalse($productSoldOut->isAvailable('M'));
        $this->assertEquals('Habis', $productSoldOut->getStockStatus('M'));

        // Blade view rendering check
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\PosInterface::class)
            ->assertSee('Kopi Susu Mantap')
            ->assertSee('Tersedia')
            ->assertSee('Matcha Latte')
            ->assertSee('Habis');
    }

    public function test_pos_interface_prevents_adding_out_of_stock_item_to_cart()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Minuman', 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Avocado Coffee',
            'price' => 32000,
            'has_recipe' => true,
            'is_active' => true,
        ]);

        $material = RawMaterial::create([
            'name' => 'Alpukat Frozen',
            'category' => 'Buah',
            'unit' => 'gram',
            'cost_per_unit' => 100,
            'stock_qty' => 50, // only 50g in stock
            'min_stock_qty' => 20,
            'is_active' => true,
        ]);

        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $material->id,
            'qty_used' => 50, // 50g per cup -> only 1 cup can be made!
            'size_variant' => 'all',
        ]);

        $component = Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\PosInterface::class);

        // First cup should succeed (uses 50g, exactly matches stock)
        $component->call('addToCart', $product->id, 'M');
        $this->assertCount(1, $component->get('cart'));
        $this->assertEquals(1, $component->get('cart')[$product->id . '_M']['quantity']);

        // Second cup should FAIL (would require 100g, exceeds 50g stock)
        $component->call('addToCart', $product->id, 'M');
        $this->assertEquals(1, $component->get('cart')[$product->id . '_M']['quantity']);

        // Direct updateQty to 5 should also FAIL
        $component->call('updateQty', $product->id . '_M', 5);
        $this->assertEquals(1, $component->get('cart')[$product->id . '_M']['quantity']);
    }

    public function test_checkout_is_protected_against_concurrent_duplicate_requests_via_session_lock()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Americano',
            'price' => 20000,
            'is_active' => true,
        ]);
        $material = RawMaterial::create([
            'name' => 'Biji Kopi Arabica',
            'category' => 'Beans',
            'unit' => 'gram',
            'cost_per_unit' => 100,
            'stock_qty' => 100,
            'min_stock_qty' => 10,
            'is_active' => true,
        ]);
        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $material->id,
            'qty_used' => 15,
            'size_variant' => 'all',
        ]);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $cart = [
            [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 20000,
                'quantity' => 1,
                'size' => 'M',
            ]
        ];

        // Simulate an in-flight checkout acquiring the atomic cache lock
        $lock = \Illuminate\Support\Facades\Cache::lock("checkout_session_{$session->id}", 10);
        $this->assertTrue($lock->get());

        // A concurrent checkout request arrives on the same session
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', $cart)
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 20000)
            ->call('createOrder')
            ->assertHasErrors(['cart' => 'Transaksi sedang diproses. Mohon tunggu sejenak.']);

        // Assert no order was created while locked
        $this->assertEquals(0, \App\Models\Order::count());
        $this->assertEquals(100, $material->fresh()->stock_qty);

        // Release lock
        $lock->release();

        // Subsequent legitimate checkout succeeds
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', $cart)
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 20000)
            ->call('createOrder')
            ->assertHasNoErrors();

        $this->assertEquals(1, \App\Models\Order::count());
        $this->assertEquals(85, $material->fresh()->stock_qty);
    }

    public function test_concurrent_order_void_is_idempotent_and_prevents_duplicate_stock_restoration()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cold Brew',
            'price' => 25000,
            'is_active' => true,
        ]);
        $material = RawMaterial::create([
            'name' => 'Cold Brew Blend',
            'category' => 'Beans',
            'unit' => 'gram',
            'cost_per_unit' => 200,
            'stock_qty' => 100,
            'min_stock_qty' => 10,
            'is_active' => true,
        ]);
        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $material->id,
            'qty_used' => 20,
            'size_variant' => 'all',
        ]);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        // Place order
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 25000,
                    'quantity' => 1,
                    'size' => 'M',
                ]
            ])
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 50000)
            ->call('createOrder');

        // Stock depleted from 100 to 80
        $this->assertEquals(80, $material->fresh()->stock_qty);
        $order = \App\Models\Order::first();

        // First void call succeeds and restores stock to 100
        $order->void('Permintaan pembatalan');
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(100, $material->fresh()->stock_qty);

        // Second concurrent void call fails and MUST NOT double-restore stock to 120
        $this->expectException(\DomainException::class);
        $order->void('Permintaan pembatalan ganda');

        // Verify stock remained at 100 and only 1 restock movement exists
        $this->assertEquals(100, $material->fresh()->stock_qty);
        $this->assertEquals(1, \App\Models\StockMovement::where('order_item_id', '!=', null)->where('type', 'in')->count());
    }

    public function test_kds_cannot_update_status_on_cancelled_order_under_concurrency()
    {
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $order = \App\Models\Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-CANCELTEST',
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 0,
            'total' => 20000,
            'payment_method' => 'cash',
            'cash_received' => 20000,
            'change_given' => 0,
            'status' => 'cancelled',
        ]);

        Livewire::actingAs($barista)
            ->test(\App\Http\Livewire\Kds\KdsBoard::class)
            ->call('updateStatus', $order->id, 'completed')
            ->assertHasErrors(['status']);

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_competing_checkouts_exhausting_stock_serializes_and_aborts_excess_order_cleanly()
    {
        $cashier1 = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $cashier2 = User::factory()->create(['role' => 'cashier', 'is_active' => true]);

        $session1 = CashierSession::create([
            'user_id' => $cashier1->id,
            'opening_balance' => 50000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $session2 = CashierSession::create([
            'user_id' => $cashier2->id,
            'opening_balance' => 50000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $category = Category::create(['name' => 'Signature', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Special Geisha',
            'price' => 50000,
            'is_active' => true,
        ]);

        // Only 15 grams in stock! Each cup uses 15 grams. Exactly 1 cup can be sold.
        $material = RawMaterial::create([
            'name' => 'Geisha Beans',
            'category' => 'Specialty',
            'unit' => 'gram',
            'cost_per_unit' => 1000,
            'stock_qty' => 15,
            'min_stock_qty' => 5,
            'is_active' => true,
        ]);

        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $material->id,
            'qty_used' => 15,
            'size_variant' => 'all',
        ]);

        $cart = [
            [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 50000,
                'quantity' => 1,
                'size' => 'M',
            ]
        ];

        // Cashier 1 processes checkout first
        Livewire::actingAs($cashier1)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', $cart)
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 50000)
            ->call('createOrder')
            ->assertHasNoErrors();

        $this->assertEquals(0, $material->fresh()->stock_qty);
        $this->assertEquals(1, \App\Models\Order::count());

        // Cashier 2 attempts checkout on the same bean concurrently
        Livewire::actingAs($cashier2)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', $cart)
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 50000)
            ->call('createOrder')
            ->assertHasErrors(['cart']);

        // Stock never becomes negative, stays at 0, second order is rolled back
        $this->assertEquals(0, $material->fresh()->stock_qty);
        $this->assertEquals(1, \App\Models\Order::count());
    }

    public function test_order_void_with_pessimistic_lock_prevents_race_condition_and_kds_cannot_overwrite_voided_order()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $category = Category::create(['name' => 'Signature', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Caramel Macchiato',
            'price' => 35000,
            'is_active' => true,
        ]);

        $material = RawMaterial::create([
            'name' => 'Espresso Blend',
            'category' => 'Beans',
            'unit' => 'gram',
            'cost_per_unit' => 200,
            'stock_qty' => 100,
            'min_stock_qty' => 10,
            'is_active' => true,
        ]);

        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $material->id,
            'qty_used' => 20,
            'size_variant' => 'all',
        ]);

        // 1. Order is placed and processed
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 35000,
                    'quantity' => 1,
                    'size' => 'M',
                ]
            ])
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 50000)
            ->call('createOrder')
            ->assertHasNoErrors();

        $this->assertEquals(80, $material->fresh()->stock_qty);
        $order = \App\Models\Order::first();

        // 2. Void is executed with pessimistic lock (F-CNC-01)
        $order->void('Alasan pembatalan resmi');
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(100, $material->fresh()->stock_qty);

        // 3. Concurrent KDS status update cannot overwrite cancelled order (F-CNC-02)
        Livewire::actingAs($barista)
            ->test(\App\Http\Livewire\Kds\KdsBoard::class)
            ->call('updateStatus', $order->id, 'completed')
            ->assertHasErrors(['status']);

        // Assert order status remains cancelled and stock was not manipulated
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(100, $material->fresh()->stock_qty);

        // 4. Simultaneous second void request throws DomainException without duplicate stock restoration
        $this->expectException(\DomainException::class);
        $order->void('Mencoba void kedua kali');
    }

    public function test_cashier_can_void_order_via_http_route_and_restores_inventory()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $category = Category::create(['name' => 'Kopi', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Latte Hot',
            'price' => 26000,
            'is_active' => true,
        ]);
        $material = RawMaterial::create([
            'name' => 'Biji Kopi House Blend',
            'category' => 'Biji Kopi',
            'unit' => 'gram',
            'cost_per_unit' => 120,
            'stock_qty' => 300,
            'min_stock_qty' => 50,
            'is_active' => true,
        ]);
        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $material->id,
            'qty_used' => 18,
            'size_variant' => 'all',
        ]);

        // Place order (18g used -> stock drops to 282g)
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 26000,
                    'quantity' => 1,
                    'size' => 'M',
                ]
            ])
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 30000)
            ->call('createOrder');

        $this->assertEquals(282, $material->fresh()->stock_qty);
        $order = \App\Models\Order::first();

        // Post void HTTP request
        $response = $this->actingAs($cashier)->post(route('cashier.orders.void', $order), [
            'reason' => 'Customer changed mind',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify order is cancelled and stock restored to 300g
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(300, $material->fresh()->stock_qty);

        // Verify stock movement log
        $this->assertDatabaseHas('stock_movements', [
            'raw_material_id' => $material->id,
            'type' => 'in',
            'qty' => 18,
            'note' => 'Void/Batal order #' . $order->order_number . ' (Customer changed mind)',
        ]);
    }

    public function test_admin_can_void_order_via_admin_portal_and_restores_inventory()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $category = Category::create(['name' => 'Tea', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Earl Grey Milk Tea',
            'price' => 24000,
            'is_active' => true,
        ]);
        $tea = RawMaterial::create([
            'name' => 'Earl Grey Leaves',
            'category' => 'Teh',
            'unit' => 'gram',
            'cost_per_unit' => 200,
            'stock_qty' => 100,
            'min_stock_qty' => 10,
            'is_active' => true,
        ]);
        \App\Models\ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $tea->id,
            'qty_used' => 10,
            'size_variant' => 'all',
        ]);

        // Place order (uses 10g -> stock drops to 90g)
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 24000,
                    'quantity' => 1,
                    'size' => 'M',
                ]
            ])
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 50000)
            ->call('createOrder');

        $this->assertEquals(90, $tea->fresh()->stock_qty);
        $order = \App\Models\Order::first();

        // Admin voids the order
        $response = $this->actingAs($admin)->post(route('cashier.orders.void', $order), [
            'reason' => 'Admin cancellation audit',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert order cancelled and inventory restored back to 100g
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(100, $tea->fresh()->stock_qty);
    }

    public function test_multi_item_order_void_restores_all_recipe_raw_materials_proportionally()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $category = Category::create(['name' => 'Coffee & Tea', 'is_active' => true]);
        $prodCoffee = Product::create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'price' => 20000,
            'is_active' => true,
        ]);
        $prodLatte = Product::create([
            'category_id' => $category->id,
            'name' => 'Vanilla Latte',
            'price' => 30000,
            'is_active' => true,
        ]);

        $beans = RawMaterial::create([
            'name' => 'Robusta Blend',
            'category' => 'Biji Kopi',
            'unit' => 'gram',
            'cost_per_unit' => 100,
            'stock_qty' => 500,
            'min_stock_qty' => 50,
            'is_active' => true,
        ]);
        $milk = RawMaterial::create([
            'name' => 'Fresh Milk Diamond',
            'category' => 'Susu',
            'unit' => 'ml',
            'cost_per_unit' => 25,
            'stock_qty' => 1000,
            'min_stock_qty' => 100,
            'is_active' => true,
        ]);
        $syrup = RawMaterial::create([
            'name' => 'Vanilla Syrup',
            'category' => 'Sirup',
            'unit' => 'ml',
            'cost_per_unit' => 50,
            'stock_qty' => 200,
            'min_stock_qty' => 20,
            'is_active' => true,
        ]);

        // Espresso: 18g beans
        \App\Models\ProductRecipe::create([
            'product_id' => $prodCoffee->id,
            'raw_material_id' => $beans->id,
            'qty_used' => 18,
            'size_variant' => 'all',
        ]);

        // Vanilla Latte: 18g beans, 150ml milk, 20ml syrup
        \App\Models\ProductRecipe::create([
            'product_id' => $prodLatte->id,
            'raw_material_id' => $beans->id,
            'qty_used' => 18,
            'size_variant' => 'all',
        ]);
        \App\Models\ProductRecipe::create([
            'product_id' => $prodLatte->id,
            'raw_material_id' => $milk->id,
            'qty_used' => 150,
            'size_variant' => 'all',
        ]);
        \App\Models\ProductRecipe::create([
            'product_id' => $prodLatte->id,
            'raw_material_id' => $syrup->id,
            'qty_used' => 20,
            'size_variant' => 'all',
        ]);

        // Order 2 Espressos (36g beans) + 1 Vanilla Latte (18g beans, 150ml milk, 20ml syrup)
        // Total deduction: Beans = 54g, Milk = 150ml, Syrup = 20ml
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', [
                [
                    'product_id' => $prodCoffee->id,
                    'name' => $prodCoffee->name,
                    'price' => 20000,
                    'quantity' => 2,
                    'size' => 'M',
                ],
                [
                    'product_id' => $prodLatte->id,
                    'name' => $prodLatte->name,
                    'price' => 30000,
                    'quantity' => 1,
                    'size' => 'M',
                ],
            ])
            ->set('paymentMethod', 'cash')
            ->set('cashPaid', 100000)
            ->call('createOrder');

        // Check decrements
        $this->assertEquals(446, $beans->fresh()->stock_qty); // 500 - 54
        $this->assertEquals(850, $milk->fresh()->stock_qty);   // 1000 - 150
        $this->assertEquals(180, $syrup->fresh()->stock_qty);  // 200 - 20

        $order = \App\Models\Order::first();

        // Void the multi-item order
        $order->void('Kesalahan transaksi kasir');

        // Assert all materials are restored back to exact original levels
        $this->assertEquals(500, $beans->fresh()->stock_qty);
        $this->assertEquals(1000, $milk->fresh()->stock_qty);
        $this->assertEquals(200, $syrup->fresh()->stock_qty);

        // Assert stock movements were created for all three materials
        $this->assertEquals(1, \App\Models\StockMovement::where('raw_material_id', $beans->id)->where('type', 'in')->count());
        $this->assertEquals(1, \App\Models\StockMovement::where('raw_material_id', $milk->id)->where('type', 'in')->count());
        $this->assertEquals(1, \App\Models\StockMovement::where('raw_material_id', $syrup->id)->where('type', 'in')->count());
    }

    public function test_admin_orders_page_renders_void_button_and_confirmation_modal()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $order = \App\Models\Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-UI-TEST',
            'subtotal' => 25000,
            'discount' => 0,
            'tax' => 0,
            'total' => 25000,
            'payment_method' => 'cash',
            'cash_received' => 25000,
            'change_given' => 0,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('TRX-UI-TEST');
        $response->assertSee('Konfirmasi Void Order');
        $response->assertSee('Batalkan Pesanan (Void)');
    }

    public function test_admin_can_create_and_update_product_with_unique_barcode()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = Category::create(['name' => 'Snack', 'is_active' => true]);

        // 1. Create product with barcode
        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Croissant Almond',
            'barcode' => '899123456001',
            'price' => 30000,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Croissant Almond',
            'barcode' => '899123456001',
        ]);

        $product = Product::where('barcode', '899123456001')->firstOrFail();

        // 2. Barcode uniqueness check
        $duplicateResponse = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Croissant Butter',
            'barcode' => '899123456001',
            'price' => 25000,
            'is_active' => 1,
        ]);
        $duplicateResponse->assertSessionHasErrors(['barcode']);

        // 3. Update product barcode
        $updateResponse = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'name' => 'Croissant Almond Supreme',
            'barcode' => '899123456002',
            'price' => 32000,
            'is_active' => 1,
        ]);
        $updateResponse->assertRedirect(route('admin.products.index'));
        $this->assertEquals('899123456002', $product->fresh()->barcode);
    }

    public function test_pos_can_search_and_scan_product_by_barcode()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $category = Category::create(['name' => 'Minuman Kemasan', 'is_active' => true]);

        $product1 = Product::create([
            'category_id' => $category->id,
            'name' => 'Cold Brew Bottle',
            'barcode' => '8999900001',
            'price' => 25000,
            'is_active' => true,
        ]);

        $product2 = Product::create([
            'category_id' => $category->id,
            'name' => 'Sparkling Water',
            'barcode' => '8999900002',
            'price' => 15000,
            'is_active' => true,
        ]);

        // 1. Search by barcode filters product catalog
        Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\PosInterface::class)
            ->set('search', '8999900001')
            ->assertSee('Cold Brew Bottle')
            ->assertDontSee('Sparkling Water');

        // 2. Scan barcode adds directly to cart and clears search
        $posTest = Livewire::actingAs($cashier)
            ->test(\App\Http\Livewire\Pos\PosInterface::class)
            ->set('search', '8999900002')
            ->call('scanBarcode')
            ->assertSet('search', '');

        $cart = $posTest->get('cart');
        $this->assertCount(1, $cart);
        $this->assertArrayHasKey($product2->id . '_M', $cart);
        $this->assertEquals('Sparkling Water', $cart[$product2->id . '_M']['name']);
        $this->assertEquals(1, $cart[$product2->id . '_M']['quantity']);

        // 3. Scan unknown barcode sets warning and does not alter cart
        $posTest->set('search', '9999999999999')
            ->call('scanBarcode');

        $this->assertCount(1, $posTest->get('cart'));
    }

    public function test_receipt_renders_correctly_with_order_items_and_variants_without_500_crash()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $category = Category::create(['name' => 'Signature', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Signature Aren Latte',
            'price' => 28000,
            'is_active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'price' => 33000,
            'is_active' => true,
        ]);

        $order = \App\Models\Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-RCPT-TEST',
            'subtotal' => 61000,
            'discount' => 5000,
            'tax' => 5600,
            'total' => 61600,
            'payment_method' => 'cash',
            'cash_received' => 100000,
            'change_given' => 38400,
            'status' => 'paid',
        ]);

        // Item 1 with variant
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'size' => 'L',
            'price' => 33000,
            'subtotal' => 33000,
            'notes' => 'Less ice, normal sugar',
        ]);

        // Item 2 without variant
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'quantity' => 1,
            'size' => 'M',
            'price' => 28000,
            'subtotal' => 28000,
            'notes' => null,
        ]);

        // Execute GET request on receipt endpoint
        $response = $this->actingAs($cashier)->get(route('cashier.receipt', $order));

        // Must succeed with 200 OK (no 500 RelationNotFoundException)
        $response->assertStatus(200);
        $response->assertSee('BREWPOS COFFEE');
        $response->assertSee('TRX-RCPT-TEST');
        $response->assertSee('Signature Aren Latte');
        $response->assertSee('Less ice, normal sugar');
        $response->assertSee('Rp 61.600');
        $response->assertSee('Rp 100.000');
        $response->assertSee('Rp 38.400');
        $response->assertDontSee('PESANAN TELAH DIBATALKAN');

        // Cancel order and verify void badge is rendered
        $order->update(['status' => 'cancelled']);
        $cancelledResponse = $this->actingAs($cashier)->get(route('cashier.receipt', $order));
        $cancelledResponse->assertStatus(200);
        $cancelledResponse->assertSee('PESANAN TELAH DIBATALKAN (VOID)');
    }

    public function test_admin_can_restock_raw_material_and_updates_cost_and_records_movement()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $material = RawMaterial::create([
            'name' => 'Biji Robusta Lampung',
            'category' => 'Biji Kopi',
            'unit' => 'gram',
            'cost_per_unit' => 120,
            'stock_qty' => 100,
            'min_stock_qty' => 50,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.restocks.store'), [
            'raw_material_id' => $material->id,
            'qty_in' => 50,
            'cost_per_unit' => 135,
            'supplier' => 'CV Kopi Nusantara',
            'note' => 'Restock rutin batch pagi',
        ]);

        $response->assertRedirect(route('admin.restocks.create'));
        $response->assertSessionHas('success');

        $fresh = $material->fresh();
        $this->assertEquals(150, $fresh->stock_qty);
        $this->assertEquals(135, $fresh->cost_per_unit);

        $this->assertDatabaseHas('stock_movements', [
            'raw_material_id' => $material->id,
            'type' => 'in',
            'qty' => 50,
            'cost_per_unit' => 135,
            'total_cost' => 6750,
            'note' => 'CV Kopi Nusantara | Restock rutin batch pagi',
        ]);
    }

    public function test_admin_can_adjust_stock_manually_with_reason_and_records_movement()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $material = RawMaterial::create([
            'name' => 'Fresh Milk Diamond',
            'category' => 'Susu',
            'unit' => 'ml',
            'cost_per_unit' => 25,
            'stock_qty' => 1000,
            'min_stock_qty' => 200,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.raw-materials.adjust', $material), [
            'adjusted_qty' => 850,
            'reason' => 'Susu tumpah di area bar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $fresh = $material->fresh();
        $this->assertEquals(850, $fresh->stock_qty);

        $this->assertDatabaseHas('stock_movements', [
            'raw_material_id' => $material->id,
            'type' => 'adjust',
            'qty' => 150,
            'cost_per_unit' => 25,
            'total_cost' => 3750,
        ]);
    }

    public function test_stock_movements_audit_log_is_viewable_and_filterable_by_admin()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);

        $material = RawMaterial::create([
            'name' => 'Syrup Hazelnut Monin',
            'category' => 'Sirup',
            'unit' => 'ml',
            'cost_per_unit' => 200,
            'stock_qty' => 500,
            'min_stock_qty' => 100,
            'is_active' => true,
        ]);

        // Create one 'in' and one 'adjust' movement
        \App\Models\StockMovement::create([
            'raw_material_id' => $material->id,
            'type' => 'in',
            'qty' => 250,
            'cost_per_unit' => 200,
            'total_cost' => 50000,
            'note' => 'Restock Monin Official',
            'created_by' => $admin->id,
            'created_at' => now(),
        ]);

        \App\Models\StockMovement::create([
            'raw_material_id' => $material->id,
            'type' => 'adjust',
            'qty' => 30,
            'cost_per_unit' => 200,
            'total_cost' => 6000,
            'note' => 'Koreksi botol pecah',
            'created_by' => $admin->id,
            'created_at' => now(),
        ]);

        // Admin can access movements audit log
        $response = $this->actingAs($admin)->get(route('admin.inventory.movements'));
        $response->assertStatus(200);
        $response->assertSee('Audit Trail Mutasi Stok');
        $response->assertSee('Syrup Hazelnut Monin');
        $response->assertSee('Restock Monin Official');
        $response->assertSee('Koreksi botol pecah');

        // Filter by type=in
        $filterIn = $this->actingAs($admin)->get(route('admin.inventory.movements', ['type' => 'in']));
        $filterIn->assertStatus(200);
        $filterIn->assertSee('Restock Monin Official');
        $filterIn->assertDontSee('Koreksi botol pecah');

        // Cashier cannot access admin stock movements audit log -> 403 Forbidden
        $this->actingAs($cashier)->get(route('admin.inventory.movements'))->assertStatus(403);
    }

    public function test_low_stock_status_and_dashboard_alerts_reflect_accurately()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $material = RawMaterial::create([
            'name' => 'Caramel Sauce Torani',
            'category' => 'Saus',
            'unit' => 'ml',
            'cost_per_unit' => 150,
            'stock_qty' => 150,
            'min_stock_qty' => 100, // 150 is exactly 1.5 * 100 -> 'Hampir Habis'
            'is_active' => true,
        ]);

        $this->assertEquals('Hampir Habis', $material->getStockStatus());

        // Drop stock to 80 (below 100 min_stock_qty -> 'Kritis')
        $material->update(['stock_qty' => 80]);
        $this->assertEquals('Kritis', $material->fresh()->getStockStatus());

        // Admin dashboard reflects critical item in banner and table
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Peringatan Bahan Baku Kritis');
        $response->assertSee('Caramel Sauce Torani');
        $response->assertSee('80.00 ml');
    }

    public function test_new_paid_order_appears_on_kds_board_with_items_and_aging_tag()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 150000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $category = Category::create(['name' => 'Signature Coffee', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Vanilla Velvet Latte',
            'price' => 32000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-KDS-9901',
            'subtotal' => 64000,
            'discount' => 0,
            'tax' => 0,
            'total' => 64000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'size' => 'L',
            'sugar_level' => '50%',
            'quantity' => 2,
            'price' => 32000,
            'subtotal' => 64000,
            'notes' => 'Less ice, oat milk substitute',
        ]);

        Livewire::actingAs($barista)
            ->test(\App\Http\Livewire\Kds\KdsBoard::class)
            ->assertSee('TRX-KDS-9901')
            ->assertSee('Vanilla Velvet Latte')
            ->assertSee('x2')
            ->assertSee('Ukuran: L')
            ->assertSee('Gula: 50%')
            ->assertSee('Less ice, oat milk substitute')
            ->assertSee('🆕 BARU')
            ->assertSee('Mulai Racik Pesanan');
    }

    public function test_kds_lifecycle_full_transitions_and_fading_retention()
    {
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $order = Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-KDS-LIFECYCLE',
            'subtotal' => 25000,
            'discount' => 0,
            'tax' => 0,
            'total' => 25000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $component = Livewire::actingAs($barista)
            ->test(\App\Http\Livewire\Kds\KdsBoard::class)
            ->assertSee('🆕 BARU')
            ->assertSee('Mulai Racik Pesanan');

        // Transition: paid -> processing (KDS-02)
        $component->call('updateStatus', $order->id, 'processing')
            ->assertSee('⚡ RACIK')
            ->assertSee('Tandai Siap Disajikan');
        $this->assertEquals('processing', $order->fresh()->status);

        // Transition: processing -> ready (KDS-03)
        $component->call('updateStatus', $order->id, 'ready')
            ->assertSee('✓ SIAP')
            ->assertSee('Selesai / Arsipkan');
        $this->assertEquals('ready', $order->fresh()->status);

        // Transition: ready -> completed (KDS-04 & SRS KDS-05)
        $component->call('updateStatus', $order->id, 'completed')
            ->assertSee('✓ SELESAI')
            ->assertSee('Pesanan Selesai (Fading 2m)');
        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_admin_and_barista_can_access_kds_view_route_while_cashier_and_guest_cannot()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);

        // Barista can access KDS
        $this->actingAs($barista)->get(route('kds'))->assertStatus(200);

        // Admin can access KDS
        $this->actingAs($admin)->get(route('kds'))->assertStatus(200);

        // Cashier forbidden -> 403
        $this->actingAs($cashier)->get(route('kds'))->assertStatus(403);

        // Logout and verify guest is redirected to login -> 302
        auth()->logout();
        $this->get(route('kds'))->assertRedirect(route('login'));
    }

    public function test_kds_queue_summary_stats_reflect_accurately()
    {
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        // 1 Paid
        Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-STATS-1',
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 0,
            'total' => 20000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        // 1 Processing
        Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-STATS-2',
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 0,
            'total' => 20000,
            'payment_method' => 'cash',
            'status' => 'processing',
        ]);

        // 2 Ready
        Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-STATS-3',
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 0,
            'total' => 20000,
            'payment_method' => 'cash',
            'status' => 'ready',
        ]);
        Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-STATS-4',
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 0,
            'total' => 20000,
            'payment_method' => 'cash',
            'status' => 'ready',
        ]);

        $component = Livewire::actingAs($barista)
            ->test(\App\Http\Livewire\Kds\KdsBoard::class)
            ->assertViewHas('stats')
            ->assertSee('Total Aktif:')
            ->assertSee('Baru:')
            ->assertSee('Diracik:')
            ->assertSee('Siap:');

        $stats = $component->viewData('stats');
        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(1, $stats['paid']);
        $this->assertEquals(1, $stats['processing']);
        $this->assertEquals(2, $stats['ready']);
    }

    public function test_admin_reports_sales_summary_and_top_products_exclude_cancelled_orders()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'opening_balance' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $category = Category::create(['name' => 'Signature', 'is_active' => true]);
        $prodA = Product::create(['category_id' => $category->id, 'name' => 'Kopi Gula Aren', 'price' => 20000, 'is_active' => true]);
        $prodB = Product::create(['category_id' => $category->id, 'name' => 'Avocado Latte', 'price' => 30000, 'is_active' => true]);

        // Order 1: Paid -> 40.000 (2x prodA)
        $order1 = Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-REP-01',
            'subtotal' => 40000,
            'discount' => 0,
            'tax' => 0,
            'total' => 40000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);
        \App\Models\OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $prodA->id,
            'quantity' => 2,
            'price' => 20000,
            'subtotal' => 40000,
        ]);

        // Order 2: Completed -> 30.000 (1x prodB)
        $order2 = Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-REP-02',
            'subtotal' => 30000,
            'discount' => 0,
            'tax' => 0,
            'total' => 30000,
            'payment_method' => 'qris',
            'status' => 'completed',
        ]);
        \App\Models\OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $prodB->id,
            'quantity' => 1,
            'price' => 30000,
            'subtotal' => 30000,
        ]);

        // Order 3: Cancelled / Voided -> 50.000 (Must be excluded from sales & top products!)
        $order3 = Order::create([
            'cashier_session_id' => $session->id,
            'user_id' => $cashier->id,
            'order_number' => 'TRX-REP-03',
            'subtotal' => 50000,
            'discount' => 0,
            'tax' => 0,
            'total' => 50000,
            'payment_method' => 'cash',
            'status' => 'cancelled',
        ]);
        \App\Models\OrderItem::create([
            'order_id' => $order3->id,
            'product_id' => $prodA->id,
            'quantity' => 2,
            'price' => 25000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.sales', ['tab' => 'sales']));
        $response->assertStatus(200);

        // Revenue must be 40.000 + 30.000 = 70.000 (excluding cancelled 50.000)
        $response->assertSee('Rp 70.000');
        // Transaction count must be 2
        $response->assertSee('<p class="mt-4 text-3xl font-semibold text-slate-900">2</p>', false);
        // AOV must be 70.000 / 2 = 35.000
        $response->assertSee('Rp 35.000');

        // Top products table shows Kopi Gula Aren (2 terjual) and Avocado Latte (1 terjual)
        $response->assertSee('Kopi Gula Aren');
        $response->assertSee('Avocado Latte');
    }

    public function test_admin_reports_inventory_and_hpp_and_sessions_tabs_render_successfully()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Tab raw_material
        $responseRaw = $this->actingAs($admin)->get(route('admin.reports.sales', ['tab' => 'raw_material']));
        $responseRaw->assertStatus(200);
        $responseRaw->assertSee('Pemakaian Bahan Baku');

        // Tab hpp_margin
        $responseHpp = $this->actingAs($admin)->get(route('admin.reports.sales', ['tab' => 'hpp_margin']));
        $responseHpp->assertStatus(200);
        $responseHpp->assertSee('HPP & Margin per Menu', false);

        // Tab cashier_session
        $responseSession = $this->actingAs($admin)->get(route('admin.reports.sales', ['tab' => 'cashier_session']));
        $responseSession->assertStatus(200);
        $responseSession->assertSee('Rekap Sesi Kasir');
    }

    public function test_admin_can_export_sales_report_to_excel_and_pdf()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Excel export
        $responseExcel = $this->actingAs($admin)->get(route('admin.reports.sales.export', ['format' => 'excel']));
        $responseExcel->assertStatus(200);
        $responseExcel->assertHeader('content-disposition');
        $this->assertStringContainsString('sales-report.xlsx', $responseExcel->headers->get('content-disposition'));

        // PDF export
        $responsePdf = $this->actingAs($admin)->get(route('admin.reports.sales.export', ['format' => 'pdf']));
        $responsePdf->assertStatus(200);
        $this->assertStringContainsString('sales-report.pdf', $responsePdf->headers->get('content-disposition'));
    }

    public function test_admin_can_export_inventory_reports_to_excel_and_pdf_for_all_sections()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $sections = ['raw_material', 'hpp_margin', 'cashier_session'];
        foreach ($sections as $section) {
            // Excel
            $resExcel = $this->actingAs($admin)->get(route('admin.reports.inventory.export', [
                'format' => 'excel',
                'section' => $section,
            ]));
            $resExcel->assertStatus(200);
            $this->assertStringContainsString('.xlsx', $resExcel->headers->get('content-disposition'));

            // PDF
            $resPdf = $this->actingAs($admin)->get(route('admin.reports.inventory.export', [
                'format' => 'pdf',
                'section' => $section,
            ]));
            $resPdf->assertStatus(200);
            $this->assertStringContainsString('.pdf', $resPdf->headers->get('content-disposition'));
        }
    }

    public function test_non_admin_cannot_access_reports_or_exports()
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);

        // Cashier forbidden
        $this->actingAs($cashier)->get(route('admin.reports.sales'))->assertStatus(403);
        $this->actingAs($cashier)->get(route('admin.reports.sales.export'))->assertStatus(403);

        // Barista forbidden
        $this->actingAs($barista)->get(route('admin.reports.sales'))->assertStatus(403);
        $this->actingAs($barista)->get(route('admin.reports.sales.export'))->assertStatus(403);

        // Guest redirected to login
        auth()->logout();
        $this->get(route('admin.reports.sales'))->assertRedirect(route('login'));
        $this->get(route('admin.reports.sales.export'))->assertRedirect(route('login'));
    }
}





