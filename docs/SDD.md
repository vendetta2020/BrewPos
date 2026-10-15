---
title: "BrewPos — Software Design Document"
version: "1.0"
date: "2026-05-28"
project: "brewpos"
type: "SDD"
stack: "Laravel 11, PHP 8.2, Livewire 3, Tailwind CSS, MySQL, Pusher"
description: "Architecture, database schema, service layer, routing, Livewire components, deployment guide"
---

## 1. Ikhtisar Arsitektur
### 1.1 Pola Arsitektur
BrewPos mengadopsi pola arsitektur MVC (Model-View-Controller) yang merupakan standar Laravel, diperkaya dengan pola Service Layer untuk logika bisnis kompleks, serta Observer Pattern untuk side-effect otomatis seperti deduction stok. Komponen reaktif dibangun menggunakan Livewire 3 yang beroperasi di atas server-side rendering dengan WebSocket untuk real-time features.

| **Layer**      | **Teknologi**                     | **Tanggung Jawab**                                        |
|----------------|-----------------------------------|-----------------------------------------------------------|
| Presentation   | Blade + Livewire 3 + Alpine.js    | Rendering UI, reaktivitas komponen, interaksi client-side |
| Styling        | Tailwind CSS 3                    | Desain visual, responsivitas, design tokens               |
| Application    | Laravel 11 Controllers + Services | Alur request, validasi, orkestrasi logika bisnis          |
| Domain         | Eloquent Models + Observers       | Representasi data, relasi, event otomatis                 |
| Infrastructure | MySQL + Redis + Pusher            | Persistensi data, cache/queue, real-time messaging        |

### 1.2 Diagram Komponen (Tekstual)

**Lapisan Arsitektur BrewPos**

