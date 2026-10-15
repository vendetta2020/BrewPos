<?php

namespace App\Http\Livewire\Pos;

use App\Events\OrderPlaced;
use App\Models\CashierSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\InventoryDeductionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class CheckoutModal extends Component
{
    public array $cart = [];
    public string $paymentMethod = 'cash';
    public ?float $cashPaid = null;
    public float $change = 0.0;
    public bool $isOpen = false;
    public bool $isProcessing = false;

    protected $listeners = [
        'openCheckout' => 'open',
    ];

    public function open(array $cart)
    {
        $this->cart = $cart;
        $this->isOpen = true;
        $this->cashPaid = null;
        $this->isProcessing = false;
        $this->recalculateChange();
    }

    public function updatedCashPaid(): void
    {
        $this->recalculateChange();
    }

    public function updatedPaymentMethod(): void
    {
        $this->recalculateChange();
    }

    private function recalculateChange(): void
    {
        $total = collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        $paid = (float) ($this->cashPaid ?? 0);
        $this->change = max(0, $paid - $total);
    }

    public function createOrder()
    {
        // F-CHK-01: In-flight execution guard against double-submits
        if ($this->isProcessing) {
            return;
        }

        if (empty($this->cart)) {
            $this->addError('cart', 'Keranjang belanja masih kosong.');
            return;
        }

        $this->isProcessing = true;
        $lockAcquired = false;

        try {
            $total = collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']);

            // 1. Strict validation: Cash payment cannot be finalized with zero or insufficient cash
            $rules = [
                'paymentMethod' => 'required|in:cash,qris,card',
            ];

            if ($this->paymentMethod === 'cash') {
                $rules['cashPaid'] = ['required', 'numeric', 'min:' . $total];
            } else {
                $rules['cashPaid'] = ['nullable', 'numeric', 'min:0'];
            }

            $this->validate($rules, [
                'paymentMethod.required' => 'Metode pembayaran wajib dipilih.',
                'paymentMethod.in'       => 'Metode pembayaran tidak valid.',
                'cashPaid.required'      => 'Jumlah uang tunai wajib diisi.',
                'cashPaid.numeric'       => 'Jumlah uang tunai harus berupa angka.',
                'cashPaid.min'           => 'Uang tunai yang dibayarkan kurang dari total tagihan (Rp ' . number_format($total, 0, ',', '.') . ').',
            ]);

            // 2. Ensure cashier session exists for current user
            $session = CashierSession::where('user_id', Auth::id())->where('status', 'open')->latest()->first();
            if (! $session) {
                session()->flash('warning', 'Sesi kasir telah berakhir. Silakan buka sesi baru.');
                return redirect()->route('cashier.session.open');
            }

            // Concurrency: Acquire atomic session lock to eliminate double-submit races
            $lock = Cache::lock("checkout_session_{$session->id}", 10);
            if (! $lock->get()) {
                $this->addError('cart', 'Transaksi sedang diproses. Mohon tunggu sejenak.');
                return;
            }
            $lockAcquired = true;

            // 3. Pre-validate stock availability to prevent negative stock (fail-fast check)
            try {
                app(InventoryDeductionService::class)->validateStockForCart($this->cart);
            } catch (\Throwable $e) {
                $this->addError('cart', $e->getMessage());
                return;
            }

            // 4. Atomic database transaction with automatic retry on deadlocks (3 attempts per F-CHK-03)
            try {
                $order = DB::transaction(function () use ($total, $session) {
                    // Re-lock cashier session to guarantee it is still open under concurrency
                    $lockedSession = CashierSession::where('id', $session->id)
                        ->where('status', 'open')
                        ->lockForUpdate()
                        ->first();

                    if (! $lockedSession) {
                        throw new \RuntimeException('Sesi kasir tidak valid atau telah ditutup.');
                    }

                    // Deterministic upfront row locking in ascending ID order prevents deadlocks
                    app(InventoryDeductionService::class)->validateAndLockStockForCart($this->cart);

                    $order = Order::create([
                        'cashier_session_id' => $lockedSession->id,
                        'user_id'            => Auth::id(),
                        'order_number'       => 'TRX-' . strtoupper(Str::random(8)),
                        'table_number'       => null,
                        'subtotal'           => $total,
                        'discount'           => 0,
                        'tax'                => 0,
                        'total'              => $total,
                        'payment_method'     => $this->paymentMethod,
                        'cash_received'      => $this->paymentMethod === 'cash' ? (float) $this->cashPaid : $total,
                        'change_given'       => $this->paymentMethod === 'cash' ? $this->change : 0,
                        'status'             => 'paid',
                    ]);

                    foreach ($this->cart as $item) {
                        OrderItem::create([
                            'order_id'           => $order->id,
                            'product_id'         => $item['product_id'],
                            'product_variant_id' => $item['product_variant_id'] ?? null,
                            'quantity'           => $item['quantity'],
                            'size'               => $item['size'] ?? 'M',
                            'price'              => $item['price'],
                            'subtotal'           => $item['price'] * $item['quantity'],
                            'sugar_level'        => $item['sugar_level'] ?? null,
                            'notes'              => $item['notes'] ?? null,
                        ]);
                    }

                    return $order;
                }, 3);
            } catch (\Throwable $e) {
                $this->addError('cart', $e->getMessage());
                return;
            }

            // 5. Broadcast order placed to KDS
            event(new OrderPlaced($order));

            // 6. Clear cart in PosInterface and notify frontend listeners
            if (method_exists($this, 'dispatch')) {
                $this->dispatch('clearCart');
                $this->dispatch('orderCompleted', order_id: $order->id, order_number: $order->order_number);
                $this->dispatch('checkout:success', order_id: $order->id, order_number: $order->order_number);
            } elseif (method_exists($this, 'emit')) {
                $this->emit('clearCart');
                $this->emit('orderCompleted', ['order_id' => $order->id, 'order_number' => $order->order_number]);
                $this->emit('checkout:success', ['order_id' => $order->id, 'order_number' => $order->order_number]);
            }

            // 7. Close modal and finish
            $this->isOpen = false;
        } finally {
            if ($lockAcquired && isset($lock)) {
                optional($lock)->release();
            }
            $this->isProcessing = false;
        }
    }

    public function render()
    {
        $total = collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']);

        return view('livewire.pos.checkout-modal', [
            'total' => $total,
        ]);
    }
}
