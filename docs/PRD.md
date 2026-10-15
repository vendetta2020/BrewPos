---
title: "BrewPos — Product Requirements Document"
version: "1.0"
date: "2026-05-28"
project: "brewpos"
type: "PRD"
description: "Business requirements, user personas, feature scope, and timeline for BrewPos POS system"
---

## 1. Ringkasan Eksekutif
BrewPos adalah sistem Point of Sale (POS) berbasis web yang dirancang khusus untuk kebutuhan operasional kedai kopi. Sistem ini dibangun menggunakan framework Laravel dan bertujuan menggantikan proses pencatatan manual yang selama ini digunakan, menjadi sistem digital yang terintegrasi, akurat, dan mudah digunakan oleh seluruh staf.

Pernyataan Masalah
Kedai kopi saat ini mengandalkan pencatatan manual untuk transaksi penjualan, pengelolaan stok, dan pembuatan laporan. Proses ini rentan kesalahan, lambat, dan tidak memberikan visibilitas real-time terhadap kondisi bisnis.

### 1.1 Tujuan Produk
- Mendigitalkan seluruh alur transaksi penjualan dari pemesanan hingga pembayaran

- Mengotomatisasi pengurangan stok bahan baku berdasarkan resep setiap menu

- Menyediakan laporan bisnis real-time yang akurat dan dapat diekspor

- Meningkatkan efisiensi operasional dan mengurangi kesalahan manual

- Memberikan visibilitas HPP (Harga Pokok Produksi) per menu kepada manajemen

### 1.2 Metrik Keberhasilan
| **Metrik**                    | **Kondisi Saat Ini** | **Target 3 Bulan**    |
|-------------------------------|----------------------|-----------------------|
| Waktu rata-rata per transaksi | ± 3 menit (manual)   | < 60 detik           |
| Akurasi stok                  | Tidak terukur        | > 98%                |
| Waktu buat laporan harian     | 30–60 menit          | < 5 menit (otomatis) |
| Selisih kas harian            | Sering terjadi       | 0 selisih             |
| Visibilitas HPP per menu      | Tidak ada            | 100% menu terdefinisi |

## 2. Pengguna & Pemangku Kepentingan
### 2.1 Persona Pengguna
| **Role**        | **Nama Persona** | **Kebutuhan Utama**                         | **Pain Point Saat Ini**                          |
|-----------------|------------------|---------------------------------------------|--------------------------------------------------|
| Admin / Pemilik | Budi (30 th)     | Laporan harian, kontrol stok, pantau margin | Tidak ada laporan otomatis, stok tidak terpantau |
| Kasir           | Rina (22 th)     | Input order cepat, proses bayar mudah       | Salah hitung kembalian, antre panjang            |
| Barista         | Dani (24 th)     | Lihat antrian pesanan dengan jelas          | Sering miskomunikasi dengan kasir                |

### 2.2 Pemangku Kepentingan
- Pemilik kedai kopi — sponsor utama, penentu keputusan bisnis

- Tim kasir — pengguna harian sistem POS

- Tim barista — pengguna kitchen display

- Tim pengembang Laravel — pelaksana pembangunan sistem

## 3. Scope & Fitur
### 3.1 Fitur Dalam Scope (v1.0)
| **ID** | **Fitur**                                             | **Prioritas**     | **Modul** |
|--------|-------------------------------------------------------|-------------------|-----------|
| F-01   | Autentikasi & manajemen role (Admin, Kasir, Barista)  | P0 — Must Have    | Auth      |
| F-02   | CRUD manajemen menu (produk, kategori, varian ukuran) | P0 — Must Have    | Menu      |
| F-03   | Antarmuka POS kasir (grid menu, cart, checkout)       | P0 — Must Have    | POS       |
| F-04   | Proses pembayaran (tunai, QRIS, kartu)                | P0 — Must Have    | POS       |
| F-05   | Kitchen display system real-time (KDS)                | P0 — Must Have    | KDS       |
| F-06   | Sesi kasir (buka/tutup, rekap kas harian)             | P1 — Should Have  | POS       |
| F-07   | Manajemen bahan baku (raw materials)                  | P1 — Should Have  | Inventori |
| F-08   | Resep menu (recipe-based inventory)                   | P1 — Should Have  | Inventori |
| F-09   | Pengurangan stok otomatis per transaksi               | P1 — Should Have  | Inventori |
| F-10   | Notifikasi stok minimum                               | P1 — Should Have  | Inventori |
| F-11   | Pencatatan restock bahan baku                         | P1 — Should Have  | Inventori |
| F-12   | Laporan penjualan (harian/mingguan/bulanan)           | P1 — Should Have  | Laporan   |
| F-13   | Laporan bahan baku & HPP per menu                     | P1 — Should Have  | Laporan   |
| F-14   | Ekspor laporan ke PDF / Excel                         | P2 — Nice to Have | Laporan   |
| F-15   | Manajemen diskon & voucher                            | P2 — Nice to Have | Promo     |
| F-16   | Manajemen meja (denah & status)                       | P2 — Nice to Have | POS       |

