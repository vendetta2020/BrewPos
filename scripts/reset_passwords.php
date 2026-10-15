<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$users = [
    'admin@brewpos.test' => 'Admin Manajer',
    'cashier@brewpos.test' => 'Cashier Budi',
    'barista@brewpos.test' => 'Barista Siti',
];

foreach ($users as $email => $name) {
    $u = User::where('email', $email)->first();
    if ($u) {
        $u->password = Hash::make('password');
        $u->is_active = true;
        $u->save();
        echo "Reset password for {$email}\n";
    } else {
        echo "User {$email} not found!\n";
    }
}
