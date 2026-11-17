# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## PAWMART — PET SHOP E-COMMERCE

**Website Penjualan Kebutuhan Hewan Peliharaan Berbasis Laravel 13**

| Informasi | Keterangan |
|---|---|
| Nama sistem | PawMart Pet Shop |
| Jenis aplikasi | Website E-Commerce |
| Framework | Laravel 13 |
| Bahasa pemrograman | PHP 8.3+ |
| Database | MySQL |
| Arsitektur | Model–View–Controller (MVC) |
| Frontend | Laravel Blade, Tailwind CSS, JavaScript |
| Payment Gateway | Midtrans Sandbox |
| Pengguna | Admin dan Pelanggan |
| Lingkungan pengembangan | Laragon, VS Code, Git, GitHub |
| Nama proyek | `ecommerce-app` |
| Status dokumen | PRD pengembangan awal |

---

## 1. Gambaran Umum Produk

### 1.1 Deskripsi Sistem

PawMart merupakan website *e-commerce* yang menyediakan berbagai kebutuhan hewan peliharaan secara daring. Website ini berfokus pada penjualan perlengkapan dan produk kebutuhan hewan, seperti makanan kucing, makanan anjing, mainan, perlengkapan kebersihan, tempat tidur, serta aksesori hewan peliharaan.

Pelanggan dapat melihat produk, melakukan pencarian, memilih kategori, memasukkan barang ke keranjang, melakukan *checkout*, dan menyelesaikan pembayaran melalui Midtrans Sandbox.

Di sisi lain, Admin memiliki halaman tersendiri untuk mengelola kategori, produk, persediaan barang, pesanan pelanggan, dan status pengiriman.

Sistem dikembangkan menggunakan Laravel 13 dengan penerapan arsitektur MVC, Eloquent ORM, autentikasi, *middleware*, serta database MySQL.

### 1.2 Tujuan Pengembangan

1. Membangun website penjualan kebutuhan hewan peliharaan yang dapat digunakan secara daring.
2. Memudahkan pelanggan menemukan dan membeli kebutuhan hewan.
3. Memudahkan Admin mengelola produk, kategori, harga, dan stok.
4. Mengimplementasikan proses transaksi dari keranjang sampai pembayaran.
5. Mengintegrasikan Midtrans Sandbox untuk melakukan simulasi pembayaran.
6. Mengimplementasikan keamanan akses berdasarkan peran pengguna.
7. Memenuhi seluruh persyaratan tugas besar Praktikum Pemrograman E-Commerce.

### 1.3 Target Pengguna

**Pelanggan**

Pemilik hewan peliharaan yang ingin membeli kebutuhan hewan secara praktis melalui website.

**Admin**

Pengelola toko yang bertanggung jawab atas operasional, termasuk produk, persediaan, transaksi, dan pesanan pelanggan.

---

## 2. Ruang Lingkup Pengembangan

### 2.1 Fitur Wajib

Seluruh fitur berikut harus diimplementasikan:

| No. | Modul | Fungsionalitas |
|---|---|---|
| 1 | Authentication | Registrasi, login, logout |
| 2 | Role & Middleware | Pembatasan akses Admin dan Pelanggan |
| 3 | Dashboard Admin | Statistik produk, kategori, pelanggan, dan pesanan |
| 4 | Kategori | Tambah, lihat, ubah, hapus |
| 5 | Produk | CRUD, harga, stok, dan foto produk |
| 6 | Katalog | Daftar, pencarian, dan filter produk |
| 7 | Detail Produk | Informasi lengkap produk |
| 8 | Shopping Cart | Tambah, ubah jumlah, hapus, subtotal |
| 9 | Checkout | Data penerima, alamat, ringkasan pesanan |
| 10 | Pesanan | Pembuatan dan penyimpanan transaksi |
| 11 | Payment Gateway | Pembayaran Midtrans Sandbox |
| 12 | Payment Verification | Verifikasi dan pembaruan status pembayaran |
| 13 | Riwayat Pesanan | Daftar, detail, invoice |
| 14 | Manajemen Pesanan | Pemrosesan dan pengiriman oleh Admin |
| 15 | Database | MySQL, migration, relasi, Eloquent ORM |
| 16 | Version Control | Git dan GitHub |

### 2.2 Batasan Sistem

Untuk menjaga pengembangan sesuai waktu tugas praktikum:

- Website hanya memiliki dua peran: Admin dan Pelanggan.
- Produk yang dijual berupa kebutuhan dan perlengkapan hewan, bukan hewan hidup.
- Tidak menyediakan pembelian obat hewan yang memerlukan resep.
- Pembayaran menggunakan Midtrans Sandbox, bukan transaksi uang sungguhan.
- Pengiriman dikelola secara manual oleh Admin, tanpa integrasi API ekspedisi.
- Sistem menggunakan satu toko, bukan *multi-vendor marketplace*.
- Tidak menyediakan sistem langganan, lelang, atau penjualan grosir.

---

