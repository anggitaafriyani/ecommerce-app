# PawMart — Laporan Tahap 4

## Lingkup selesai

Tahap 4 menyediakan beranda, katalog, dan detail produk publik. Pengunjung, Pelanggan, serta Admin dapat melihat katalog. Tidak ada Shopping Cart, Checkout, pembuatan order, atau pembayaran baru. Tahap 5 menunggu instruksi pengguna.

### Beranda

- Navbar responsif, tautan Beranda/Produk/Kategori, login/registrasi untuk tamu, profil/dashboard sesuai role dan logout untuk pengguna login.
- Hero dengan ilustrasi SVG kucing/anjing, warna Forest Green, Warm Cream, Soft Peach dan font DM Sans. Ilustrasi bersifat dekoratif, bukan produk atau data toko palsu.
- Kategori dan jumlah produk aktif berasal dari database, termasuk kategori kosong. Tautan kategori membuka katalog terfilter.
- Maksimal delapan produk aktif terbaru dari database, kartu produk dan footer.

### Katalog

- URL `/products`, produk aktif dan belum diarsipkan saja, eager loading kategori, urutan ID terbaru.
- Pencarian nama melalui parameter `q`, filter `category` berdasarkan ID kategori, dapat digunakan bersamaan.
- Validasi server untuk pencarian, kategori dan nomor halaman; query melalui Eloquent dengan binding.
- Pagination 12 produk per halaman dengan parameter pencarian/filter dipertahankan, tombol reset serta empty state.
- Kartu menampilkan gambar Storage, nama, kategori, harga rupiah dengan dua desimal dan tautan detail. Stok nol tetap terlihat dengan label Stok habis.
- Produk lama tanpa gambar memakai placeholder bertuliskan Foto produk belum tersedia. Tidak memasukkan foto maupun produk contoh baru ke database.

### Detail

- URL `/products/{slug}` memakai slug yang sudah dibuat Tahap 3. Produk nonaktif, terarsip, atau slug tidak dikenal mengembalikan 404, termasuk jika diakses langsung oleh Admin pada URL publik.
- Gambar, nama, kategori, deskripsi, harga dan stok berasal dari database. Perubahan Admin langsung tercermin.
- Input jumlah memiliki batas 1 sampai stok dan step 1; saat perubahan selesai, JavaScript membatasi nilai ke bilangan bulat dalam rentang tersebut. Saat stok nol, input dinonaktifkan.
- Tombol Tambah ke Keranjang dinonaktifkan dan disertai penjelasan. Pemilihan jumlah belum disimpan; tidak ada request transaksi atau notifikasi sukses palsu.
- Navbar menampilkan tombol informasi keranjang untuk tamu/Pelanggan; tombol membuka penjelasan bahwa keranjang belum tersedia. Admin mendapat navigasi Dashboard Admin dan tidak mendapat tombol keranjang pada navbar.

### Kompatibilitas

Controller, model, validasi, view dan middleware Admin tidak diubah. Halaman Akun Pelanggan diperbarui agar dapat menuju katalog. Route autentikasi dan tujuan login sebelumnya tetap sama. Layout storefront menggunakan komponen tersendiri dan kelas Tailwind yang sudah tersedia, dengan varian dark untuk konsistensi.

Tidak menambah migration atau mengubah database/APP_KEY/.env. Seluruh sembilan migration sebelumnya tetap Ran. Data katalog dipakai apa adanya; tidak reset, drop, migrate:fresh, atau penambahan data statis. Pemeriksaan akhir MySQL: 3 kategori, 3 produk, 2 akun, 0 pesanan.

## File yang berubah pada Tahap 4

Baru:
- app/Http/Controllers/Customer/StorefrontController.php
- app/Http/Requests/Customer/CatalogRequest.php
- resources/views/components/store-layout.blade.php
- resources/views/components/product-card.blade.php
- resources/views/components/product-image.blade.php
- resources/views/customer/home.blade.php
- resources/views/customer/products/index.blade.php
- resources/views/customer/products/show.blade.php
- tests/Feature/Customer/StorefrontTest.php
- docs/PHASE-4-REPORT.md

Diperbarui:
- routes/web.php: mengganti route root dengan beranda dinamis dan menambahkan dua route katalog publik; grup Admin tetap sama.
- resources/views/customer/dashboard.blade.php: tautan katalog dan penjelasan status fitur belanja.
- tests/Feature/ExampleTest.php: menggunakan trait database memori karena beranda sekarang membaca database; skenario lama tetap dipertahankan.
- README.md: status implementasi dan petunjuk Tahap 4.

View welcome bawaan lama dipertahankan di filesystem tetapi tidak lagi digunakan oleh route `/`. Bundle public/build diperbarui secara lokal dan tetap diabaikan Git.

## Hasil pengujian

