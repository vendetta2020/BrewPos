# PROJECT KNOWLEDGE — BREWPOS SYSTEM

---

## 1. Project Overview
BrewPOS adalah sistem Point of Sale (POS) dan Kitchen Display System (KDS) terintegrasi yang dirancang khusus untuk operasional kedai kopi / specialty coffee shop. Sistem ini mengintegrasikan seluruh alur kerja operasional kafe:
- Pembukaan dan penutupan shift laci kasir (*Cashier Sessions*).
- Katalog produk dinamis dengan dukungan multi-varian ukuran (S, M, L) dan kustomisasi (sugar level, catatan racikan).
- Transaksi POS kasir dengan kalkulasi real-time (subtotal, diskon, pajak, nominal tunai, kembalian).
- Manajemen persediaan bahan baku (*Raw Materials*) dan resep menu (*Product Recipes*).
- Pengurangan stok otomatis berbasis resep (*Recipe-based automatic stock deduction*) saat transaksi selesai diproses.
- Tampilan dapur (*Kitchen Display System* / KDS) untuk barista dengan pembaruan status real-time (*Paid* → *Processing* → *Ready* → *Completed*).
- Modul laporan analitik: ringkasan penjualan, produk terlaris, pemakaian bahan baku, serta kalkulasi Harga Pokok Produksi (HPP) dan margin keuntungan (*profit margin*).

---

## 2. Tech Stack
- **Framework Backend:** Laravel 11.x (PHP 8.2+)
- **Frontend / Reactivity:** Livewire 3 (komponen reaktif berbasis server-side rendering tanpa reload halaman)
- **Styling & Design System:** Tailwind CSS 3 (mengadopsi panduan desain fungsional minimalis / anti-AI slop dengan tema dark warm obsidian dan amber accents)
- **Database Engine:** MySQL 8.0+ / SQLite (in-memory testing)
- **Real-Time Layer:** Laravel Echo & Pusher Channels (Event: `OrderPlaced`, `LowStockAlert`)
- **Export Engine:** Maatwebsite Laravel-Excel & Barryvdh DomPDF
- **Testing Framework:** PHPUnit 10.x & Laravel Feature Test suite

---

## 3. Architecture
Sistem BrewPOS mengimplementasikan pola arsitektur berlapis (*layered architecture*):
1. **Presentation Layer:**
   - Blade templates untuk layout statis dan kerangka halaman (`resources/views/`).
   - Komponen reaktif Livewire 3 untuk modul dinamis: `PosInterface`, `CheckoutModal`, dan `KdsBoard`.
2. **Application / Controller Layer:**
   - Controllers dipartisi berdasarkan modul domain: `Admin/`, `Cashier/`, `Inventory/`, `Reports/`, dan `Auth/`.
   - Mengatur validasi HTTP request, pemanggilan session, dan pengembalian respons/view.
3. **Service Layer:**
   - Memisahkan logika bisnis kompleks dari controller:
     - `InventoryDeductionService`: Memvalidasi ketersediaan bahan, memotong stok secara atomik saat pesanan selesai, dan mengembalikan stok saat pesanan dibatalkan/void.
     - `ReportService`: Memfilter periode tanggal, menghitung ringkasan pendapatan, produk terlaris, pemakaian material, dan formula HPP/margin keuntungan.
4. **Domain & Observer Layer:**
   - Eloquent Models (`User`, `Product`, `ProductVariant`, `Category`, `RawMaterial`, `ProductRecipe`, `CashierSession`, `Order`, `OrderItem`, `StockMovement`).
   - `OrderItemObserver`: Memicu deduksi stok inventaris otomatis saat item pesanan baru dibuat pada transaksi aktif.
5. **Infrastructure & Security Layer:**
   - Autentikasi berbasis session guard Laravel.
   - Otorisasi berbasis peran melalui `RoleMiddleware` (`admin`, `cashier`, `barista`).

---

## 4. Folder Structure
```text
brewpos/
├── app/
│   ├── Events/
│   │   ├── LowStockAlert.php            # Broadcast notifikasi stok kritis ke admin
│   │   └── OrderPlaced.php              # Broadcast pesanan baru ke KDS barista
│   ├── Exports/
│   │   └── ArrayExport.php              # Export array data ke file XLSX
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                   # ProductController, CategoryController, UserController
│   │   │   ├── Auth/                    # LoginController (autentikasi & proteksi user nonaktif)
│   │   │   ├── Cashier/                 # PosController, CashierSessionController
│   │   │   ├── Inventory/               # RawMaterialController, RecipeController, RestockController
│   │   │   └── Reports/                 # ReportController, SalesController, InventoryReportController
│   │   ├── Livewire/
│   │   │   ├── Kds/                     # KdsBoard (layar antrean barista)
│   │   │   └── Pos/                     # PosInterface (katalog & keranjang), CheckoutModal (pembayaran)
│   │   └── Middleware/
│   │       └── RoleMiddleware.php       # Proteksi hak akses role & verifikasi is_active
│   ├── Models/                          # Eloquent models
│   ├── Observers/
│   │   └── OrderItemObserver.php        # Trigger InventoryDeductionService pada order item
│   ├── Providers/
│   │   └── AppServiceProvider.php       # Registrasi observer & alias komponen Livewire
│   └── Services/
│       ├── InventoryDeductionService.php# Validasi stok, pemotongan stok atomik, dan restorasi void
│       └── ReportService.php            # Logika analitik, ringkasan sesi, dan formula HPP/margin
├── database/
│   ├── factories/                       # UserFactory
│   ├── migrations/                      # 12 file migrasi skema database
│   └── seeders/                         # DatabaseSeeder
├── docs/                                # Spesifikasi resmi (PRD.md, SRS.md, SDD.md, design.md)
├── resources/views/                     # Layouts Blade & views modul
├── routes/
│   ├── channels.php                     # Pusher broadcast channels
│   └── web.php                          # Deklarasi seluruh route aplikasi
└── tests/
    └── Feature/
        └── BrewPosFlowTest.php          # 11 skenario pengujian komprehensif end-to-end
```

---

## 5. Database Schema & Field Analysis

### 1. `users`
- `id`: BIGINT UNSIGNED, Primary Key, Auto Increment
- `name`: VARCHAR(255), Not Null
- `email`: VARCHAR(255), Unique, Not Null
- `email_verified_at`: TIMESTAMP, Nullable
- `password`: VARCHAR(255), Not Null (Bcrypt hash)
- `role`: ENUM('admin', 'cashier', 'barista'), Default 'cashier', Not Null
- `is_active`: BOOLEAN (TINYINT(1)), Default TRUE, Not Null
- `remember_token`: VARCHAR(100), Nullable
- `timestamps`: `created_at`, `updated_at`

### 2. `categories`
- `id`: BIGINT UNSIGNED, Primary Key, Auto Increment
- `name`: VARCHAR(255), Not Null
- `is_active`: BOOLEAN, Default TRUE, Not Null
- `timestamps`: `created_at`, `updated_at`

### 3. `products`
- `id`: BIGINT UNSIGNED, Primary Key, Auto Increment
- `category_id`: BIGINT UNSIGNED, Foreign Key constrained to `categories.id` (ON DELETE CASCADE)
- `name`: VARCHAR(255), Not Null
- `description`: TEXT, Nullable
- `image`: VARCHAR(255), Nullable
- `price`: DECIMAL(12, 2), Not Null (Base price)
- `has_variants`: BOOLEAN, Default FALSE, Not Null
- `has_recipe`: BOOLEAN, Default FALSE, Not Null
- `is_active`: BOOLEAN, Default TRUE, Not Null
- `deleted_at`: TIMESTAMP, Soft Deletes
- `timestamps`: `created_at`, `updated_at`

### 4. `product_variants`
- `id`: BIGINT UNSIGNED, Primary Key, Auto Increment
- `product_id`: BIGINT UNSIGNED, Foreign Key constrained to `products.id` (ON DELETE CASCADE)
- `size`: ENUM('S', 'M', 'L'), Not Null
- `price`: DECIMAL(12, 2), Not Null
- `is_active`: BOOLEAN, Default TRUE, Not Null
- `timestamps`: `created_at`, `updated_at`
- *Unique Constraint:* `['product_id', 'size']`

### 5. `raw_materials`
- `id`: BIGINT UNSIGNED, Primary Key, Auto Increment
- `name`: VARCHAR(255), Not Null
- `category`: VARCHAR(255), Not Null
- `unit`: ENUM('gram', 'ml', 'pcs', 'liter'), Not Null
- `cost_per_unit`: DECIMAL(10, 2), Not Null
- `stock_qty`: DECIMAL(10, 2), Default 0.00, Not Null
- `min_stock_qty`: DECIMAL(10, 2), Default 0.00, Not Null
- `is_active`: BOOLEAN, Default TRUE, Not Null
- `deleted_at`: TIMESTAMP, Soft Deletes
- `timestamps`: `created_at`, `updated_at`

### 6. `product_recipes`
- `id`: BIGINT UNSIGNED, Primary Key, Auto Increment
- `product_id`: BIGINT UNSIGNED, Foreign Key constrained to `products.id` (ON DELETE CASCADE)
- `raw_material_id`: BIGINT UNSIGNED, Foreign Key constrained to `raw_materials.id` (ON DELETE CASCADE)
- `qty_used`: DECIMAL(10, 2), Not Null
- `size_variant`: ENUM('S', 'M', 'L', 'all'), Default 'all', Not Null
- `notes`: VARCHAR(255), Nullable
- `timestamps`: `created_at`, `updated_at`
- *Unique Constraint:* `['product_id', 'raw_material_id', 'size_variant']`

### 7. `cashier_sessions`
- `id`: BIGINT UNSIGNED, Primary Key, Auto Increment
- `user_id`: BIGINT UNSIGNED, Foreign Key constrained to `users.id`
- `opening_balance`: DECIMAL(12, 2), Not Null
- `closing_balance`: DECIMAL(12, 2), Nullable
- `expected_balance`: DECIMAL(12, 2), Nullable
- `difference`: DECIMAL(12, 2), Default 0.00, Not Null
- `opened_at`: TIMESTAMP, Not Null
- `closed_at`: TIMESTAMP, Nullable
- `status`: ENUM('open', 'closed'), Default 'open', Not Null
- `notes`: TEXT, Nullable
- `timestamps`: `created_at`, `updated_at`

### 8. `orders`
- `id`: BIGINT UNSIGNED, Primary Key, Auto Increment
- `cashier_session_id`: BIGINT UNSIGNED, Foreign Key constrained to `cashier_sessions.id`
- `user_id`: BIGINT UNSIGNED, Foreign Key constrained to `users.id`
- `order_number`: VARCHAR(255), Unique, Not Null (contoh: `TRX-XXXXXX`)
- `table_number`: VARCHAR(255), Nullable
- `subtotal`: DECIMAL(12, 2), Not Null
- `discount`: DECIMAL(12, 2), Default 0.00, Not Null
- `tax`: DECIMAL(12, 2), Default 0.00, Not Null
- `total`: DECIMAL(12, 2), Not Null
- `payment_method`: ENUM('cash', 'qris', 'card'), Not Null
- `cash_received`: DECIMAL(12, 2), Nullable
- `change_given`: DECIMAL(12, 2), Nullable
- `status`: ENUM('pending', 'paid', 'processing', 'ready', 'completed', 'cancelled'), Default 'pending', Not Null
- `timestamps`: `created_at`, `updated_at`

