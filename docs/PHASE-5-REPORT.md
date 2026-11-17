# Laporan Tahap 5 — Shopping Cart, Checkout, dan Order

Tanggal: 16 November 2026. Proyek: Laravel 13, Blade, Tailwind CSS 3, Eloquent, MySQL.

## Fitur yang selesai

- Keranjang tersimpan per akun pelanggan dalam `carts` dan `cart_items`. Produk yang sama digabung; jumlah dapat diperbarui dan produk dapat dihapus.
- Katalog dan detail produk memiliki aksi tambah ke keranjang. Pengunjung diarahkan login dan dikembalikan ke detail produk; akses langsung keranjang/checkout juga dilanjutkan setelah login. Tidak ada pembelian otomatis setelah login.
- Harga, kuantitas, subtotal, total, gambar/placeholder, notifikasi, kondisi kosong, dan peringatan stok ditampilkan. Produk nonaktif, arsip, kosong, atau jumlah melebihi stok ditolak kembali oleh server.
- Checkout memvalidasi seluruh data penerima, alamat, telepon, kota, provinsi, kode pos 5 digit, serta catatan opsional. Ongkir simulasi default Rp15.000 berasal dari konfigurasi server.
- Pesanan memiliki nomor unik `PWM-<ULID>`, token checkout unik, alamat, snapshot nama/harga/jumlah produk, subtotal, ongkir, total, status pembayaran, status proses, dan batas reservasi.
- Pembuatan pesanan menggunakan transaksi MySQL dan `lockForUpdate`. Semua perubahan keranjang mengunci keranjang terlebih dahulu; checkout mengunci produk berurutan berdasarkan ID. Harga dihitung ulang menggunakan bilangan bulat dalam satuan sen. Input browser untuk harga, pemilik, ongkir, atau status pembayaran tidak digunakan.
- Token berubah saat isi keranjang berubah. Token lama ditolak, sedangkan pengulangan token dari checkout yang sudah berhasil mengembalikan pesanan yang sama. Keranjang dibersihkan hanya setelah order dan item berhasil disimpan.
- Stok tersedia dikurangi saat reservasi. Pembatalan dan command kedaluwarsa mengembalikan stok tepat satu kali, termasuk produk yang telah diarsipkan. Reservasi default 60 menit.
- Halaman detail pesanan hanya dapat dibuka pemiliknya. Pembayaran awal `pending`, proses awal `awaiting_payment`; halaman secara jelas menyatakan pembayaran belum tersedia sampai Tahap 6 selesai.
- Pembatalan menggunakan `order_status=cancelled` dan `payment_status=failed`, mengikuti enum pembayaran lama. Kedaluwarsa menggunakan `payment_status=expired`. Tidak ada endpoint pembayaran berhasil, token Midtrans palsu, atau simulasi pembayaran.
- Akun dengan pesanan tidak dapat dihapus. Foreign key order → user menggunakan RESTRICT agar riwayat transaksi tidak ikut terhapus. Penghapusan akun tanpa pesanan tetap berfungsi.

## Keamanan database dan kompatibilitas

Satu migration tambahan: `2026_11_16_135458_add_checkout_and_cart_tables.php`.

- Menambahkan tabel carts, cart_items, dan order_items, serta melengkapi orders.
- `order_details` lama dipertahankan. Transaksi baru memakai `order_items`; data transaksi lama tidak dipindahkan atau direkayasa menjadi snapshot baru.
- `total_price` lama diperluas menjadi decimal(12,2) dan tetap diisi untuk kompatibilitas. `total_amount` menjadi total utama transaksi baru.
- Kolom tambahan untuk data penerima nullable pada schema agar record lama tetap kompatibel; request checkout baru mewajibkan data tersebut.
- Tidak menjalankan migrate:fresh, refresh, rollback, truncate, atau menghapus database aplikasi.
- Backup tabel kategori, produk, order, dan detail lama tersimpan privat di `storage/app/private/backups/pawmart-before-phase5.json`.
- Migrasi telah dijalankan dengan `php artisan migrate --no-interaction`. Perbandingan sebelum/sesudah memastikan 3 produk, 3 kategori, 0 order, dan 0 order_details awal tidak berubah; 2 akun tetap tersedia.
- Pengujian utama menggunakan SQLite in-memory dengan pemeriksaan pengaman. Pengujian konkurensi memakai database terpisah `pawmart_phase5_testing`; hanya fixture milik test tersebut yang dibersihkan. Database pengujian tidak dihapus atau direset.

