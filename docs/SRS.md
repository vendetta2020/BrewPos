---
title: "BrewPos — Software Requirements Specification"
version: "1.0"
date: "2026-05-28"
project: "brewpos"
type: "SRS"
standard: "IEEE 830"
description: "Functional and non-functional requirements for all modules: AUTH, MENU, POS, KDS, INV, RPT"
---

## 1. Pendahuluan
### 1.1 Tujuan Dokumen
Dokumen ini menetapkan persyaratan perangkat lunak lengkap untuk sistem BrewPos versi 1.0. Dokumen ditujukan untuk tim pengembang Laravel, QA engineer, dan pemilik produk sebagai acuan pengembangan dan pengujian.

### 1.2 Scope Sistem
BrewPos mencakup empat subsistem utama yang saling terintegrasi:

- Subsistem POS — antarmuka kasir untuk transaksi harian

- Subsistem Inventori — manajemen bahan baku dan resep menu

- Subsistem KDS — kitchen display untuk barista

- Subsistem Laporan — analitik penjualan dan biaya bahan baku

### 1.3 Definisi & Akronim
| **Istilah**    | **Definisi**                                                                        |
|----------------|-------------------------------------------------------------------------------------|
| POS            | Point of Sale — sistem kasir untuk transaksi penjualan                              |
| KDS            | Kitchen Display System — layar antrian pesanan untuk barista                        |
| HPP            | Harga Pokok Produksi — biaya bahan baku per satu sajian menu                        |
| Raw Material   | Bahan baku mentah (kopi, susu, sirup, dll) yang digunakan dalam resep               |
| Recipe         | Daftar bahan baku beserta kuantitas yang digunakan untuk membuat satu produk        |
| Stock Movement | Log pergerakan stok, baik masuk (restock) maupun keluar (pemakaian)                 |
| Session        | Sesi kerja kasir yang dimulai dengan input modal awal dan diakhiri dengan rekap kas |
| QRIS           | Quick Response Code Indonesian Standard — standar pembayaran QR Code nasional       |

### 1.4 Referensi
- BrewPos PRD v1.0 — Product Requirements Document

- Laravel 11 Official Documentation — laravel.com/docs

- Livewire 3 Documentation — livewire.laravel.com

- IEEE 830-1998 — Recommended Practice for SRS

## 2. Deskripsi Umum Sistem
### 2.1 Perspektif Produk
BrewPos adalah sistem mandiri berbasis web yang berjalan di browser modern. Sistem berkomunikasi dengan database MySQL melalui Laravel ORM, dan menggunakan Pusher untuk komunikasi real-time antara POS kasir dan KDS barista.

Batas Sistem
BrewPos tidak mengintegrasikan gateway pembayaran online, sistem akuntansi eksternal, atau aplikasi mobile native pada versi 1.0. Semua operasi berlangsung dalam jaringan lokal kedai kopi.

### 2.2 Fungsi Utama Sistem
| **Kode** | **Fungsi**          | **Deskripsi Singkat**                                                 |
|----------|---------------------|-----------------------------------------------------------------------|
| SYS-01   | Manajemen Pengguna  | Registrasi, login, penetapan role, dan kontrol akses berbasis role    |
| SYS-02   | Manajemen Menu      | CRUD produk dengan kategori, harga, varian ukuran, dan status aktif   |
| SYS-03   | Transaksi POS       | Pemilihan menu, kustomisasi, checkout, dan integrasi pembayaran       |
| SYS-04   | Kitchen Display     | Tampilan antrian pesanan real-time untuk barista dengan update status |
| SYS-05   | Manajemen Inventori | CRUD bahan baku, penetapan resep menu, dan pencatatan restock         |
| SYS-06   | Deduction Otomatis  | Pengurangan stok bahan baku secara otomatis setiap transaksi selesai  |
| SYS-07   | Sesi Kasir          | Pembukaan dan penutupan sesi kerja dengan rekap kas                   |
| SYS-08   | Laporan & Analitik  | Laporan penjualan, pemakaian bahan baku, HPP, dan margin per menu     |