## 3. Kategori dan Produk Pet Shop

### 3.1 Kategori Produk

Kategori awal yang disarankan:

| Kategori | Contoh Produk |
|---|---|
| Makanan Kucing | Dry Food, Wet Food, Kitten Food |
| Makanan Anjing | Dog Food, Puppy Food |
| Snack Hewan | Cat Treats, Dog Treats |
| Mainan Hewan | Bola, Cat Teaser, Bone Toy |
| Grooming & Kebersihan | Sampo Hewan, Sisir, Pasir Kucing |
| Aksesori Hewan | Kalung, Tali, Harness |
| Kandang & Tempat Tidur | Pet Bed, Carrier, Kandang |

Kategori tersebut merupakan data awal dan tetap bisa ditambahkan, diubah, atau dihapus oleh Admin.

### 3.2 Informasi Produk

Setiap produk wajib menyimpan:

- Nama produk
- Kategori produk
- Deskripsi produk
- Harga dalam rupiah
- Jumlah stok
- Gambar produk

Informasi tambahan yang direkomendasikan adalah SKU, berat produk, merek, *slug*, dan status aktif atau nonaktif.

### 3.3 Aturan Produk

1. Harga produk tidak boleh bernilai negatif.
2. Stok menggunakan bilangan bulat dan tidak boleh negatif.
3. Produk dengan stok kosong tidak dapat dibeli.
4. Gambar diunggah menggunakan validasi jenis dan ukuran berkas.
5. Produk yang dinonaktifkan tidak ditampilkan sebagai produk yang dapat dibeli.
6. Perubahan harga tidak mengubah harga pada pesanan lama.
7. Produk yang sudah tercatat dalam transaksi tetap memiliki riwayat pembelian meskipun kemudian dihapus dari katalog.

---

## 4. Hak Akses Pengguna

### 4.1 Matriks Hak Akses

| Fitur | Admin | Pelanggan |
|---|:---:|:---:|
| Login dan logout | Ya | Ya |
| Registrasi publik | Tidak | Ya |
| Dashboard Admin | Ya | Tidak |
| Kelola kategori | Ya | Tidak |
| Kelola produk | Ya | Tidak |
| Kelola stok | Ya | Tidak |
| Lihat katalog | Ya | Ya |
| Kelola keranjang | Tidak | Ya |
| Checkout | Tidak | Ya |
| Pembayaran Midtrans | Tidak | Ya |
| Lihat pesanan sendiri | Tidak | Ya |
| Lihat semua pesanan | Ya | Tidak |
| Ubah status pengiriman | Ya | Tidak |
| Mengelola profil sendiri | Ya | Ya |

### 4.2 Aturan Autentikasi

- Pengguna yang belum login dapat melihat halaman beranda, katalog, dan detail produk.
- Pengguna wajib login sebagai Pelanggan untuk menggunakan keranjang dan melakukan transaksi.
- Registrasi publik hanya menghasilkan akun Pelanggan.
- Akun Admin dibuat melalui *seeder* atau proses yang diizinkan pengembang.
- Pelanggan tidak boleh mengakses URL maupun fungsi Admin.
- Admin dan Pelanggan diarahkan ke halaman masing-masing setelah login.
- Sistem menggunakan Laravel Authentication dan *middleware* berbasis peran.

---

## 5. Kebutuhan Fungsional Admin

### FR-ADM-01 — Login Admin

Admin dapat login menggunakan email dan password.

**Kriteria penerimaan:**

- Kredensial benar mengarahkan Admin ke dashboard.
- Kredensial salah menampilkan pesan kesalahan.
- Pelanggan yang mengakses halaman Admin menerima penolakan akses.
- Logout mengakhiri sesi autentikasi.

### FR-ADM-02 — Dashboard Admin

Dashboard menampilkan enam statistik wajib:

1. Total produk
2. Total kategori
3. Total pelanggan
4. Total pesanan
5. Pesanan menunggu pembayaran
6. Pesanan telah dibayar

Dashboard juga dapat menampilkan daftar pesanan terbaru sebagai pelengkap.

Statistik harus berasal dari database, bukan angka yang ditulis secara statis.

### FR-ADM-03 — Manajemen Kategori

Admin dapat menambah, melihat, mengedit, dan menghapus kategori.

**Ketentuan:**

- Nama kategori wajib diisi.
- Nama kategori tidak boleh duplikat.
- Setiap kategori mempunyai ID unik.
- Kategori yang masih digunakan produk tidak dapat dihapus sebelum relasinya diselesaikan.
- Setelah tindakan berhasil, sistem menampilkan notifikasi.

### FR-ADM-04 — Manajemen Produk

Admin dapat:

- Menambahkan produk.
- Menampilkan tabel produk.
- Mengedit nama, kategori, deskripsi, harga, dan stok.
- Mengunggah dan mengganti gambar.
- Menghapus produk atau menonaktifkannya.
- Melihat ketersediaan stok.

**Kriteria penerimaan:**