| Pemeriksaan | Hasil |
|---|---|
| Tes baru StorefrontTest | 16 tes, 101 assertion, lulus |
| Suite lengkap termasuk Tahap 1–3 | **120 tes, 593 assertion, seluruhnya lulus** |
| Pint --dirty --format agent | Selesai/lulus; format tes baru disesuaikan |
| npm run build | Lulus |
| Sintaks PHP app/database/routes/tests | 66 file lulus php -l |
| Kompilasi Blade view:cache | Lulus, cache dibersihkan kembali |
| route:list | Route publik terdaftar bersama route Admin lama |
| migrate:status | Sembilan migration lama tetap Ran, tidak ada migration tambahan |
| Browser desktop | Beranda diperiksa pada viewport 1440 × 1000; hero, navbar Admin, kategori, produk database dan footer tampil |
| Browser katalog/ponsel/dark mode | Belum diverifikasi visual penuh: koneksi alat browser timeout saat berpindah ke katalog, termasuk saat mencoba menyambungkan ulang |

Tes mencakup beranda dinamis, produk terbaru, jumlah kategori, katalog publik, produk aktif/nonaktif/arsip, stok habis, pencarian, filter, kombinasi keduanya, pagination, empty state, detail slug, harga desimal, gambar/placeholder, escaping input, navigasi role, validasi query, dan perubahan harga/stok/status oleh Admin. Suite memakai SQLite :memory: yang terpisah dari MySQL aplikasi.

Perintah tes:

```powershell
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit --no-progress
npm run build
```

Tidak ada kegagalan otomatis tersisa. Pengujian responsivitas dirancang melalui breakpoint mobile-first; verifikasi visual lengkap katalog/detail pada perangkat kecil masih perlu dilakukan dengan checklist berikut. Perubahan viewport browser pengujian sempat diterapkan sebelum koneksi terputus.

## Langkah pengujian manual

1. Pastikan server Laravel aktif, lalu buka http://127.0.0.1:8000/ . Periksa hero, kategori dan produk terbaru. Klik logo untuk kembali ke beranda.
2. Klik Produk atau Jelajahi produk. Semua produk aktif harus tampil; harga dan kategori harus sama dengan data Admin.
3. Cari `Royal`, lalu klik Tampilkan produk. Produk Royal Canin yang ada di database harus ditemukan. Coba kata yang tidak cocok untuk melihat empty state.
4. Pilih kategori Makanan Hewan, lalu terapkan. Coba kombinasikan dengan pencarian; tombol Hapus pencarian & filter harus mengembalikan seluruh katalog.
5. Jika tersedia >12 produk aktif, buka halaman kedua. Pencarian dan kategori harus tetap tersimpan pada URL.
6. Buka Lihat detail. Periksa gambar/placeholder, nama, kategori, deskripsi, harga dan stok. Klik kategori pada detail untuk membuka katalog terfilter.
7. Pada produk dengan stok positif, isi jumlah 0, pecahan, atau lebih besar dari stok lalu pindahkan fokus. Nilai harus dibatasi ke rentang 1–stok. Tombol Tambah ke Keranjang tetap nonaktif dan penjelasannya terlihat.
8. Melalui Admin, ubah stok sebuah produk menjadi 0. Katalog/detail harus menampilkan Stok habis dan input jumlah nonaktif. Setelah menguji, kembalikan stok jika perubahan hanya untuk percobaan.
9. Nonaktifkan sebuah produk melalui Admin. Produk harus hilang dari beranda/katalog dan URL detail publiknya menjadi 404. Admin tetap dapat mengeditnya melalui route Admin. Aktifkan kembali sesuai kebutuhan.
10. Untuk tamu: navbar memiliki Masuk/Daftar dan informasi Keranjang. Untuk Pelanggan: Profil/Akun saya/Keluar dan informasi Keranjang. Untuk Admin: Profil/Dashboard Admin/Keluar tanpa keranjang navbar.
11. Klik Keranjang saat sebagai tamu/Pelanggan: modal menjelaskan fitur belum tersedia; Mengerti menutup modal dan tidak menambah pesanan.
12. Uji pada desktop dan ponsel (misalnya 390 px), termasuk dark mode: navbar, form pencarian/filter, kartu produk, detail dan footer tidak boleh terpotong. Bila browser pengujian masih memakai ukuran desktop tetap, kembalikan ukuran viewport melalui browser atau buka browser biasa.
13. Uji ulang dashboard/CRUD Admin serta login/logout. Tidak ada perubahan alur Admin yang disengaja pada Tahap 4.

## Batasan

Keranjang, checkout, invoice, transaksi dan pembayaran belum dibuat. Jumlah pada detail hanya persiapan UI; validasi harga/stok server untuk transaksi harus dibuat pada Tahap 5. Tiga produk awal belum memiliki foto dan dapat dilengkapi melalui Admin. Commit/push belum dilakukan.