### 9. `order_items`
- `id`: BIGINT UNSIGNED, Primary Key, Auto Increment
- `order_id`: BIGINT UNSIGNED, Foreign Key constrained to `orders.id` (ON DELETE CASCADE)
- `product_id`: BIGINT UNSIGNED, Foreign Key constrained to `products.id`
- `product_variant_id`: BIGINT UNSIGNED, Nullable, Foreign Key constrained to `product_variants.id` (ON DELETE SET NULL)
- `quantity`: INT, Not Null
- `size`: ENUM('S', 'M', 'L', 'all'), Default 'M', Not Null
- `price`: DECIMAL(12, 2), Not Null
- `subtotal`: DECIMAL(12, 2), Not Null
- `sugar_level`: VARCHAR(255), Nullable
- `notes`: VARCHAR(255), Nullable
- `timestamps`: `created_at`, `updated_at`

### 10. `stock_movements`
- `id`: BIGINT UNSIGNED, Primary Key, Auto Increment
- `raw_material_id`: BIGINT UNSIGNED, Foreign Key constrained to `raw_materials.id`
- `order_item_id`: BIGINT UNSIGNED, Nullable, Foreign Key constrained to `order_items.id`
- `type`: ENUM('in', 'out', 'adjust'), Not Null
- `qty`: DECIMAL(10, 2), Not Null
- `cost_per_unit`: DECIMAL(10, 2), Not Null
- `total_cost`: DECIMAL(12, 2), Not Null
- `note`: VARCHAR(255), Nullable
- `created_by`: BIGINT UNSIGNED, Foreign Key constrained to `users.id`
- `created_at`: TIMESTAMP, Default CURRENT_TIMESTAMP

---

## 6. Entity Relationships (ERD Textual)
```text
users
├── has many orders (user_id)
├── has many cashier_sessions (user_id)
└── has many stock_movements (created_by)

categories
└── has many products (category_id)

products
├── belongs to category
├── has many product_variants (product_id)
├── has many product_recipes (product_id)
└── has many order_items (product_id)

product_variants
├── belongs to product
└── has many order_items (product_variant_id)

raw_materials
├── has many product_recipes (raw_material_id)
└── has many stock_movements (raw_material_id)

product_recipes
├── belongs to product
└── belongs to raw_material

cashier_sessions
├── belongs to user
└── has many orders (cashier_session_id)

orders
├── belongs to cashier_session
├── belongs to user
└── has many order_items (order_id)

order_items
├── belongs to order
├── belongs to product
├── belongs to product_variant (optional)
└── has many stock_movements (order_item_id)

stock_movements
├── belongs to raw_material
├── belongs to order_item (optional)
└── belongs to user (created_by)
```

---

## 7. Authentication & Authorization
- **Authentication:** Menggunakan session guard Laravel dengan hashing Bcrypt (`cost: 12`).
- **Deactivated Employee Enforcement:**
  - Pada `LoginController@store`, sistem memvalidasi kolom `is_active`. Jika user berstatus nonaktif (`is_active == false`), autentikasi langsung dibatalkan, session dihancurkan, dan melempar pesan validasi error `"Akun Anda telah dinonaktifkan."`.
  - Pada `RoleMiddleware`, setiap request yang masuk divalidasi status aktifnya. Jika karyawan dinonaktifkan saat sesi sedang berjalan, middleware otomatis me-logout dan me-redirect ke login.
- **Authorization:** `RoleMiddleware` mengisolasi akses route berdasarkan peran pengguna: `admin`, `cashier`, `barista`.

---

## 8. Role & Permission Matrix

| Modul / Fitur | Admin | Cashier | Barista | Backend Protected | Keterangan Proteksi |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **Login / Logout** | ✅ | ✅ | ✅ | Yes | Guest & Auth middleware, cek `is_active` |
| **Admin Dashboard** | ✅ | ❌ | ❌ | Yes | `role:admin` |
| **Product & Category CRUD** | ✅ | ❌ | ❌ | Yes | `role:admin` |
| **Inventory & Restock** | ✅ | ❌ | ❌ | Yes | `role:admin` |
| **Recipe Management** | ✅ | ❌ | ❌ | Yes | `role:admin` |
| **User Management (CRUD)** | ✅ | ❌ | ❌ | Yes | `role:admin` |
| **Sales & Stock Reports** | ✅ | ❌ | ❌ | Yes | `role:admin` |
| **Open / Close Sesi Kasir** | ❌ | ✅ | ❌ | Yes | `role:cashier`, sesi terkunci per user ID |
| **POS Interface & Checkout** | ❌ | ✅ | ❌ | Yes | `role:cashier`, wajib buka sesi |
| **Struk Transaksi (Receipt)** | ❌ | ✅ | ❌ | Yes | `role:cashier` |
| **Void / Batal Pesanan** | ✅ | ✅ | ❌ | Yes | Method `Order::void` memulihkan stok |
| **Kitchen Display (KDS)** | ✅ | ❌ | ✅ | Yes | `role:barista`, aksi KDS diproteksi peran |

---

## 9. POS Workflow
1. **Login Kasir:** Kasir login dengan email dan password. Sistem memverifikasi kredensial dan memastikan `is_active == true`.
2. **Pemeriksaan Sesi Kasir:** Route `/pos` memeriksa apakah kasir memiliki sesi aktif (`status == 'open'`). Jika tidak, dialihkan ke `/cashier/session/open`.
3. **Buka Sesi (Open Shift):** Kasir menginput modal awal kas (`opening_balance`) dan catatan shift. Sesi tersimpan di tabel `cashier_sessions`.
4. **Katalog & Filter:** Di halaman POS, kasir dapat mencari menu dan memfilter kategori via komponen `PosInterface` (Livewire).
5. **Kustomisasi & Keranjang:** Kasir memilih varian ukuran (S/M/L), tingkat gula, dan catatan khusus. Item masuk ke properti `$cart`.
6. **Buka Modal Checkout:** Kasir mengklik "Bayar", memicu event `openCheckout` ke komponen `CheckoutModal`.
7. **Validasi Transaksi:**
   - Memastikan keranjang tidak kosong.
   - Jika metode pembayaran adalah `cash`, nilai `cashPaid` **wajib diisi, berupa angka, dan minimal sebesar total tagihan** (`cashPaid >= total`).
   - Sistem memvalidasi ketersediaan stok bahan baku untuk seluruh item keranjang (`validateStockForCart`). Jika ada bahan yang kurang, proses berhenti dan menampilkan pesan error informatif tanpa mengurangi stok atau membuat order fiktif.
8. **Eksekusi Atomik (Database Transaction):**
   - Transaksi database dibuka (`DB::transaction`).
   - Header `Order` dibuat dengan nomor transaksi unik (`TRX-XXXXXX`) dan status `'paid'`.
   - Looping pembuatan `OrderItem`. Setiap item yang tersimpan memicu `OrderItemObserver` yang mengeksekusi `InventoryDeductionService@deductFromOrder`.
   - Bahan baku dikurangi (`decrement('stock_qty')`) dan log pergerakan stok `out` dicatat pada `stock_movements`.
   - Jika stok di bawah `min_stock_qty`, event `LowStockAlert` dibroadcast ke admin.
   - Jika terjadi kendala pada salah satu item, seluruh transaksi di-rollback secara utuh tanpa meninggalkan record yatim (*orphaned records*).
9. **KDS Broadcast:** Event `OrderPlaced` dibroadcast ke channel Pusher `orders`.
10. **Pencetakan Struk:** Kasir diarahkan ke tampilan cetak struk (`/cashier/orders/{order}/receipt`).
11. **Tutup Sesi (Close Shift):** Kasir menghitung fisik uang laci di `/cashier/session/close`. Sistem mengkalkulasi `expectedCash = opening_balance + total_cash_sales` dan menghitung `difference`.

---

## 10. Product & Category Management
- CRUD Kategori dan Menu dikelola penuh oleh Admin melalui `ProductController` dan `CategoryController`.
- Setiap produk mendukung soft delete (`deleted_at`).
- Mendukung varian ukuran (S, M, L) dengan harga unik per varian di tabel `product_variants`.
- Method `Product::priceForSize($size)` otomatis mengambil harga varian spesifik jika `has_variants == true`, atau harga dasar produk jika non-varian.
- Menu dapat dihubungkan ke resep bahan baku melalui `RecipeController`.

---

## 11. Inventory & Recipe Workflow
1. **Pendaftaran Bahan Baku:** Admin memasukkan nama bahan, kategori, satuan (`gram`, `ml`, `pcs`, `liter`), biaya pokok per unit (`cost_per_unit`), stok awal, dan ambang batas minimum (`min_stock_qty`).
2. **Definisi Resep:** Pada `/admin/recipes`, admin menentukan kuantitas bahan yang digunakan per produk dan varian ukuran (`S`, `M`, `L`, atau `all`).
3. **Restock Barang Masuk:** Admin mencatat kedatangan bahan di `/admin/restocks/create`. Nilai `stock_qty` bertambah (`increment`), dan dicatat pada `stock_movements` dengan `type = 'in'`.
4. **Koreksi Stok Manual:** Admin dapat menyesuaikan stok fisik riil via `RawMaterialController@adjust` dengan mencantumkan alasan. Dicatat pada `stock_movements` dengan `type = 'adjust'`.
5. **Pemotongan Stok Penjualan:** Dijalankan secara otomatis saat pesanan kasir berstatus `'paid'` atau `'completed'`. Dicatat dengan `type = 'out'`.
6. **Restorasi Stok Saat Void:** Jika pesanan dibatalkan, seluruh bahan baku yang terpakai dikembalikan ke stok (`increment`) dan dicatat sebagai pergerakan masuk (`type = 'in'`) dengan catatan pembatalan.

---

## 12. Order & Transaction Workflow
- Transaksi dieksekusi secara atomik (`DB::transaction`) di `CheckoutModal::createOrder`.
- Mengamankan konsistensi data finansial:
  - `subtotal`: Jumlah harga item * qty.
  - `discount`: Nilai potongan (default 0).
  - `tax`: Nilai pajak (default 0).
  - `total`: Nilai akhir yang harus dibayarkan.
  - `cash_received`: Nilai uang tunai riil yang diserahkan konsumen.
  - `change_given`: Kembalian (`cash_received - total`).
  - `payment_method`: 'cash', 'qris', atau 'card'.

---

## 13. Order Status Lifecycle
Siklus hidup status pesanan (`orders.status`):
```text
[ pending ] ──(Kasir bayar)──> [ paid ] ──(Barista racik)──> [ processing ] ──(Barista siap)──> [ ready ] ──(Serah terima)──> [ completed ]
     │                              │                                │                           │
     └──────────────────────────────┴────────────────────────────────┴───────────────────────────┴──(Void / Batal)──> [ cancelled ]
                                                                                                                           │
                                                                                                                   (Stok dipulihkan)
```

