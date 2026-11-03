# SISTEM INFORMASI MANAJEMEN TOKO RETAIL MODERN
### Berbasis Laravel 10 & MySQL (Full-Stack MVC)

Aplikasi manajemen toko retail profesional yang mengintegrasikan tiga pilar operasional toko:
1. **Kasir (POS)**: Penjualan cepat, pemindaian barcode USB & kamera HP/laptop, kalkulasi pembayaran & kembalian, cetak struk thermal (58mm/80mm), dan proteksi race-condition/overselling.
2. **Gudang (Inventory)**: Master data produk (SKU/barcode/harga), penerimaan barang masuk (Goods Receipt), penyesuaian stok opname (Stock Adjustment), kartu mutasi stok (Audit Trail), dan notifikasi stok menipis/habis.
3. **Admin (Executive & Reporting)**: Pemantauan omzet harian, estimasi keuntungan kotor (Gross Profit = Penjualan - HPP Snapshot), grafik tren Chart.js, analisis produk terlaris, filter fleksibel, serta ekspor laporan ke CSV dan format cetak.

---

## 1. Akun Demo Pengujian (Demo Accounts)

Semua password akun demo lokal adalah: `password123`

| Role | Username | Email | Password | Hak Akses Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin@tokoretail.com` | `password123` | Dashboard eksekutif, grafik Chart.js, laporan penjualan, laba kotor, laporan stok persediaan, ekspor CSV, cetak laporan (Read-Only operasional). |
| **Gudang** | `gudang` | `gudang@tokoretail.com` | `password123` | CRUD master barang, input penerimaan barang supplier, penyesuaian stok fisik (opname), audit trail kartu mutasi stok. |
| **Kasir** | `kasir` | `kasir@tokoretail.com` | `password123` | Transaksi kasir POS, barcode scanner USB/Kamera, hotkey F2/F4, kalkulasi kembalian, cetak & cetak ulang struk thermal. |
| **Nonaktif (Uji Coba)** | `kasir_nonaktif` | `kasir_nonaktif@tokoretail.com` | `password123` | Akun nonaktif untuk membuktikan sistem keamanan menolak login pengguna yang dinonaktifkan. |

*Tersedia tombol pintas **Quick Demo Fill** pada halaman login untuk mengisi kredensial dengan 1 klik.*

---

## 2. Persyaratan Sistem & Lingkungan Lokal

- **PHP**: Versi 8.1 atau lebih baru (dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `fileinfo`).
- **Web Server**: Apache / Nginx (Laragon, XAMPP, atau Laravel built-in server).
- **Database**: MySQL 5.7+ / MariaDB 10.3+.
- **Composer**: Versi 2.x.
- **Browser**: Google Chrome, Mozilla Firefox, atau Microsoft Edge versi modern.

---

## 3. Panduan Instalasi & Menjalankan Aplikasi

Jika Anda baru meng-clone atau membuka proyek ini:

### Langkah 1: Pengaturan File `.env`
Pastikan file `.env` sudah ada dan terkonfigurasi ke database MySQL Anda:
```env
APP_NAME="Toko Retail Modern"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE="Asia/Jakarta"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tugasip
DB_USERNAME=root
DB_PASSWORD=
```

### Langkah 2: Buat Database MySQL
Buat database bernama `tugasip` di MySQL / phpMyAdmin:
```sql
CREATE DATABASE `tugasip` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Langkah 3: Jalankan Migration & Seeder
Perintah ini akan membuat seluruh tabel, relasi foreign key, indeks, akun demo, kategori, supplier, dan katalog produk awal:
```bash
php artisan migrate:fresh --seed
```

