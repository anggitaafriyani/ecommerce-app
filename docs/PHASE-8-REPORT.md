# PawMart Pet Shop — Audit, Pengujian, dan Finalisasi Tahap 8

Tanggal audit: 17 November 2026. Acuan: seluruh PRD.md dan ketentuan dosen yang disampaikan pengguna dalam percakapan. PDF asli tidak diberikan pada tahap ini; audit ketentuan dosen menggunakan isi ketentuan yang dikutip pengguna, bukan klaim pemeriksaan berkas PDF asli.

## 1. Ringkasan dan keputusan kesiapan

PawMart mengimplementasikan Laravel MVC, Blade/Tailwind, Eloquent/MySQL, dua role, administrasi katalog, keranjang, checkout, Midtrans Sandbox, pemrosesan pesanan, riwayat, profil, dan invoice. Tahap 8 mengaudit implementasi, memperbaiki kekurangan profil/pencarian, menjalankan regresi, serta menyiapkan dokumentasi pengumpulan.

**Implementasi inti dan pengujian otomatis siap; pengumpulan akhir masih bersyarat.** Pengujian visual lima ukuran layar dan print preview belum dilakukan secara nyata; password demo historis masih dipakai dua akun lokal pada saat audit; commit/push terbaru belum diizinkan; screenshot asli dan penyusunan laporan akademik final dengan identitas mahasiswa masih perlu diselesaikan. Tidak ada klaim seluruh persyaratan pengumpulan sudah tuntas.

Tidak melakukan commit, push, deployment, reset database, migrate:fresh, penghapusan transaksi, perubahan password manual, atau pembayaran baru. PRD tidak diubah.

## 2. Audit fitur PRD

Lokasi di bawah relatif terhadap root proyek. Lulus otomatis berarti pengujian HTTP/unit/integrasi yang disebut benar-benar dijalankan, bukan pengganti pengujian visual.