## Hasil pengujian

| Pemeriksaan | Hasil |
|---|---|
| Seluruh suite otomatis, termasuk regresi Tahap 1–4 | **156 test, 846 assertion lulus** |
| Konkurensi MySQL dengan dua proses PHP nyata | **2 test, 19 assertion lulus** |
| Jumlah keseluruhan dua suite | **158 test, 865 assertion lulus** |
| Pemeriksaan sintaks PHP | **99 file lulus**, file yang kemudian disempurnakan diperiksa ulang |
| Laravel Pint | Lulus |
| Build Vite produksi | Lulus |
| Kompilasi Blade | Lulus |
| Route aplikasi | 45 route terdaftar; cart, checkout, order dilindungi auth dan role:customer |
| Scheduler | orders:expire terdaftar setiap menit dengan withoutOverlapping |

Cakupan mencakup tambah/gabung/ubah/hapus keranjang, batas jumlah, stok, produk nonaktif/arsip, formulir, total palsu, perubahan harga, snapshot historis, token kedaluwarsa, submit berulang, pemisahan akun, akses guest/admin, pembatalan berulang, kedaluwarsa, pesanan terbayar yang tidak boleh dibatalkan, rollback seluruh keranjang, batas nominal database, pengalihan login aman, dan ID URL tidak valid.

Uji MySQL menahan row lock dari koneksi induk, memulai dua proses checkout, menunggu keduanya mencapai titik penguncian, kemudian melepas lock. Hasil: dua pelanggan dengan stok 1 menghasilkan tepat 1 order; dua submit pelanggan yang sama menghasilkan ID order yang sama dan satu pengurangan stok.

Regresi penghapusan akun tanpa pesanan sempat ditemukan saat penyesuaian urutan logout. Sudah diperbaiki dan seluruh suite di atas dijalankan kembali sampai lulus.

## File Tahap 5

File baru:

- `app/Models/Cart.php`
- `app/Models/CartItem.php`
- `app/Models/OrderItem.php`
- `app/Services/CartService.php`
- `app/Services/CheckoutService.php`
- `app/Services/StockReservationService.php`
- `app/Support/Money.php`
- `app/Http/Controllers/Customer/CartController.php`
- `app/Http/Controllers/Customer/CheckoutController.php`
- `app/Http/Controllers/Customer/OrderController.php`
- `app/Http/Requests/Customer/CartQuantityRequest.php`
- `app/Http/Requests/Customer/CheckoutRequest.php`
- `app/Console/Commands/ExpireOrders.php`
- `database/migrations/2026_11_16_135458_add_checkout_and_cart_tables.php`
- `resources/views/customer/cart.blade.php`
- `resources/views/customer/checkout.blade.php`
- `resources/views/customer/order.blade.php`
- `tests/Feature/Customer/CheckoutTest.php`
- `tests/Feature/Customer/OrderValidationTest.php`
- `tests/Integration/MySqlCheckoutConcurrencyTest.php`
- `tests/Support/checkout-worker.php`
- `docs/PHASE-5-REPORT.md`

File yang diperbarui dari implementasi sebelumnya:

- `.env.example`
- `app/Models/Order.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Http/Controllers/ProfileController.php`
- `config/pawmart.php`
- `routes/web.php`
- `routes/console.php`
- `resources/views/components/store-layout.blade.php`
- `resources/views/components/product-card.blade.php`
- `resources/views/customer/home.blade.php`
- `resources/views/customer/products/show.blade.php`
- `resources/views/customer/dashboard.blade.php`
- `tests/Feature/Customer/StorefrontTest.php` — ekspektasi tombol placeholder Tahap 4 disesuaikan dengan keranjang yang sekarang aktif.

Working tree sudah memuat perubahan Tahap 1–4 sebelum pekerjaan ini; daftar di atas hanya cakupan Tahap 5. Asset build berada di public/build sesuai konfigurasi proyek.

## Langkah menjalankan dan menguji manual