### Langkah 4: Jalankan Server Lokal
Jalankan server pengembangan Laravel:
```bash
php artisan serve
```
Akses aplikasi melalui browser:
👉 **[http://localhost:8000](http://localhost:8000)** atau **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 4. Panduan Pengujian Fitur Utama

### A. Pengujian Autentikasi & Keamanan Role
1. Buka `http://localhost:8000/login`.
2. Klik tombol **Nonaktif** lalu klik Masuk: sistem akan menolak dengan pesan *"Akun Anda dinonaktifkan oleh administrator"*.
3. Login sebagai **Kasir** (`kasir` / `password123`): Anda diarahkan ke `/kasir`.
4. Coba ketik URL gudang secara manual: `http://localhost:8000/gudang` atau `http://localhost:8000/admin`.
   - Sistem merespons dengan **403 Forbidden** (Akses Ditolak).
5. Logout, lalu login sebagai **Gudang** (`gudang` / `password123`): diarahkan ke `/gudang`.
6. Coba akses `/kasir`: sistem menolak dengan **403 Forbidden**.

### B. Pengujian Kasir POS (Point of Sale)
1. Login sebagai `kasir`.
2. **Pencarian Cepat**: Ketik `Indomie` pada kolom pencarian. Pilih produk dari daftar dropdown.
3. **Scan Barcode USB**: Ketik barcode `8998866200225` lalu tekan **Enter** pada keyboard. Produk otomatis masuk ke keranjang.
4. **Scan Kamera**: Klik ikon kamera 📷 untuk mengaktifkan pemindai barcode kamera browser (didukung oleh `html5-qrcode`).
5. **Ubah Jumlah**: Tekan tombol `+` atau `-` pada keranjang. Subtotal otomatis terhitung ulang.
6. **Validasi Stok**: Coba pesan melebihi sisa stok yang tertera. Sistem akan mencegah penambahan.
7. **Hotkey Keyboard**:
   - Tekan <kbd>F4</kbd>: Kursor otomatis fokus ke kolom pencarian/barcode.
   - Tekan <kbd>F2</kbd>: Otomatis fokus ke input pembayaran atau memproses checkout.
8. **Pembayaran**:
   - Pilih metode `Tunai`. Klik tombol **Uang Pas** atau pilih nominal cepat (`+50.000`, `+100.000`).
   - Masukkan uang bayar: nilai kembalian akan otomatis terhitung secara real-time.
   - Klik **Proses Pembayaran** (<kbd>F2</kbd>).
9. **Cetak Struk**:
   - Modal sukses akan muncul menampilkan nomor faktur dan kembalian.
   - Klik **Cetak Struk Penjualan**: halaman struk format thermal 58mm/80mm akan terbuka dan siap dicetak ke printer struk.

### C. Pengujian Modul Gudang & Mutasi Stok
1. Login sebagai `gudang`.
2. Buka **Katalog Barang**: lihat daftar produk, status stok normal, menipis, dan habis.
3. Klik **Catat Barang Masuk**:
   - Pilih supplier (contoh: PT Indofood).
   - Pilih produk dan tentukan jumlah masuk (contoh: 20 pcs) serta harga beli satuan.
   - Klik **Konfirmasi Penerimaan**: stok produk bertambah otomatis di database.
4. Buka **Penyesuaian Stok**:
   - Pilih produk yang ingin dikoreksi.
   - Masukkan stok fisik riil hasil opname (contoh: dari 120 menjadi 118 karena 2 pcs bocor).
   - Selisih (-2) otomatis terhitung. Tulis alasan koreksi.
   - Simpan penyesuaian: stok berkurang dan riwayat tercatat.
5. Buka **Riwayat Mutasi**: seluruh pergerakan barang (stok awal, penerimaan, penjualan kasir, opname) tercatat lengkap dengan waktu dan nama petugas.

### D. Pengujian Modul Admin & Laporan
1. Login sebagai `admin`.
2. **Dashboard**:
   - Periksa 6 kartu statistik harian (Omzet, Estimasi Laba Kotor, Unit Terjual, dll).
   - Periksa grafik **Chart.js**: Tren Penjualan & Laba Kotor 7 hari terakhir serta Donut Chart Top 5 Produk Terlaris.
3. **Laporan Penjualan**:
   - Gunakan tombol periode cepat: *Hari Ini*, *Kemarin*, *Minggu Ini*, *Bulan Ini*.
   - Filter berdasarkan Kasir.
   - Klik **Ekspor CSV**: browser akan mengunduh file `.csv` lengkap.
   - Klik **Cetak Laporan**: format cetak resmi pimpinan toko terbuka.
4. **Laporan Keuntungan Kotor**:
   - Melihat rincian `Total Penjualan - Total HPP Snapshot = Keuntungan Kotor`.
   - Margin persentase terhitung otomatis.
5. **Laporan Stok**:
   - Menampilkan valuasi aset persediaan total (Rp) berdasarkan stok x harga beli HPP.

---

## 5. Menjalankan Pengujian Otomatis (Automated Feature Tests)

Aplikasi dilengkapi dengan rangkaian tes otomatis komprehensif menggunakan PHPUnit:
```bash
php artisan test tests/Feature/RetailAppTest.php
```

Skenario pengujian yang terverifikasi (14 skenario, 43 assertions):
- [x] Login kasir berhasil & redirect ke `/kasir`
- [x] Login admin berhasil & redirect ke `/admin`
- [x] Login gudang berhasil & redirect ke `/gudang`
- [x] Login password salah ditolak
- [x] Login akun nonaktif ditolak
- [x] Kasir tidak dapat membuka modul Gudang atau Admin (403 Forbidden)
- [x] Gudang tidak dapat membuka modul Kasir atau Admin (403 Forbidden)
- [x] Admin tidak dapat membuka modul Kasir atau Gudang (403 Forbidden)
- [x] Kasir dapat mencari produk & scan barcode
- [x] Transaksi kasir mengurangi stok secara atomik & mencatat kartu mutasi
- [x] Transaksi kasir ditolak jika kuantitas melebihi sisa stok (mencegah overselling)
- [x] Penerimaan barang gudang menambah stok & mencatat faktur masuk
- [x] Penyesuaian stok opname menyimpan selisih, alasan, dan audit trail
- [x] Admin dapat melihat seluruh laporan, rincian faktur, dan ekspor CSV

---

## 6. Struktur Database & Model Relasional

- **`users`**: Pengguna sistem (`admin`, `gudang`, `kasir`, `is_active`).
- **`categories`**: Kategori produk barang.
- **`suppliers`**: Pemasok distributor barang.
- **`products`**: Master barang (`sku`, `barcode`, `name`, `purchase_price`, `selling_price`, `stock`, `minimum_stock`, `is_active`).
- **`goods_receipts` & `goods_receipt_items`**: Faktur dan item penerimaan pasokan gudang.
- **`sales` & `sale_items`**: Faktur penjualan kasir dengan snapshot harga jual dan HPP modal (`purchase_price_snapshot`).
- **`stock_adjustments` & `stock_adjustment_items`**: Berita acara penyesuaian opname fisik.
- **`stock_movements`**: Buku besar kartu audit trail mutasi stok (`masuk`, `keluar`, `penyesuaian`).

---

&copy; {{ date('Y') }} Sistem Informasi Manajemen Toko Retail Modern. Dibuat dengan standar Laravel 10 & MySQL.