| Fitur | Ketentuan PRD | Status implementasi | Lokasi implementasi | Bukti pengujian | Kekurangan/batas |
|---|---|---|---|---|---|
| Authentication | FR-ADM-01, FR-CUS-01: registrasi/login/logout | Selesai | Controllers/Auth, Requests/Auth, routes/auth.php | AuthenticationTest, RegistrationTest, PasswordSafetyTest lulus | Visual form belum diuji ulang pada lima viewport |
| Role | §4, NFR-01: Admin/customer dan redirect | Selesai | RoleMiddleware, User, bootstrap/app.php, routes/web.php | RoleAccessTest, CatalogTest, CheckoutTest, OrderManagementTest lulus | Password demo lama perlu diganti |
| Dashboard | FR-ADM-02: enam statistik database | Selesai | Admin/DashboardController; admin/dashboard.blade.php | CatalogTest::test_dashboard_counts_real_data_and_existing_payment_statuses lulus | Bukan angka statis |
| Kategori | FR-ADM-03: CRUD unik, proteksi relasi | Selesai | Admin/CategoryController, CategoryRequest, Category | CatalogTest CRUD/duplikat/produk arsip lulus | Kategori terpakai memang tidak dapat dihapus |
| Produk | FR-ADM-04, §3: CRUD, harga, stok, gambar, status | Selesai | Admin/ProductController, ProductRequest, ProductService, Product | CatalogTest create/edit/replace image/invalid upload/rollback/archive lulus | Data produk awal tanpa foto menampilkan placeholder |
| Beranda | FR-CUS-02: navbar, hero, kategori, produk, footer | Selesai | Customer/StorefrontController, customer/home, store-layout | StorefrontTest home/navigation lulus | Visual responsive masih perlu bukti |
| Katalog | FR-CUS-03: search/filter/pagination | Selesai; bug q=0 diperbaiki | StorefrontController, CatalogRequest, customer/products/index | StorefrontTest + FinalAuditTest lulus | Pencarian menggunakan LIKE; bukan full-text |
| Detail produk | FR-CUS-04: gambar/harga/stok/jumlah | Selesai | customer/products/show, StorefrontController | StorefrontTest stok nol/nonaktif/arsip lulus | Produk aktif stok nol dapat dilihat, tidak dibeli |
| Keranjang | FR-CUS-05: tambah/update/hapus/total per akun | Selesai | CartController, CartService, Cart/CartItem, customer/cart | CheckoutTest cart/isolation/quantity lulus | Stok divalidasi ulang di checkout |
| Checkout | FR-CUS-06: alamat, ongkir server, total server | Selesai | CheckoutRequest, CheckoutService, config/pawmart | CheckoutTest, OrderValidationTest lulus | Ongkir simulasi tetap, bukan API ekspedisi |
| Order | §7–9: snapshot, atomik, unik | Selesai | CheckoutService, Order/OrderItem, migrations | CheckoutTest snapshot/repeat/rollback + MySQL concurrency lulus | Tidak ada transaksi demo baru pada database aplikasi |
| Pembayaran | FR-CUS-07, §8: Midtrans Sandbox | Selesai | MidtransClient, PaymentService, PaymentController, payment.js | PaymentTest lulus; QRIS nyata sebelumnya settlement Rp30.000 | Metode selain QRIS belum diuji nyata |
| Webhook | §8.4: signature, nominal, server status | Selesai | MidtransNotificationRequest/Controller, PaymentService, config/midtrans | PaymentTest + bukti ngrok HTTP 200 dan event webhook Tahap 6 | Signature Secret Key khusus konfigurasi Sandbox yang telah dibuktikan |
| Reservasi stok | §8.5, NFR-08: atomik/final/release sekali | Selesai | CheckoutService, StockReservationService, PaymentService, orders:expire | CheckoutTest/PaymentTest dan 5 test MySQL lulus | Ketidakpastian gateway menahan stok untuk review |
| Manajemen order | FR-ADM-05/06: daftar/detail/filter/status | Selesai | Admin/OrderController, OrderFulfillmentService, Order requests | OrderManagementTest lulus | Transisi mundur/lompat/paid manual ditolak |
| Riwayat/detail | FR-CUS-08: hanya pemilik, tanggal/status | Selesai | Customer/OrderController, PaymentController@index, customer/orders/order | OrderManagementTest dan PaymentTest lulus | Perkembangan berdasarkan status saat ini, bukan pelacakan ekspedisi |
| Invoice | FR-CUS-08: snapshot transaksi, cetak | Selesai kode/HTTP | customer/invoice, OrderController@invoice | OrderManagementTest historis/owner-only lulus | Dialog cetak/PDF dan layout cetak belum diperiksa visual |
| Profil | FR-CUS-09: nama/email/telepon | Selesai setelah perbaikan | ProfileUpdateRequest, User, profile partial; migration phone | ProfileTest + FinalAuditTest telepon lulus | Telepon opsional; data lama tetap null |
| Database/relasi | §9, NFR-03: MySQL/FK/Eloquent | Selesai | database/migrations, app/Models | migrations test, MySQL concurrency, orphan check lulus | order_details legacy dipertahankan; transaksi aktif memakai order_items |
| UI/aksesibilitas | §11, NFR-04/05 | Implementasi tersedia; verifikasi manual tertunda | Blade layouts/components, app.css, Tailwind config | Build dan render HTTP lulus | Tidak dapat dinyatakan lulus viewport/kontras/print sebelum inspeksi nyata |
| Dokumentasi/Git | §15/18, NFR-10 | README/laporan disiapkan; publikasi tertunda | README, docs, .env.example, Git | Audit lokal 4 commit/114 blob | Password literal historis; banyak perubahan belum committed; push menunggu persetujuan |

## 3. Kesesuaian ketentuan dosen dan rubrik

| Komponen | Bobot | Bukti PawMart | Status audit |
|---|---:|---|---|
| Database/migration/model/relasi | 10% | MySQL, FK, Eloquent, migration tambahan, test integritas | Terimplementasi dan diuji |
| Authentication/role/middleware | 10% | Dua role, registrasi customer, middleware auth/role | Test lulus; password demo harus diganti |
| Fungsionalitas Admin | 15% | Enam statistik, kategori, produk/gambar, order/status | Test lulus |
| Fungsionalitas Pelanggan | 15% | Katalog, profil, detail, riwayat/invoice | Test lulus; print manual tertunda |
| Cart/Checkout/Order | 15% | Keranjang akun, snapshot, transaksi, penguncian stok | Test termasuk MySQL konkurensi lulus |
| Midtrans/verifikasi | 15% | QRIS Sandbox, signature, API verification, idempotensi | Otomatis + bukti nyata Tahap 6 |
| Pengujian | 5% | 232 test utama + 5 MySQL, laporan hasil | Otomatis selesai; checklist visual belum diisi |
| UI/UX/responsif | 10% | Tema PawMart dan layout responsif | Implementasi tersedia; bukti visual wajib dilengkapi |
| GitHub/dokumentasi/demo | 5% | Source, README, laporan, daftar bukti, panduan demo | Belum siap publikasi sampai temuan Git/password ditangani dan push disetujui |

