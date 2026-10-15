<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'cashier')->first();
if ($user) {
    App\Models\CashierSession::firstOrCreate(
        ['user_id' => $user->id, 'status' => 'open'],
        ['opening_balance' => 100000, 'opened_at' => now()]
    );
    echo "SESSION_INITIALIZED\n";
}