1. **`paid`**: Pesanan telah diverifikasi pembayarannya oleh kasir dan stok bahan telah dipotong.
2. **`processing`**: Barista di KDS menekan "Mulai Racik Pesanan".
3. **`ready`**: Barista selesai meracik dan menandai pesanan "Siap Disajikan".
4. **`completed`**: Pesanan telah diambil/diserahkan kepada pelanggan dan diarsipkan.
5. **`cancelled`**: Pesanan dibatalkan melalui aksi Void. Stok bahan baku otomatis dikembalikan ke inventaris.

---

## 14. Reporting
Dikelola oleh `ReportService` dan `ReportController`:
- **Sales Summary:** Menghitung total omset (`sum('total')`), jumlah transaksi (`count()`), rata-rata belanja konsumen (AOV - *Average Order Value*), dan jumlah item inventaris dalam status kritis (`stock_qty <= min_stock_qty`). Hanya menghitung pesanan valid non-batal (`whereIn('status', ['paid', 'processing', 'ready', 'completed'])`).
- **Top Products:** Mengelompokkan item terjual per `product_id`, menjumlahkan kuantitas dan pendapatan.
- **Raw Material Usage:** Menganalisis total konsumsi bahan dan total biaya dari `stock_movements` bertipe `'out'`.
- **HPP & Profit Margin per Menu:**
  - Menghitung HPP akurat: Untuk varian ukuran (S, M, L), HPP adalah akumulasi biaya bahan varian ukuran tersebut **ditambah** bahan yang berlaku untuk `'all'`.
  - Mengambil harga jual riil per ukuran dari `product_variants.price`.
  - Menghitung Margin Rp (`Sale Price - HPP`) dan Margin % (`(Margin Rp / Sale Price) * 100`).
- **Rekap Sesi Kasir:** Merekap performa kasir per shift, total penjualan, dan selisih kas fisik vs sistem.

---

## 15. API / Route Map

### Publik & Autentikasi
- `GET /` -> Redirect ke `/login`
- `GET /login` (`login`) -> `LoginController@show`
- `POST /login` (`login.store`) -> `LoginController@store`
- `POST /logout` (`logout`) -> `LoginController@destroy`

### Area Admin (`middleware: auth, role:admin`)
- `GET /dashboard` (`dashboard`) -> Admin Dashboard View
- `GET /admin/reports` (`admin.reports.sales`) -> `ReportController@index`
- `GET /admin/reports/inventory` (`admin.reports.inventory`) -> `ReportController@index`
- `GET /admin/reports/export/sales` (`admin.reports.sales.export`) -> `SalesController@export`
- `GET /admin/reports/export/inventory` (`admin.reports.inventory.export`) -> `InventoryReportController@export`
- `GET /admin/orders` (`admin.orders.index`) -> `PosController@adminOrders`
- `RESOURCE /admin/products` -> `ProductController`
- `RESOURCE /admin/categories` -> `CategoryController`
- `RESOURCE /admin/users` -> `UserController`
- `RESOURCE /admin/raw-materials` -> `RawMaterialController`
- `POST /admin/raw-materials/{rawMaterial}/adjust` -> `RawMaterialController@adjust`
- `GET /admin/recipes` -> `RecipeController@index`
- `GET /admin/recipes/create` -> `RecipeController@create`
- `GET /admin/recipes/{product}/edit` -> `RecipeController@edit`
- `POST /admin/recipes` -> `RecipeController@store`
- `PUT /admin/recipes/{product}` -> `RecipeController@update`
- `DELETE /admin/recipes/{product}` -> `RecipeController@destroy`
- `GET /admin/restocks/create` -> `RestockController@create`
- `POST /admin/restocks` -> `RestockController@store`

### Area Kasir (`middleware: auth, role:cashier`)
- `GET /cashier/session/open` (`cashier.session.open`) -> `CashierSessionController@openForm`
- `POST /cashier/session/open` (`cashier.session.store`) -> `CashierSessionController@storeSession`
- `GET /cashier/session/close` (`cashier.session.close`) -> `CashierSessionController@closeForm`
- `POST /cashier/session/close` (`cashier.session.update`) -> `CashierSessionController@updateSession`
- `GET /pos` (`pos`) -> `PosController@index`
- `GET /cashier/orders` (`cashier.orders.index`) -> `PosController@orders` (SRS POS-10: Riwayat Pesanan Sesi Aktif)

### Aksi Pesanan (`middleware: auth, role:cashier,admin` - Terproteksi IDOR)
- `GET /cashier/orders/{order}/receipt` (`cashier.receipt`) -> `PosController@receipt`
- `POST /cashier/orders/{order}/void` (`cashier.orders.void`) -> `PosController@void`

### Area Barista (`middleware: auth, role:barista`)
- `GET /kds` (`kds`) -> KDS Kitchen Display Blade view

---

## 16. Frontend Architecture
- Server-Side Rendering (SSR) via Laravel Blade layouts (`layouts/admin.blade.php`, `layouts/cashier.blade.php`).
- Komponen Livewire 3:
  - `PosInterface`: Menangani state pencarian, filter kategori, penambahan produk, modifikasi kuantitas, dan kalkulasi subtotal cart.
  - `CheckoutModal`: Menangani state modal pembayaran, pilihan nominal tunai, hitungan kembalian, dan validasi submit.
  - `KdsBoard`: Menangani polling otomatis (5 detik) untuk antrean dapur, pembaruan status racikan, dan timer penuaan pesanan (*order aging badges*).
- Styling utilitas Tailwind CSS dengan integrasi ikon Lucide.

---

## 17. Backend Architecture
- Request Lifecycle dikawal oleh middleware otentikasi dan otorisasi peran.
- Service Layer memisahkan logika kalkulasi analitik dan mutasi inventori.
- Database Transaction (`DB::transaction`) diterapkan secara konsisten pada semua operasi multi-tabel (pembuatan order, deduksi stok, dan void).
- Row Locking (`lockForUpdate()`) mencegah konkurensi data stok pada transaksi kasir paralel.

---

## 18. Business Rules
1. Transaksi kasir dilarang diproses tanpa adanya Sesi Kasir aktif atas nama kasir yang bersangkutan.
2. Kasir tidak diizinkan menyelesaikan transaksi tunai jika nominal uang yang diterima kurang dari total tagihan.
3. Transaksi kasir ditolak jika stok bahan baku resep di inventaris tidak mencukupi untuk memenuhi pesanan.
4. Nilai stok bahan baku tidak boleh bernilai negatif dalam kondisi apa pun.
5. Pemotongan stok bahan baku harus mencatat log historis pada tabel `stock_movements`.
6. Pembatalan pesanan (Void) wajib mengembalikan stok bahan baku ke gudang secara utuh dan mencatat log pergerakan stok bertipe `in`.
7. Hanya pengguna dengan peran `barista` atau `admin` yang diizinkan memperbarui status pesanan pada layar KDS.
8. Karyawan yang telah dinonaktifkan oleh Admin (`is_active == false`) dilarang masuk ke sistem dan sesi aktifnya langsung diputus.

---

## 19. Validation Rules
- **Login:** Email valid, password required, verifikasi status `is_active`.
- **Checkout POS:**
  - `paymentMethod`: required, in: `cash,qris,card`.
  - `cashPaid`: jika cash wajib diisi, numerik, dan `min:total`.
  - `cart`: wajib memiliki item, stok bahan baku terverifikasi mencukupi.
- **Sesi Kasir:** `opening_balance` numerik min:0; `closing_balance` numerik min:0 saat tutup sesi.
- **Bahan Baku:** `name`, `category`, `unit` (in: `gram,ml,pcs,liter`), `cost_per_unit` numerik min:0, `stock_qty` min:0, `min_stock_qty` min:0.
- **Resep:** `product_id` valid, array recipes dengan `raw_material_id` valid, `qty_used` numerik min:0.01.
- **KDS Status Update:** User terotentikasi, role `barista` / `admin`, status in: `pending,paid,processing,ready,completed,cancelled`.

---

## 20. Error Handling
- Validasi Livewire mengembalikan pesan error langsung ke antarmuka pengguna (`$this->addError('cart', ...)` atau pesan field error).
- Operasi database dibungkus dalam blok `try { DB::transaction(...) } catch (\Throwable $e)` untuk mencegah data inkonsisten dan menampilkan notifikasi kesalahan.
- Akses tidak sah menghasilkan kode status HTTP standar: `401 Unauthorized` untuk request tanpa login, dan `403 Forbidden` untuk pelanggaran peran.

---

## 21. Security Analysis
- **Autentikasi & Password:** Hashing Bcrypt terstandar, regenerasi session ID saat login untuk mencegah session fixation.
- **Enforcement Akun Nonaktif:** Akun nonaktif diblokir di level controller dan middleware.
- **Otorisasi API & Komponen:** Metode pembaruan status KDS memvalidasi `Auth::check()` dan hak akses role, mencegah bypass otorisasi dari client-side request.
- **Injeksi SQL:** Dilindungi oleh Query Builder dan Eloquent parameter binding.
- **XSS & CSRF:** Proteksi token CSRF otomatis dari middleware Laravel dan sanitasi Blade templating (`{{ }}`).
- **Insecure Direct Object Reference (IDOR):** Sesi kasir dikunci pada user yang sedang login (`where('user_id', Auth::id())`).

---

## 22. Hardcoded vs Real Data
- **Real Data:**
  - Seluruh data transaksi, mutasi stok, resep, dan laporan penjualan bersumber langsung dari database relasional.
  - HPP dan margin keuntungan dikalkulasi dinamis berdasarkan harga beli terbaru pada `stock_movements` atau `cost_per_unit` bahan baku.
  - Sesi kasir menghitung data riil penjualan kas untuk menentukan selisih uang fisik.
- **Konfigurasi / Enum Terdefinisi:**
  - Satuan unit bahan baku: `['gram', 'ml', 'pcs', 'liter']`.
  - Varian ukuran: `['S', 'M', 'L', 'all']`.
  - Metode pembayaran: `['cash', 'qris', 'card']`.
  - Status pesanan: `['pending', 'paid', 'processing', 'ready', 'completed', 'cancelled']`.

---

## 23. Testing & Verification
Sistem diuji menggunakan test suite otomatis PHPUnit (`tests/Feature/BrewPosFlowTest.php`).
**Status Saat Ini: 17 Tests Passed (78 Assertions, 0 Failures)**.