1. Aktifkan MySQL Laragon. Server lokal yang digunakan adalah `http://127.0.0.1:8000`. Jika belum aktif, jalankan `php artisan serve`.
2. Migrasi dan build sudah dijalankan. Pada checkout proyek baru jalankan `php artisan migrate`, `php artisan storage:link` jika link belum ada, dan `npm run build`. Jangan memakai migrate:fresh.
3. Login akun pelanggan yang tersedia: `customer@petshop.com` / `password`. Akun admin: `admin@petshop.com` / `password`.
4. Sebelum login, klik Tambah ke Keranjang pada katalog/detail. Pastikan menuju login, lalu kembali ke detail produk untuk memilih jumlah dan menambahkan produk.
5. Tambahkan produk dari katalog; tambahkan kembali produk yang sama. Pastikan satu baris dengan jumlah gabungan. Ubah jumlah, coba melebihi stok, lalu hapus. Periksa total dan kondisi kosong.
6. Tambahkan produk dan buka Checkout. Isi semua data alamat, lalu coba kode pos/telepon tidak valid. Setelah diperbaiki, klik Buat pesanan.
7. Pastikan halaman detail berisi nomor pesanan, snapshot item, subtotal + ongkir Rp15.000, status menunggu pembayaran, serta penjelasan Tahap 6. Tidak ada tombol pembayaran palsu.
8. Submit ulang request/token yang sama harus menuju pesanan yang sama. Keranjang harus kosong setelah checkout berhasil.
9. Batalkan pesanan pending. Pastikan stok kembali; mengirim ulang pembatalan tidak boleh menambah stok lagi.
10. Dengan pelanggan kedua, membuka `/orders/{id-pelanggan-lain}` harus 404. Admin yang membuka `/cart` atau `/checkout` harus 403.
11. Periksa halaman pada desktop dan smartphone, termasuk formulir panjang, notifikasi error, tabel/ringkasan, dan navigasi.

## Konfigurasi dan scheduler

Konfigurasi opsional di `.env` (nilai default sudah bekerja tanpa perubahan):

```dotenv
PAWMART_SHIPPING_COST=15000
PAWMART_RESERVATION_MINUTES=60
```

Setelah mengubah konfigurasi yang dicache, jalankan `php artisan config:clear`.

Untuk pelepasan reservasi otomatis selama pengembangan, jalankan di terminal terpisah:

```powershell
php artisan schedule:work
```

Untuk menjalankan pemeriksaan sekali:

```powershell
php artisan orders:expire
```

Tanpa scheduler aktif, pembatalan manual tetap melepas stok, tetapi pesanan yang melewati batas waktu menunggu command kedaluwarsa dijalankan. Scheduler tidak dibuat sebagai proses latar permanen oleh pengerjaan ini.

## Mengulang pengujian

```powershell
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit --no-progress
npm run build
php artisan view:cache
```

Pengujian konkurensi terpisah, menggunakan database test yang sudah disediakan:

```powershell
$env:DB_CONNECTION = 'mysql'
$env:DB_DATABASE = 'pawmart_phase5_testing'
try {
    php vendor/phpunit/phpunit/phpunit tests/Integration/MySqlCheckoutConcurrencyTest.php --no-progress
} finally {
    Remove-Item Env:DB_CONNECTION
    Remove-Item Env:DB_DATABASE
}
```

Jangan mengarahkan pengujian konkurensi ke database aplikasi. Test memiliki guard nama database dan melakukan migrate biasa, tidak mereset tabel.

## Batas tahap berikutnya

Midtrans, verifikasi pembayaran, invoice, serta manajemen pengiriman belum dibuat. Pada Tahap 6, handler pembayaran terverifikasi harus mengambil lock pesanan yang sama sebelum memfinalisasi reservasi; notifikasi terlambat dan rekonsiliasi gateway perlu ditangani sebelum mengubah status pembayaran. Tidak ada pembayaran yang ditandai berhasil oleh implementasi Tahap 5.
## Pemeriksaan browser

Pemeriksaan langsung melalui Chrome mode Samaran berhasil untuk pengalihan `/cart` → login → `/cart`, empty state, navigasi katalog, tombol Tambah ke Keranjang, notifikasi berhasil, tampilan item/subtotal, serta halaman checkout dan ringkasan Rp15.000 + Rp15.000 = Rp30.000. Layout diperiksa pada jendela desktop dengan form yang tersusun responsif. Satu item uji yang ditambahkan ke akun pelanggan dikembalikan ke kondisi kosong; tidak ada pesanan uji yang dibuat pada database aplikasi melalui browser.

Layanan browser dalam aplikasi sempat timeout; pemeriksaan dilanjutkan melalui Chrome menggunakan Computer Use. Uji submit order, pembatalan, dan konkurensi dibuktikan lewat pengujian otomatis. Tampilan pada perangkat smartphone fisik dan dark mode belum diperiksa secara visual; langkah uji manual tersedia di atas.