- Data produk tersimpan pada MySQL.
- Gambar tersimpan melalui Laravel Storage.
- Produk tampil pada katalog Pelanggan.
- Perubahan stok dan harga diperbarui pada katalog.
- Produk dengan stok nol ditandai sebagai habis.

### FR-ADM-05 — Manajemen Pesanan

Admin dapat melihat seluruh pesanan beserta:

- Nomor pesanan
- Nama pelanggan
- Tanggal transaksi
- Produk yang dibeli
- Total pembayaran
- Status pembayaran
- Status pemrosesan dan pengiriman
- Informasi penerima dan alamat pengiriman

Admin dapat memperbarui status proses pesanan, tetapi **tidak dapat menetapkan pembayaran sebagai berhasil secara manual**.

### FR-ADM-06 — Status Pemrosesan Pesanan

Status yang digunakan:

| Status | Keterangan |
|---|---|
| Menunggu Pembayaran | Pelanggan belum menyelesaikan pembayaran |
| Diproses | Pembayaran terverifikasi dan pesanan dipersiapkan |
| Dikirim | Pesanan telah diserahkan untuk pengiriman |
| Selesai | Pesanan telah diselesaikan |
| Dibatalkan | Pesanan dibatalkan sesuai aturan sistem |

Status pemrosesan pesanan dipisahkan dari status pembayaran.

---

## 6. Kebutuhan Fungsional Pelanggan

### FR-CUS-01 — Registrasi dan Login

Pelanggan dapat membuat akun menggunakan nama, email, password, dan konfirmasi password.

Sistem melakukan validasi email unik, kecocokan konfirmasi password, serta aturan keamanan password.

Setelah login berhasil, pelanggan dapat mengakses halaman keranjang, profil, *checkout*, dan riwayat pesanan.

### FR-CUS-02 — Halaman Beranda

Beranda menampilkan:

- Navbar dengan logo PawMart.
- Navigasi Beranda, Produk, dan Kategori.
- Tombol Login atau profil akun.
- Ikon keranjang.
- Hero section promosi Pet Shop.
- Kategori produk.
- Produk pilihan atau produk terbaru.
- Footer berisi informasi toko.

### FR-CUS-03 — Katalog Produk

Pelanggan dapat melihat produk dalam bentuk *product card*.

Setiap kartu menampilkan gambar, nama, harga, kategori atau label produk, serta tombol lihat detail.

**Fitur wajib:**

- Pencarian berdasarkan nama produk.
- Filter berdasarkan kategori.
- Menampilkan harga dan gambar.
- Membuka detail produk.

Fitur pengurutan berdasarkan harga atau produk terbaru dapat ditambahkan sebagai penyempurnaan.

### FR-CUS-04 — Detail Produk

Halaman detail menampilkan:

- Gambar produk.
- Nama dan kategori.
- Harga.
- Deskripsi.
- Stok tersedia.
- Jumlah pembelian.
- Tombol Tambah ke Keranjang.

Jumlah pembelian harus lebih dari nol dan tidak boleh melebihi stok tersedia.

### FR-CUS-05 — Shopping Cart

Keranjang menampilkan produk yang dipilih pelanggan.

Pengguna dapat menambahkan barang, mengubah jumlah, menghapus barang, dan melihat subtotal serta total harga.

**Aturan:**

- Setiap item memiliki produk, kuantitas, harga saat ini, dan subtotal.
- Jumlah tidak boleh melebihi stok.
- Keranjang tidak boleh diproses jika kosong.
- Harga dan stok diperiksa kembali pada saat *checkout*.
- Total tidak boleh ditentukan berdasarkan input harga dari browser.

### FR-CUS-06 — Checkout

Halaman *checkout* menampilkan ringkasan pesanan dan formulir penerima.

Field yang diperlukan:

| Field | Wajib |
|---|---|
| Nama penerima | Ya |
| Nomor telepon | Ya |
| Alamat lengkap | Ya |
| Kota/Kabupaten | Ya |
| Provinsi | Ya |
| Kode pos | Ya |
| Catatan pengiriman | Tidak |

Sistem menghitung total harga kembali melalui *backend* berdasarkan data produk dan biaya pengiriman.

Untuk versi tugas besar, biaya pengiriman dapat menggunakan tarif tetap yang dikonfigurasi pada server, misalnya Rp15.000 per pesanan sebagai nilai simulasi.

### FR-CUS-07 — Pembayaran

Setelah pesanan tersimpan, pelanggan diarahkan ke Midtrans Snap Sandbox untuk memilih dan menguji metode pembayaran yang disediakan.

Website menampilkan status pembayaran berdasarkan hasil verifikasi yang diterima server.

Status pembayaran minimal mencakup:

- Menunggu
- Berhasil
- Kedaluwarsa
- Gagal

Status dibatalkan dapat digunakan sebagai tambahan.

### FR-CUS-08 — Riwayat Pesanan

Pelanggan dapat melihat daftar seluruh pesanan miliknya sendiri.

