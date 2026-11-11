# Website Warung Makan Fina Berry (Laravel)

Website responsif untuk Warung Makan Fina Berry, mengikuti **Usulan Teknis (Ustek)** Kelompok 1. Satu aplikasi Laravel (monolith modular, pola MVC, Blade) yang berisi situs publik dan dashboard admin. Data menu, harga, dan kontak awal diambil dari SRS/proyek lama, sedangkan alur dan sistemnya mengikuti Ustek.

## Cara menjalankan (Windows + Laragon)

Syarat: PHP 8.3+, Composer, MySQL (bawaan Laragon). Ekstensi PHP `gd` (dengan WebP), `mbstring`, `pdo_mysql` aktif.

```bash
composer install
copy .env.example .env          # Linux/Mac: cp .env.example .env
php artisan key:generate
# buat database kosong bernama fina_berry (HeidiSQL/phpMyAdmin), sesuaikan DB_* di .env
php artisan migrate --seed
php artisan storage:link
php artisan serve               # buka http://localhost:8000
```

Data demo untuk melihat laporan dan grafik (opsional, jangan di produksi):

```bash
php artisan db:seed --class=DemoDataSeeder
```

Tes otomatis: `php artisan test`.

## Akun awal (hasil seeder)

Password awal diambil dari `SEED_DEFAULT_PASSWORD` di `.env` (bawaan `GantiSaya#2026`). **Ganti setelah login pertama** lewat menu Akun (klik nama di pojok kanan atas).

| Peran | Email | Akses |
|---|---|---|
| Super Admin (bos/manajer) | bos@finaberry.test | Semua fitur + laba rugi, margin, analitik |
| Pemilik | pemilik@finaberry.test | Kelola menu/promo/galeri/pengaturan/akun staf, laporan penjualan, biaya |
| Staf | staf@finaberry.test | Pesanan, validasi pembayaran, input di warung, tandai menu habis |

Login admin: `/admin/login`.

## Alur pemesanan (sesuai Ustek)

1. Pelanggan memilih menu, mengisi keranjang, lalu checkout (nama, WhatsApp, ambil/antar, catatan, persetujuan privasi).
2. **Simpan dulu, kirim kemudian:** server memvalidasi, menghitung ulang total dari harga terkini, menyimpan pesanan berstatus *Menunggu* (kode `FB-YYYYMMDD-NNN`), baru membuka WhatsApp (`wa.me`) dengan teks pesanan standar. Halaman konfirmasi punya tombol *Buka WhatsApp* dan *Salin pesan* bila WhatsApp tidak terbuka.
3. Staf melihat pesanan di dashboard (penanda pesanan baru, penyegaran otomatis), menghubungi pelanggan lewat WhatsApp, lalu: **Proses → Validasi pembayaran (offline) → Selesai**. Pembatalan wajib beralasan.
4. Pesanan di warung (offline) diinput lewat menu *Input di warung*; langsung berstatus *Selesai* dan *lunas*.
5. Semua pesanan (online dan offline) masuk satu tabel `orders`, sehingga laporan dan analitik dihitung dari satu sumber.

**Pembayaran hanya offline:** sistem tidak memproses pembayaran. Staf hanya memvalidasi bahwa uang sudah diterima (metode tunai/transfer/QRIS) untuk rekap riwayat transaksi. Pesanan tidak bisa *Selesai* sebelum pembayaran divalidasi.

**Pendapatan** = jumlah total pesanan berstatus *Selesai* dengan pembayaran tervalidasi, semua kanal. Pesanan *Dibatalkan* tidak dihitung. Periode laporan memakai tanggal pesanan dibuat.

## Peran dan hak akses

Akses ditolak secara default (middleware `role`), dan diuji di `tests/Feature/AccessControlTest.php`.

- **Staf:** pesanan, validasi pembayaran, input di warung, ubah status ketersediaan menu. Tidak bisa melihat laporan, mengubah harga, atau membuka pengaturan.
- **Pemilik:** semua milik Staf + kelola menu (harga, HPP, foto), kategori, promo, galeri, pengaturan toko, QR Code, akun Staf, biaya operasional, laporan penjualan (cetak dan CSV), log aktivitas, koreksi pesanan *Selesai*.
- **Super Admin (tambahan di luar Ustek):** semua milik Pemilik + **laba rugi, margin kotor/bersih, laporan analitik** (tren harian, jam dan hari ramai, kontribusi laba per menu, pembanding periode sebelumnya), cetak dan CSV, serta kelola semua akun.