Bobot bukan nilai yang diklaim diperoleh. Penilaian akhir merupakan kewenangan dosen.

Ketentuan MVC, MySQL/Eloquent, dua role dan Sandbox telah dipenuhi dalam implementasi. Branding PawMart, domain kebutuhan hewan, Blade, serta service transaksi khusus dapat dijelaskan dari source. Audit ini **tidak membuktikan keaslian seluruh riwayat proyek atau kepatuhan kebijakan AI yang belum diberikan**. Kinetic Laptops hanya boleh dijelaskan sebagai referensi jika memang digunakan; mahasiswa harus mengungkapkan penggunaan referensi/AI sesuai aturan dosen dan mampu menjelaskan kode sendiri. Library utama: Laravel, Breeze untuk scaffolding autentikasi, Tailwind/Alpine untuk UI, PHPUnit/Pint untuk pengujian/format, Vite untuk build. Tidak menambah package pada Tahap 8.

Pengumpulan harus memuat repository dan riwayat commit yang jujur, laporan akademik implementasi/pengujian, serta screenshot asli. PRD tidak menggantikan laporan akhir. Jangan membuat commit mundur/tanggal palsu untuk meniru perkembangan.

## 4. Bug dan perbaikan

| Temuan | Perbaikan | Verifikasi |
|---|---|---|
| Telepon profil pada FR-CUS-09 belum tersedia | Tambah users.phone nullable, fillable terbatas, validasi dan input tel | Simpan, kosongkan, pertahankan saat field tidak dikirim, input invalid, role tetap customer diuji |
| Query pencarian string `0` dianggap false | Kondisi eksplisit null/string kosong pada katalog, produk Admin dan pesanan Admin | Produk/order cocok tampil, noncocok tidak tampil |
| Relasi balik yang disebut PRD belum lengkap | User::orders/cart, Cart::user, OrderItem::order | Schema relasional tetap; regresi seluruh suite lulus |
| README menyebut Tahap 5–7 belum dibuat | Tulis ulang sesuai implementasi, Sandbox key terpisah, scheduler dan prosedur test | Pemeriksaan konsistensi route/config/report |
| Password demo literal di commit lama | Seeder aktif sudah memakai konfigurasi; temuan historis diungkapkan | Dua akun lokal masih cocok; tidak mereset password atau menulis ulang Git tanpa tindakan pengguna |

Migration `2026_11_17_003448_add_phone_to_users_table.php` sudah dijalankan secara spesifik pada database lokal. Hanya menambah kolom nullable. Rollback kolom akan menghapus nilai telepon yang disimpan kemudian; gunakan perbaikan maju bila perlu. Tidak mengedit migration yang sudah dipakai sebelumnya.

## 5. Hasil pengujian otomatis dan teknis

| Pemeriksaan | Hasil aktual |
|---|---|
| FinalAuditTest baru | 7 test, 44 assertion lulus |
| Suite Unit/Feature Tahap 1–8 | **232 test, 1.473 assertion lulus** |
| Integrasi konkurensi MySQL terpisah | **5 test, 48 assertion lulus** |
| Sintaks PHP app/config/routes/database/tests | **113 file, 0 kegagalan** |
| Pint --dirty --format agent | Lulus setelah normalisasi line ending test |
| npm run build | Lulus, Vite 8.3.8/Tailwind 3.4.19 |
| Blade view:cache | Lulus |
| Routes | 54 route aplikasi (58 termasuk vendor/health) terdaftar |
| Tautan Blade literal | Tidak ada helper route literal yang mengarah ke nama route tidak terdaftar |
| Form POST Blade | Tidak ditemukan form POST tanpa directive CSRF pada pemeriksaan statis |
| Scheduler | orders:expire terdaftar setiap menit |

Audit route literal/form bersifat statis; tidak menyatakan semua interaksi JavaScript dan browser sudah diperiksa. Kasus `$request->route('token')` pada reset password dibedakan dari helper URL route() sehingga tidak dianggap tautan rusak.