Daftar Skenario yang Teruji:
1. `test_cashier_must_open_session_before_accessing_pos` — Memverifikasi kasir tanpa sesi aktif dilarang mengakses POS.
2. `test_cashier_can_open_session_and_access_pos` — Memverifikasi kasir berhasil membuka sesi dan membuka antarmuka POS.
3. `test_pos_checkout_creates_order_and_updates_kds` — Memverifikasi alur checkout standar dan broadcast ke KDS.
4. `test_barista_can_update_kds_status` — Memverifikasi barista dapat mengubah status pesanan (processing -> ready -> completed).
5. `test_cash_payment_fails_if_cash_paid_is_zero_or_insufficient` — Memverifikasi pembayaran tunai ditolak jika uang tunai nol atau kurang dari total tagihan.
6. `test_checkout_fails_and_does_not_deduct_when_stock_is_insufficient` — Memverifikasi sistem menolak checkout dan tidak memotong stok jika bahan baku tidak mencukupi (mencegah stok negatif).
7. `test_checkout_transaction_is_atomic_and_rolls_back_on_failure` — Memverifikasi transaksi checkout dibungkus DB::transaction dan tidak meninggalkan data yatim jika terjadi kegagalan (termasuk verifikasi rollback stok bahan baku dan tabel stock_movements).
8. `test_order_can_be_voided_and_restores_inventory` — Memverifikasi pesanan dapat dibatalkan, status berubah menjadi cancelled, stok bahan dipulihkan, dan log movement dicatat.
9. `test_kds_status_update_requires_authentication_and_authorization` — Memverifikasi method updateStatus di KDS menolak request unauthenticated (401), menolak peran non-barista (403), dan memvalidasi nama status.
10. `test_deactivated_employees_cannot_login` — Memverifikasi karyawan yang dinonaktifkan (`is_active = false`) ditolak saat login.
11. `test_hpp_and_profit_margin_reporting_formulas_are_accurate` — Memverifikasi akurasi matematis formula HPP (kombinasi varian + 'all') dan persentase margin keuntungan.
12. `test_cashier_can_view_active_session_order_history` — Memverifikasi kasir dengan sesi aktif dapat mengakses riwayat transaksi sesi berjalan di `/cashier/orders` (SRS POS-10), dan di-redirect jika belum membuka sesi.
13. `test_cashier_cannot_access_receipt_or_void_another_session_order_idor_protection` — Memverifikasi mitigasi IDOR pada endpoint struk dan void (kasir tidak dapat melihat atau membatalkan pesanan kasir/sesi lain; Admin memiliki akses penuh).
14. `test_kds_retains_completed_orders_within_two_minutes` — Memverifikasi antrean KDS mempertahankan pesanan selesai dalam rentang 2 menit dengan styling transparan sebelum di-purge (SRS KDS-05).
15. `test_checkout_is_protected_against_double_submit_and_events_dispatch_after_commit` — Memverifikasi proteksi in-flight flag $isProcessing untuk mencegah duplikasi order akibat multi-click, serta memastikan event LowStockAlert dan OrderPlaced mengimplementasikan ShouldDispatchAfterCommit.
16. `test_raw_material_cannot_be_saved_with_negative_stock` — Memverifikasi model RawMaterial menolak nilai negatif pada atribut stock_qty / min_stock_qty dengan melempar InvalidArgumentException.
17. `test_raw_material_decrement_cannot_reduce_stock_below_zero` — Memverifikasi override method decrement pada RawMaterial menolak pengurangan stok yang melebihi stok fisik saat ini dengan melempar RuntimeException.
18. `test_pos_catalog_displays_accurate_stock_status_for_recipes` — Memverifikasi katalog POS menampilkan status stok dinamis (Tersedia / Habis) berdasarkan bahan baku resep.
19. `test_pos_interface_prevents_adding_out_of_stock_item_to_cart` — Memverifikasi POS interface memblokir penambahan menu yang stok bahannya habis ke keranjang dan menolak peningkatan kuantitas yang melebihi stok.
20. `test_that_true_is_true` (Unit test)
21. `test_the_application_returns_a_successful_response` (Feature test)

---

## 24. Audit Temuan & Perbaikan Cacat Kritis (Resolved Defects)

### Bagian 1: Backend & Domain Logic Defects
#### 1. [CRITICAL] Non-Atomic Checkout Transactions
- **File:** `app/Http/Livewire/Pos/CheckoutModal.php`
- **Problem:** Pembuatan `Order`, iterasi `OrderItem`, dan deduksi stok dieksekusi tanpa transaksi database. Jika iterasi gagal di tengah jalan, order induk tertinggal tanpa item lengkap atau stok terpotong sebagian.
- **Resolution:** Seluruh blok pembuatan order dibungkus dalam `DB::transaction(function () { ... })`. Jika terjadi kesalahan, database di-rollback secara utuh.

#### 2. [CRITICAL] Stock Decrementing Into Negative Numbers
- **File:** `app/Services/InventoryDeductionService.php` & `CheckoutModal.php`
- **Problem:** Tidak ada validasi ketersediaan stok fisik bahan baku sebelum order diproses. Akibatnya, `stock_qty` dapat dipotong hingga bernilai minus/negatif.
- **Resolution:**
  - Menambahkan metode `validateStockForCart` untuk memvalidasi stok sebelum transaksi dibuat.
  - Menerapkan penguncian baris (`lockForUpdate()`) dan validasi `stock_qty >= totalUsed` di dalam `InventoryDeductionService`.

#### 3. [CRITICAL] Cash Payments Finalized with Insufficient Cash
- **File:** `app/Http/Livewire/Pos/CheckoutModal.php`
- **Problem:** Input `cashPaid` hanya divalidasi `nullable|numeric|min:0`. Kasir dapat menyelesaikan pesanan tunai dengan nominal Rp 0 atau nominal di bawah total belanja.
- **Resolution:** Menambahkan validasi bersyarat: jika `paymentMethod === 'cash'`, `cashPaid` wajib diisi dan memiliki nilai minimal sebesar total tagihan (`min:$total`).

#### 4. [CRITICAL] Mathematically Flawed HPP & Margin Formulas
- **File:** `app/Services/ReportService.php`
- **Problem:**
  - Pengelompokan `groupBy('size_variant')` memisahkan bahan baku `'all'` dari varian ukuran (S, M, L), menyebabkan HPP varian terhitung sangat rendah karena kehilangan bahan dasar.
  - Harga jual produk non-varian dipaksakan ke seluruh varian, mengabaikan tabel `product_variants.price`.
- **Resolution:** Merekonstruksi metode `getHppAndMargin()` agar untuk setiap varian ukuran, HPP dihitung dari akumulasi bahan varian ukuran tersebut **ditambah** bahan berstatus `'all'`, serta mencocokkan harga jual riil dari varian tersebut.

#### 5. [CRITICAL] Missing Order Voiding & Inventory Restoration
- **File:** `app/Models/Order.php`, `InventoryDeductionService.php`, `PosController.php`
- **Problem:** Tidak ada mekanisme untuk membatalkan pesanan (Void) dan mengembalikan bahan baku yang sudah terpotong ke inventaris.
- **Resolution:**
  - Menambahkan metode `Order::void()` dan `InventoryDeductionService::restoreOrderStock()`.
  - Mengembalikan stok (`increment`) dan mencatat log pergerakan stok masuk bertipe `'in'` dengan keterangan pembatalan pesanan.
  - Menyediakan endpoint POST `/cashier/orders/{order}/void`.

#### 6. [CRITICAL] Unauthenticated & Unvalidated KDS Status Updates
- **File:** `app/Http/Livewire/Kds/KdsBoard.php`
- **Problem:** Metode `updateStatus($orderId, $status)` dapat dipanggil oleh publik tanpa autentikasi, tanpa pengecekan peran barista/admin, dan tanpa validasi string status.
- **Resolution:**
  - Menambahkan pemeriksaan `Auth::check()` (abort 401 jika unauthenticated).
  - Menambahkan pemeriksaan peran `in_array(Auth::user()->role, ['barista', 'admin'])` (abort 403 jika tidak berhak).
  - Memvalidasi nilai `$status` terhadap enum yang sah.
  - Menolak perubahan status pada order yang sudah dibatalkan.

#### 7. [CRITICAL] Deactivated Employees Able to Log In
- **File:** `app/Http/Controllers/Auth/LoginController.php` & `RoleMiddleware.php`
- **Problem:** `Auth::attempt` hanya memeriksa email dan password tanpa memverifikasi flag `is_active`. Karyawan yang telah dinonaktifkan tetap dapat masuk.
- **Resolution:** Menambahkan verifikasi status `is_active` saat proses login dan di level `RoleMiddleware`. Pengguna nonaktif otomatis di-logout dan diarahkan kembali ke halaman login.

### Bagian 2: UI/UX & Security Hardening Remediation
#### 8. [FINDING-R01] [HIGH] Void Order Feature Had No UI
- **File:** `resources/views/cashier/receipt.blade.php`, `resources/views/cashier/orders/index.blade.php`
- **Problem:** Fitur `Order::void()` di backend tidak memiliki tombol pemicu atau modal konfirmasi di frontend kasir.
- **Resolution:** Menambahkan tombol "Batalkan Pesanan (Void Order)" dengan modal konfirmasi interaktif dan input alasan pembatalan pada halaman struk kasir dan tabel riwayat transaksi, serta menampilkan banner status pesanan dibatalkan jika order sudah di-void.

#### 9. [FINDING-R02] [HIGH] Silent Checkout Validation & Broken Shortage Calculation
- **File:** `resources/views/livewire/pos/checkout-modal.blade.php`
- **Problem:** Modal checkout tidak menampilkan `@error('cart')` dan `@error('cashPaid')`, menampilkan "Kekurangan Uang: Rp 0" saat uang tunai kurang, dan tombol submit tetap aktif.
- **Resolution:**
  - Menambahkan banner feedback error `@error('cart')` dan pesan kesalahan inline `@error('cashPaid')`.
  - Memperbaiki kalkulasi selisih nominal: `$deficit = $total - $paidVal` ditampilkan secara tepat sebagai kekurangan uang.
  - Men-disable tombol "Proses Pembayaran" jika metode cash dipilih namun uang yang diinputkan kurang dari total belanja.

#### 10. [FINDING-R03] [HIGH] Absence of Active Session Order History View
- **File:** `resources/views/cashier/orders/index.blade.php`, `resources/views/admin/orders/index.blade.php`, `app/Http/Controllers/Cashier/PosController.php`, `routes/web.php`
- **Problem:** Kebutuhan SRS POS-10 (riwayat pesanan sesi aktif kasir) tidak tersedia, dan tautan di sidebar admin mengarah ke `javascript:void(0)`.
- **Resolution:**
  - Membuat view `cashier/orders/index.blade.php` dengan rincian transaksi sesi berjalan, status badge, tombol cetak struk, dan aksi void.
  - Membuat view `admin/orders/index.blade.php` dengan fitur pencarian nomor order, filter status pesanan, dan paginasi.
  - Mendaftarkan route `/cashier/orders` dan `/admin/orders`, serta menghubungkan tombol riwayat pada top bar kasir dan sidebar admin.

#### 11. [FINDING-R04] [MEDIUM] IDOR Vulnerability on Receipt and Void Endpoints
- **File:** `app/Http/Controllers/Cashier/PosController.php`, `routes/web.php`
- **Problem:** Kasir dapat mengakses struk atau membatalkan pesanan milik sesi atau kasir lain dengan mengganti parameter ID order pada URL.
- **Resolution:** Menambahkan validasi otorisasi di `PosController@receipt` dan `PosController@void`: jika user berstatus `cashier`, order wajib dimiliki oleh kasir atau sesi yang sedang aktif. Jika tidak cocok, sistem mengembalikan `403 Forbidden`. Akses untuk `admin` tetap diizinkan.