## 3. Kebutuhan Fungsional
### 3.1 Modul Autentikasi (AUTH)
| **ID Req** | **Deskripsi Kebutuhan**                                                                | **Prioritas** |
|------------|----------------------------------------------------------------------------------------|---------------|
| AUTH-01    | Sistem harus menyediakan halaman login dengan input email/username dan password        | Wajib         |
| AUTH-02    | Sistem harus memvalidasi kredensial dan memberikan pesan error yang jelas jika gagal   | Wajib         |
| AUTH-03    | Sistem harus menerapkan tiga role: Admin, Kasir, dan Barista dengan hak akses berbeda  | Wajib         |
| AUTH-04    | Sistem harus mengarahkan pengguna ke halaman yang sesuai dengan role-nya setelah login | Wajib         |
| AUTH-05    | Admin harus dapat membuat, mengedit, menonaktifkan, dan menghapus akun pengguna        | Wajib         |
| AUTH-06    | Sesi pengguna harus kedaluwarsa otomatis setelah 8 jam tidak aktif                     | Wajib         |
| AUTH-07    | Admin harus dapat mereset password pengguna lain                                       | Wajib         |

### 3.2 Modul Menu (MENU)
| **ID Req** | **Deskripsi Kebutuhan**                                                               | **Prioritas** |
|------------|---------------------------------------------------------------------------------------|---------------|
| MENU-01    | Admin harus dapat menambah produk dengan nama, deskripsi, kategori, harga, dan gambar | Wajib         |
| MENU-02    | Sistem harus mendukung varian ukuran (S/M/L) dengan harga berbeda per varian          | Wajib         |
| MENU-03    | Admin harus dapat menonaktifkan produk tanpa menghapusnya dari database               | Wajib         |
| MENU-04    | Admin harus dapat mengelola kategori menu (tambah, ubah, hapus)                       | Wajib         |
| MENU-05    | Halaman POS hanya menampilkan produk dengan status aktif                              | Wajib         |
| MENU-06    | Setiap produk harus memiliki tombol 'Atur Resep' yang mengarah ke modul inventori     | Wajib         |

### 3.3 Modul POS Kasir (POS)
| **ID Req** | **Deskripsi Kebutuhan**                                                                | **Prioritas** |
|------------|----------------------------------------------------------------------------------------|---------------|
| POS-01     | Kasir harus dapat membuka sesi dengan menginput modal kas awal sebelum bertransaksi    | Wajib         |
| POS-02     | Halaman POS menampilkan grid menu dengan pencarian dan filter kategori                 | Wajib         |
| POS-03     | Kasir dapat menambah item ke keranjang dengan satu klik; item duplikat menambah qty    | Wajib         |
| POS-04     | Kasir dapat mengatur kustomisasi: ukuran, tingkat gula, dan catatan bebas per item     | Wajib         |
| POS-05     | Sistem menghitung subtotal, diskon, pajak 10%, dan total secara otomatis dan real-time | Wajib         |
| POS-06     | Kasir dapat memproses pembayaran tunai; sistem menampilkan kembalian otomatis          | Wajib         |
| POS-07     | Kasir dapat memproses pembayaran QRIS dan kartu debit/kredit                           | Wajib         |
| POS-08     | Setiap transaksi selesai menghasilkan nomor order unik dan struk yang dapat dicetak    | Wajib         |
| POS-09     | Kasir harus dapat menutup sesi dengan rekap total pemasukan dan selisih kas            | Wajib         |
| POS-10     | Kasir dapat melihat riwayat transaksi dalam sesi yang sedang berjalan                  | Disarankan    |

### 3.4 Modul Kitchen Display (KDS)
| **ID Req** | **Deskripsi Kebutuhan**                                                                          | **Prioritas** |
|------------|--------------------------------------------------------------------------------------------------|---------------|
| KDS-01     | Halaman KDS menampilkan semua pesanan aktif dalam tampilan kartu real-time                       | Wajib         |
| KDS-02     | Setiap kartu menampilkan nomor order, nama meja/tipe, daftar item, dan waktu masuk               | Wajib         |
| KDS-03     | Barista dapat mengubah status pesanan: Baru → Diproses → Selesai                                 | Wajib         |
| KDS-04     | Pesanan baru masuk muncul di KDS tanpa perlu refresh halaman (real-time)                         | Wajib         |
| KDS-05     | Kartu pesanan yang selesai ditampilkan dengan opacity lebih rendah selama 2 menit sebelum hilang | Wajib         |
| KDS-06     | KDS menggunakan tema gelap (dark mode) untuk kenyamanan baca dari jarak jauh                     | Wajib         |

