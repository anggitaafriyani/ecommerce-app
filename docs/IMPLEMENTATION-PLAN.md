# PawMart — Analisis dan rencana implementasi

Audit: 16 November 2026. Acuan: seluruh PRD.md, khususnya bagian 15.

## Kondisi awal

- Laravel 13.42.0, PHP CLI 8.4.23, Composer 2.4.1, Node 24.15.0. Composer lama mengeluarkan deprecation pada PHP 8.4; gunakan Composer 2 terbaru.
- MVC Laravel dengan Breeze Blade, Tailwind 3, Alpine, dan Vite. Jangan memasang ulang Breeze karena akan menimpa kode yang sudah ada.
- Git sudah aktif; origin menuju https://github.com/anggitaafriyani/ecommerce-app.git. PRD.md awalnya belum dilacak Git. Tidak perlu membuat repository baru.
- Koneksi database berhasil; tujuh migration awal sudah dijalankan. Konfigurasi koneksi lokal dan APP_KEY tidak perlu diganti.
- Controller Auth sudah menyediakan registrasi, login, logout, reset password, konfirmasi password dan verifikasi email. ProfileController menyediakan edit profil/password dan hapus akun.
- LoginRequest sudah memvalidasi kredensial serta membatasi percobaan login. Registrasi masih memakai validasi inline dan semua login menuju dashboard yang sama.
- Model User memiliki hashing password dan proteksi mass assignment. Role belum fillable: pertahankan proteksi ini, tetapkan role secara eksplisit di server.
- RoleMiddleware sudah terdaftar di bootstrap/app.php tetapi belum dipakai pada route. Dashboard hanya memakai auth/verified; User belum mengimplementasikan MustVerifyEmail, sehingga verifikasi email belum menjadi syarat login.
- Model Category, Product, Order, OrderDetail masih kosong, belum memiliki relasi atau konfigurasi mass assignment.
- Seeder lama tidak idempoten, memakai password contoh, dan tidak aman untuk dijalankan ulang. Laravel menonaktifkan proteksi mass assignment saat menjalankan seeder, sehingga Admin lama memang tersimpan benar (terverifikasi di MySQL). Di luar proses seed, User::create dengan role dapat mengabaikan role; role tetap ditetapkan secara eksplisit oleh server. Model katalog belum siap digunakan melalui mass assignment biasa.
- Views masih bawaan Breeze/Laravel: welcome, dashboard, auth, profil, layout dan komponen. Belum ada antarmuka katalog atau transaksi PawMart.
- Route web/auth tersedia; belum ada controller/rute CRUD, keranjang, checkout, pesanan, pembayaran, atau webhook.
- Pengujian bawaan menggunakan SQLite in-memory, tetapi driver pdo_sqlite CLI belum aktif. Jalankan pengujian dengan extension CLI sementara, terpisah dari MySQL aplikasi.

## Selisih database terhadap PRD

| Tabel | Kondisi awal dan tindak lanjut |
|---|---|
| users | role enum admin/customer default customer sudah tersedia; phone belum tersedia, dijadwalkan bersama penyempurnaan profil |
| categories | hanya name; tambahkan slug, description, unique name setelah audit duplikat pada Tahap 3 |
| products | price integer, stock integer, image_path nullable; perlu slug, SKU, is_active, validasi stok/harga dan keputusan kompatibilitas image pada Tahap 3 |
| orders | total_price, payment_status pending/success/failed/expired, snap_token, resi_pengiriman; belum ada penerima/alamat, nomor order, subtotal/ongkir, order_status, reservasi stok |
| order_details | quantity dan subtotal_price; belum ada snapshot nama/harga. Migrasikan ke rancangan order_items secara bertahap tanpa menghilangkan riwayat |
| carts/cart_items | belum tersedia; Tahap 5, dengan unique cart_id/product_id |
| payments | belum tersedia; Tahap 6, termasuk ID Midtrans unik dan audit notifikasi |

FK kategori-produk sudah restrict. FK produk-detail juga restrict. FK user-order masih cascade: sebelum transaksi digunakan, tinjau penghapusan akun agar riwayat transaksi tidak terhapus. Penyesuaian dilakukan lewat migration tambahan dengan backfill dan verifikasi data, bukan mengubah migration yang sudah dijalankan.

## Delapan tahap PRD

| Tahap | Implementasi dan kriteria selesai |
|---|---|
| 1 — Persiapan dan analisis | Audit runtime, koneksi/schema/migration, controller/model/views/routes/config, GitHub, struktur Admin/Customer; dokumentasikan gap dan batas lingkup |
| 2 — Authentication dan role | Pertahankan Breeze; Form Request registrasi, role customer dari server, seeder admin idempoten tanpa password bawaan, middleware pada grup route, redirect sesuai role, pengujian auth dan akses silang |
| 3 — Dashboard dan CRUD Admin | Layout Admin, enam statistik database, CRUD kategori/produk, relasi Eloquent, migrasi aditif, upload Storage tervalidasi, harga/stok, unique kategori dan proteksi penghapusan |
| 4 — Katalog Pelanggan | Beranda PawMart, kartu produk, pencarian/filter, detail, produk aktif/stok habis, responsivitas; lengkapi profil phone sesuai FR-CUS-09 |
| 5 — Keranjang dan checkout | CartService/OrderService, tabel/relasi keranjang dan pesanan, data penerima, total server/ongkir konfigurasi, snapshot harga/nama, transaksi dan reservasi stok atomik |
| 6 — Midtrans Sandbox | Konfigurasi rahasia, Snap Token, pembayaran, webhook terverifikasi signature/nominal, idempotensi, status pembayaran terpisah, pelepasan stok sekali dan rekonsiliasi |
| 7 — Manajemen/riwayat pesanan | Semua pesanan untuk Admin, perubahan status hanya untuk pembayaran terverifikasi; pelanggan hanya melihat pesanan/invoice sendiri |
| 8 — Pengujian/dokumentasi | TC-01–TC-24, keamanan/harga/stok/notifikasi, desktop/mobile, README, screenshot, laporan akhir/demonstrasi, commit/push GitHub |

Hanya Tahap 1–2 dikerjakan pada iterasi ini. Halaman tujuan login bukan implementasi statistik atau katalog. Tidak menjalankan migrate:fresh, reset database, atau menghapus data pengguna. Pengujian otomatis memakai SQLite :memory: dan migration biasa.

Referensi implementasi autentikasi: https://laravel.com/framework/docs/13.x/authentication dan https://laravel.com/framework/docs/13.x/middleware.