#### 12. [FINDING-R05] [MEDIUM] KDS Immediate Purging of Completed Orders
- **File:** `app/Http/Livewire/Kds/KdsBoard.php`, `resources/views/livewire/kds/kds-board.blade.php`
- **Problem:** Pesanan berstatus `completed` langsung hilang dari layar antrean barista, bertentangan dengan SRS KDS-05 yang mensyaratkan retensi selama 2 menit dengan tampilan redup (*dimming*).
- **Resolution:** Memodifikasi query di `KdsBoard::render()` untuk menyertakan order `completed` dengan `updated_at >= now()->subMinutes(2)`, dan merender kartu dengan transparansi 50%, skala abu-abu (*grayscale*), badge `SELESAI`, dan keterangan `Pesanan Selesai (Fading 2m)`.

#### 13. [FINDING-R06] [LOW] Inconsistent Design Tokens & Emoji Usage
- **File:** `resources/views/admin/dashboard.blade.php`
- **Problem:** Terdapat penggunaan karakter emoji pada kartu metrik dan header dashboard admin, melanggar konsistensi token desain warm obsidian & Lucide icons.
- **Resolution:** Mengganti seluruh emoji dengan ikon Lucide SVG standar (`wallet`, `shopping-bag`, `alert-triangle`, `users`, `bell-ring`, `award`, `coffee`, `zap`, `package`, `boxes`, `book-open`, `trending-up`).

### Bagian 3: Hardening Checkout Atomic (Audit F-CHK)
#### 14. [F-CHK-01] [MEDIUM] Missing Double-Click & Double-Submit Protection on Checkout Button
- **File:** `resources/views/livewire/pos/checkout-modal.blade.php`, `app/Http/Livewire/Pos/CheckoutModal.php`
- **Problem:** Tombol "Proses Pembayaran" tidak memiliki `wire:loading.attr="disabled"` dan komponen tidak memiliki flag status proses. Klik ganda cepat dapat mengirim request paralel dan memproses pesanan ganda.
- **Resolution:** Menambahkan properti `$isProcessing` di `CheckoutModal.php` dengan pengecekan guard di awal `createOrder()` dan pelepasan flag di blok `finally`. Pada Blade template, tombol aksi diberi `wire:loading.attr="disabled"`, `wire:target="createOrder"`, atribut disabled reaktif `@if($cannotSubmitCash || $isProcessing)`, serta animasi spinner pemrosesan.

#### 15. [F-CHK-02] [LOW] Premature WebSocket Event Dispatch Inside Uncommitted Transaction
- **File:** `app/Events/LowStockAlert.php`, `app/Events/OrderPlaced.php`, `app/Services/InventoryDeductionService.php`
- **Problem:** `LowStockAlert` dipicu di dalam `OrderItemObserver::created()` yang berjalan di dalam `DB::transaction()`. Jika item berikutnya gagal dan terjadi rollback, notifikasi stok kritis phantom tetap terkirim via WebSockets.
- **Resolution:** Mengimplementasikan contract `Illuminate\Contracts\Events\ShouldDispatchAfterCommit` pada event `LowStockAlert` dan `OrderPlaced`, serta membungkus pemanggilan event di dalam `DB::afterCommit(...)` untuk menjamin event hanya dibroadcast setelah transaksi database berhasil di-commit.

#### 16. [F-CHK-03] [LOW] Lack of Deadlock Automatic Retry Configuration
- **File:** `app/Http/Livewire/Pos/CheckoutModal.php`
- **Problem:** `DB::transaction()` dieksekusi tanpa konfigurasi percobaan ulang (default 1 attempt). Pada kondisi traffic tinggi di mana banyak kasir mengakses baris bahan baku yang sama secara bersamaan, deadlock InnoDB dapat menyebabkan transaksi langsung gagal tanpa retry.
- **Resolution:** Menambahkan parameter percobaan ulang otomatis sebanyak 3 kali: `DB::transaction(callback, 3)`.

### Bagian 4: Hardening Non-Negative Stock Guarantee (DoD A.3)
#### 17. [A3-DB-01] [CRITICAL] Missing Database Engine CHECK Constraint on Stock Columns
- **File:** `database/migrations/2026_10_15_000001_add_check_constraints_to_raw_materials_table.php`
- **Problem:** Kolom `stock_qty` dan `min_stock_qty` bertipe decimal bertanda (*signed*) tanpa batasan database level. Jika kueri kustom atau kegagalan isolasi terjadi, MySQL tetap mengizinkan nilai minus/negatif tersimpan di disk.
- **Resolution:** Membuat migration untuk menerapkan CHECK constraint `CHECK (stock_qty >= 0)` dan `CHECK (min_stock_qty >= 0)` di level database engine MySQL 8.0+.

#### 18. [A3-MDL-01] [HIGH] Model-Level Defense on RawMaterial Saving and Decrement
- **File:** `app/Models/RawMaterial.php`
- **Problem:** Pemanggilan `$rawMaterial->decrement('stock_qty', $qty)` mengeksekusi kueri langsung tanpa validasi model jika dipanggil di luar service, dan event `saving` tidak memvalidasi nilai negatif.
- **Resolution:** Menambahkan hook `booted()` dengan `static::saving(...)` yang melempar `\InvalidArgumentException` jika `stock_qty < 0` atau `min_stock_qty < 0`. Meng-override method `decrement()` untuk memastikan `$freshStock >= $amount` sebelum query dieksekusi, melempar `\RuntimeException` jika stok tidak mencukupi.

#### 19. [A3-UI-01] [MEDIUM] Static "Tersedia" Indicator in POS Catalog
- **File:** `resources/views/livewire/pos/pos-interface.blade.php`, `app/Http/Livewire/Pos/PosInterface.php`, `app/Models/Product.php`
- **Problem:** Katalog POS menampilkan badge statis "Tersedia" pada seluruh kartu menu kopi meskipun bahan baku resepnya telah habis (0 stock). Kasir dapat memasukkan menu habis ke keranjang tanpa feedback hingga tahap checkout.
- **Resolution:** 
  - Menambahkan method `isAvailable($size, $quantity)` dan `getStockStatus($size)` pada model `Product` untuk menghitung ketersediaan bahan baku resep secara dinamis.
  - Memperbarui `PosInterface::render()` untuk meng-eager load relasi `recipes.rawMaterial`.
  - Pada Blade, merender badge dinamis: `Tersedia` (hijau), `Stok Menipis` (kuning), atau `Habis` (merah redup dengan filter grayscale dan ikon ban).
  - Melindungi `addToCart()` dan `updateQty()` di `PosInterface.php` agar menolak penambahan item ke keranjang yang melebihi stok bahan baku dan menampilkan flash warning.

### Bagian 5: Hardening Concurrency & Locking Safety (DoD A.4)
#### 20. [A4-CONC-01] [CRITICAL] Deadlock Prevention via Deterministic Upfront Lock Ordering
- **File:** `app/Services/InventoryDeductionService.php`, `app/Http/Livewire/Pos/CheckoutModal.php`
- **Problem:** Pada multi-item checkout, baris bahan baku dikunci secara individual saat setiap item dibuat oleh observer. Jika transaksi A membutuhkan Material 2 lalu 1, sementara transaksi B membutuhkan Material 1 lalu 2, InnoDB akan mendeteksi circular wait deadlock dan membatalkan transaksi.
- **Resolution:** 
  - Mengimplementasikan `getRawMaterialRequirementsForCart(array $cart)` yang mengagregasi total kebutuhan bahan dan mengurutkan kunci ID material secara menaik (`ksort()`).
  - Mengimplementasikan `validateAndLockStockForCart(array $cart)` yang mengunci seluruh baris bahan baku yang dibutuhkan keranjang sekaligus di awal transaksi dengan pengurutan ID menaik: `RawMaterial::whereIn('id', $ids)->orderBy('id', 'asc')->lockForUpdate()->get()`.
  - Karena seluruh transaksi checkout dan void selalu mengunci baris dalam urutan ascending ID yang deterministik, circular wait deadlock tereliminasi secara matematis.

#### 21. [A4-CONC-02] [HIGH] Atomic Session Cache Lock Against Double-Click & Parallel Submissions
- **File:** `app/Http/Livewire/Pos/CheckoutModal.php`
- **Problem:** State in-memory komponen `$isProcessing` hanya aktif per-instance PHP request tunggal. Jika dua HTTP request checkout tiba secara bersamaan akibat double-click atau koneksi lambat sebelum browser menerima response, kedua request dapat lolos validasi dan membuat order ganda serta memotong stok dua kali.
- **Resolution:** Mengakuisisi distributed atomic cache lock `Cache::lock("checkout_session_{$session->id}", 10)` sebelum pemrosesan order. Request bersamaan berikutnya akan langsung ditolak dengan pesan error *"Transaksi sedang diproses. Mohon tunggu sejenak."* tanpa menyentuh database atau stok. Lock dilepaskan pada blok `finally`.

#### 22. [A4-CONC-03] [HIGH] Cashier Session Concurrency State Re-Verification
- **File:** `app/Http/Livewire/Pos/CheckoutModal.php`
- **Problem:** Sesi kasir yang valid saat request masuk bisa saja ditutup oleh admin atau ditutup dari tab lain tepat saat transaksi checkout sedang berlangsung.
- **Resolution:** Di dalam blok `DB::transaction()`, dilakukan kueri `CashierSession::where('id', $session->id)->where('status', 'open')->lockForUpdate()->first()`. Jika sesi telah ditutup di tengah proses, transaksi langsung di-abort dengan `\RuntimeException`.

#### 23. [F-CNC-01] [HIGH] Idempotent & Concurrency-Safe Order Voiding via Pessimistic Row Lock
- **File:** `app/Models/Order.php`, `app/Services/InventoryDeductionService.php`
- **Problem:** Pemeriksaan status order pada method `void()` sebelumnya berada di luar transaksi database tanpa baris lock. Jika dua request void dikirimkan secara bersamaan, keduanya bisa lolos pengecekan dan memanggil `restoreOrderStock()`, menyebabkan pengembalian stok ganda (*phantom stock inflation*).
- **Resolution:**
  - Memindahkan seluruh logika pengecekan status ke dalam `DB::transaction(..., 3)` dengan row locking pesimistik:
    ```php
    $order = Order::where('id', $this->id)->lockForUpdate()->firstOrFail();
    if ($order->status === 'cancelled') {
        throw new \DomainException("Order #{$order->order_number} sudah dibatalkan sebelumnya.");
    }
    ```
    Request void kedua akan mendeteksi status telah `cancelled` dan melempar `\DomainException` tanpa menyentuh stok.
  - Memperbarui `restoreOrderStock(Order $order)` untuk mengagregasi kebutuhan restorasi dan mengunci baris bahan baku secara deterministik menaik (`orderBy('id', 'asc')->lockForUpdate()`), mencegah deadlock dengan transaksi kasir yang sedang berjalan.