Setiap riwayat menampilkan nomor pesanan, tanggal, total, status pembayaran, dan status pemrosesan.

Pelanggan dapat membuka detail pesanan dan invoice.

Pelanggan tidak boleh melihat pesanan milik pengguna lain meskipun mengetahui nomor atau ID pesanannya.

### FR-CUS-09 — Profil Pelanggan

Pelanggan dapat melihat dan memperbarui informasi profil dasar, seperti nama, email, dan nomor telepon.

Perubahan email harus memenuhi validasi keunikan data.

---

## 7. Alur Penggunaan Sistem

### 7.1 Alur Pelanggan

```text
Pengunjung
    |
    v
Beranda PawMart
    |
    v
Katalog Produk
    |
    v
Pencarian / Filter Kategori
    |
    v
Detail Produk
    |
    v
Login / Registrasi (jika belum login)
    |
    v
Tambah ke Keranjang
    |
    v
Lihat Keranjang
    |
    v
Checkout
    |
    v
Isi Data Penerima dan Alamat
    |
    v
Validasi Stok dan Total di Server
    |
    v
Pembuatan Pesanan
    |
    v
Midtrans Snap Sandbox
    |
    v
Verifikasi Pembayaran oleh Server
    |
    +-- Berhasil --> Pesanan Diproses
    |
    +-- Pending ----> Menunggu Pembayaran
    |
    +-- Gagal ------> Status Gagal
    |
    +-- Expire -----> Status Kedaluwarsa
    |
    v
Riwayat dan Detail Pesanan
```

### 7.2 Alur Admin

```text
Login Admin
    |
    v
Dashboard Admin
    |
    +--> Kelola Kategori
    |
    +--> Kelola Produk
    |       |
    |       +--> Harga
    |       +--> Stok
    |       +--> Gambar
    |
    +--> Lihat Pesanan Pelanggan
            |
            v
       Detail Pesanan
            |
            v
       Cek Status Pembayaran
            |
            v
       Pembayaran Terverifikasi
            |
            v
       Proses Pesanan
            |
            v
       Tandai Dikirim
            |
            v
       Tandai Selesai
```

---

## 8. Integrasi Midtrans Sandbox

### 8.1 Tujuan

Mengimplementasikan pembayaran yang memungkinkan pelanggan menyelesaikan simulasi transaksi menggunakan Midtrans.

### 8.2 Konfigurasi

Konfigurasi menggunakan:

- Midtrans Server Key
- Midtrans Client Key
- Mode Sandbox
- URL notifikasi pembayaran
- URL pengembalian pelanggan

Kunci rahasia disimpan pada file `.env` dan tidak dimasukkan ke repository GitHub.

### 8.3 Proses Pembayaran

1. Pelanggan melakukan *checkout*.
2. Server memvalidasi data, harga, dan stok.
3. Server menyimpan pesanan dengan ID transaksi unik.
4. Server mengirim detail transaksi dan total pembayaran ke Midtrans.
5. Midtrans memberikan Snap Token.
6. Pelanggan membuka antarmuka pembayaran Midtrans.
7. Pelanggan melakukan simulasi pembayaran.
8. Midtrans mengirim notifikasi status pembayaran ke server.
9. Server memverifikasi keaslian notifikasi.
10. Sistem memperbarui status pembayaran dan menampilkannya pada riwayat pesanan.

### 8.4 Verifikasi dan Keamanan

- Server wajib memeriksa `signature_key` notifikasi Midtrans.
- Nilai total transaksi harus cocok dengan transaksi dalam database.
- Status berhasil hanya diterima dari notifikasi terverifikasi atau pemeriksaan status melalui API Midtrans.
- Proses harus aman dari notifikasi ganda (*idempotent*).
- Callback dari browser tidak langsung mengubah status menjadi berhasil.
- Pesanan yang gagal atau kedaluwarsa ditangani sesuai aturan reservasi stok.
- URL *webhook* harus dapat diakses Midtrans melalui jaringan publik dan menggunakan HTTPS untuk integrasi yang aman.

### 8.5 Pengelolaan Stok

Untuk mencegah pembelian melebihi stok:

- Saat pesanan dibuat, stok dapat direservasi melalui transaksi database.
- Pemeriksaan dan reservasi dilakukan secara atomik agar dua pelanggan tidak menggunakan stok yang sama.
- Pesanan yang dibatalkan atau kedaluwarsa melepaskan stok reservasi tepat satu kali.
- Pembayaran berhasil menetapkan stok terpakai secara final.
- Proses pengecekan berkala dapat menangani transaksi yang tidak menerima notifikasi tepat waktu.

---

## 9. Rancangan Database

Database menggunakan MySQL dan dikelola melalui Laravel Migration serta Eloquent ORM.

### 9.1 Tabel `users`

| Field | Tipe | Keterangan |
|---|---|---|
| id | BIGINT | Primary Key |
| name | VARCHAR | Nama pengguna |
| email | VARCHAR | Email unik |
| password | VARCHAR | Password ter-hash |
| phone | VARCHAR, nullable | Nomor telepon |
| role | VARCHAR/ENUM | admin / customer |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 9.2 Tabel `categories`