### 3.5 Modul Inventori (INV)
| **ID Req** | **Deskripsi Kebutuhan**                                                                        | **Prioritas** |
|------------|------------------------------------------------------------------------------------------------|---------------|
| INV-01     | Admin harus dapat menambah bahan baku dengan nama, satuan, harga/unit, stok awal, stok minimum | Wajib         |
| INV-02     | Sistem harus menampilkan status stok: Aman, Hampir Habis, atau Kritis                          | Wajib         |
| INV-03     | Admin harus dapat mendefinisikan resep per produk: bahan + qty per sajian + varian ukuran      | Wajib         |
| INV-04     | Halaman edit resep harus menampilkan preview HPP yang dikalkulasi secara real-time             | Wajib         |
| INV-05     | Setiap transaksi checkout yang selesai harus memicu pengurangan stok otomatis                  | Wajib         |
| INV-06     | Setiap pengurangan stok harus dicatat di tabel stock_movements dengan referensi order_item     | Wajib         |
| INV-07     | Sistem harus mengirim notifikasi ke admin saat stok bahan mencapai atau di bawah minimum       | Wajib         |
| INV-08     | Admin harus dapat mencatat restock dengan jumlah masuk, harga beli, tanggal, dan supplier      | Wajib         |
| INV-09     | Admin harus dapat melakukan koreksi manual stok dengan alasan yang tercatat                    | Disarankan    |

### 3.6 Modul Laporan (RPT)
| **ID Req** | **Deskripsi Kebutuhan**                                                             | **Prioritas** |
|------------|-------------------------------------------------------------------------------------|---------------|
| RPT-01     | Dashboard menampilkan metrik harian: pendapatan, jumlah transaksi, AOV, stok kritis | Wajib         |
| RPT-02     | Laporan penjualan menampilkan data per periode (hari, minggu, bulan, custom range)  | Wajib         |
| RPT-03     | Laporan menampilkan menu terlaris dengan bar chart horizontal                       | Wajib         |
| RPT-04     | Laporan bahan baku menampilkan pemakaian per bahan dan total biaya per periode      | Wajib         |
| RPT-05     | Laporan HPP & Margin menampilkan HPP, harga jual, margin Rp dan % per menu          | Wajib         |
| RPT-06     | Admin dapat mengekspor laporan ke format PDF dan Excel (xlsx)                       | Disarankan    |
| RPT-07     | Laporan rekap sesi kasir menampilkan ringkasan per sesi dalam periode yang dipilih  | Wajib         |

## 4. Kebutuhan Non-Fungsional
### 4.1 Performa
| **ID Req** | **Deskripsi**                                       | **Ukuran**                   |
|------------|-----------------------------------------------------|------------------------------|
| NF-P01     | Waktu respons halaman POS setelah aksi kasir        | < 1 detik (95th percentile) |
| NF-P02     | Delay notifikasi order baru di KDS setelah checkout | < 2 detik                   |
| NF-P03     | Waktu render laporan untuk data 1 bulan             | < 5 detik                   |
| NF-P04     | Kapasitas transaksi harian                          | Minimal 500 transaksi/hari   |

### 4.2 Keamanan
- Seluruh password di-hash menggunakan bcrypt (Laravel default)

- Semua endpoint dilindungi middleware auth dan role-check

- Input pengguna di-sanitasi dan di-validasi sebelum diproses (XSS, SQL Injection prevention)

- CSRF protection aktif di semua form POST

- Koneksi database menggunakan kredensial environment variable, tidak hardcoded

### 4.3 Ketersediaan & Reliabilitas
- Sistem harus tersedia 99% selama jam operasional (06.00–22.00 WIB)

- Backup database otomatis setiap 24 jam, disimpan di lokasi terpisah

- Jika koneksi Pusher terputus, KDS harus fallback ke polling setiap 10 detik

## 5. Kebutuhan Antarmuka
### 5.1 Antarmuka Pengguna
| **Halaman**     | **Layout**              | **Resolusi Target** | **Catatan**                           |
|-----------------|-------------------------|---------------------|---------------------------------------|
| Login           | Centered card           | 1024px+             | Responsive hingga 768px               |
| Dashboard Admin | Sidebar + main          | 1280px+             | Sidebar collapsible di layar kecil    |
| POS Kasir       | Fullscreen split panel  | 1280px+             | Desktop/tablet landscape only         |
| KDS Barista     | Fullscreen dark grid    | 1920px+ (TV)        | Font besar, dapat dibaca dari 2m      |
| Laporan         | Sidebar + main + tabs   | 1280px+             | Chart responsive menggunakan Recharts |
| Form Inventori  | Centered card max-560px | 1024px+             | Single column di mobile               |