#### 24. [F-CNC-02] [MEDIUM] Serialized KDS Status Transitions Preventing Overwrites on Voided Orders
- **File:** `app/Http/Livewire/Kds/KdsBoard.php`, `app/Http/Controllers/Inventory/RestockController.php`
- **Problem:** Jika Barista menekan tombol status di layar KDS pada detik yang persis sama ketika Kasir menekan tombol "Void" di meja kasir, tanpa isolasi atomik status order yang sudah dibatalkan bisa ditimpa kembali menjadi `completed` atau `ready`.
- **Resolution:** Membungkus transisi status `KdsBoard::updateStatus` di dalam `DB::transaction(..., 3)` dengan pessimistic row lock `Order::lockForUpdate()->findOrFail($orderId)`. KDS memvalidasi bahwa order dengan status `cancelled` tidak dapat diubah statusnya, menolak request barista dengan pesan error validasi yang jelas dan mempertahankan integritas laporan keuangan. Operasional restock `RestockController::store` juga dibungkus dalam transaksi dengan `RawMaterial::lockForUpdate()`.

### Bagian 6: Order Void & Inventory Restoration End-to-End (DoD A.5 & A.6)
#### 25. [A5-VOID-01] [HIGH] Full UI & Endpoint Integration for Order Voiding
- **File:** `resources/views/admin/orders/index.blade.php`, `resources/views/cashier/orders/index.blade.php`, `routes/web.php`, `app/Http/Controllers/Cashier/PosController.php`
- **Problem:** Sebelumnya hanya backend model yang memiliki method `void()`, sementara portal Admin tidak memiliki tombol atau modal konfirmasi void dengan input alasan pembatalan.
- **Resolution:**
  - Menambahkan tombol Void dan modal konfirmasi interaktif pada tabel riwayat transaksi Admin (`admin/orders/index.blade.php`) dengan textarea wajib isi untuk alasan pembatalan (*cancellation reason*).
  - Memperbaiki penamaan route admin orders di `routes/web.php` menjadi `orders.index` (sehingga teresolusi dengan tepat sebagai `admin.orders.index` di bawah prefix grup).
  - Melindungi endpoint `POST /cashier/orders/{order}/void` dengan otorisasi berbasis kepemilikan sesi untuk kasir dan hak akses penuh untuk admin.

#### 26. [A6-REST-01] [CRITICAL] Proportional Recipe Inventory Restoration & Audit Trail
- **File:** `app/Models/Order.php`, `app/Services/InventoryDeductionService.php`
- **Problem:** Pengembalian stok saat void harus menjamin akurasi restorasi pada pesanan dengan banyak item dan multi-resep, serta mencatat alasan pembatalan ke log audit `stock_movements`.
- **Resolution:**
  - `Order::void(string $reason)` meneruskan `$reason` ke `restoreOrderStock($order, $reason)`.
  - `restoreOrderStock` mengagregasi total bahan baku terpakai dari semua `orderItems` (mendukung size variant `M`, `L`, maupun `all`), mengunci baris bahan baku dengan pengurutan ID menaik untuk mencegah deadlock, lalu menambahkan kembali kuantitas fisik ke `stock_qty`.
  - Membuat catatan log mutasi inventori `StockMovement` bertipe `in` dengan keterangan lengkap: `"Void/Batal order #TRX-XXXX (Alasan pembatalan)"`.
  - Pesanan dengan status `cancelled` otomatis dikeluarkan dari antrean KDS aktif dan dikeluarkan dari perhitungan ringkasan omset/laporan laba rugi di `ReportService`.

---

## 25. Technical Debt
- **Event Broadcasting Driver:** Saat ini konfigurasi default masih menggunakan driver `log`. Untuk lingkungan produksi multi-device, perlu diarahkan ke instance Pusher atau Soketi self-hosted.
- **Cart Session Persistence:** Keranjang belanja pada `PosInterface` saat ini disimpan dalam state Livewire komponen. Jika kasir me-refresh browser, isi keranjang akan ter-reset.

---

## 26. Architecture Risks
- **High Concurrency Stock Locks:** Penggunaan `lockForUpdate()` pada tabel `raw_materials` menjamin integritas data stok, namun pada volume transaksi yang sangat tinggi (ratusan transaksi per detik), transaksi dapat mengalami antrean *lock wait timeout*. Dianjurkan menjaga durasi transaksi sependek mungkin.

---

## 27. Recommended Improvements
1. **Offline Mode & IndexedDB:** Menambahkan Service Worker dan caching IndexedDB pada halaman POS agar kasir tetap dapat menginput pesanan saat jaringan internet lokal terputus sementara.
2. **Thermal ESC/POS Hardware Integration:** Menyediakan endpoint cetak langsung via WebSocket atau WebUSB API ke printer thermal struk mini tanpa harus melalui dialog cetak browser.
3. **Audit Log & Activity Tracking:** Menambahkan pencatatan riwayat siapa yang membatalkan order dan riwayat perubahan resep menu.

---

## 28. Unknown / Unverified Areas
- **Integrasi Hardware Thermal Printer Fisik:** Sistem saat ini menyediakan view receipt HTML (`cashier/receipt.blade.php`) dengan styling CSS print. Integrasi ke driver USB/Bluetooth RAW thermal printer spesifik di lokasi kedai masih bergantung pada konfigurasi sistem operasi terminal kasir.
- **Hardware Barcode Scanner:** Telah diimplementasikan dan diverifikasi (`POS-05`). Scanner hardware mengirimkan keystroke barcode yang diakhiri karakter Enter (`wire:keydown.enter.prevent="scanBarcode"`), mencari produk aktif berdasarkan barcode, memvalidasi ketersediaan bahan resep, dan langsung menambahkan produk ke keranjang belanja kasir.

---

## 29. Docker Containerized Development Environment
BrewPOS telah sepenuhnya dimigrasi ke arsitektur container Docker (menggantikan kebutuhan XAMPP lokal Apache/MySQL). Seluruh stack dapat dijalankan mandiri dengan satu perintah `docker compose up -d`.

### 1. Topology Layanan Container
| Service | Container Name | Base Image | Port Host | Fungsi |
| :--- | :--- | :--- | :--- | :--- |
| `app` | `brewpos-app` | `php:8.2-fpm-alpine` | Internal (9000) | PHP-FPM 8.2 runtime dengan ekstensi `pdo_mysql`, `mbstring`, `intl`, `exif`, `pcntl`, `bcmath`, `gd`, `zip`, `opcache`, dan Composer |
| `web` | `brewpos-web` | `nginx:alpine` | `8000:80` | Web server Nginx routing request ke `app:9000` |
| `mysql` | `brewpos-mysql` | `mysql:8.0` | `3306:3306` | MySQL 8.0 database engine dengan persistent volume `brewpos_mysql_data` |
| `phpmyadmin` | `brewpos-phpmyadmin` | `phpmyadmin:5.2.1-apache` | `8080:80` | Database management GUI terhubung ke `brewpos-mysql` |