```php
┌─────────────────────────────────────────────────────┐
│ BROWSER CLIENT │
│ Blade Views + Livewire Components + Alpine.js │
└────────────────────┬────────────────────────────────┘
│ HTTP / WebSocket (Pusher)
┌────────────────────▼────────────────────────────────┐
│ LARAVEL APPLICATION SERVER │
│ ┌──────────┐ ┌──────────┐ ┌──────────────────┐ │
│ │ Routes │ │Middleware│ │ Controllers │ │
│ └──────────┘ └──────────┘ └────────┬─────────┘ │
│ │ │
│ ┌─────────────────────────────────────▼──────────┐ │
│ │ Service Layer │ │
│ │ PosService InventoryDeductionService │ │
│ │ ReportService SessionService │ │
│ └─────────────────────────────────────┬──────────┘ │
│ │ │
│ ┌──────────────────────────────────────▼──────────┐ │
│ │ Eloquent Models + Observers │ │
│ │ Order Product RawMaterial StockMovement │ │
│ └──────────────────────────────────────┬──────────┘ │
└─────────────────────────────────────────┼────────────┘
│
┌─────────────────────────────────────────▼────────────┐
│ INFRASTRUCTURE LAYER │
│ MySQL Database Redis (Queue/Cache) Pusher │
└──────────────────────────────────────────────────────┘

## 2. Struktur Folder Proyek
Struktur Direktori Laravel BrewPos
brewpos/
├── app/
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── Admin/ # DashboardController, MenuController
│ │ │ ├── Cashier/ # PosController, SessionController
│ │ │ └── Inventory/ # RawMaterialController, RecipeController
│ │ ├── Livewire/
│ │ │ ├── Pos/ # PosInterface, CartComponent, CheckoutModal
│ │ │ ├── Kds/ # KdsBoard, OrderCard
│ │ │ └── Inventory/ # RecipeEditor, RestockForm
│ │ └── Middleware/ # RoleMiddleware, EnsureSessionOpen
│ ├── Models/
│ │ ├── User.php # role: admin|cashier|barista
│ │ ├── Product.php # hasMany recipes
│ │ ├── Order.php # hasMany orderItems
│ │ ├── OrderItem.php # hasMany stockMovements
│ │ ├── RawMaterial.php # hasMany stockMovements
│ │ ├── ProductRecipe.php # belongs to Product &amp; RawMaterial
│ │ └── StockMovement.php # polymorphic log
│ ├── Observers/
│ │ └── OrderItemObserver.php # trigger deduction on created
│ ├── Services/
│ │ ├── InventoryDeductionService.php
│ │ ├── PosService.php
│ │ ├── ReportService.php
│ │ └── CashierSessionService.php
│ └── Events/
│ ├── OrderPlaced.php # broadcast ke Pusher → KDS
│ └── LowStockAlert.php # notif ke admin
├── resources/views/
│ ├── layouts/
│ │ ├── admin.blade.php # sidebar layout
│ │ └── pos.blade.php # fullscreen layout
│ ├── admin/ # dashboard, menu, users, settings
│ ├── cashier/ # pos, session
│ ├── inventory/ # raw-materials, recipes, restock
│ ├── kds/ # kitchen display
│ └── reports/ # sales, inventory, hpp
├── database/
│ ├── migrations/
│ └── seeders/ # ProductSeeder, RawMaterialSeeder
└── routes/
├── web.php # semua route web
└── channels.php # Pusher broadcast channels

## 3. Desain Database
### 3.1 Migration — Tabel Utama
**users**

Schema: users
Schema::create('users', function (Blueprint $table) {
$table-&gt;id();
$table-&gt;string('name');
$table-&gt;string('email')-&gt;unique();
$table-&gt;string('password');
$table-&gt;enum('role', ['admin', 'cashier', 'barista'])-&gt;default('cashier');
$table-&gt;boolean('is_active')-&gt;default(true);
$table-&gt;timestamps();
});

**products**

Schema: products
Schema::create('products', function (Blueprint $table) {
$table-&gt;id();
$table-&gt;foreignId('category_id')-&gt;constrained()-&gt;cascadeOnDelete();
$table-&gt;string('name');
$table-&gt;text('description')-&gt;nullable();
$table-&gt;string('image')-&gt;nullable();
$table-&gt;decimal('price', 12, 2);
$table-&gt;boolean('has_variants')-&gt;default(false);
$table-&gt;boolean('has_recipe')-&gt;default(false);
$table-&gt;boolean('is_active')-&gt;default(true);
$table-&gt;softDeletes();
$table-&gt;timestamps();
});

**raw_materials**

Schema: raw_materials
Schema::create('raw_materials', function (Blueprint $table) {
$table-&gt;id();
$table-&gt;string('name');
$table-&gt;string('category');
$table-&gt;enum('unit', ['gram', 'ml', 'pcs', 'liter']);
$table-&gt;decimal('cost_per_unit', 10, 2);
$table-&gt;decimal('stock_qty', 10, 2)-&gt;default(0);
$table-&gt;decimal('min_stock_qty', 10, 2)-&gt;default(0);
$table-&gt;boolean('is_active')-&gt;default(true);
$table-&gt;softDeletes();
$table-&gt;timestamps();
});

**product_recipes**

Schema: product_recipes
Schema::create('product_recipes', function (Blueprint $table) {
$table-&gt;id();
$table-&gt;foreignId('product_id')-&gt;constrained()-&gt;cascadeOnDelete();
$table-&gt;foreignId('raw_material_id')-&gt;constrained()-&gt;cascadeOnDelete();
$table-&gt;decimal('qty_used', 10, 2);
$table-&gt;enum('size_variant', ['S', 'M', 'L', 'all'])-&gt;default('all');
$table-&gt;string('notes')-&gt;nullable();
$table-&gt;timestamps();
$table-&gt;unique(['product_id', 'raw_material_id', 'size_variant']);
});

**stock_movements**

Schema: stock_movements
Schema::create('stock_movements', function (Blueprint $table) {
$table-&gt;id();
$table-&gt;foreignId('raw_material_id')-&gt;constrained();
$table-&gt;foreignId('order_item_id')-&gt;nullable()-&gt;constrained();
$table-&gt;enum('type', ['in', 'out', 'adjust']);
$table-&gt;decimal('qty', 10, 2);
$table-&gt;decimal('cost_per_unit', 10, 2);
$table-&gt;decimal('total_cost', 12, 2);
$table-&gt;string('note')-&gt;nullable();
$table-&gt;foreignId('created_by')-&gt;constrained('users');
$table-&gt;timestamp('created_at');
});

## 4. Service Layer Design
### 4.1 InventoryDeductionService
Service ini dipanggil oleh OrderItemObserver setiap kali order item baru dibuat (status completed). Bertanggung jawab membaca resep, mengurangi stok, mencatat movement, dan memeriksa batas minimum.

**InventoryDeductionService.php**

```php
namespace App\Services;
class InventoryDeductionService
{
public function deductFromOrder(OrderItem $orderItem): void
{
$size = $orderItem-&gt;size ?? 'all';
$recipes = $orderItem-&gt;product-&gt;recipes()
-&gt;where(fn($q) =&gt; $q
-&gt;where('size_variant', $size)
-&gt;orWhere('size_variant', 'all')
)-&gt;get();
foreach ($recipes as $recipe) {
$material = $recipe-&gt;rawMaterial;
$totalUsed = $recipe-&gt;qty_used * $orderItem-&gt;quantity;
$material-&gt;decrement('stock_qty', $totalUsed);
StockMovement::create([
'raw_material_id' =&gt; $material-&gt;id,
'order_item_id' =&gt; $orderItem-&gt;id,
'type' =&gt; 'out',
'qty' =&gt; $totalUsed,
'cost_per_unit' =&gt; $material-&gt;cost_per_unit,
'total_cost' =&gt; $totalUsed * $material-&gt;cost_per_unit,
'created_by' =&gt; auth()-&gt;id() ?? 1,
]);
if ($material-&gt;fresh()-&gt;stock_qty &lt;= $material-&gt;min_stock_qty) {
event(new LowStockAlert($material));
}
}
}
}
```

### 4.2 OrderItemObserver
**OrderItemObserver.php**

```php
namespace App\Observers;
class OrderItemObserver
{
public function created(OrderItem $orderItem): void
{
// Hanya deduct jika order sudah completed
if ($orderItem-&gt;order-&gt;status === 'completed') {
app(InventoryDeductionService::class)
-&gt;deductFromOrder($orderItem);
}
}
}
// Register di AppServiceProvider:
// OrderItem::observe(OrderItemObserver::class);

