# Laporan Tahap 6 — Integrasi Midtrans Sandbox

Tanggal: 16 November 2026. Cakupan: pembayaran Sandbox, verifikasi server, dan rekonsiliasi stok. Tahap 7 tidak dikerjakan.

## Ringkasan implementasi

1. Checkout sekarang mengarahkan pelanggan ke `/orders/{order}/payment`. Membuka ulang halaman tidak membuat order atau transaksi baru.
2. Tombol Bayar Sekarang meminta Snap Token melalui endpoint POST yang dilindungi authentication, role pelanggan, kepemilikan order, CSRF, dan rate limit. Token yang sudah tersedia digunakan kembali.
3. Midtrans Snap menggunakan endpoint Sandbox tetap. Environment selain sandbox ditolak; keabsahan kredensial ditentukan oleh autentikasi endpoint Sandbox, bukan awalan key. Tidak ada SDK/dependency tambahan; integrasi memakai HTTP client Laravel.
4. Halaman menampilkan nomor pesanan, tanggal, snapshot item, ongkir, total, status, pemeriksaan status, detail/alamat, dan pembatalan. Daftar pesanan sederhana hanya ditambahkan untuk navigasi kembali ke pembayaran; invoice dan manajemen pengiriman tetap di luar tahap ini.
5. Callback JavaScript success/pending/error/close hanya memicu pemeriksaan melalui server. Query parameter dari redirect tidak mengubah status pembayaran.
6. Webhook `/midtrans/notification` memvalidasi SHA-512 atas gabungan order_id, status_code, gross_amount, dan Server Key dengan perbandingan konstan waktu. Order dan nominal dicocokkan, kemudian status terkini diverifikasi lagi melalui API Midtrans. Ini mengikuti mekanisme [notifikasi resmi Midtrans](https://docs.midtrans.com/docs/https-notification-webhooks).
7. Pembayaran berhasil hanya diterima untuk settlement/capture, status_code 200, serta fraud_status yang memenuhi ketentuan. Capture harus fraud accept; challenge/deny tidak otomatis dianggap berhasil. Terminal gagal/batal/kedaluwarsa tidak dapat menurunkan pembayaran yang sudah terverifikasi berhasil.
8. Webhook ganda tidak menggandakan pengurangan atau pengembalian stok. Setiap pemeriksaan dicatat secara ringkas pada payment_events tanpa Server Key, signature, nomor kartu, atau payload pribadi lengkap.

## Model, status, dan integritas data

Migration `2026_11_16_142537_add_midtrans_payments.php` menambahkan:

- payments: satu baris unik per order, midtrans_order_id unik, transaction_id unik, token, nominal, payment_type, gateway status, attempted_at, checked_at, paid_at, serta review_reason.
- payment_events: sumber pemeriksaan, status gateway, hasil penerapan, dan waktu.
- payment_status pada orders diperluas dari enum menjadi string agar mendukung pending, paid, failed, expired, cancelled. Status success lama tetap dikenali sebagai lunas agar kompatibel; statistik Admin menghitung paid dan success.

Status proses normal menjadi processing setelah pembayaran terverifikasi. Konflik pembayaran menjadi payment_review, tidak dianggap pesanan selesai.

### Reservasi dan konkurensi

- Semua operasi pembayaran mengunci row order yang sama; pelepasan stok mengunci produk berurutan dan tetap dilakukan dalam transaksi database.
- Pembayaran berhasil mengubah reservasi menjadi pemakaian final tanpa mengurangi stok lagi.
- Pembayaran terminal yang terverifikasi melepas stok sekali. Pembatalan tanpa payment record tetap menggunakan mekanisme Tahap 5.
- Setelah payment attempt tercatat, service stok menolak pelepasan langsung tanpa rekonsiliasi gateway.
- Scheduler orders:expire memeriksa status Midtrans dahulu. Jika pending, scheduler meminta expire dan membaca ulang status. Jika pembayaran ternyata berhasil, stok tidak dikembalikan.
- Jika token Snap belum menghasilkan transaksi Core API, respons 404 tidak dianggap pembayaran gagal. Sesi Snap harus terkonfirmasi dibatalkan dan status diperiksa ulang sebelum stok dilepas. API mengikuti [pembatalan sesi Snap](https://docs.midtrans.com/reference/cancel-a-snap-session).
- Bila gateway tidak tersedia atau hasil pembatalan tidak pasti, stok tetap ditahan dan review_reason dicatat. Scheduler berikutnya dapat mencoba rekonsiliasi kembali.
- Jika pembayaran menang saat permintaan pembatalan berlangsung, status tetap paid dan order ditandai payment_review agar pengelola menangani konflik tersebut; stok tidak dikembalikan.
- Jika pembayaran berhasil datang setelah stok terlepas, pembayaran dicatat paid tetapi order menjadi payment_review. Stok tidak dipotong ulang dan pesanan tidak otomatis diproses; pengelola harus menangani konflik secara operasional.
- Attempt disimpan sebelum panggilan pembuatan token. Timeout/crash tidak memicu pengiriman create transaksi kedua secara otomatis. Jika token tidak dapat dipastikan dan transaksi belum muncul di API, perlu pemeriksaan pengelola. Jangan menghapus marker attempt atau memaksa paid secara manual.

### Nominal dan masa berlaku

Nominal berasal dari snapshot order, bukan browser. Midtrans memerlukan rupiah bulat, sehingga nominal/item pecahan rupiah ditolak dengan pesan yang jelas; tidak dibulatkan diam-diam. Produk/checkout lama tetap mendukung decimal untuk kompatibilitas. Untuk membayar produk dengan harga pecahan, Admin perlu memperbaiki harga rupiah bulat dan pelanggan membuat pesanan baru, tanpa mengubah snapshot pesanan lama. Rujukan: [transaction details](https://docs.midtrans.com/reference/transaction-details-object), [item details](https://docs.midtrans.com/reference/item-details-object).

Expiry dikirim dengan start_time eksplisit (zona Asia/Jakarta) dan durasi dari created_at → expires_at, sehingga memilih metode pembayaran belakangan tidak memperpanjang reservasi. Durasi dibatasi 5 menit sampai 7 hari. Pembayaran baru ditutup jika sisa reservasi tidak lebih dari satu menit. Rujukan: [custom expiry](https://docs.midtrans.com/docs/snap-advanced-feature), [Snap session expiry](https://docs.midtrans.com/reference/expire-a-snap-session).

## Keamanan database

- Tidak menjalankan migrate:fresh, refresh, truncate, rollback, atau menghapus database.
- Migration baru sudah diterapkan menggunakan migrate biasa pada MySQL aplikasi.
- Backup privat tersedia di `storage/app/private/backups/pawmart-before-phase6.json`.
- Sebelum migrasi terdapat 3 produk, 3 kategori, 0 orders, dan 0 order_items. Data awal dipertahankan; tidak dibuat transaksi pembayaran palsu di database aplikasi.
- Database MySQL `pawmart_phase5_testing` yang sudah tersedia dipakai untuk pengujian konkurensi. Hanya fixture yang dibuat test tersebut yang dibersihkan; database tidak direset.
- Kunci rahasia tidak ditambahkan ke source code atau dicetak. `.env` lokal tidak diubah.

## Hasil pengujian

| Pemeriksaan | Hasil |
|---|---|
| Seluruh suite aplikasi Tahap 1–6 | 204 test, 1228 assertion lulus setelah perbaikan key webhook |
| Konkurensi MySQL terpisah | 5 test, 48 assertion lulus |
| Sintaks PHP | 110 file lulus |
| Laravel Pint | Lulus |
| Build Vite | Lulus |
| Kompilasi Blade | Lulus |
| Migrasi MySQL dan route pembayaran | Berhasil |
| Transaksi Sandbox nyata | QRIS settlement; retry webhook HTTP 200, event webhook paid_preserved, stok tetap 99. Lihat verifikasi 17 November 2026 di bawah. |

Pengujian otomatis menggunakan Http::fake dan preventStrayRequests. Tidak ada panggilan pembayaran nyata dari test. Lima test MySQL mencakup dua test checkout Tahap 5, dua proses pembuatan token untuk satu order, scheduler bertemu pembayaran berhasil, serta pembayaran terlambat saat pelepasan stok. API pada worker tetap dimock, sedangkan transaksi/row lock benar-benar menggunakan MySQL.

Cakupan tambahan: snapshot nominal, expiry, token reuse, pending/paid/failed/expired/cancelled, fraud challenge, kode status tidak sesuai, signature salah, nominal salah, identitas transaksi berubah, unknown order, webhook duplikat, outage/timeout, 404 sementara, akses pelanggan lain/Admin, callback palsu, larangan perubahan status manual, stok final/release, serta status review untuk konflik.

## File yang ditambahkan

- `app/Models/Payment.php`
- `app/Services/MidtransClient.php`
- `app/Services/PaymentService.php`
- `app/Http/Controllers/Customer/PaymentController.php`
- `app/Http/Controllers/MidtransNotificationController.php`
- `app/Http/Requests/MidtransNotificationRequest.php`
- `config/midtrans.php`
- `database/migrations/2026_11_16_142537_add_midtrans_payments.php`
- `resources/views/customer/payment.blade.php`
- `resources/views/customer/orders.blade.php`
- `resources/js/payment.js`
- `tests/Feature/Customer/PaymentTest.php`
- `tests/Integration/MySqlPaymentConcurrencyTest.php`
- `tests/Support/payment-worker.php`
- `docs/PHASE-6-REPORT.md`

## File yang diperbarui

- `.env.example`
- `app/Models/Order.php`
- `app/Services/StockReservationService.php`
- `app/Console/Commands/ExpireOrders.php`
- `app/Http/Controllers/Customer/CheckoutController.php`
- `app/Http/Controllers/Customer/OrderController.php`
- `app/Http/Controllers/Admin/DashboardController.php`
- `bootstrap/app.php`
- `routes/web.php`
- `resources/js/app.js`
- `resources/views/customer/order.blade.php`
- `resources/views/customer/checkout.blade.php`
- `resources/views/customer/dashboard.blade.php`
- `resources/views/components/store-layout.blade.php`
- `tests/Feature/Customer/CheckoutTest.php` — redirect sekarang ke pembayaran, dan pesan placeholder Tahap 5 disesuaikan.

Perubahan Tahap 1–5 yang sudah ada di working tree tetap dipertahankan. PRD dan konfigurasi database tidak diubah.

## Konfigurasi yang harus diisi pengguna

Isi hanya kredensial dari dashboard **Sandbox**, secara lokal pada `.env`:

```dotenv
MIDTRANS_ENVIRONMENT=sandbox
MIDTRANS_SERVER_KEY=
MIDTRANS_SIGNATURE_SECRET_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_NOTIFICATION_URL=https://DOMAIN-PUBLIK/midtrans/notification
```

Gunakan Server Key dan Client Key langsung dari dashboard Sandbox tanpa mengubah nilainya. Awalan key tidak digunakan untuk menentukan environment; Client Key dengan awalan Mid-client- maupun SB-Mid-client- tidak ditolak hanya karena formatnya. Nilai di atas sengaja tidak diberi kredensial palsu. Jangan memasukkan key ke Git atau laporan.

- Sediakan domain/tunnel HTTPS publik menuju aplikasi dan atur APP_URL sesuai alamat publik. Webhook localhost tidak dapat dijangkau Midtrans.
- Pada dashboard Midtrans Sandbox, atur Payment Notification URL ke `https://DOMAIN-PUBLIK/midtrans/notification`.
- Finish/Unfinish/Error Redirect URL dashboard dapat menuju `/orders`. Per transaksi, backend mengirim finish callback ke `/orders/{id}/payment`; URL ini hanya menampilkan data pemilik dan tidak mengesahkan pembayaran.
- MIDTRANS_NOTIFICATION_URL opsional sebagai override per transaksi; tetap konfigurasikan URL notifikasi di dashboard.
- Setelah mengisi environment, jalankan `php artisan config:clear` dan restart server bila diperlukan.
- Jalankan `php artisan schedule:work` pada terminal terpisah selama pengujian lokal. Untuk pemeriksaan satu kali: `php artisan orders:expire`.

Pada implementasi awal belum dibuat tunnel publik atau akun Midtrans otomatis. Pengguna kemudian mengonfigurasi Sandbox dan berhasil menguji QRIS; bukti serta batas verifikasi terbaru dicatat pada bagian akhir laporan.

## Langkah pengujian manual

1. Login pelanggan, pilih produk dengan harga rupiah bulat, tambahkan ke keranjang, dan checkout. Pastikan diarahkan ke halaman pembayaran tanpa order tambahan.
2. Tanpa kredensial, pastikan muncul pemberitahuan konfigurasi belum tersedia dan tidak ada tombol Bayar Sekarang aktif.
3. Setelah kredensial Sandbox dan HTTPS publik siap, klik Bayar Sekarang. Pastikan Snap menampilkan nomor/nominal sesuai order. Gunakan simulator resmi Sandbox untuk metode yang dipilih.
4. Tutup popup atau pilih pembayaran pending; halaman harus memeriksa status melalui backend. Buka ulang dan pastikan token/order yang sama digunakan.
5. Selesaikan pembayaran Sandbox. Setelah webhook/periksa status, pastikan payment_status paid, order_status processing, dan stok tidak berkurang lagi.
6. Uji gagal, batal, dan kedaluwarsa; pastikan stok kembali sekali saja. Kirim ulang notifikasi melalui fasilitas pengujian Midtrans dan pastikan tidak ada perubahan stok ganda.
7. Jalankan scheduler pada pesanan yang melewati expires_at. Jika gateway tidak bisa dihubungi, pastikan stok ditahan dan muncul peringatan pemeriksaan, bukan dianggap gagal otomatis.
8. Gunakan pelanggan kedua untuk membuka URL pembayaran/pemeriksaan milik pelanggan pertama: harus 404. Admin harus ditolak menggunakan endpoint pelanggan.
9. Ubah query redirect menjadi transaction_status=settlement tanpa pembayaran: status aplikasi tidak boleh berubah menjadi paid.
10. Periksa layout desktop/smartphone, tombol status, riwayat pembayaran sederhana, serta notifikasi error.

Pengelola dapat menelusuri payment_events dan payments.review_reason untuk kasus tidak pasti. Resolusi konflik/refund perlu mengacu pada transaksi gateway yang nyata; tahap ini tidak menyediakan tombol Admin untuk memaksa pembayaran berhasil. Pengujian visual mobile end-to-end belum diverifikasi dalam audit ini.

## Menjalankan pengujian kembali

```powershell
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit --no-progress
php vendor/laravel/pint/builds/pint --dirty --format agent
npm run build
php artisan view:cache
```

Pengujian MySQL terpisah:

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

Pada lingkungan baru, database pengujian harus disediakan terpisah terlebih dahulu. Test memeriksa driver/nama database, lalu menjalankan migrate biasa. Jangan mengganti target menjadi database aplikasi.
## Verifikasi transaksi Sandbox nyata — 17 November 2026

Pengguna melaporkan simulator QRIS berstatus PAID dan halaman PawMart menampilkan Pembayaran berhasil / Diproses. Audit database dan pemeriksaan langsung ke API Midtrans mengonfirmasi:

| Data | Hasil |
|---|---|
| Order | ID 2 — PWM-01M7JGX9P7HT3P6X7NVM4M6EGQ |
| Status order | payment_status=paid, order_status=processing |
| Payment | ID 1, payment_type=qris, transaction_status=settlement |
| Total order / nominal payment | Rp30.000,00 / Rp30.000,00 — sesuai |
| Rincian | 1 Kalung Lonceng Kulit Sintetis × Rp15.000 + ongkir Rp15.000 |
| Finalisasi stok | stock_reserved=false, stock_released_at=null |
| Review pembayaran | null |
| Waktu paid pertama | 16 November 2026, 23:06:49 WIB |
| Pemeriksaan status sebelum retry | 17 November 2026, 02:31:04 WIB |
| Retry webhook berhasil | 17 November 2026, 02:54:11 WIB — HTTP 200 |
| Stok sebelum / setelah pemeriksaan terbaru | 99 / 99 |
| Jumlah order sebelum / setelah | 2 / 2 |
| Jumlah payment sebelum / setelah | 1 / 1 |

### Stok dan pemeriksaan status

Backup sebelum Tahap 6 mencatat stok produk ID 3 sebanyak 100. Satu order sebelumnya dibatalkan dengan pelepasan reservasi, dan order ID 2 membeli satu unit. Stok aktual 99 konsisten dengan satu pengurangan bersih. Pengulangan pemeriksaan gateway tidak mengurangi stok lagi.

Audit menjalankan PaymentService::sync, service yang dipakai tombol Periksa Status Pembayaran. Status gateway kembali settlement dan aplikasi mempertahankan paid/processing. Tidak ada pemanggilan create transaksi atau perubahan status manual; hanya checked_at serta catatan pemeriksaan normal yang bertambah. Endpoint tombol tersebut juga tercakup dalam pengujian otomatis.

### Bukti webhook: retry berhasil setelah perbaikan key

Riwayat payment_events untuk payment ID 1:

- Event 1: source=status, gateway_status=settlement, result=paid.
- Event 2: source=status, gateway_status=settlement, result=paid_preserved.
- Event 3: source=status, gateway_status=settlement, result=paid_preserved, tercatat saat audit sebelum retry.
- Event 4: source=webhook, gateway_status=settlement, result=paid_preserved, tercatat 17 November 2026 pukul 02:54:14 WIB (16 November 2026 pukul 19:54:14 UTC pada database).

Inspector ngrok mengonfirmasi POST `/midtrans/notification` untuk order ini pada 17 November 2026 pukul **02:54:11 WIB mendapat HTTP 200**. Event 4 membuktikan webhook berhasil diproses dan pembayaran paid dipertahankan. Pengguna melaporkan Notification History Midtrans berstatus **Completed** (hijau). Riwayat sebelumnya pada 02:40:27 WIB tetap tercatat HTTP 403 / Undelivered; itu merupakan percobaan sebelum perbaikan, bukan kegagalan retry terbaru.

Konfigurasi URL notifikasi tersedia, menggunakan HTTPS, dan path sesuai `/midtrans/notification`. Tunnel HTTPS aktif menuju `http://localhost:8000`. Temuan HTTP 404 dan inspector tidak tersedia pada pemeriksaan sebelumnya tidak lagi menggambarkan kondisi terbaru: bukti POST menunjukkan tunnel berhasil meneruskan request ke Laravel.

Perbandingan dilakukan hanya di memori, tanpa menampilkan atau menyimpan key, signature, maupun payload lengkap:

- Nomor order dan nominal webhook cocok dengan order ID 2.
- Identitas transaksi, seluruh field pembentuk signature, dan signature webhook sama dengan respons GET Status API Midtrans yang diautentikasi.
- API tetap memberikan `settlement` dengan nominal Rp30.000; Server Key tersedia dan autentikasi API berhasil.
- Signature webhook maupun respons API tidak cocok dengan SHA512 atas gabungan `order_id`, `status_code`, `gross_amount`, dan Server Key konfigurasi saat ini.

Implementasi awal memakai Server Key sesuai formula pada dokumentasi publik webhook. Pengguna kemudian mengonfirmasi Signature Secret Key terpisah pada dashboard Sandbox dan mengisinya secara lokal. Pembandingan baca-saja terhadap respons API transaksi ini membuktikan signature cocok dengan Signature Secret Key tersebut, sedangkan Server Key tidak cocok. Temuan ini berlaku untuk konfigurasi Sandbox PawMart yang diuji, bukan klaim bahwa semua akun Midtrans menggunakan konfigurasi yang sama.

Perbaikan menambahkan `MIDTRANS_SIGNATURE_SECRET_KEY` ke `.env.example` dan `config/midtrans.php`. `PaymentService` membaca `config('midtrans.signature_secret_key')` untuk perhitungan SHA512 dan perbandingan `hash_equals`; key kosong/tidak valid ditolak tanpa fallback ke Server Key. Snap/Core API tetap diautentikasi menggunakan Server Key. Environment selain Sandbox tetap ditolak. Identitas dan nominal pesanan tetap diperiksa, kemudian status/fraud/identitas transaksi diverifikasi melalui API Midtrans. Tidak ada pelonggaran validasi signature.

File perbaikan: `.env.example`, `config/midtrans.php`, `app/Services/PaymentService.php`, dan `tests/Feature/Customer/PaymentTest.php`. Laporan ini diperbarui setelah hasil retry nyata dikonfirmasi. Pengujian tambahan mencakup penolakan signature berbasis Server Key, secret kosong/tidak valid, dan environment Production; pengujian nominal palsu, signature palsu, settlement berulang, autentikasi API, dan larangan downgrade paid tetap lulus.

Pengguna melakukan Retry untuk notifikasi Settlement **order yang sama**. Setelah retry, database tetap menunjukkan payment_status=paid, order_status=processing, nominal order/payment Rp30.000, stock_reserved=false, stock_released_at=null, serta stok produk ID 3 **99**. Tidak terjadi pengurangan atau pelepasan stok tambahan. Jumlah order tetap 2 dan payment tetap 1.

Pemeriksaan lanjutan setelah pengguna mengaktifkan layanan hanya menggunakan GET Status API dan pembacaan database/inspector. Tidak menjalankan sinkronisasi yang menulis ke database, mengirim webhook buatan, atau mengubah status pesanan. Jumlah order tetap 2, payment tetap 1, dan stok tetap 99. Pemanggilan sync pada bagian sebelumnya merupakan pemeriksaan terdahulu pukul 02:31 WIB.

### Pengujian ulang

- Seluruh suite aplikasi setelah perbaikan: **204 test, 1.228 assertion lulus**, menggunakan SQLite in-memory.
- Suite pembayaran: **48 test, 382 assertion lulus**. Pemeriksaan sintaks PHP dan Pint lulus.
- Konkurensi MySQL terpisah: **5 test, 48 assertion lulus** setelah MySQL aktif kembali.
- MySQL sempat menolak koneksi saat awal kelanjutan audit; pemeriksaan database, API status, dan test MySQL kemudian diulang dan berhasil. Kendala sementara tersebut sudah teratasi.
- Tidak ada reset database, penghapusan order aplikasi, atau perubahan status pembayaran manual. Perubahan file pada audit ini hanya laporan ini. Data fixture pengujian berada pada database sementara/terpisah.

### Kesimpulan terhadap PRD

Pembayaran QRIS Sandbox, kecocokan nominal, penyimpanan order/payment, finalisasi stok satu kali, pemeriksaan status server, dan penerimaan webhook terverifikasi sudah terbukti berjalan. Retry nyata menghasilkan HTTP 200 dan event webhook paid_preserved tanpa perubahan stok. **Kendala webhook Tahap 6 telah terselesaikan dan verifikasi akhir integrasi Sandbox yang diminta telah terpenuhi**, dengan perlindungan signature tetap aktif. Hasil ini tidak menyatakan kesiapan Production atau bahwa seluruh metode pembayaran telah diuji secara nyata; cakupan nyata adalah QRIS dan retry Settlement pesanan ini. Tahap 7 tidak dikerjakan.