### 2. URL Akses & Kredensial
- **Aplikasi Web BrewPOS:** [http://localhost:8000](http://localhost:8000)
- **phpMyAdmin Database Manager:** [http://localhost:8080](http://localhost:8080)
- **Koneksi Database MySQL:**
  - Host (dari dalam Docker): `mysql:3306`
  - Host (dari host Windows / DBeaver / Navicat): `localhost:3306`
  - Database: `brewpos`
  - Username: `root`
  - Password: `secret`
- **Default Seeded Accounts:**
  - Admin: `admin@brewpos.com` / `password123`
  - Kasir: `cashier@brewpos.com` / `password123`
  - Barista: `barista@brewpos.com` / `password123`

### 3. Otomatisasi Entrypoint Container (`docker/entrypoint.sh`)
Setiap kali container `brewpos-app` dinyalakan, entrypoint secara otomatis:
1. Menyiapkan folder direktori `storage/` dan `bootstrap/cache` dengan permission write yang tepat.
2. Melakukan polling koneksi database hingga MySQL siap (`healthy`).
3. Menjalankan migrasi database otomatis (`php artisan migrate --force`).
4. Menjalankan seeder awal secara idempoten (`php artisan db:seed --force`).
5. Menjalankan proses master PHP-FPM.

### 4. Perintah Operasional Docker
```bash
# Menyalakan seluruh service di background
docker compose up -d

# Memeriksa status kesehatan container
docker compose ps

# Melihat logs container secara live
docker compose logs -f app
docker compose logs -f web

# Menjalankan perintah artisan di dalam container
docker compose exec app php artisan [command]

# Menjalankan automated test suite di dalam container
docker compose exec app php artisan test

# Mematikan seluruh service Docker
docker compose down
```

---

## 30. DoD POS Verification Matrix (100% Complete)

| Kriteria DoD POS | Status | Implementasi & File Terkait | Bukti Verifikasi & Automated Test |
| :--- | :---: | :--- | :--- |
| **1. Cashier session reliable** | `VERIFIED_PASS` | `app/Http/Controllers/Cashier/CashierSessionController.php`<br>`app/Models/CashierSession.php`<br>`app/Http/Controllers/Cashier/PosController.php` | • Shift wajib dibuka sebelum akses `/pos` (mencegah transaksi yatim).<br>• Rekonsiliasi kas: `expected_cash = opening_balance + cash_sales`, pencatatan selisih (`difference`).<br>• Test: `test_cashier_must_open_session_before_accessing_pos`, `test_cashier_can_open_session_and_access_pos`. |
| **2. Payment works** | `VERIFIED_PASS` | `app/Http/Livewire/Pos/CheckoutModal.php`<br>`resources/views/livewire/pos/checkout-modal.blade.php` | • Metode bayar: Cash, QRIS, Kartu Debit/Kredit.<br>• Validasi server: `cashPaid >= total`, kalkulasi uang kembalian instan, tombol nominal cepat (`Uang Pas`, `50.000`, `100.000`, `200.000`).<br>• Distributed lock `Cache::lock("checkout_session_...")` mencegah double checkout.<br>• Test: `test_cash_payment_fails_if_cash_paid_is_zero_or_insufficient`, `test_checkout_is_protected_against_concurrent_duplicate_requests_via_session_lock`. |
| **3. Receipt works** | `VERIFIED_PASS` | `app/Http/Controllers/Cashier/PosController.php@receipt`<br>`app/Models/OrderItem.php`<br>`resources/views/cashier/receipt.blade.php` | • Layout cetak thermal 80mm standar POS (`@media print`).<br>• Rincian item, varian ukuran, nomor nota, info kasir, session ID, pajak, diskon, dan status void.<br>• Proteksi IDOR ketat: kasir lain dilarang mengakses struk kasir berbeda (`403 Forbidden`).<br>• Eager loading diperbaiki: memuat `orderItems.variant` dan mendefinisikan alias `productVariant` pada `OrderItem`.<br>• Test: `test_cashier_cannot_access_receipt_or_void_another_session_order_idor_protection`, `test_receipt_renders_correctly_with_order_items_and_variants_without_500_crash`. |
| **4. Order history works** | `VERIFIED_PASS` | `app/Http/Controllers/Cashier/PosController.php@orders`<br>`resources/views/cashier/orders/index.blade.php`<br>`resources/views/admin/orders/index.blade.php` | • Kasir memiliki riwayat shift aktif di `/cashier/orders` lengkap dengan status, tautan cetak struk, dan tombol modal pembatalan (Void).<br>• Admin memiliki riwayat lintas sesi di `/admin/orders` lengkap dengan filter tanggal & status.<br>• Test: `test_cashier_can_view_active_session_order_history`, `test_admin_orders_page_renders_void_button_and_confirmation_modal`. |
| **5. Barcode works** | `VERIFIED_PASS` | `database/migrations/2026_10_15_000002_add_barcode_to_products_table.php`<br>`app/Models/Product.php`<br>`app/Http/Controllers/Admin/ProductController.php`<br>`resources/views/admin/products/create.blade.php`<br>`resources/views/admin/products/edit.blade.php`<br>`app/Http/Livewire/Pos/PosInterface.php`<br>`resources/views/livewire/pos/pos-interface.blade.php` | • Kolom `barcode` unik dan opsional di tabel `products`.<br>• Form admin create/edit mendukung input & validasi barcode unik.<br>• Live search POS mencari via `name` maupun `barcode`.<br>• Event handler `wire:keydown.enter.prevent="scanBarcode"` menerima input hardware scanner langsung menambahkan menu ke keranjang dengan pengecekan stok otomatis.<br>• Badge barcode tampil pada kartu katalog menu kasir.<br>• Test: `test_admin_can_create_and_update_product_with_unique_barcode`, `test_pos_can_search_and_scan_product_by_barcode`. |

> **Catatan Stabilitas Frontend (F-CDN-01 & F-CDN-02 Resolved)**: Seluruh referensi script Lucide Icons di seluruh aplikasi (termasuk view mandiri `cashier/session/open.blade.php` dan `close.blade.php`) telah dipin ke versi stabil `https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js`, mengeliminasi syntax error `Identifier 'Infinity' has already been declared` dan menjamin konsistensi render ikon.

---

## 31. DoD INVENTORY Verification Matrix (100% Complete)

| Kriteria DoD INVENTORY | Status | Implementasi & File Terkait | Bukti Verifikasi & Automated Test |
| :--- | :---: | :--- | :--- |
| **1. Recipe deduction works** | `VERIFIED_PASS` | `app/Services/InventoryDeductionService.php`<br>`app/Observers/OrderItemObserver.php`<br>`app/Models/Recipe.php`<br>`app/Models/RawMaterial.php` | • Potong stok otomatis saat pesanan dibuat (`OrderItemObserver::created`).<br>• Mendukung multi-size variant: `S`, `M`, `L`, dan `all`. Kuantitas resep dikalikan kuantitas order item.<br>• Transaksi atomik & `lockForUpdate()` dengan pengurutan ID bahan baku menaik (`orderBy('id', 'asc')`) mencegah deadlock dan race condition.<br>• Mencegah stok menjadi negatif (`DomainException`).<br>• Test: `test_checkout_deducts_inventory_based_on_recipes_for_single_and_multi_size_items`, `test_stock_cannot_become_negative_during_checkout_and_rolls_back_entire_order`. |
| **2. Restock works** | `VERIFIED_PASS` | `app/Http/Controllers/Inventory/RestockController.php`<br>`resources/views/inventory/restock/create.blade.php`<br>`app/Models/StockMovement.php` | • Penambahan stok bahan baku fisik dari supplier.<br>• Transaksi atomik (`DB::transaction(..., 3)`) dengan `lockForUpdate()` pada `RawMaterial`.<br>• Sinkronisasi otomatis HPP (`cost_per_unit = $costPerUnit`) di `RawMaterial` berdasarkan harga pembelian terbaru.<br>• Pencatatan mutasi masuk `StockMovement` (type: `'in'`) dengan catatan supplier dan PIC kasir/admin (`created_by`).<br>• Test: `test_admin_can_restock_raw_material_and_updates_cost_and_records_movement`. |
| **3. Adjustment works** | `VERIFIED_PASS` | `app/Http/Controllers/Inventory/RawMaterialController.php@adjust`<br>`resources/views/inventory/raw-materials/index.blade.php` | • Penyesuaian stok manual akibat selisih fisik / opname / tumpah / kadaluarsa.<br>• Modal penyesuaian stok interaktif di halaman bahan baku (`index.blade.php`) dengan ikon Lucide bersih.<br>• Mewajibkan alasan penyesuaian (`reason`).<br>• Transaksi aman dengan `lockForUpdate()`, menghitung selisih kuantitas (`diff = newQty - currentQty`), mencatat `StockMovement` (type: `'adjust'`).<br>• Memicu event `LowStockAlert::dispatchAfterCommit()` jika stok hasil penyesuaian berada di bawah atau sama dengan batas minimum.<br>• Test: `test_admin_can_adjust_stock_manually_with_reason_and_records_movement`. |
| **4. Stock movements auditable** | `VERIFIED_PASS` | `app/Http/Controllers/Inventory/RawMaterialController.php@movements`<br>`resources/views/admin/inventory/movements.blade.php`<br>`routes/web.php`<br>`resources/views/components/sidebar.blade.php` | • Halaman log mutasi stok lengkap dan terperinci di `/admin/inventory/movements`.<br>• Filter fleksibel: filter tipe mutasi (`in`, `out`, `adjust`), filter bahan baku, dan pencarian referensi/catatan.<br>• Audit trail transparan: Timestamp, Bahan Baku, Badge Tipe, Kuantitas (+/-), Harga Satuan, Total Biaya, Referensi/Keterangan, dan PIC Operator/Kasir.<br>• Tautan navigasi sidebar "Log Mutasi Stok" dan tombol pintas di daftar bahan baku.<br>• Test: `test_stock_movements_audit_log_is_viewable_and_filterable_by_admin`. |
| **5. Low stock works** | `VERIFIED_PASS` | `app/Models/RawMaterial.php`<br>`app/Events/LowStockAlert.php`<br>`app/Http/Controllers/Admin/DashboardController.php`<br>`resources/views/admin/dashboard.blade.php`<br>`resources/views/livewire/pos/pos-interface.blade.php` | • Peringatan dini bahan baku menipis (`stock_qty <= min_stock_qty`).<br>• Metrik status stok di Model (`RawMaterial::getStockStatus()` -> `'Aman'`, `'Hampir Habis'`, `'Kritis'`).<br>• Event `LowStockAlert` dengan `ShouldDispatchAfterCommit` mencegah false alert pada rollback transaksi.<br>• Dashboard admin menampilkan counter bahan kritis dan alert banner merah dengan daftar bahan yang menipis.<br>• POS interface menampilkan badge status ketersediaan bahan resep (`Tersedia`, `Stok Menipis`, `Habis`) dan menonaktifkan klik menu jika bahan baku habis.<br>• Test: `test_low_stock_status_and_dashboard_alerts_reflect_accurately`. |

---

## 32. DoD KDS Verification Matrix (100% Complete)

| Kriteria DoD KDS | Status | Implementasi & File Terkait | Bukti Verifikasi & Automated Test |
| :--- | :---: | :--- | :--- |
| **1. New order appears** | `VERIFIED_PASS` | `app/Events/OrderPlaced.php`<br>`app/Http/Livewire/Kds/KdsBoard.php`<br>`resources/views/livewire/kds/kds-board.blade.php` | • Pesanan baru dari checkout kasir (`status = 'paid'`) langsung tampil di antrean barista.<br>• Tiket pesanan menampilkan nomor nota `#TRX-...`, jam order, rincian menu, ukuran, level gula, catatan khusus kasir, badge `🆕 BARU`, indikator waktu tunggu bulat (`⚡ Fresh (< 1m)`), serta tombol "Mulai Racik Pesanan".<br>• Test: `test_pos_checkout_creates_order_and_updates_kds`, `test_new_paid_order_appears_on_kds_board_with_items_and_aging_tag`. |
| **2. Processing works** | `VERIFIED_PASS` | `app/Http/Livewire/Kds/KdsBoard.php@updateStatus`<br>`resources/views/livewire/kds/kds-board.blade.php` | • Barista/admin menekan "Mulai Racik Pesanan" mengubah status menjadi `processing`.<br>• Dilindungi otorisasi role (`barista` & `admin`) dan transaksi pesimistik `Order::lockForUpdate()` agar tidak dapat ditimpa jika order dibatalkan kasir secara konkuren.<br>• Kartu pesanan otomatis berubah berbingkai kuning keemasan dengan badge `⚡ RACIK` dan tombol berganti menjadi "Tandai Siap Disajikan".<br>• Test: `test_barista_can_update_kds_status`, `test_kds_lifecycle_full_transitions_and_fading_retention`. |
| **3. Ready works** | `VERIFIED_PASS` | `app/Http/Livewire/Kds/KdsBoard.php@updateStatus`<br>`resources/views/livewire/kds/kds-board.blade.php` | • Barista menekan "Tandai Siap Disajikan" saat minuman/makanan selesai dibuat.<br>• Status pesanan menjadi `ready`.<br>• Kartu pesanan otomatis berubah berbingkai hijau emerald dengan badge `✓ SIAP` dan tombol berganti menjadi "Selesai / Arsipkan".<br>• Test: `test_barista_can_update_kds_status`, `test_kds_lifecycle_full_transitions_and_fading_retention`. |
| **4. Completed works** | `VERIFIED_PASS` | `app/Http/Livewire/Kds/KdsBoard.php`<br>`resources/views/livewire/kds/kds-board.blade.php` | • Barista menekan "Selesai / Arsipkan" ketika pesanan diserahkan ke pelanggan.<br>• Sesuai spesifikasi SRS KDS-05, pesanan `completed` tetap dipertahankan di layar selama 2 menit dalam status redup (grayscale, opacity 50%, badge `✓ SELESAI`, pill `Pesanan Selesai (Fading 2m)`) untuk mencegah disorientasi barista saat rush hour.<br>• Setelah melewati batas 2 menit (`updated_at < now()->subMinutes(2)`), pesanan otomatis hilang dari layar KDS.<br>• Test: `test_kds_retains_completed_orders_within_two_minutes`, `test_kds_lifecycle_full_transitions_and_fading_retention`. |
| **5. Real-time update works** | `VERIFIED_PASS` | `app/Http/Livewire/Kds/KdsBoard.php`<br>`resources/views/livewire/kds/kds-board.blade.php`<br>`resources/views/barista/kds.blade.php`<br>`routes/web.php` | • Pembaruan live otomatis via polling Livewire `wire:poll.5s` dan tombol manual refresh antrian.<br>• Counter antrian ringkasan live di header KDS: Total Aktif, Baru, Sedang Diracik, dan Siap Disajikan.<br>• Jam digital real-time berdetik setiap detik (`#digital-clock`).<br>• Audio chime synthesizer via Web Audio API (SRS KDS-04 audio cues).<br>• Pinned script Lucide Icons (`0.344.0`) dengan listener morph DOM Livewire 3.<br>• Fallback aman `window.Echo` dengan `socketId` guard sehingga 0 console error/warning pada browser.<br>• Akses rute `/kds` terbuka untuk `barista` dan `admin`, dilengkapi shortcut KDS di sidebar admin.<br>• Test: `test_admin_and_barista_can_access_kds_view_route_while_cashier_and_guest_cannot`, `test_kds_queue_summary_stats_reflect_accurately`. |

---

## 33. DoD REPORT Verification Matrix (100% Complete)

| Kriteria DoD REPORT | Status | Implementasi & File Terkait | Bukti Verifikasi & Automated Test |
| :--- | :---: | :--- | :--- |
| **1. Sales** | `VERIFIED_PASS` | `app/Services/ReportService.php@getSalesSummary`<br>`resources/views/admin/reports/index.blade.php` | • Agregasi metrik pendapatan kotor (`total_revenue`), jumlah transaksi (`transaction_count`), dan nilai rata-rata pesanan (`average_order_value` / AOV).<br>• Filter periode dinamis: Hari Ini, Minggu Ini, Bulan Ini, 30 Hari Terakhir, dan Tanggal Kustom (`start` s/d `end`).<br>• Secara ketat mengecualikan transaksi yang dibatalkan kasir (`cancelled`) dari perhitungan omset.<br>• Test: `test_admin_reports_sales_summary_and_top_products_exclude_cancelled_orders`. |
| **2. HPP** | `VERIFIED_PASS` | `app/Services/ReportService.php@getHppAndMargin`<br>`resources/views/admin/reports/index.blade.php` | • Menghitung Harga Pokok Penjualan (HPP / COGS) menu secara riil berdasarkan akumulasi biaya bahan baku resep.<br>• Akurat mengombinasikan bahan baku varian ukuran spesifik (`S`, `M`, `L`) dengan bahan baku basis (`all`).<br>• Sinkron otomatis dengan harga beli bahan baku supplier terbaru dari log mutasi stok.<br>• Test: `test_hpp_and_profit_margin_reporting_formulas_are_accurate`. |
| **3. Margin** | `VERIFIED_PASS` | `app/Services/ReportService.php@getHppAndMargin`<br>`resources/views/admin/reports/index.blade.php` | • Menghitung margin laba kotor dalam nominal Rupiah (`margin_rp = sale_price - hpp`) dan persentase (`margin_pct = (margin_rp / sale_price) * 100`).<br>• Perhitungan dinamis per varian ukuran menu dengan proteksi pembagian nol (`zero-division safety`).<br>• Test: `test_hpp_and_profit_margin_reporting_formulas_are_accurate`. |
| **4. Top products** | `VERIFIED_PASS` | `app/Services/ReportService.php@getTopProducts`<br>`resources/views/admin/reports/index.blade.php` | • Pemeringkatan menu terlaris berdasarkan kuantitas penjualan (`quantity_sold`) dan total pendapatan (`revenue`).<br>• Transaksi dibatalkan/void secara otomatis dikecualikan dari pemeringkatan.<br>• Test: `test_admin_reports_sales_summary_and_top_products_exclude_cancelled_orders`. |
| **5. Export works** | `VERIFIED_PASS` | `app/Http/Controllers/Reports/SalesController.php@export`<br>`app/Http/Controllers/Reports/InventoryReportController.php@export`<br>`app/Exports/ArrayExport.php`<br>`resources/views/admin/reports/pdf/`<br>`resources/views/admin/reports/index.blade.php` | • Ekspor laporan lengkap ke format Microsoft Excel (`.xlsx`) via `Maatwebsite\Excel` dan Adobe PDF (`.pdf`) via `Barryvdh\DomPDF`.<br>• Mendukung ekspor seluruh tab analitik: Laporan Penjualan, Pemakaian Bahan Baku, Analisis HPP & Margin, serta Rekap Sesi Kasir.<br>• Mengatasi bug ketidaksesuaian nama rute (`admin.reports.sales.export` dan `admin.reports.inventory.export`) yang sebelumnya memicu HTTP 500 error.<br>• Terproteksi middleware otorisasi: hanya role `admin` yang berhak mengunduh berkas laporan (kasir, barista, dan guest dicegah dengan `403 Forbidden` / `302 Redirect`).<br>• Test: `test_admin_can_export_sales_report_to_excel_and_pdf`, `test_admin_can_export_inventory_reports_to_excel_and_pdf_for_all_sections`, `test_non_admin_cannot_access_reports_or_exports`. |

---

## 34. DoD UI/UX & Design Quality Verification Matrix (100% Complete)

| Kriteria DoD UI/UX | Status | Implementasi & File Terkait | Bukti Verifikasi & Audit Visual |
| :--- | :---: | :--- | :--- |
| **UIX-01 — Consistent visual design system** | `VERIFIED_PASS` | `design.md`<br>`tailwind.config.js`<br>`resources/views/layouts/` | • Palet warna terkurasi artisanal kedai kopi modern: Dark Background (`#0E0D0D`, `#181716`), Border halus (`#2D2A26`), Warm Amber Accent (`#D97706`, `#F59E0B`).<br>• Tipografi konsisten: font Sans Inter untuk headings/body dan JetBrains Mono (`font-mono`) untuk data finansial.<br>• Sistem border radius seragam (`rounded-xl` dan `rounded-2xl`). |
| **UIX-02 — Anti-AI slop & vector icons** | `VERIFIED_PASS` | All view files<br>`resources/views/layouts/admin.blade.php`<br>`resources/views/layouts/cashier.blade.php` | • 100% eliminasi gradien ungu/biru generik AI slop.<br>• Penghapusan seluruh emotikon dekoratif (`☕`, `💾`, `➕`, `🔴`, `🟡`, `🟢`, `💰`, `📥`, `🖨️`, `⚠️`, `❌`).<br>• Standarisasi ikon vektor Lucide SVG dengan CDN ter-pin stabil `lucide@0.344.0` tanpa error browser `Identifier Infinity`. |
| **UIX-03 — Clear hierarchy & tabular data** | `VERIFIED_PASS` | `resources/views/cashier/pos.blade.php`<br>`resources/views/admin/` | • Seluruh angka moneter (Rupiah), nomor order (`#TRX-XXXX`), barcode EAN, kuantitas stok, dan timestamp menggunakan format `font-mono` dengan perataan kanan (*right-aligned*).<br>• Kepadatan data tinggi pada tabel transaksi, mutasi stok, dan rincian analitik tanpa layout wrapping canggung. |
| **UIX-04 — Fast cashier workflow** | `VERIFIED_PASS` | `resources/views/livewire/pos/pos-interface.blade.php`<br>`resources/views/livewire/pos/checkout-modal.blade.php` | • Navigasi keyboard kasir: tombol `/` otomatis memfokuskan pencarian menu/barcode, tombol `Esc` mengaburkan/menutup modal.<br>• Penambahan item 1-klik ke keranjang kasir dengan drawer keranjang lekat (*sticky cart drawer*).<br>• Preset tombol nominal uang cepat (`Uang Pas`, `50.000`, `100.000`, `200.000`).<br>• Tombol bayar otomatis disable dan menghitung nominal kekurangan uang jika uang tunai pelanggan belum cukup. |
| **UIX-05 — KDS quick scanning** | `VERIFIED_PASS` | `resources/views/livewire/kds/kds-board.blade.php` | • Tiket pesanan berukuran besar dengan nomor nota `#TRX-XXXX` yang kontras dan jelas terbaca dari jarak jauh (layar TV/monitor barista).<br>• Tag penuaan berbasis warna: `⚡ Fresh (< 1m)`, `⏳ Wait (1-3m)`, `🔥 Urgent (> 3m)`.<br>• Modifiers catatan kasir ditonjolkan dengan background kontras (`Less ice, normal sugar`, `Hangatkan`). |
| **UIX-06 — Informative admin dashboard** | `VERIFIED_PASS` | `resources/views/admin/dashboard.blade.php` | • Ringkasan eksekutif bisnis: Total Pendapatan, Jumlah Transaksi, AOV, Bahan Kritis, Sesi Kasir Aktif.<br>• Banner peringatan bahan kritis terintegrasi langsung dengan tautan restock.<br>• Tabel 5 Menu Terlaris (*Top Selling Products*) dengan kontribusi omset. |
| **UIX-07 — Standardized admin CRUDs** | `VERIFIED_PASS` | `resources/views/admin/products/`<br>`resources/views/categories/`<br>`resources/views/inventory/`<br>`resources/views/admin/users/`<br>`resources/views/components/sidebar.blade.php` | • 6 modul CRUD admin (Produk, Kategori, Bahan Baku, Resep, Pengguna, Mutasi) memiliki struktur layout identik.<br>• Header halaman standar dengan breadcrumb, deskripsi singkat modul, dan tombol aksi utama (*Primary CTA*) seragam.<br>• Empty state seragam menggunakan kartu dengan ikon Lucide SVG terpusat dan instruksi tindak lanjut. |
| **UIX-08 — Responsive layout** | `VERIFIED_PASS` | `resources/views/layouts/admin.blade.php`<br>`resources/views/layouts/cashier.blade.php` | • Admin portal dilengkapi slide-over drawer responsif dengan tombol hamburger mobile (`md:hidden`), backdrop blur, dan animasi transisi halus.<br>• POS kasir responsif di tablet landscape (`1024x768`) dan desktop (`1280x800`).<br>• KDS adaptif untuk layar monitor widescreen/TV (`1920x1080`). |
| **UIX-09 — Consistent states** | `VERIFIED_PASS` | `resources/views/livewire/`<br>`resources/views/admin/` | • Loading indicator `wire:loading` spinner pada checkout modal dan refresh KDS.<br>• Desain empty state kartu terpadu untuk keranjang kosong, KDS kosong, dan tabel data kosong.<br>• Notifikasi feedback non-blocking berupa banner toast animasi di sudut kanan atas menggantikan popup `alert()` browser. |
| **UIX-10 — Predictable form & modal UX** | `VERIFIED_PASS` | All form & modal views | • Input text field memiliki hover border dan focus ring amber yang seragam.<br>• Tombol aksi primer (`bg-amber-600 hover:bg-amber-500 active:scale-[0.99]`), sekunder (`border border-[#2D2A26] bg-[#141312]`), dan bahaya/void (`rose-600`).<br>• Modal dapat ditutup dengan tombol Esc atau klik backdrop. |
| **UIX-11 — Accessibility basics** | `VERIFIED_PASS` | All views | • Rasio kontras teks terang di atas latar belakang gelap memenuhi standar WCAG AA.<br>• Ukuran target sentuh tombol kasir $\ge 44 \times 44\text{px}$.<br>• Aksesibilitas keyboard teruji pada alur POS (`Tab`, `Enter`, `/`, `Esc`). |
| **UIX-12 — Strict anti-AI slop** | `VERIFIED_PASS` | All views | • Diverifikasi dalam audit kode: tidak ada warna gradien ungu-pink, tidak ada kartu melengkung berlebihan (`rounded-3xl` / `rounded-full` non-avatar), tidak ada dekorasi visual tanpa fungsi. |
| **UIX-13 — Robust layouts without overflow** | `VERIFIED_PASS` | All views | • Diverifikasi via DOM snapshot Playwright: tidak terjadi overflow horizontal, teks menu panjang terpotong rapi dengan ellipsis, dan alignment label konsisten. |
| **UIX-14 — Comprehensive visual audit** | `VERIFIED_PASS` | `docs/screenshots/` | • Audit visual otomatis via Playwright mencakup 15 tangkapan layar beresolusi tinggi (full-page device-scaled PNG) yang disimpan permanen di `docs/screenshots/` (`01_login.png` s/d `15_thermal_receipt.png`). |