```

## 5. Desain Routing
### 5.1 Struktur Route
| **Method** | **URI**                      | **Controller / Livewire**       | **Middleware**     |
|------------|------------------------------|---------------------------------|--------------------|
| GET        | /login                       | Auth\LoginController            | guest              |
| POST       | /login                       | Auth\LoginController@store      | guest              |
| GET        | /dashboard                   | Admin\DashboardController       | auth, role:admin   |
| GET        | /pos                         | Cashier\PosController           | auth, role:cashier |
| GET        | /kds                         | Kds\KdsController               | auth, role:barista |
| GET        | /inventory/materials         | Inventory\RawMaterialController | auth, role:admin   |
| GET        | /inventory/recipes           | Inventory\RecipeController      | auth, role:admin   |
| POST       | /inventory/recipes/{product} | RecipeController@store          | auth, role:admin   |
| GET        | /inventory/restock           | Inventory\RestockController     | auth, role:admin   |
| POST       | /orders                      | Cashier\OrderController@store   | auth, role:cashier |
| GET        | /reports/sales               | Report\SalesController          | auth, role:admin   |
| GET        | /reports/inventory           | Report\InventoryController      | auth, role:admin   |

### 5.2 Broadcast Channels (Pusher)
**routes/channels.php**

```php
// Channel untuk KDS — hanya barista &amp; admin
Broadcast::channel('orders', function (User $user) {
return in_array($user-&gt;role, ['barista', 'admin']);
});
// Channel notifikasi low-stock — hanya admin
Broadcast::channel('admin.notifications', function (User $user) {
return $user-&gt;role === 'admin';
});