| Field | Tipe | Keterangan |
|---|---|---|
| id | BIGINT | Primary Key |
| name | VARCHAR | Nama kategori |
| slug | VARCHAR | URL kategori |
| description | TEXT, nullable | Deskripsi |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 9.3 Tabel `products`

| Field | Tipe | Keterangan |
|---|---|---|
| id | BIGINT | Primary Key |
| category_id | BIGINT | Foreign Key |
| name | VARCHAR | Nama produk |
| slug | VARCHAR | URL produk |
| sku | VARCHAR, nullable | Kode produk |
| description | TEXT | Deskripsi |
| price | DECIMAL(12,2) | Harga |
| stock | INT | Persediaan |
| image | VARCHAR | Lokasi gambar |
| is_active | BOOLEAN | Status produk |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 9.4 Tabel `carts`

| Field | Tipe | Keterangan |
|---|---|---|
| id | BIGINT | Primary Key |
| user_id | BIGINT | Pemilik keranjang |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 9.5 Tabel `cart_items`

| Field | Tipe | Keterangan |
|---|---|---|
| id | BIGINT | Primary Key |
| cart_id | BIGINT | Relasi keranjang |
| product_id | BIGINT | Relasi produk |
| quantity | INT | Jumlah barang |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

Kombinasi `cart_id` dan `product_id` harus unik agar produk yang sama tidak mempunyai baris keranjang ganda.

### 9.6 Tabel `orders`

| Field | Tipe | Keterangan |
|---|---|---|
| id | BIGINT | Primary Key |
| user_id | BIGINT | Pelanggan |
| order_number | VARCHAR | Nomor pesanan unik |
| recipient_name | VARCHAR | Nama penerima |
| recipient_phone | VARCHAR | Telepon |
| shipping_address | TEXT | Alamat lengkap |
| city | VARCHAR | Kota |
| province | VARCHAR | Provinsi |
| postal_code | VARCHAR | Kode pos |
| notes | TEXT, nullable | Catatan |
| subtotal | DECIMAL(12,2) | Subtotal produk |
| shipping_cost | DECIMAL(12,2) | Biaya pengiriman |
| total_amount | DECIMAL(12,2) | Total transaksi |
| payment_status | VARCHAR | Status pembayaran |
| order_status | VARCHAR | Status pemrosesan |
| stock_reserved | BOOLEAN | Penanda reservasi stok |
| stock_released_at | TIMESTAMP, nullable | Waktu pelepasan stok |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 9.7 Tabel `order_items`

| Field | Tipe | Keterangan |
|---|---|---|
| id | BIGINT | Primary Key |
| order_id | BIGINT | Relasi pesanan |
| product_id | BIGINT, nullable | Produk terkait |
| product_name | VARCHAR | Salinan nama produk |
| product_price | DECIMAL(12,2) | Harga saat dibeli |
| quantity | INT | Jumlah |
| subtotal | DECIMAL(12,2) | Total item |

Penyimpanan nama dan harga produk pada `order_items` memastikan transaksi historis tidak berubah ketika data produk diperbarui.

### 9.8 Tabel `payments`

| Field | Tipe | Keterangan |
|---|---|---|
| id | BIGINT | Primary Key |
| order_id | BIGINT | Pesanan |
| midtrans_order_id | VARCHAR | ID transaksi Midtrans |
| transaction_id | VARCHAR, nullable | ID dari Midtrans |
| snap_token | TEXT, nullable | Token pembayaran |
| payment_type | VARCHAR, nullable | Metode pembayaran |
| transaction_status | VARCHAR | Status dari Midtrans |
| gross_amount | DECIMAL(12,2) | Jumlah pembayaran |
| paid_at | TIMESTAMP, nullable | Waktu pembayaran |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

Tabel tambahan `payment_notifications` dapat digunakan untuk mencatat pemberitahuan Midtrans dan membantu proses audit serta pencegahan pemrosesan ganda.

### 9.9 Relasi Antar Tabel

```text
users
  |--- carts
  |      |--- cart_items --- products
  |
  |--- orders
         |--- order_items --- products
         |
         |--- payments

categories
  |
  |--- products
```

**Relasi Eloquent:**

| Model | Relationship |
|---|---|
| User | hasMany Orders |
| User | hasOne Cart |
| Category | hasMany Products |
| Product | belongsTo Category |
| Cart | belongsTo User |
| Cart | hasMany CartItems |
| CartItem | belongsTo Product |
| Order | belongsTo User |
| Order | hasMany OrderItems |
| Order | hasMany Payments |
| OrderItem | belongsTo Order |
| Payment | belongsTo Order |

Catatan: rancangan database ini menjadi target akhir. Migration yang sudah ada pada `ecommerce-app` harus diperiksa lebih dahulu, lalu disesuaikan dengan migration tambahan, bukan langsung dihapus dan dibuat ulang.

