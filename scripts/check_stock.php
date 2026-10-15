<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\RawMaterial::all() as $m) {
    echo "{$m->name}: {$m->stock_qty} {$m->unit}\n";
}