Suite utama memakai SQLite in-memory dengan guard sebelum migrate biasa. Suite MySQL hanya menerima `pawmart_phase5_testing`; fixture dibersihkan oleh test. Tidak menjalankan pengujian mutasi pada database aplikasi.

### Pemetaan skenario PRD §14

| Skenario | Bukti | Hasil |
|---|---|---|
| TC-01/02 registrasi valid/duplikat | Auth/RegistrationTest | Lulus otomatis |
| TC-03/04 login benar/salah | Auth/AuthenticationTest | Lulus otomatis |
| TC-05 role Admin | Auth/RoleAccessTest, Admin/CatalogTest | Lulus otomatis |
| TC-06 kategori | Admin/CatalogTest | Lulus otomatis |
| TC-07/08 edit produk/upload | Admin/CatalogTest, fake public Storage | Lulus otomatis |
| TC-09/10 search/filter | Customer/StorefrontTest, FinalAuditTest | Lulus otomatis |
| TC-11/12/13 cart/update/stok | Customer/CheckoutTest | Lulus otomatis |
| TC-14/15 checkout/manipulasi harga | CheckoutTest, OrderValidationTest | Lulus otomatis |
| TC-16 paid Sandbox | PaymentTest + transaksi QRIS lama | Lulus otomatis dan nyata Tahap 6 |
| TC-17/18 pending/expired | Customer/PaymentTest | Lulus otomatis; tidak mengklaim simulasi nyata baru |
| TC-19/20 webhook palsu/ganda | PaymentTest + retry settlement lama | Lulus otomatis; retry nyata HTTP 200 terdokumentasi |
| TC-21 proses Admin | OrderManagementTest | Lulus otomatis |
| TC-22 akses pesanan lain | OrderManagementTest, CheckoutTest, PaymentTest | Lulus otomatis |
| TC-23 invoice sendiri | OrderManagementTest | HTTP/historis lulus; cetak manual tertunda |
| TC-24 smartphone | Checklist berikut | **Belum diverifikasi visual** |

## 6. Pemeriksaan keamanan dan database

- Middleware auth/role melindungi route mutasi dan data sesuai peran. Query pesanan pelanggan dibatasi user_id; akses silang diuji 404. Admin tidak dapat menggunakan pembayaran pelanggan.
- Form Request memvalidasi harga, stok, upload, alamat, telepon dan status. Data browser tidak dipakai menentukan total/paid. Template menggunakan escaping Blade.
- Test CSRF baru mengaktifkan perilaku non-testing middleware: PATCH profil, POST checkout dan POST kategori tanpa token mendapat 419; endpoint webhook yang dikecualikan CSRF tetap menolak payload invalid dengan 422. Signature invalid/nominal palsu sudah tercakup PaymentTest.
- Webhook tetap memakai Signature Secret Key khusus Sandbox; hash_equals, identitas/nominal dan status/fraud API dipertahankan. Tidak menonaktifkan verifikasi. Public Client Key tetap diperlukan SDK Snap; Server Key/Signature Secret tidak dikirim ke view. Jangan menangkap raw HTML/network payload saat dokumentasi.
- File upload dibatasi jenis/ukuran/dimensi; penggantian gambar dan kegagalan simpan diuji. Produk diarsipkan dengan soft delete, sehingga histori tetap tersedia.
- FK kategori/produk, order/user, item/order, payment/order, dan cart item/cart diperiksa: **0 orphan** pada setiap relasi tersebut pada database lokal.
- Kolom phone terpasang tanpa backfill paksa; relasi balik Eloquent dilengkapi. Payment satu per order dan checkout_token unik sesuai desain implementasi. Tabel order_details legacy tidak dihapus; transaksi baru tetap memakai order_items.
- Reservasi, pelepasan sekali, anti-oversell dan anti-duplikasi diuji. Admin fulfillment hanya mengubah status proses; webhook paid berulang tidak mengembalikan Dikirim/Selesai ke Diproses.
- Snapshot invoice berasal dari order_items, bukan harga/nama produk terkini. Uji perubahan/arsip produk lulus.

## 7. Bukti Midtrans dan keutuhan transaksi lama

