# PawMart — Laporan Tahap 7: Manajemen dan Riwayat Pesanan

Tanggal: 17 November 2026. Cakupan: FR-ADM-05, FR-ADM-06, FR-CUS-08 dan Tahap 7 pada PRD. Tahap 8 tidak dikerjakan.

## Analisis sebelum implementasi

- Laravel terpasang v13.42.0, Blade, Tailwind CSS v3, Eloquent, dan MySQL digunakan sesuai struktur proyek. Tidak ada perubahan dependency.
- Tahap 5 sudah menyediakan Order, OrderItem, checkout dengan snapshot harga/nama, alamat, reservasi stok, transaksi database, serta pembatalan.
- Tahap 6 sudah menyediakan Payment, PaymentService, MidtransClient, payment_events, riwayat sederhana pada PaymentController@index, detail pelanggan pada OrderController@show, halaman pembayaran, serta webhook terverifikasi.
- Routes pelanggan sudah menggunakan auth dan role:customer. Detail/pembayaran dibatasi dengan user_id akun yang login.
- Migration orders/order_items/payments dan service checkout, pembayaran, serta reservasi stok diperiksa. Kolom status, alamat, nominal, dan snapshot sudah cukup untuk Tahap 7; tidak diperlukan migration, tabel pesanan baru, atau perubahan data lama.
- Sebelumnya belum ada controller manajemen pesanan Admin, aturan transisi pengiriman, dan invoice. Riwayat pelanggan belum menampilkan ringkasan item, status proses, maupun tautan invoice.

## Fitur yang dibuat dan disempurnakan

### Manajemen Admin

- Menu Pesanan pada navigasi Admin; daftar seluruh pesanan dengan pagination 15 baris.
- Nomor pesanan, tanggal WIB, pelanggan, jumlah unit barang, total, badge pembayaran dan pemrosesan, serta tautan detail.
- Pencarian nomor pesanan/nama pelanggan; filter pembayaran dan pemrosesan dapat digabungkan. Pagination mempertahankan query filter dan tersedia reset/empty state.
- Detail memakai snapshot item, subtotal, ongkir, total, alamat, catatan penerima, metode pembayaran, dan waktu pembayaran terverifikasi.
- Tombol tindakan hanya tersedia jika pesanan memenuhi syarat: Diproses → Dikirim → Selesai. Notifikasi keberhasilan ditampilkan setelah update.

### Aturan pemrosesan

- OrderFulfillmentService menggunakan database transaction dan mengunci order, lalu payment, sebelum memeriksa status terbaru. Urutan penguncian mengikuti service pembayaran yang sudah ada.
- Pembayaran harus paid, mempunyai paid_at, nominal payment/order cocok, gateway settlement/capture, tanpa review_reason, dan reservasi stok telah difinalisasi tanpa pelepasan stok.
- Belum dibayar, dibatalkan, kedaluwarsa, gagal, payment_review, status mundur, melompati Dikirim, serta submit ulang status yang sudah diterapkan ditolak. Form lama tidak dapat memaksa status mundur.
- Endpoint hanya menulis order_status dan timestamp update milik order. Status pembayaran, payment, item, nominal, serta stok tidak ditulis oleh service pemrosesan.
- Input payment_status, transaction_status, total_amount, dan field reservasi dilarang. Field browser lainnya tidak diteruskan ke update model.
- Tidak tersedia tindakan Admin untuk menetapkan paid secara manual.
- Webhook settlement setelah Dikirim/Selesai mempertahankan status proses dan stok; diuji melalui endpoint webhook dengan signature valid dan API palsu yang terisolasi dalam test.

### Pelanggan dan invoice

