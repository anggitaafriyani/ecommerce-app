# PawMart Pet Shop

Website e-commerce kebutuhan hewan peliharaan berbasis Laravel 13, Blade, Tailwind CSS, Eloquent ORM, dan MySQL. Fitur Admin dan Pelanggan mencakup katalog, keranjang, checkout, pembayaran Midtrans Sandbox, pengiriman, riwayat, serta invoice ramah cetak.

Implementasi Tahap 1–7 tersedia. Audit/finalisasi Tahap 8 beserta batas verifikasinya dicatat dalam [laporan Tahap 8](docs/PHASE-8-REPORT.md). PRD merupakan acuan pengembangan, bukan pengganti laporan implementasi dan pengujian.

## Teknologi dan persyaratan

- PHP 8.4+ untuk aplikasi dan suite PHPUnit yang terpasang; extension PDO MySQL, PDO SQLite (test), mbstring, fileinfo, openssl, curl, dan extension standar Laravel.
- Laravel 13.42.0, PHPUnit 12, Composer 2 yang kompatibel dengan PHP setempat.
- MySQL dengan database aplikasi dan database pengujian terpisah.
- Node.js sesuai engine Vite: `^20.19.0 || >=22.12.0`; lingkungan audit menggunakan Node 24.15.0.
- Tailwind CSS 3.4.19, Vite 8.3.8, Alpine.js. Versi yang direproduksi mengikuti composer.lock dan package-lock.json.
- Laragon memudahkan PHP/MySQL lokal; ngrok diperlukan hanya untuk callback HTTPS ke server lokal.

## Proyek yang sudah tersedia

Pertahankan `.env`, APP_KEY, database, dan akun yang sudah ada. Jangan menyalin contoh konfigurasi di atas `.env` lama, menjalankan migrate:fresh, atau mengganti APP_KEY proyek yang sudah berjalan.

```powershell
composer install
npm ci
php artisan migrate --no-interaction
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Migration terbaru hanya menambah kolom telepon nullable pada users; tidak menghapus transaksi. Pada workspace pengembangan kolom ini sudah dimigrasikan. Jalankan scheduler di terminal terpisah:

```powershell
php artisan schedule:work
```

Aplikasi: http://127.0.0.1:8000. Untuk hot reload gunakan `npm run dev` sebagai pengganti build statis selama pengembangan. Untuk menjalankan dari hasil build, hentikan Vite dev server dengan benar agar tidak menyisakan referensi public/hot yang tidak aktif.

## Instalasi pada mesin baru

1. Ambil source dari repository yang sudah disetujui untuk dibagikan. Jalankan `composer install` dan `npm ci`.
2. Salin `.env.example` menjadi `.env` **hanya pada instalasi baru tanpa .env**.
3. Buat database MySQL kosong khusus aplikasi. Isi DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, dan DB_PASSWORD secara lokal. Gunakan DB_CONNECTION=mysql. Jangan memasukkan password ke README atau Git.
4. Pada instalasi baru, jalankan:

```powershell
php artisan key:generate --no-interaction
php artisan migrate --no-interaction
php artisan storage:link --no-interaction
npm run build
```

5. Isi konfigurasi Admin lalu jalankan AdminSeeder. Seeder tidak menghasilkan transaksi atau katalog palsu.
6. Jalankan server Laravel dan scheduler seperti bagian sebelumnya.

Gambar produk menggunakan disk public pada storage/app/public/products. Jika tautan public/storage sudah ada, tidak perlu membuat ulang. Bawa aset produk yang memang diizinkan ketika memindahkan instalasi; migration tidak membawa gambar atau isi database lama.

## Akun Admin dan Pelanggan

- Admin dibuat melalui `php artisan db:seed --class=AdminSeeder --no-interaction` setelah PAWMART_ADMIN_NAME, PAWMART_ADMIN_EMAIL, dan PAWMART_ADMIN_PASSWORD diisi secara lokal.
- Password Admin baru minimal 12 karakter, huruf besar/kecil, angka dan simbol, maksimal 72 byte. Seeder mempertahankan Admin yang sudah ada dan menolak menaikkan akun pelanggan menjadi Admin.
- Pelanggan mendaftar melalui `/register`. Registrasi selalu menghasilkan role customer.
- Gunakan akun demo milik sendiri. Tidak ada password akun dicantumkan dalam repository. Simpan kredensial demonstrasi di luar source code dan berikan kepada penguji melalui kanal yang sesuai.
- Audit menemukan password demo literal pada seeder dalam riwayat commit lama; dua akun lokal masih cocok saat audit. Ganti password melalui `/profile` sebelum membagikan repository atau demonstrasi publik. Seeder saat ini tidak mengandung password literal. Penghapusan riwayat Git tidak dilakukan otomatis.

## Fitur dan navigasi

| Pengguna | Halaman |
|---|---|
| Publik | `/`, `/products`, `/products/{slug}`, `/login`, `/register` |
| Admin | `/admin/dashboard`, `/admin/categories`, `/admin/products`, `/admin/orders` |
| Pelanggan | `/dashboard`, `/cart`, `/checkout`, `/orders`, `/orders/{id}`, `/orders/{id}/invoice`, `/orders/{id}/payment` |
| Kedua role | `/profile`, logout |

Admin mendapat enam statistik dari database. Kategori yang digunakan produk/arsip tidak dapat dihapus. Produk mendukung upload/ganti JPG/PNG/WebP maksimal 2 MB dan 4096×4096, harga/stok tervalidasi, nonaktif, serta arsip soft delete. Stok yang ditampilkan adalah stok tersedia setelah reservasi.

Checkout menghitung ulang harga di server, menyimpan snapshot order_items, mengunci stok, dan mencegah order ganda. Pengiriman hanya dapat maju Diproses → Dikirim → Selesai setelah pembayaran terverifikasi. Admin tidak dapat memaksa paid. Invoice menggunakan snapshot produk; tombol Cetak / Simpan PDF membuka dialog browser.

## Midtrans Sandbox dan webhook HTTPS

Isi sendiri variabel berikut di `.env`; contoh ini sengaja tidak berisi key:

```dotenv
MIDTRANS_ENVIRONMENT=sandbox
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_SIGNATURE_SECRET_KEY=
MIDTRANS_NOTIFICATION_URL=
```

Server Key digunakan untuk Basic Auth Snap/Core API. Untuk akun Sandbox PawMart yang sudah diuji, Signature Secret Key terpisah digunakan pada verifikasi SHA512 webhook dengan hash_equals, lalu status/nominal diverifikasi melalui API. Konfigurasi selain Sandbox ditolak. Client Key digunakan oleh SDK Snap; browser memang membutuhkan public Client Key untuk SDK, tetapi nilainya tidak ditulis pada dokumentasi/screenshot/Git.

1. Aktifkan Sandbox di dashboard Midtrans, ambil key yang sesuai, simpan hanya di `.env`.
2. Jalankan Laravel pada port 8000. Aktifkan tunnel dengan `ngrok http 8000` setelah autentikasi ngrok disiapkan secara privat; jangan menaruh authtoken dalam repository.
3. Atur MIDTRANS_NOTIFICATION_URL menjadi `https://DOMAIN-TUNNEL/midtrans/notification`. Gunakan URL yang sama pada Payment Notification URL dashboard Midtrans.
4. Jalankan `php artisan config:clear --no-interaction`; restart proses Laravel/scheduler jika memegang konfigurasi lama. Jangan membagikan hasil config:show midtrans.
5. Route webhook adalah **POST /midtrans/notification**, bukan URL halaman pembayaran atau `/api/midtrans/notification`.
6. Tunnel harus tetap aktif saat notifikasi dikirim. Inspeksi lokal ngrok dapat mengandung payload sensitif; jangan membagikan raw request atau signature.
7. Transaksi QRIS Sandbox Rp30.000 sebelumnya telah settlement dan webhook HTTP 200 terverifikasi. Gunakan bukti lama; pembayaran baru tidak diperlukan untuk demonstrasi dokumentasi. Detail bukti ada pada [laporan Tahap 6](docs/PHASE-6-REPORT.md).