Pesanan `PWM-01M7JGX9P7HT3P6X7NVM4M6EGQ` tetap **paid / processing**, nominal order/payment **Rp30.000**, stok produk ID 3 **99**, jumlah order **2**, payment **1** setelah perbaikan/migration dan pengujian. Pembacaan audit tidak menulis transaksi.

Bukti nyata sebelumnya: QRIS settlement, retry POST `/midtrans/notification` HTTP **200** pada 17 November 2026 pukul 02:54:11 WIB, event webhook `paid_preserved`, dashboard Midtrans **Completed** menurut konfirmasi pengguna. Rincian pada PHASE-6-REPORT.md. Tidak membuat pembayaran atau replay webhook nyata baru pada Tahap 8.

## 8. Kesiapan GitHub dan audit rahasia

- Empat commit pada seluruh ref Git lokal yang tersedia dipindai, mencakup 114 blob unik. Tidak ditemukan `.env` sensitif dalam pohon commit yang diperiksa atau kecocokan nilai kredensial `.env` saat ini. Pola API key nyata tidak ditemukan oleh pemindaian pola terbatas.
- Sebanyak 285 calon berkas tracked/untracked non-ignored pada saat scan diperiksa terhadap nilai kredensial lokal saat ini: tidak ditemukan kecocokan. Jumlah berkas dapat bertambah dengan laporan ini; README/laporan hanya memuat nama variabel dan status, bukan nilainya.
- **Ditemukan password literal historis** pada `database/seeders/DatabaseSeeder.php`, blob `a5cc7335`. Pemeriksaan hash lokal secara privat menunjukkan **dua akun masih menggunakan password lama tersebut**. Nilai password tidak dicetak atau disalin ke laporan.
- Seeder aktif sudah aman dari password literal, tetapi mengedit file terbaru tidak menghapus password dari riwayat. Pengguna perlu mengganti password dua akun melalui profil dan menentukan penanganan riwayat sebelum publikasi. Tidak dilakukan perubahan password, rewrite history, commit, atau push otomatis.
- Audit ini tidak membuktikan bebas semua rahasia: hanya ref lokal yang tersedia, kecocokan nilai saat ini, serta pola terbatas. Tidak memindai seluruh remote/reflog/backup/binary secara forensik; kredensial lama yang berbeda mungkin memerlukan pemeriksaan tambahan.
- Banyak implementasi Tahap 1–8 masih berupa perubahan working tree. Remote origin tersedia, tetapi kondisi remote/GitHub terbaru tidak disinkronkan. Riwayat pengembangan lengkap belum dapat diklaim.
- Sebelum commit yang nanti disetujui: pilih source/lockfile/migration/seeder/views/config contoh/README/docs; periksa staged diff tanpa membagikan rahasia. Jangan memasukkan .env, password/key/authtoken, log, dump database, backup privat, sesi, vendor/node_modules, atau screenshot berisi data sensitif.

## 9. Panduan pengujian visual/manual

Otomasi browser pada tahap sebelumnya berhenti karena Computer Use tidak dapat memastikan URL aktif. Tidak menggunakan hasil build atau test HTTP sebagai bukti visual. Tahap 8 menyediakan panduan manual sesuai instruksi pengguna; tidak ada screenshot sintetis atau klaim pemeriksaan visual berhasil.

Di browser, buka DevTools → mode perangkat/responsive. Masukkan lebar berikut satu per satu (tinggi dapat 900px), zoom 100%. Uji scroll sampai footer, navigasi keyboard Tab, menu, dropdown, dan seluruh tombol yang relevan. Ulangi dengan tema gelap jika aktif menurut preferensi sistem. Untuk invoice buka print preview ukuran A4 dan pastikan tombol tidak tercetak.

| Halaman/komponen | 1440 | 1366 | 768 | 390 | 360 |
|---|---|---|---|---|---|
| Login/registrasi/profil | Belum | Belum | Belum | Belum | Belum |
| Beranda/navbar/footer | Belum | Belum | Belum | Belum | Belum |
| Katalog/search/filter/card | Belum | Belum | Belum | Belum | Belum |
| Detail produk/jumlah | Belum | Belum | Belum | Belum | Belum |
| Keranjang/empty state | Belum | Belum | Belum | Belum | Belum |
| Checkout/form/ringkasan | Belum | Belum | Belum | Belum | Belum |
| Pembayaran/status Midtrans | Belum | Belum | Belum | Belum | Belum |
| Admin dashboard/sidebar | Belum | Belum | Belum | Belum | Belum |
| Kategori/produk/form/upload | Belum | Belum | Belum | Belum | Belum |
| Admin order/filter/detail | Belum | Belum | Belum | Belum | Belum |
| Riwayat/detail pelanggan | Belum | Belum | Belum | Belum | Belum |
| Invoice/print preview | Belum | Belum | Belum | Belum | Belum |