---

## 10. Struktur Halaman Website

### 10.1 Halaman Publik dan Pelanggan

| Halaman | Contoh URL | Fungsi |
|---|---|---|
| Beranda | `/` | Landing page Pet Shop |
| Katalog Produk | `/products` | Semua produk |
| Detail Produk | `/products/{slug}` | Informasi produk |
| Login | `/login` | Autentikasi |
| Registrasi | `/register` | Membuat akun |
| Keranjang | `/cart` | Daftar produk yang dipilih |
| Checkout | `/checkout` | Formulir transaksi |
| Pembayaran | `/orders/{order}/payment` | Pembayaran Midtrans |
| Riwayat Pesanan | `/orders` | Pesanan pelanggan |
| Detail Pesanan | `/orders/{order}` | Informasi transaksi |
| Invoice | `/orders/{order}/invoice` | Ringkasan pembelian |
| Profil | `/profile` | Informasi pelanggan |

### 10.2 Halaman Admin

| Halaman | Contoh URL | Fungsi |
|---|---|---|
| Dashboard | `/admin/dashboard` | Statistik |
| Kategori | `/admin/categories` | Daftar kategori |
| Tambah Kategori | `/admin/categories/create` | Form kategori |
| Edit Kategori | `/admin/categories/{category}/edit` | Edit kategori |
| Produk | `/admin/products` | Daftar produk |
| Tambah Produk | `/admin/products/create` | Form produk |
| Edit Produk | `/admin/products/{product}/edit` | Edit produk |
| Pesanan | `/admin/orders` | Seluruh pesanan |
| Detail Pesanan | `/admin/orders/{order}` | Detail dan pemrosesan |

URL tersebut merupakan rancangan rute yang dapat disesuaikan dengan struktur Laravel yang sudah tersedia.

---

## 11. Ketentuan UI/UX

### 11.1 Konsep Desain

Website mengusung konsep **Modern Minimalist Pet Shop**, yaitu tampilan yang bersih, hangat, ramah, dan mudah digunakan.

Desain tidak menggunakan terlalu banyak warna mencolok ataupun dekorasi yang mengganggu proses belanja.

### 11.2 Warna Utama

| Elemen | Warna | Kode |
|---|---|---|
| Background | Warm Cream | `#FAF7F2` |
| Primary | Forest Green | `#2F5D50` |
| Accent | Soft Peach | `#E8B98A` |
| Text | Charcoal | `#25302B` |
| Surface | White | `#FFFFFF` |
| Border | Warm Gray | `#E6E4DF` |

### 11.3 Tipografi

Font utama: **DM Sans** atau **Plus Jakarta Sans**.

Font digunakan secara konsisten pada halaman Pelanggan dan Admin, dengan perbedaan ukuran untuk judul, label, harga, dan teks penjelasan.

### 11.4 Komponen UI

Website menggunakan:

- Navbar responsif.
- Product card.
- Search bar.
- Filter kategori.
- Tombol utama dan sekunder.
- Form input konsisten.
- Badge stok dan status pesanan.
- Modal konfirmasi penghapusan.
- Toast notifikasi.
- Tabel responsif untuk Admin.
- Sidebar Admin.
- Tampilan *empty state* untuk keranjang dan pesanan kosong.

### 11.5 Responsivitas

Website harus nyaman digunakan pada perangkat desktop, tablet, dan smartphone.

Pada layar kecil, kartu produk menyesuaikan jumlah kolom, navigasi berubah menjadi menu yang ringkas, dan tabel Admin tidak boleh menyebabkan konten utama terpotong tanpa mekanisme akses yang jelas.

### 11.6 Aksesibilitas

Setiap gambar produk memiliki teks alternatif, formulir memiliki label, tombol memiliki status fokus, dan teks harus memiliki kontras yang cukup terhadap latar belakang.

---

## 12. Arsitektur Implementasi Laravel 13

### 12.1 Pendekatan

Aplikasi menggunakan Laravel MVC dengan Blade untuk tampilan, Eloquent untuk akses database, *Form Request* untuk validasi, dan *middleware* untuk autentikasi serta otorisasi.

### 12.2 Struktur Folder yang Disarankan

```text
ecommerce-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── Customer/
│   │   │   └── Auth/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Services/
│       ├── CartService.php
│       ├── OrderService.php
│       └── MidtransService.php
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── layouts/
│       ├── auth/
│       ├── customer/
│       ├── admin/
│       └── components/
├── routes/
│   └── web.php
├── tests/
│   └── Feature/
├── .env.example
└── README.md
```

Untuk notifikasi Midtrans, dibuat endpoint server tersendiri, misalnya `/api/midtrans/notification`, yang diproses melalui controller atau service dan dikonfigurasi sesuai mekanisme *routing* Laravel 13.

### 12.3 Aturan Pengembangan