### 5.2 Antarmuka Hardware
- Thermal printer untuk cetak struk — terkoneksi via USB atau jaringan lokal

- Barcode scanner (opsional) untuk pencarian produk di halaman POS

- Monitor/TV tambahan untuk KDS barista

### 5.3 Antarmuka Komunikasi
- HTTP/HTTPS untuk semua request browser ke server Laravel

- WebSocket melalui Pusher untuk notifikasi real-time KDS dan low-stock alert

## 6. Kebutuhan Basis Data
### 6.1 Entitas Utama
| **Tabel**        | **Deskripsi**             | **Relasi Kunci**                        |
|------------------|---------------------------|-----------------------------------------|
| users            | Akun pengguna dengan role | —                                       |
| products         | Master produk menu        | → categories, → product_recipes         |
| categories       | Kategori menu             | → products                              |
| orders           | Header transaksi          | → users, → cashier_sessions             |
| order_items      | Detail item per transaksi | → orders, → products, → stock_movements |
| cashier_sessions | Sesi kerja kasir          | → users                                 |
| raw_materials    | Master bahan baku         | → product_recipes, → stock_movements    |
| product_recipes  | Resep per produk/ukuran   | → products, → raw_materials             |
| stock_movements  | Log gerakan stok          | → raw_materials, → order_items          |

### 6.2 Aturan Integritas Data
- Soft delete digunakan pada tabel products dan raw_materials — data tidak benar-benar dihapus

- Field cost_per_unit pada stock_movements adalah snapshot harga saat transaksi, tidak ikut berubah jika harga bahan diupdate

- Order yang sudah selesai (status = completed) tidak dapat diubah atau dihapus

- Saldo stok pada raw_materials tidak boleh bernilai negatif — sistem menolak transaksi jika stok tidak mencukupi (opsional v1.1)

## 7. Batasan Desain & Implementasi
| **Kategori** | **Ketentuan**                                                                                   |
|--------------|-------------------------------------------------------------------------------------------------|
| Framework    | Laravel 11, PHP 8.2+                                                                            |
| Frontend     | Livewire 3 untuk komponen reaktif, Tailwind CSS untuk styling, Alpine.js untuk interaksi ringan |
| Database     | MySQL 8.0+ atau PostgreSQL 15+                                                                  |
| Real-time    | Laravel Echo + Pusher Channels (atau alternatif: Soketi self-hosted)                            |
| Ekspor       | Maatwebsite/Laravel-Excel untuk xlsx, barryvdh/laravel-dompdf untuk PDF                         |
| Queue        | Laravel Queue dengan driver database atau Redis untuk proses deduction stok asinkron            |
| Deployment   | Dapat dijalankan di shared hosting cPanel atau VPS Ubuntu 22.04+                                |

## 8. Appendix — Use Case Diagram Tekstual
#### UC-01: Kasir Memproses Transaksi
Primary Actor: Kasir
Precondition: Kasir telah login dan membuka sesi. Main Flow: Kasir membuka halaman POS → Memilih menu → Mengatur kustomisasi → Klik checkout → Memilih metode bayar → Transaksi tersimpan → Struk dicetak → Pesanan muncul di KDS. Alternate Flow: Jika stok bahan tidak cukup (v1.1), sistem menampilkan peringatan. Postcondition: Order tersimpan, stok berkurang otomatis, KDS diperbarui.

#### UC-02: Admin Mengelola Resep
Primary Actor: Admin
Precondition: Bahan baku sudah terdaftar di sistem. Main Flow: Admin buka Inventori &gt; Manajemen Resep → Pilih produk → Tambah bahan dan qty → Sistem kalkulasi HPP otomatis → Simpan resep. Postcondition: Produk memiliki resep aktif, deduction otomatis berjalan pada transaksi berikutnya.

#### UC-03: Admin Mencatat Restock
Primary Actor: Admin
Precondition: Bahan baku sudah terdaftar. Main Flow: Admin buka Inventori &gt; Catat Restock → Pilih bahan → Isi qty masuk dan harga beli → Submit. Postcondition: Stok bertambah, stock_movement bertipe 'in' tersimpan, nilai stok terupdate.