Kriteria lulus setiap sel: tidak bertumpuk/terpotong, input berlabel/fokus terlihat, tombol terbaca dan dapat dijangkau, gambar proporsional, teks panjang membungkus, tidak ada scroll horizontal halaman yang tidak perlu. Scroll horizontal **di dalam tabel Admin** diperbolehkan karena memang disediakan untuk layar kecil. Simpan tanggal, browser, ukuran, hasil aktual, dan screenshot asli setelah pengujian; ganti “Belum” hanya setelah benar-benar diuji.

Gunakan langkah fungsional manual PHASE-7-REPORT serta README. Pengujian perubahan kategori/produk/status dilakukan dengan data demo yang disepakati; jangan memodifikasi transaksi bukti lama secara tidak sengaja. Validasi otomatis sudah membuktikan backend, tetapi hasil manual harus dicatat terpisah.

## 10. Daftar screenshot laporan akademik

Nomor sementara dapat disesuaikan format kampus. Ambil langsung dari aplikasi yang berjalan, tanpa edit status atau membuat gambar pengujian palsu. Gunakan akun/data demo tanpa identitas pribadi. Jangan menampilkan key, token, password, signature, QR pembayaran aktif, raw notification, atau panel konfigurasi rahasia.

| Gambar | Judul / halaman | BAB / subbab | Informasi yang wajib terlihat | Keterangan laporan |
|---|---|---|---|---|
| 4.1 | Login dan registrasi | IV Implementasi / Authentication | Identitas PawMart, label form, tombol; tanpa password | Sistem menyediakan autentikasi dan pendaftaran pelanggan. |
| 4.2 | Pembatasan role | IV / Otorisasi | Pelanggan membuka URL Admin dan respons 403 | Middleware membatasi akses berdasarkan peran. |
| 4.3 | Dashboard Admin | IV / Dashboard | Keenam statistik dan navigasi | Statistik toko diperoleh dari database. |
| 4.4 | Daftar dan form kategori | IV / Kategori | Kategori serta pesan simpan/validasi | Admin dapat mengelola kategori dengan validasi. |
| 4.5 | Proteksi kategori terpakai | IV / Integritas kategori | Pesan penolakan hapus kategori berelasi | Relasi produk dipertahankan saat penghapusan ditolak. |
| 4.6 | Produk Admin dan form edit | IV / Produk | Nama, kategori, harga, stok, gambar, status | Data katalog dikelola melalui formulir tervalidasi. |
| 4.7 | Upload/ganti gambar | IV / Media produk | Gambar hasil simpan pada detail produk demo | Gambar tersimpan melalui Laravel Storage. |
| 4.8 | Beranda PawMart | IV / Tampilan pelanggan | Hero, navbar, kategori dan produk | Beranda memperkenalkan katalog kebutuhan hewan. |
| 4.9 | Katalog dengan filter | IV / Katalog | Query/filter dan hasil yang sesuai | Pencarian dan kategori membantu pemilihan produk. |
| 4.10 | Detail produk | IV / Detail | Gambar, harga, stok, jumlah dan tombol cart | Pelanggan dapat meninjau informasi sebelum membeli. |
| 4.11 | Keranjang | IV / Shopping Cart | Item, kuantitas, subtotal, total | Keranjang tersimpan per akun pelanggan. |
| 4.12 | Checkout | IV / Checkout | Form penerima demo dan ringkasan/ongkir | Backend menghitung total dan memvalidasi alamat. |
| 4.13 | Pembayaran QRIS berhasil lama | IV / Midtrans | Halaman pembayaran berhasil, order dan Rp30.000 | Pembayaran Sandbox diverifikasi melalui server. |
| 4.14 | Notification History | IV / Webhook | Order yang sama, Completed dan waktu; tanpa payload | Notifikasi settlement berhasil diterima aplikasi. |
| 4.15 | Bukti HTTP 200 | IV / Webhook | Ringkasan inspector metode/path/status saja | Endpoint merespons 200 setelah verifikasi notifikasi. |
| 4.16 | Daftar/detail order Admin | IV / Manajemen pesanan | Pelanggan demo, status, alamat demo, filter | Admin memantau pesanan dan status pembayaran. |
| 4.17 | Pengiriman pesanan demo | IV / Pemrosesan | Dikirim/Selesai pada pesanan yang boleh diubah | Pemrosesan hanya maju setelah pembayaran terverifikasi. |
| 4.18 | Riwayat/detail pelanggan | IV / Riwayat | Pesanan akun, status, perkembangan, detail/invoice | Pelanggan melihat transaksi miliknya sendiri. |
| 4.19 | Invoice dan print preview | IV / Invoice | Snapshot item, ongkir, total, status; preview tanpa tombol | Invoice menggunakan data historis transaksi. |
| 4.20 | Profil telepon | IV / Profil | Field nama/email/telepon dengan data demo | Pelanggan dapat memperbarui informasi profil. |
| 5.1 | Pengujian otomatis | V Pengujian / Regresi | Perintah dan hasil test aktual terbaru | Seluruh suite utama dan konkurensi dilaporkan sesuai hasil. |
| 5.2 | Uji mobile/tablet | V / Responsivitas | Viewport 360/390/768, halaman penting, tidak terpotong | Bukti ini baru dibuat setelah pemeriksaan manual nyata. |