- Kode bisnis tidak ditumpuk dalam file Blade.
- Akses database menggunakan Eloquent dan relasi model.
- Validasi dilakukan di server.
- Query data sensitif dibatasi sesuai pengguna.
- Logika transaksi dan Midtrans ditempatkan pada service.
- Password disimpan melalui hashing.
- Rahasia aplikasi tidak dimasukkan ke GitHub.
- Migration dan Seeder disertakan dalam repository.

---

## 13. Kebutuhan Nonfungsional

| ID | Kebutuhan | Kriteria |
|---|---|---|
| NFR-01 | Keamanan | Role dan middleware diterapkan |
| NFR-02 | Validasi | Input diperiksa server |
| NFR-03 | Database | Relasional dengan foreign key |
| NFR-04 | Responsivitas | Desktop dan mobile berfungsi |
| NFR-05 | Konsistensi | Warna, tombol, dan form seragam |
| NFR-06 | Integritas | Total transaksi dihitung di server |
| NFR-07 | Pembayaran | Status berdasarkan verifikasi Midtrans |
| NFR-08 | Ketersediaan stok | Tidak terjadi pembelian melebihi persediaan |
| NFR-09 | Pemeliharaan | Struktur MVC dan kode terorganisasi |
| NFR-10 | Dokumentasi | README, instruksi instalasi, GitHub |
| NFR-11 | Audit | Status transaksi dapat ditelusuri |
| NFR-12 | Pengujian | Fitur wajib mempunyai skenario uji |

---

## 14. Skenario Pengujian

Pengujian dilakukan menggunakan Laravel Feature Test dan pengujian manual melalui browser.

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| TC-01 | Registrasi valid | Akun Pelanggan tercipta |
| TC-02 | Registrasi email duplikat | Ditolak |
| TC-03 | Login benar | Berhasil |
| TC-04 | Login salah | Pesan error |
| TC-05 | Pelanggan membuka dashboard Admin | Akses ditolak |
| TC-06 | Admin menambah kategori | Tersimpan |
| TC-07 | Admin mengedit produk | Data berubah |
| TC-08 | Admin upload gambar produk | Berhasil |
| TC-09 | Pelanggan mencari produk | Hasil sesuai |
| TC-10 | Filter kategori | Produk terfilter |
| TC-11 | Tambah produk ke keranjang | Item tersimpan |
| TC-12 | Ubah jumlah produk | Subtotal berubah |
| TC-13 | Jumlah melebihi stok | Ditolak |
| TC-14 | Checkout valid | Pesanan tercipta |
| TC-15 | Harga dimanipulasi dari browser | Total server tetap benar |
| TC-16 | Pembayaran Sandbox berhasil | Status menjadi paid |
| TC-17 | Pembayaran tertunda | Status pending |
| TC-18 | Pembayaran kedaluwarsa | Status expired |
| TC-19 | Notifikasi Midtrans palsu | Ditolak |
| TC-20 | Notifikasi pembayaran ganda | Tidak diproses dua kali |
| TC-21 | Admin memproses pesanan dibayar | Status proses berubah |
| TC-22 | Pelanggan melihat pesanan orang lain | Akses ditolak |
| TC-23 | Pelanggan membuka invoice sendiri | Invoice tampil |
| TC-24 | Tampilan melalui smartphone | Layout tetap berfungsi |

Seluruh pengujian wajib dicatat hasilnya dalam laporan akhir.

---

## 15. Tahapan Pengerjaan

### Tahap 1 — Persiapan dan Analisis Proyek

- Memeriksa Laravel 13 dan konfigurasi PHP.
- Memastikan MySQL terhubung.
- Memeriksa migration yang sudah dibuat.
- Memeriksa route dan file bawaan Laravel.
- Menyiapkan GitHub.
- Menetapkan struktur Admin dan Pelanggan.

**Output:** Fondasi aplikasi siap dikembangkan.

### Tahap 2 — Authentication dan Role

- Registrasi Pelanggan.
- Login dan logout.
- Penambahan kolom role.
- Seeder akun Admin.
- Middleware pembatasan akses.
- Redirect berdasarkan role.

**Output:** Admin dan Pelanggan memiliki hak akses berbeda.

### Tahap 3 — Dashboard dan CRUD Admin

- Layout dashboard Admin.
- Statistik dashboard.
- CRUD kategori.
- CRUD produk.
- Upload gambar.
- Pengaturan harga dan stok.

**Output:** Admin dapat mengelola katalog dan data toko.

### Tahap 4 — Katalog Pelanggan

- Halaman beranda.
- Katalog produk.
- Pencarian.
- Filter kategori.
- Detail produk.
- Desain responsif.

**Output:** Pelanggan dapat menjelajahi produk Pet Shop.

### Tahap 5 — Keranjang dan Checkout

- Tambah produk.
- Update jumlah.
- Hapus item.
- Validasi stok.
- Form alamat.
- Hitung total server.
- Simpan order dan order item.

**Output:** Pesanan dapat dibuat dari keranjang.

### Tahap 6 — Integrasi Midtrans