- Riwayat milik akun login: pagination 10 pesanan, tanggal WIB, snapshot ringkasan produk, total, kedua status, detail, invoice, dan tautan pembayaran yang mengikuti masa reservasi/status pesanan.
- Detail menampilkan perkembangan empat langkah berdasarkan status saat ini, tanpa mengarang timestamp pengiriman atau bukti pelacakan kurir.
- Status dibatalkan/pemeriksaan ditampilkan terpisah dari alur normal. Pembatalan dan pembayaran tetap memakai service Tahap 5–6.
- Informasi pembayaran di detail dibatasi pada metode dan waktu terverifikasi; tidak menampilkan token atau konfigurasi key.
- Invoice `/orders/{order}/invoice` khusus pemilik pesanan. Admin maupun pelanggan lain tidak dapat membuka endpoint invoice pelanggan.
- Invoice berisi logo/nama PawMart, nomor/tanggal pesanan, pelanggan, penerima/alamat, snapshot item/harga/kuantitas/subtotal, ongkir, total, dan status pembayaran.
- Perubahan nama/harga serta pengarsipan produk tidak mengubah invoice lama. Nama pelanggan mengikuti profil saat ini; nama/alamat penerima dan seluruh item berasal dari pesanan.
- Tombol Cetak / Simpan PDF memakai dialog cetak browser. Aksi navigasi disembunyikan saat cetak; tidak ada dependency generator PDF.
- Tampilan mengikuti komponen PawMart, warna Forest Green/Warm Cream/Soft Peach, dark mode, tabel Admin yang dapat digeser, dan layout responsif.

## File Tahap 7

File baru:

- `app/Http/Controllers/Admin/OrderController.php`
- `app/Http/Requests/Admin/OrderFilterRequest.php`
- `app/Http/Requests/Admin/UpdateOrderStatusRequest.php`
- `app/Services/OrderFulfillmentService.php`
- `resources/views/admin/orders/index.blade.php`
- `resources/views/admin/orders/show.blade.php`
- `resources/views/customer/invoice.blade.php`
- `resources/views/components/order-status.blade.php`
- `resources/views/components/order-summary.blade.php`
- `resources/views/components/order-progress.blade.php`
- `tests/Feature/OrderManagementTest.php`
- `docs/PHASE-7-REPORT.md`

File diperbarui:

- `app/Models/Order.php`: relasi pelanggan, label status, syarat pemrosesan dan kelanjutan pembayaran.
- `app/Http/Controllers/Customer/OrderController.php`: detail dengan payment serta invoice yang dibatasi pemilik.
- `app/Http/Controllers/Customer/PaymentController.php`: eager loading item/payment pada riwayat.
- `routes/web.php`: route Admin dan invoice dalam kelompok middleware yang sesuai.
- `resources/views/customer/orders.blade.php`
- `resources/views/customer/order.blade.php`
- `resources/views/customer/payment.blade.php`: label Dikirim/Selesai yang konsisten.
- `resources/views/components/admin-layout.blade.php`
- `resources/views/components/store-layout.blade.php`

Build Vite juga memperbarui output `public/build` yang dihasilkan secara otomatis. Perubahan Git dari Tahap 1–6 yang sudah ada dipertahankan; daftar di atas khusus pekerjaan Tahap 7.

## Hasil pengujian

| Pemeriksaan | Hasil |
|---|---|
| Test baru Tahap 7 | 21 test, 201 assertion lulus |
| Seluruh Unit/Feature Tahap 1–7 | 225 test, 1.429 assertion lulus |
| Konkurensi MySQL pada database terpisah | 5 test, 48 assertion lulus |
| Sintaks sembilan file PHP yang dibuat/diubah | Lulus |
| Laravel Pint --dirty --format agent | Lulus; format import dan line ending diperbaiki |
| npm run build | Lulus |
| php artisan view:cache | Lulus |
| php artisan route:list --path=orders | 10 route pesanan terdaftar tanpa konflik |

Test mencakup akses Admin/pelanggan/tamu, pembatasan kepemilikan, transisi sah/ilegal, pembayaran belum terverifikasi, larangan manipulasi nominal/pembayaran, keutuhan stok/payment, webhook setelah pengiriman, pencarian/filter/pagination, invoice historis setelah perubahan/arsip produk, informasi rahasia tidak muncul, dan empty state.