Scheduler memeriksa reservasi setiap menit; gateway yang tidak dapat dipastikan tidak dianggap pembayaran gagal secara otomatis. Jangan memaksa status di database.

## Pengujian otomatis

Suite utama memverifikasi SQLite in-memory sebelum migration test; database aplikasi tidak direset.

```powershell
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit --no-progress
php vendor/laravel/pint/builds/pint --dirty --format agent
npm run build
php artisan view:cache --no-interaction
```

Jika PDO SQLite sudah aktif di php.ini, opsi extension dapat dihilangkan. Konkurensi memakai database terpisah yang harus disediakan bernama `pawmart_phase5_testing`:

```powershell
$env:DB_CONNECTION='mysql'
$env:DB_DATABASE='pawmart_phase5_testing'
try {
    php vendor/phpunit/phpunit/phpunit tests/Integration --no-progress
} finally {
    Remove-Item Env:DB_CONNECTION
    Remove-Item Env:DB_DATABASE
}
```

Jangan mengarahkan test konkurensi ke database aplikasi. Test melakukan migration biasa dan membersihkan fixture yang dibuatnya sendiri. Hasil terbaru, langkah uji browser pada lima ukuran layar, dan batas verifikasi tercantum di laporan Tahap 8.

## Struktur dan dokumentasi

- app/Http/Controllers dan Requests: HTTP, validasi, middleware role.
- app/Models: data dan relasi Eloquent.
- app/Services: katalog, cart, checkout, stok, pembayaran, pemrosesan pesanan.
- resources/views: Blade Admin/Pelanggan dan komponen bersama.
- database/migrations, factories, seeders: schema dan data uji/Admin.
- tests/Feature dan Integration: regresi, keamanan, konkurensi.
- [PRD](PRD.md), [rencana pengembangan](docs/IMPLEMENTATION-PLAN.md), [laporan Tahap 7](docs/PHASE-7-REPORT.md), [audit akhir dan panduan bukti/demonstrasi](docs/PHASE-8-REPORT.md).

## Kesiapan GitHub dan pengumpulan

Tidak ada commit atau push otomatis pada finalisasi ini. Periksa perubahan dan file untracked sebelum menyimpan hasil. `.env`, vendor, node_modules, build, log dan storage privat tidak dikumpulkan; source, lockfile, migration, seeder, .env.example, README dan laporan harus disertakan. Gunakan build dari source setelah instalasi.

Riwayat lokal memuat empat commit awal; perubahan Tahap 1–8 belum seluruhnya masuk commit. Jangan mengklaim riwayat pengembangan lengkap atau bebas password historis sebelum temuan audit ditangani. Tidak ada penulisan ulang riwayat atau perubahan remote. Laporan, screenshot asli, validasi visual, pemahaman kode, dan kebijakan AI dosen tetap menjadi bagian persiapan pengumpulan.