- Konfigurasi Sandbox.
- Membuat Snap Token.
- Antarmuka pembayaran.
- Endpoint notifikasi.
- Verifikasi status.
- Penanganan pembayaran gagal dan kedaluwarsa.
- Penanganan stok.

**Output:** Alur pembayaran simulasi berjalan.

### Tahap 7 — Manajemen dan Riwayat Pesanan

- Daftar seluruh pesanan Admin.
- Detail transaksi Admin.
- Update status pemrosesan.
- Riwayat pesanan Pelanggan.
- Detail pesanan dan invoice.

**Output:** Siklus pemesanan dapat dipantau sampai selesai.

### Tahap 8 — Pengujian dan Dokumentasi

- Pengujian seluruh fitur wajib.
- Perbaikan kesalahan.
- Pengujian keamanan akses.
- Pengujian mobile.
- Penyusunan README.
- Push ke GitHub.
- Dokumentasi screenshot.
- Penyusunan laporan akhir.
- Persiapan demonstrasi aplikasi.

**Output:** Website siap dikumpulkan dan didemonstrasikan.

---

## 16. Prioritas Pengerjaan

| Prioritas | Fitur | Status Kebutuhan |
|---|---|---|
| P0 | Authentication dan role | Wajib |
| P0 | Database, model, migration | Wajib |
| P0 | Dashboard dan CRUD Admin | Wajib |
| P0 | Katalog dan detail produk | Wajib |
| P0 | Keranjang dan checkout | Wajib |
| P0 | Midtrans dan verifikasi | Wajib |
| P0 | Riwayat dan status pesanan | Wajib |
| P0 | Pengujian dan GitHub | Wajib |
| P1 | Penyempurnaan UI/UX | Penting |
| P2 | Wishlist | Opsional |
| P2 | Ulasan produk | Opsional |
| P2 | Kupon diskon | Opsional |
| P2 | Integrasi ekspedisi | Opsional |
| P2 | Notifikasi email | Opsional |

Fitur prioritas P2 tidak boleh menghambat penyelesaian seluruh fitur wajib.

---

## 17. Kesesuaian dengan Rubrik Penilaian

| Komponen Penilaian | Bobot | Implementasi PawMart |
|---|---:|---|
| Database, migration, model, relasi | 10% | Rancangan MySQL dan Eloquent |
| Authentication, role, middleware | 10% | Admin dan Pelanggan |
| Fungsionalitas Admin | 15% | Dashboard, kategori, produk, pesanan |
| Fungsionalitas Pelanggan | 15% | Katalog, profil, pembelian, riwayat |
| Shopping Cart, Checkout, Order | 15% | Keranjang dan transaksi |
| Midtrans dan validasi pembayaran | 15% | Snap Sandbox dan verifikasi |
| Pengujian fungsionalitas | 5% | Feature Test dan pengujian manual |
| UI/UX dan responsivitas | 10% | Modern Minimalist Pet Shop |
| GitHub, dokumentasi, demonstrasi | 5% | Repository, README, laporan |
| **Total** | **100%** | **Seluruh komponen tercakup** |

---

## 18. Hasil Akhir dan Kriteria Selesai

Proyek PawMart dinyatakan selesai jika:

1. Website dapat dijalankan menggunakan Laravel 13 dan MySQL.
2. Admin dan Pelanggan dapat login sesuai perannya.
3. Middleware mencegah akses yang tidak sesuai.
4. Admin dapat mengelola kategori, produk, harga, dan stok.
5. Pelanggan dapat mencari, melihat, dan memilih produk.
6. Keranjang dan checkout menghasilkan pesanan yang valid.
7. Pembayaran Midtrans Sandbox berjalan dan statusnya terverifikasi.
8. Tidak ada manipulasi harga maupun pembelian melebihi stok.
9. Admin dapat memproses dan memperbarui status pengiriman.
10. Pelanggan dapat melihat riwayat, detail, dan invoice pesanan.
11. Seluruh data dan relasi tersimpan dengan benar pada MySQL.
12. Tampilan dapat digunakan pada desktop maupun smartphone.
13. Fitur wajib telah diuji dan hasilnya didokumentasikan.
14. Repository GitHub berisi source code, migration, seeder, `.env.example`, README, serta riwayat commit pengembangan.
15. Laporan akhir dibuat sesuai format tugas besar pada umumnya dan sistem siap didemonstrasikan.

**Kesimpulan PRD**

PawMart merupakan sistem *e-commerce* Pet Shop yang dirancang untuk menangani seluruh proses penjualan kebutuhan hewan peliharaan, mulai dari pengelolaan katalog oleh Admin hingga penyelesaian transaksi oleh Pelanggan.

Pengembangan berfokus pada pemenuhan persyaratan tugas besar, integritas database, keamanan autentikasi, keandalan pembayaran Midtrans, serta tampilan website yang mudah digunakan.

Dokumen ini menjadi acuan utama dalam proses perancangan antarmuka, pembuatan database, pengembangan fitur, pengujian, dan penyelesaian proyek menggunakan Laravel 13.