### 3.2 Fitur Di Luar Scope v1.0
- Aplikasi mobile native (iOS/Android)

- Integrasi payment gateway online

- Program loyalitas pelanggan berbasis poin

- Multi-cabang / multi-outlet

- Integrasi akuntansi (Jurnal, Accurate, dll)

## 4. Alur Pengguna (User Stories)
### 4.1 Alur Kasir
1.  Kasir login dengan akun yang diberikan admin

2.  Kasir membuka sesi dengan menginput modal kas awal

3.  Kasir memilih menu dari grid produk, klik untuk tambah ke keranjang

4.  Kasir mengatur kustomisasi (ukuran, tingkat gula, catatan)

5.  Kasir memproses pembayaran — sistem kalkulasi kembalian otomatis

6.  Struk dicetak / dikirim digital — pesanan masuk ke KDS barista

7.  Di akhir shift, kasir menutup sesi dan sistem merekap kas

### 4.2 Alur Admin — Setup Resep
8.  Admin membuka halaman Inventori > Bahan Baku, menambahkan semua raw material

9.  Admin membuka Inventori > Manajemen Resep

10. Admin memilih produk, mengisi bahan baku yang digunakan beserta kuantitasnya

11. Sistem menampilkan preview HPP otomatis berdasarkan harga beli bahan

12. Admin menyimpan resep — sistem mulai mengurangi stok setiap ada transaksi

### 4.3 Alur Otomatis — Pengurangan Stok
13. Transaksi checkout berhasil

14. Sistem membaca resep setiap item dalam order

15. Sistem mengurangi stok bahan baku sesuai resep × kuantitas

16. Sistem mencatat stock_movement bertipe 'out'

17. Sistem memeriksa apakah stok mencapai batas minimum

18. Jika ya, sistem mengirim notifikasi ke admin

## 5. Persyaratan Non-Fungsional
| **Kategori**   | **Persyaratan**                                                                    |
|----------------|------------------------------------------------------------------------------------|
| Performa       | Halaman POS harus merespons dalam < 1 detik. KDS refresh real-time < 2 detik.    |
| Ketersediaan   | Sistem harus tersedia > 99% selama jam operasional (06.00–22.00 WIB)              |
| Keamanan       | Password di-hash (bcrypt). Session timeout 8 jam. Role-based access control ketat. |
| Skalabilitas   | Sistem harus mampu menangani hingga 500 transaksi per hari                         |
| Kompatibilitas | Berjalan di Chrome, Firefox, Safari versi terbaru. Responsif di layar 10"+         |
| Audit Trail    | Semua perubahan data sensitif dicatat dengan user dan timestamp                    |
| Backup         | Backup database otomatis setiap 24 jam                                             |

## 6. Constraints & Asumsi
### 6.1 Constraints
- Sistem dibangun dengan Laravel 11 dan PHP 8.2+

- Database menggunakan MySQL / PostgreSQL

- Real-time KDS menggunakan Laravel Echo + Pusher

- Frontend menggunakan Livewire 3 + Tailwind CSS

- Cetak struk menggunakan thermal printer atau PDF

### 6.2 Asumsi
- Setiap produk memiliki resep yang didefinisikan sebelum sistem digunakan

- Koneksi internet tersedia untuk fitur real-time KDS dan Pusher

- Admin bertanggung jawab mengupdate harga beli bahan baku secara berkala

- Satu sesi kasir = satu shift kerja (tidak lebih dari 12 jam)

## 7. Timeline & Milestone
| **Fase**              | **Durasi** | **Deliverable**                                               |
|-----------------------|------------|---------------------------------------------------------------|
| Fase 1 — Foundation   | 2 minggu   | Auth, CRUD menu, struktur database, layout sidebar            |
| Fase 2 — POS Core     | 3 minggu   | Halaman POS kasir, checkout, sesi kasir, KDS                  |
| Fase 3 — Inventori    | 3 minggu   | Manajemen bahan baku, resep menu, deduction otomatis, restock |
| Fase 4 — Laporan      | 2 minggu   | Dashboard laporan, HPP per menu, ekspor PDF/Excel             |
| Fase 5 — Polish & UAT | 2 minggu   | Bug fix, UAT bersama pemilik & kasir, deployment              |

**Total estimasi: 12 minggu (3 bulan) untuk v1.0 production-ready.**