Tidak perlu screenshot tiap halaman identik atau tiap test individual. Bukti kegagalan keamanan yang penting dapat memakai hasil test dan satu screenshot representatif. Jika gambar mengandung data sensitif, ulangi dengan data demo aman atau batasi area tangkapan sejak awal; jangan memalsukan nilai/hasil.

## 11. Urutan demonstrasi

1. Aktifkan Laragon/MySQL, jalankan `php artisan serve` dan `php artisan schedule:work`; pastikan build tersedia. Tunnel hanya perlu aktif jika menunjukkan konektivitas webhook.
2. Login Admin menggunakan akun yang passwordnya sudah diganti secara privat; tampilkan enam statistik dan jelaskan sumber query.
3. Tambahkan kategori demo yang jelas namanya, edit deskripsi; jelaskan mengapa kategori terpakai tidak boleh dihapus.
4. Tambahkan/edit produk demo, unggah gambar yang diizinkan, jelaskan validasi harga/stok/status dan arsip. Ini tindakan demo yang dilakukan pengguna saat presentasi, bukan data yang dibuat oleh audit.
5. Logout Admin, login pelanggan demo, buka beranda/katalog. Tunjukkan search/filter/detail dan stok.
6. Tambah produk demo ke keranjang, ubah kuantitas dan tampilkan validasi stok. Buka checkout, jelaskan alamat, ongkir dan perhitungan server.
7. Untuk demonstrasi tanpa transaksi baru, berhenti sebelum submit checkout lalu jelaskan CheckoutService dan hasil test snapshot/anti-duplikasi. Jika dosen memang meminta submit langsung, gunakan pesanan demo yang disepakati; tidak perlu membuat pembayaran nyata baru.
8. Jelaskan Snap/Core API versus webhook/signature, tanpa membuka .env atau menampilkan key.
9. Buka bukti pesanan QRIS lama Rp30.000 yang sudah paid, Notification History Completed, dan bukti HTTP 200. Tidak perlu membayar ulang atau mengirim notifikasi palsu.
10. Tampilkan riwayat dan detail pelanggan serta perkembangan pesanan.
11. Logout/login Admin; buka detail pesanan. Tunjukkan tombol Dikirim/Selesai dan jelaskan validasi server. Jika transaksi bukti lama harus tetap processing, jangan klik tombolnya; demonstrasikan transisi melalui hasil OrderManagementTest atau pesanan demo lain yang telah disetujui.
12. Login kembali sebagai pelanggan pemilik, buka invoice dan print preview. Jelaskan bahwa perubahan produk tidak mengubah snapshot historis.
13. Tunjukkan hasil test, struktur MVC/service, schema dan laporan; sampaikan batas verifikasi yang masih ada secara jujur.