Suite utama memakai SQLite `:memory:` dengan guard; test konkurensi hanya menggunakan `pawmart_phase5_testing` dengan guard nama database. Data test tidak dibuat pada database aplikasi. Tidak menjalankan migrate:fresh atau reset database aplikasi.

Pemeriksaan visual melalui Computer Use tidak dapat diselesaikan: alat menghentikan otomasi karena tidak dapat memastikan URL browser aktif. Pengujian HTTP merender view dan build/kompilasi berhasil, tetapi tampilan desktop/smartphone, dark mode, serta dialog cetak masih perlu pemeriksaan manual. Ini tidak dilaporkan sebagai pengujian visual yang sudah lulus.

## Perlindungan transaksi Sandbox yang sudah ada

Pemeriksaan baca-saja setelah implementasi untuk PWM-01M7JGX9P7HT3P6X7NVM4M6EGQ: pembayaran paid, proses processing, total order/payment Rp30.000, stok produk ID 3 tetap 99, jumlah order 2 dan payment 1. Event webhook paid_preserved dari verifikasi Tahap 6 tetap tersedia.

Tidak membuat pesanan atau pembayaran demonstrasi pada database aplikasi, tidak mengubah status transaksi ini, serta tidak memanggil pembayaran/webhook nyata untuk menguji fitur baru. Konfigurasi dan service Midtrans, signature verification, checkout, serta pelepasan stok tidak diubah pada Tahap 7. Hasil HTTP 200 webhook nyata tetap merujuk verifikasi Tahap 6; tidak mengklaim ada retry nyata baru pada pekerjaan ini.

## Uji manual melalui browser

1. Jalankan MySQL dan Laravel. Tidak perlu migration/seeder baru. Build sudah dijalankan; lakukan refresh browser untuk memuat aset terbaru.
2. Login Admin lalu buka `/admin/orders` atau menu Pesanan. Periksa nomor, pelanggan, total, kedua status dan jumlah barang.
3. Cari nomor pesanan atau nama pelanggan; gabungkan filter pembayaran/proses, coba pencarian tanpa hasil, dan Reset filter. Jika data lebih dari 15, pastikan halaman berikutnya tetap mempertahankan filter.
4. Buka detail pesanan Sandbox yang sudah paid. Pastikan snapshot item, alamat, total Rp30.000, status Diproses, dan tombol Tandai Dikirim tampil. Jangan menekan tombol tersebut pada transaksi bukti apabila ingin mempertahankan statusnya.
5. Untuk uji manual perubahan status, gunakan pesanan uji yang memang telah disetujui untuk diproses. Tandai Dikirim lalu Selesai; periksa notifikasi, status pelanggan, pembayaran tetap paid, dan stok tidak berubah. Pengujian otomatis sudah menguji alur ini tanpa memodifikasi pesanan lokal.
6. Buka pesanan belum dibayar/dibatalkan. Tombol pemrosesan harus tidak tersedia. Tidak ada kontrol untuk mengubah status pembayaran.
7. Login pelanggan pemilik pesanan, buka `/orders`. Coba Lihat Detail, Lihat Invoice, dan Lihat pembayaran. Periksa ringkasan perkembangan dan waktu WIB.
8. Pada invoice, periksa harga historis, subtotal, ongkir, total dan status. Klik Cetak / Simpan PDF; periksa pratinjau lalu pilih Save as PDF bila ingin menyimpan. Pastikan navigasi/tombol tidak ikut dicetak.
9. Login pelanggan lain dan buka URL detail/invoice pesanan tersebut: harus 404. Pelanggan yang membuka `/admin/orders` harus 403; tamu diarahkan login.
10. Periksa layar desktop dan smartphone serta dark mode. Pada layar kecil tabel Admin dapat digeser horizontal; item/invoice dan tombol harus tetap terbaca.

Tahap 7 selesai di sisi implementasi dan pengujian otomatis. Batas verifikasi visual dijelaskan di atas. Tidak melanjutkan Tahap 8.
