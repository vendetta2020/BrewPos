<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\CashierSession;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;

$cashier = User::where('email', 'cashier@brewpos.test')->first();
$session = CashierSession::where('user_id', $cashier->id)->where('status', 'open')->latest()->first();

if (!$session) {
    $session = CashierSession::create([
        'user_id' => $cashier->id,
        'opening_balance' => 100000,
        'status' => 'open',
        'opened_at' => now(),
    ]);
}

$latte = Product::where('name', 'like', '%Latte%')->first();
$croissant = Product::where('name', 'like', '%Croissant%')->first();

$order = Order::create([
    'order_number' => 'TRX-' . strtoupper(Str::random(8)),
    'user_id' => $cashier->id,
    'cashier_session_id' => $session->id,
    'subtotal' => 80000,
    'discount' => 0,
    'tax' => 0,
    'total' => 80000,
    'payment_method' => 'cash',
    'cash_received' => 100000,
    'change_given' => 20000,
    'status' => 'paid',
]);

if ($latte) {
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $latte->id,
        'quantity' => 1,
        'size' => 'M',
        'price' => 35000,
        'subtotal' => 35000,
        'notes' => 'Less ice, normal sugar',
    ]);
}

if ($croissant) {
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $croissant->id,
        'quantity' => 1,
        'size' => 'M',
        'price' => 45000,
        'subtotal' => 45000,
        'notes' => 'Hangatkan',
    ]);
}

echo "Created Order ID: {$order->id} (#{$order->order_number})\n";