Mahasiswa perlu dapat menjelaskan: middleware role, Form Request, eager loading, transaction/lockForUpdate, snapshot harga, token checkout unik, signature hash_equals, GET Status API, idempotensi pembayaran, reservasi stok, pemisahan payment_status/order_status, dan batas akses invoice. Bantuan AI harus dijelaskan sesuai kebijakan dosen.

## 12. Struktur laporan tugas besar yang disarankan

Dokumen ini merupakan laporan teknis Tahap 8 dan bahan laporan akademik, bukan pengganti dokumen beridentitas mahasiswa/format kampus.

- Bagian awal: sampul, identitas mahasiswa/kelas/dosen, daftar isi, daftar gambar/tabel. Isi dengan data benar, tidak dikarang.
- BAB I Pendahuluan: latar belakang kebutuhan pet shop, rumusan masalah, tujuan dan batas sistem Sandbox/satu toko.
- BAB II Landasan Teori: MVC Laravel, Eloquent/MySQL, autentikasi/otorisasi, transaksi, payment gateway; cantumkan sumber yang benar-benar digunakan.
- BAB III Analisis dan Perancangan: kebutuhan role, alur belanja/Admin, ERD dari migration, rancangan status dan tema UI.
- BAB IV Implementasi: struktur source, kode inti, halaman dan screenshot 4.1–4.20 sesuai implementasi aktual.
- BAB V Pengujian: tabel PRD/TC, hasil otomatis, bukti QRIS/webhook, checklist visual yang sudah diisi setelah diuji, bug dan perbaikannya.
- BAB VI Penutup: hasil, batasan dan saran. Jangan menyebut Production siap atau semua metode pembayaran diuji nyata.
- Daftar pustaka/lampiran: repository yang sudah disetujui dipublikasikan, petunjuk instalasi, hasil test, daftar package/referensi/AI sesuai kebijakan.

## 13. File yang berubah pada Tahap 8

- `app/Models/User.php`: phone fillable serta relasi orders/cart.
- `app/Models/Cart.php`: relasi user.
- `app/Models/OrderItem.php`: relasi order.
- `app/Http/Requests/ProfileUpdateRequest.php`: validasi telepon opsional.
- `resources/views/profile/partials/update-profile-information-form.blade.php`: field telepon.
- `database/migrations/2026_11_17_003448_add_phone_to_users_table.php`: kolom nullable tambahan.
- `app/Http/Controllers/Customer/StorefrontController.php`: pencarian string nol.
- `app/Http/Controllers/Admin/ProductController.php`: pencarian string nol.
- `app/Http/Controllers/Admin/OrderController.php`: pencarian string nol.
- `tests/Feature/FinalAuditTest.php`: regresi profil/search dan CSRF.
- `README.md`: instalasi, penggunaan, Sandbox, scheduler, akun, test, kesiapan Git.
- `docs/PHASE-8-REPORT.md`: laporan ini.

Output Vite/Blade di direktori generated diperbarui oleh build/kompilasi. Konfigurasi Midtrans, key lokal, service pembayaran, PRD dan transaksi lama tidak diubah.

## 14. Tindak lanjut sebelum pengumpulan

1. Ganti password dua akun demo yang cocok dengan password historis. Jangan kirim nilai password kepada asisten atau masukkan ke repository.
2. Putuskan penanganan password pada riwayat Git bersama pemilik repository sebelum publikasi; jika pernah dipakai di tempat lain, ganti di tempat tersebut juga. Tidak ada rewrite otomatis pada pekerjaan ini.
3. Jalankan checklist visual lima viewport, dark mode dan print preview; catat hasil nyata serta ambil screenshot yang diperlukan.
4. Lengkapi laporan akademik beridentitas mahasiswa dan sesuai format/kebijakan AI dosen. Audit tidak memberikan jaminan keaslian atau nilai.
5. Tinjau perubahan source/docs, lalu berikan persetujuan terpisah untuk commit/push. Pastikan file yang diwajibkan benar-benar masuk repository setelah persetujuan.

Kesimpulan: aplikasi inti siap untuk demonstrasi fungsional terarah, dengan pengujian otomatis dan bukti Sandbox yang kuat. **Pengumpulan/publikasi final belum boleh dinyatakan sepenuhnya siap** sampai tindak lanjut keamanan akun/riwayat, verifikasi visual, screenshot/laporan akademik, serta GitHub selesai. Tidak melakukan pekerjaan di luar Tahap 8.