```

## 6. Desain Livewire Components
| **Komponen**     | **Class**              | **State Utama**                     | **Event / Emit**    |
|------------------|------------------------|-------------------------------------|---------------------|
| POS Interface    | Pos\PosInterface       | cart\[\], selectedCategory, search  | order-placed        |
| Cart Component   | Pos\CartComponent      | items\[\], total, discount          | checkout-success    |
| Checkout Modal   | Pos\CheckoutModal      | paymentMethod, cashPaid, change     | order-completed     |
| KDS Board        | Kds\KdsBoard           | orders\[\], filter                  | listen: OrderPlaced |
| Order Card (KDS) | Kds\OrderCard          | order, status                       | status-updated      |
| Recipe Editor    | Inventory\RecipeEditor | product, ingredients\[\], hpp       | recipe-saved        |
| Restock Form     | Inventory\RestockForm  | materialId, qty, price, supplier    | restock-saved       |
| Stock Table      | Inventory\StockTable   | materials\[\], filterStatus, search | —                   |

### 6.1 Contoh: PosInterface Component
Livewire\Pos\PosInterface.php (ringkasan)
class PosInterface extends Component
{
public string $search = '';
public ?int $selectedCategory = null;
public array $cart = [];
public function addToCart(int $productId, string $size = 'M'): void
{
$key = $productId . '_' . $size;
if (isset($this-&gt;cart[$key])) {
$this-&gt;cart[$key]['qty']++;
} else {
$product = Product::find($productId);
$this-&gt;cart[$key] = [
'id' =&gt; $productId, 'name' =&gt; $product-&gt;name,
'size' =&gt; $size, 'price' =&gt; $product-&gt;priceForSize($size),
'qty' =&gt; 1, 'note' =&gt; '',
];
}
}
public function getCartTotalProperty(): int
{
return collect($this-&gt;cart)-&gt;sum(fn($i) =&gt; $i['price'] * $i['qty']);
}
}

## 7. Desain Keamanan
### 7.1 Role Middleware
**Middleware\RoleMiddleware.php**

```php
class RoleMiddleware
{
public function handle(Request $request, Closure $next, string ...$roles): Response
{
if (!in_array(auth()-&gt;user()-&gt;role, $roles)) {
abort(403, 'Akses ditolak.');
}
return $next($request);
}
}
// Daftarkan di bootstrap/app.php:
// -&gt;withMiddleware(fn($m) =&gt; $m-&gt;alias(['role' =&gt; RoleMiddleware::class]))

```

### 7.2 Matriks Akses Per Role
| **Modul / Halaman**      | **Admin**      | **Kasir**           | **Barista**    |
|--------------------------|----------------|---------------------|----------------|
| Dashboard & analitik     | ✅ Full access | ❌                  | ❌             |
| Manajemen menu (CRUD)    | ✅ Full access | ❌                  | ❌             |
| Halaman POS kasir        | ✅ Read-only   | ✅ Full access      | ❌             |
| Buka / tutup sesi kasir  | ✅             | ✅ Own session      | ❌             |
| Kitchen display (KDS)    | ✅ Monitor     | ❌                  | ✅ Full access |
| Manajemen bahan baku     | ✅ Full access | ❌                  | ❌             |
| Manajemen resep          | ✅ Full access | ❌                  | ❌             |
| Catat restock            | ✅ Full access | ❌                  | ❌             |
| Laporan penjualan        | ✅ Full access | ✅ Own session only | ❌             |
| Laporan bahan baku & HPP | ✅ Full access | ❌                  | ❌             |
| Manajemen pengguna       | ✅ Full access | ❌                  | ❌             |
| Pengaturan sistem        | ✅ Full access | ❌                  | ❌             |

## 8. Desain Deployment
### 8.1 Persyaratan Server
| **Komponen** | **Minimum**                  | **Rekomendasi**          |
|--------------|------------------------------|--------------------------|
| OS           | Ubuntu 20.04 LTS             | Ubuntu 22.04 LTS         |
| PHP          | 8.2                          | 8.3 dengan OPcache aktif |
| Web Server   | Apache 2.4 / Nginx 1.18      | Nginx 1.24               |
| Database     | MySQL 8.0                    | MySQL 8.0 dengan InnoDB  |
| RAM          | 2 GB                         | 4 GB                     |
| Storage      | 20 GB                        | 50 GB SSD                |
| Queue Worker | Supervisor + database driver | Supervisor + Redis       |

### 8.2 Environment Variables Kritis
.env — variabel wajib dikonfigurasi
APP_NAME=BrewPos
APP_ENV=production
APP_KEY=base64:...
APP_URL=https://pos.namatoko.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=brewpos_production
DB_USERNAME=brewpos_user
DB_PASSWORD=strong_password_here
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap1
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=database

### 8.3 Langkah Deployment Awal
| **Langkah** | **Perintah / Aksi**                                                           |
|-------------|-------------------------------------------------------------------------------|
| 1           | composer install --optimize-autoloader --no-dev                               |
| 2           | php artisan key:generate                                                      |
| 3           | php artisan migrate --seed (jalankan seeder produk & bahan baku contoh)       |
| 4           | php artisan storage:link                                                      |
| 5           | php artisan config:cache && php artisan route:cache && php artisan view:cache |
| 6           | Konfigurasi Supervisor untuk queue worker: php artisan queue:work             |
| 7           | Set cron job: \* \* \* \* \* php /path/to/artisan schedule:run                |
| 8           | Konfigurasi Nginx virtual host, arahkan document root ke /public              |
```


