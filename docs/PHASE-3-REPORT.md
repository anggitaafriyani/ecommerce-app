# PawMart — Laporan Tahap 3

## Lingkup dan hasil

Tahap 3 selesai: dashboard Admin, CRUD kategori/produk, upload gambar, proteksi Admin dan tampilan responsif. Tahap 4 tidak dikerjakan. Seluruh implementasi autentikasi dan role sebelumnya dipertahankan.

### Dashboard

Enam statistik diambil dari database: total produk belum diarsipkan (aktif maupun nonaktif), kategori, pengguna ber-role customer, seluruh pesanan, pending, dan success. Status success mengikuti enum payment_status yang sudah tersedia; bukan mengubah schema pembayaran menjadi paid. Terdapat lima produk terbaru dan daftar produk aktif dengan stok <= 5 sebagai pelengkap.

### Kategori

Tambah, daftar dengan jumlah produk, edit nama/deskripsi, hapus kategori tidak terpakai, pagination, notifikasi dan modal konfirmasi. Nama wajib unik di validasi dan database. Kategori yang digunakan produk, termasuk produk arsip, ditolak saat dihapus. Foreign key restrict tetap dipertahankan dan transaksi memakai row lock saat penghapusan.

### Produk dan gambar

Tambah, daftar/pencarian nama, detail, edit nama/kategori/deskripsi/harga/stok/status, upload/ganti gambar, dan soft delete. Harga DECIMAL(12,2), maksimal dua angka desimal dan tidak negatif; stok bilangan bulat 0–2147483647. Kategori harus ada. Status hanya aktif/nonaktif. Gambar baru wajib pada pembuatan; edit tanpa gambar mempertahankan gambar lama. Produk lama yang belum memiliki gambar tetap dapat diedit.

Upload menggunakan Laravel Storage disk public, path products, nama acak, MIME JPG/JPEG/PNG/WebP, maksimal 2 MB, dimensi maksimal 4096 × 4096. SVG tidak diterima. Penggantian gambar menghapus berkas lama setelah database berhasil disimpan; kegagalan database membatalkan transaksi dan menghapus berkas baru. Soft delete menyimpan data/gambar untuk riwayat transaksi, dan menyetel produk nonaktif.

Slug unik dibuat otomatis dan stabil saat nama diperbarui. Kolom image_path lama dipertahankan untuk kompatibilitas. Tidak membuat tabel kategori/produk duplikat.

### Keamanan dan UI

Seluruh 14 route Admin memakai web, auth, role:admin. Form Request juga memeriksa role. Input tervalidasi dipisahkan dari file upload; payload image_path/slug tidak dapat mengubah lokasi file melalui mass assignment. Form mutasi menyertakan CSRF, keluaran pengguna di-escape Blade.

Sidebar desktop, navigasi ponsel, warna cream/forest green/peach PRD, DM Sans, kartu statistik, tabel bergulir, label form, focus state, notifikasi, empty state dan modal hapus. Style Admin terpisah melalui komponen dan kelas pm-* sehingga halaman auth/Pelanggan tidak ditimpa. Varian dark tersedia; inspeksi visual browser dilakukan pada mode terang.

## Database dan data lama

Dua migration tambahan sudah dijalankan pada MySQL lokal, batch 2:

1. `2026_11_16_121457_extend_categories_for_admin_catalog.php`: description, slug dan unique name; backfill slug berdasarkan nama/ID. Migration menolak nama duplikat sebelum perubahan schema.
2. `2026_11_16_121458_extend_products_for_admin_catalog.php`: perluasan price integer ke decimal, slug, is_active default true, deleted_at, dan backfill slug.

Tujuh migration lama tidak diubah. Tidak menjalankan migrate:fresh, reset/drop database, atau mengganti .env/APP_KEY/password. Salinan katalog sebelum perubahan tersedia di `storage/app/private/backups/pawmart-before-phase3.json` (tidak masuk Git). Perbandingan setiap field lama setelah migrasi menunjukkan data tetap sama; harga dibandingkan secara numerik. Jumlah tetap 3 kategori, 3 produk, 2 akun. Tidak ada data QA yang ditambahkan ke MySQL.

`public/storage` sudah dibuat sebagai junction ke storage/app/public. APP_URL lokal http://localhost:8000, URL gambar menggunakan /storage. Pada mesin/deployment lain, atur APP_URL sesuai domain lalu jalankan `php artisan storage:link`.

Rollback migration ini bukan langkah operasional yang disarankan: rollback menghilangkan kolom tambahan/penanda arsip; tipe harga sengaja tetap decimal agar nilai tidak terpotong. Gunakan migration perbaikan ke depan setelah ada data baru.

## Pengujian

| Pemeriksaan | Hasil |
|---|---|
| Suite lengkap PHPUnit | **104 tes, 491 assertion, lulus** |
| Regresi Tahap 1–2 | Seluruh 56 tes lama tetap lulus |
| Tambahan Tahap 3 | 48 kasus: statistik, CRUD, validasi, akses tamu/customer, upload, soft delete, pencarian, escaping, kegagalan DB/file cleanup |
| Pint --dirty --format agent | Lulus |
| Sintaks PHP app/database/routes/tests | 63 file lulus |
| npm run build | Lulus |
| Blade view:cache | Lulus; cache view dibersihkan setelah pemeriksaan |
| route:list Admin | 14 route dengan auth + role:admin |
| Migration MySQL | Sembilan migration Ran, termasuk dua tambahan |
| Perbandingan data katalog sebelum/sesudah | Seluruh field lama tetap terjaga |
| Browser desktop 1440 × 1000 | Login Admin, dashboard statistik database dan navigasi tampil |
| Browser ponsel 390 × 844 | Form produk tersusun satu kolom; tabel dapat digeser hingga Edit/Hapus terlihat |
| Modal kategori | Membuka konfirmasi dan Batal berhasil tanpa menghapus data |
| Logout browser | Berhasil, kembali ke beranda publik |

