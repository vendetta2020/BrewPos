<?php

namespace Tests\Feature;

use App\Models\CashierSession;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\User;
use App\Services\QrisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosQrisApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected CashierSession $session;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'role'      => 'cashier',
            'is_active' => true,
        ]);

        $this->session = CashierSession::create([
            'user_id'         => $this->cashier->id,
            'opening_balance' => 100000,
            'opened_at'       => now(),
            'status'          => 'open',
        ]);

        $category = Category::create([
            'name' => 'Signature Coffee',
            'slug' => 'signature-coffee',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name'        => 'Espresso Romano',
            'price'       => 30000,
            'is_active'   => true,
            'has_recipe'  => false,
        ]);
    }

    public function test_api_generate_qris_returns_valid_emvco_payload_and_svg_image(): void
    {
        $response = $this->postJson('/api/pos/qris/generate', [
            'amount'       => 50000,
            'order_number' => 'TRX-TEST-001',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'QRIS generated successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'reference_no',
                    'order_number',
                    'amount',
                    'qr_string',
                    'qr_image',
                    'status',
                    'expires_at',
                ],
            ]);

        $data = $response->json('data');
        $this->assertStringStartsWith('QRIS-', $data['reference_no']);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $data['qr_image']);
        $this->assertEquals('pending', $data['status']);
        $this->assertEquals(50000, $data['amount']);
    }

    public function test_api_qris_status_returns_current_payment_state(): void
    {
        $service = app(QrisService::class);
        $trx = $service->createTransaction(45000, 'TRX-TEST-002');

        $response = $this->getJson("/api/pos/qris/{$trx['reference_no']}/status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'reference_no' => $trx['reference_no'],
                    'status'       => 'pending',
                    'amount'       => 45000,
                ],
            ]);
    }

    public function test_api_qris_callback_marks_transaction_and_order_as_paid(): void
    {
        // 1. Create a pending order
        $order = Order::create([
            'cashier_session_id' => $this->session->id,
            'user_id'            => $this->cashier->id,
            'order_number'       => 'TRX-TEST-CB01',
            'subtotal'           => 60000,
            'discount'           => 0,
            'tax'                => 0,
            'total'              => 60000,
            'payment_method'     => 'qris',
            'status'             => 'pending',
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $this->product->id,
            'quantity'   => 2,
            'price'      => 30000,
            'subtotal'   => 60000,
        ]);

        // 2. Generate QRIS for this order
        $service = app(QrisService::class);
        $trx = $service->createTransaction(60000, $order->order_number, $order->id);
        $order->update(['payment_reference' => $trx['reference_no']]);

        // 3. Post webhook / callback as external payment gateway would
        $response = $this->postJson('/api/pos/qris/callback', [
            'reference_no' => $trx['reference_no'],
            'status'       => 'settlement', // Common gateway status
            'amount'       => 60000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'reference_no' => $trx['reference_no'],
                    'status'       => 'paid',
                ],
            ]);

        // 4. Verify order in DB updated to paid
        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertEquals('qris', $order->payment_method);
        $this->assertEquals($trx['reference_no'], $order->payment_reference);
    }

    public function test_api_simulate_pay_marks_transaction_paid(): void
    {
        $service = app(QrisService::class);
        $trx = $service->createTransaction(25000);

        $response = $this->postJson('/api/pos/qris/simulate-pay', [
            'reference_no' => $trx['reference_no'],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'reference_no' => $trx['reference_no'],
                    'status'       => 'paid',
                ],
            ]);

        $updatedTrx = $service->getTransaction($trx['reference_no']);
        $this->assertEquals('paid', $updatedTrx['status']);
    }

    public function test_checkout_modal_generates_qris_and_completes_order(): void
    {
        $cart = [
            [
                'product_id' => $this->product->id,
                'name'       => $this->product->name,
                'price'      => 30000,
                'quantity'   => 1,
                'size'       => 'M',
            ],
        ];

        $component = Livewire::actingAs($this->cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', $cart)
            ->set('paymentMethod', 'qris')
            ->assertSet('qrisStatus', 'pending');

        $this->assertNotEmpty($component->get('qrisImage'));
        $this->assertNotEmpty($component->get('qrisReference'));

        $component->call('simulateQrisPayment')
            ->assertSet('qrisStatus', 'paid')
            ->call('createOrder')
            ->assertDispatched('clearCart')
            ->assertDispatched('checkout:success');

        $this->assertDatabaseHas('orders', [
            'cashier_session_id' => $this->session->id,
            'payment_method'     => 'qris',
            'total'              => 30000,
            'status'             => 'paid',
        ]);
    }

    public function test_checkout_modal_accumulates_cash_presets_and_calculates_change(): void
    {
        $cart = [
            [
                'product_id' => $this->product->id,
                'name'       => $this->product->name,
                'price'      => 30000,
                'quantity'   => 1,
                'size'       => 'M',
            ],
        ];

        $component = Livewire::actingAs($this->cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', $cart)
            ->set('paymentMethod', 'cash');

        // Initial state
        $this->assertNull($component->get('cashPaid'));
        $this->assertEquals(0, $component->get('change'));

        // Click +50.000 -> cashPaid becomes 50000, change is 20000
        $component->call('addCashAmount', 50000)
            ->assertSet('cashPaid', 50000)
            ->assertSet('change', 20000);

        // Click +200.000 -> cashPaid accumulates to 250000, change is 220000
        $component->call('addCashAmount', 200000)
            ->assertSet('cashPaid', 250000)
            ->assertSet('change', 220000);

        // Click +10.000 -> accumulates to 260000
        $component->call('addCashAmount', 10000)
            ->assertSet('cashPaid', 260000)
            ->assertSet('change', 230000);

        // Click +20.000 -> accumulates to 280000
        $component->call('addCashAmount', 20000)
            ->assertSet('cashPaid', 280000)
            ->assertSet('change', 250000);

        // Clear cash -> cashPaid becomes null, change 0
        $component->call('clearCash')
            ->assertSet('cashPaid', null)
            ->assertSet('change', 0);

        // Set exact cash -> cashPaid becomes 30000, change 0
        $component->call('setExactCash')
            ->assertSet('cashPaid', 30000)
            ->assertSet('change', 0);
    }

    public function test_checkout_modal_prevents_order_creation_when_qris_is_not_paid(): void
    {
        $cart = [
            [
                'product_id' => $this->product->id,
                'name'       => $this->product->name,
                'price'      => 30000,
                'quantity'   => 1,
                'size'       => 'M',
            ],
        ];

        $component = Livewire::actingAs($this->cashier)
            ->test(\App\Http\Livewire\Pos\CheckoutModal::class)
            ->call('open', $cart)
            ->set('paymentMethod', 'qris')
            ->assertSet('qrisStatus', 'pending');

        $initialOrderCount = \App\Models\Order::count();

        // Attempting to finalize order without QRIS paid MUST fail and NOT dispatch success
        $component->call('createOrder')
            ->assertHasErrors(['qris'])
            ->assertNotDispatched('checkout:success');

        // Verify no order created
        $this->assertEquals($initialOrderCount, \App\Models\Order::count());

        // Now simulate payment, then createOrder MUST succeed
        $component->call('simulateQrisPayment')
            ->assertSet('qrisStatus', 'paid')
            ->call('createOrder')
            ->assertHasNoErrors()
            ->assertDispatched('checkout:success');

        $this->assertEquals($initialOrderCount + 1, \App\Models\Order::count());
    }
}