## Pemetaan ke Ustek

| Ustek | Di aplikasi |
|---|---|
| FR-01 s.d. FR-05 | `/`, `/menu`, `/galeri`, `/promo`, `/kontak`, tombol WhatsApp melayang |
| FR-06 s.d. FR-10 | `/keranjang`, `/checkout`, `/pesanan/{token}` (CheckoutController, OrderService) |
| FR-11 | `/admin/login`, tiga peran |
| FR-12, FR-13 | Menu (nonaktif, bukan hapus, bila pernah dipesan), Promo, Galeri |
| FR-14, FR-15 | `/admin/pesanan` + penanda baru, status dengan riwayat |
| FR-16 | `/admin/kasir` |
| FR-17 | `/admin/laporan` |
| FR-18, FR-19 | Meta SEO per halaman, `sitemap.xml`, JSON-LD, `/admin/qr` |
| FR-20 | `/admin/pengaturan` (WA, jam buka, sosmed, nomor halal) |
| FR-21 | Cetak laporan (Cetak/Simpan PDF dari browser) dan CSV, untuk laporan dan analitik |
| FR-22 | Log aktivitas (`audit_logs`, halaman `/admin/log`) |
| NFR-05 | bcrypt, CSRF, validasi server, ORM berparameter, throttle login, header keamanan, upload divalidasi dan diacak namanya |
| NFR-07 | Total dihitung ulang di server, snapshot harga dan HPP di `order_items`, uang sebagai integer rupiah |
| E-01 s.d. E-09 | Ditangani (lihat tes) |

## Yang saya tambahkan di luar Ustek (perlu disetujui kelompok/dosen)

- Peran **Super Admin** dan halaman laba rugi/analitik (permintaan khusus).
- Kolom **HPP** (`products.cost_price`, di-snapshot ke `order_items.unit_cost`) dan tabel **`expenses`** (biaya operasional) supaya laba kotor/bersih bisa dihitung. Ustek tidak punya keduanya.
- **Validasi pembayaran offline** (`payment_status`, `paid_at`, `paid_validated_by`) dan aturan *Selesai* hanya setelah lunas.
- Ekspor dan cetak laporan dibuat sejak awal (di Ustek ini FR-21, Fase 2) karena diminta.

## Hal yang harus diisi/diganti sebelum dipakai nyata

- **HPP di seeder hanyalah perkiraan sementara (±55% harga jual)** agar demo laba rugi bisa tampil. Isi HPP asli lewat Admin → Menu, jangan dipakai sebagai angka nyata.
- Jam buka (`08.00 - 17.00 WIB`), alamat, dan nomor WhatsApp diambil dari proyek lama. Konfirmasi ke pemilik (Lampiran A Ustek) lalu ubah di Admin → Pengaturan toko.
- Foto menu dicocokkan dari nama berkas proyek lama. Periksa dan ganti yang kurang pas lewat Admin → Menu. Menu tanpa foto tampil dengan ikon piring.
- Nomor sertifikat halal kosong; label halal baru tampil jika diisi.
- Produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` domain asli (dipakai QR Code dan sitemap), HTTPS, `.env` di luar folder publik, ganti semua password awal.

## Belum termasuk (ada di Ustek, di luar cakupan kode ini)

Docker/Nginx, GitHub Actions CI, backup terjadwal, pemantau uptime, verifikasi dua langkah, WhatsApp Business API, pembayaran online (Fase 3), dan penghapusan/anonimisasi data pelanggan otomatis (FR-23).

## Struktur singkat

```
app/Http/Controllers/        publik: Home, Cart, Checkout | Admin/: Order, Pos, Product, Report, Analytics, ...
app/Services/                OrderService (aturan status/pembayaran/WA), CartService, ReportService
app/Models/                  User, Category, Product, Order, OrderItem, OrderStatusLog, Promo, GalleryPhoto, Setting, Expense, AuditLog
database/migrations/         5 berkas (users, katalog, pesanan, konten, keuangan+audit)
resources/views/             layouts/, public/, admin/, partials/, errors/
routes/web.php               seluruh rute + pembagian peran
tests/                       unit dan feature test (checkout, otorisasi, alur pesanan, laporan)
```