Perintah tes: `php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit --no-progress`. Suite memakai SQLite :memory: dengan migration biasa, bukan database MySQL aplikasi. Operasi tulis CRUD/upload/delete diuji otomatis; browser memeriksa tampilan, navigasi, modal/batal, login/logout tanpa mengubah katalog nyata. Integrasi upload nyata melalui browser dapat diverifikasi menggunakan langkah manual di bawah.

Masalah yang ditemukan saat implementasi: satu penutup directive Blade menempel pada teks unit dan menyebabkan error tabel; sudah diperbaiki, seluruh tes kemudian lulus. Tidak ada kegagalan tersisa dalam cakupan yang diuji.

## File Tahap 3

Backend:
- app/Models/Category.php
- app/Models/Product.php
- app/Http/Controllers/Admin/DashboardController.php
- app/Http/Controllers/Admin/CategoryController.php (baru)
- app/Http/Controllers/Admin/ProductController.php (baru)
- app/Http/Requests/Admin/CategoryRequest.php (baru)
- app/Http/Requests/Admin/ProductRequest.php (baru)
- app/Services/ProductService.php (baru)
- routes/web.php
- database/migrations/2026_11_16_121457_extend_categories_for_admin_catalog.php (baru)
- database/migrations/2026_11_16_121458_extend_products_for_admin_catalog.php (baru)
- database/factories/CategoryFactory.php (baru)
- database/factories/ProductFactory.php (baru)

Tampilan:
- resources/views/components/admin-layout.blade.php (baru)
- resources/views/components/admin-delete.blade.php (baru)
- resources/views/admin/dashboard.blade.php
- resources/views/admin/categories/index.blade.php (baru)
- resources/views/admin/categories/form.blade.php (baru)
- resources/views/admin/products/index.blade.php (baru)
- resources/views/admin/products/form.blade.php (baru)
- resources/views/admin/products/show.blade.php (baru)
- resources/css/app.css

Pengujian/dokumentasi:
- tests/Feature/Admin/CatalogTest.php (baru)
- README.md
- docs/PHASE-3-REPORT.md (baru)

Hasil build public/build, junction public/storage, dan salinan katalog privat merupakan hasil lokal yang diabaikan Git. Perubahan Tahap 1–2 sebelumnya masih berada di working tree dan tidak dihapus. Commit/push belum dilakukan.

## Checklist manual browser

1. Jalankan `php artisan serve` bila belum aktif. Buka http://127.0.0.1:8000/login dan gunakan akun Admin lama.
2. Dashboard awal harus menampilkan 3 produk, 3 kategori, 1 pelanggan, dan 0 untuk tiga statistik pesanan. Nilai berubah mengikuti data yang Anda tambahkan.
3. Kategori → Tambah kategori. Simpan nama baru dan deskripsi; cek notifikasi serta daftar. Ulangi nama yang sama untuk melihat penolakan. Edit kategori dan simpan.
4. Produk → Tambah produk. Isi kategori, nama, deskripsi, harga, stok, gambar JPG/PNG/WebP <= 2 MB, status Aktif. Simpan; periksa detail, gambar, dan kenaikan statistik.
5. Edit produk: ganti harga/stok/kategori, ganti gambar, lalu simpan. Edit kembali tanpa gambar baru untuk memastikan gambar tetap ada. Set stok 0 untuk label Habis; ubah Nonaktif dan periksa status.
6. Coba harga/stok negatif, stok pecahan, file bukan gambar atau > 2 MB: simpan harus ditolak dan data lama tetap ada.
7. Coba hapus kategori yang digunakan produk: ditolak. Pindahkan produk yang belum diarsipkan ke kategori lain melalui Edit, lalu hapus kategori yang benar-benar kosong.
8. Produk → Hapus → Batal: tidak berubah. Ulangi → Ya, hapus: produk hilang dari daftar, total produk berkurang; data/gambar tetap diarsipkan. Kategori terkait arsip tetap dilindungi.
9. Uji pencarian produk; buka detail; gunakan pagination bila data > 12 baris. Pada ponsel, geser tabel ke samping untuk mencapai kolom tindakan.
10. Logout, login sebagai Pelanggan, lalu kunjungi /admin/dashboard, /admin/categories, /admin/products: harus 403. Tanpa login, route tersebut mengarahkan ke login.

## Batas lingkup

Katalog Pelanggan/pencarian publik/detail publik belum dibuat (Tahap 4). Dashboard tidak menyediakan pengelolaan pesanan atau perubahan pembayaran (Tahap 6–7). Produk lama belum memiliki gambar; unggah melalui Edit sesuai kebutuhan. Tidak ada UI pemulihan arsip pada iterasi ini. Catatan perlindungan penghapusan akun dengan pesanan tetap perlu ditangani sebelum modul transaksi digunakan.
