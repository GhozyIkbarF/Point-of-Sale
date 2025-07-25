-----

# Point of Sale (Mini POS)

Sistem aplikasi Point of Sale (POS) berbasis web menggunakan **Laravel** dan **Vue 3**. Aplikasi ini dirancang untuk kebutuhan kasir dan manajemen toko skala kecil-menengah, menawarkan solusi yang efisien dan modern.

## Fitur Utama

Aplikasi Mini POS ini dilengkapi dengan fitur-fitur esensial untuk mengelola penjualan dan operasional toko:

  * **Manajemen Penjualan**: Meliputi transaksi, detail transaksi, dan pelaporan penjualan.
  * **Manajemen Produk**: Memudahkan pengelolaan data produk Anda.
  * **Laporan Komprehensif**: Tersedia laporan penjualan harian, mingguan, bulanan, dan tahunan.
  * **Ekspor Data**: Laporan dapat diekspor ke format **Excel** atau **PDF**.
  * **Pencarian & Filter**: Fitur pencarian dan filter yang memudahkan penemuan data.
  * **Role-Based Access Control (RBAC)**: Sistem otorisasi berbasis peran untuk mengatur hak akses pengguna.
  * **Antarmuka Modern**: UI yang intuitif dan modern berkat penggunaan **Element Plus**.

## Peran Pengguna

Aplikasi ini mendukung dua jenis peran pengguna dengan hak akses yang berbeda:

  * **Admin**: Memiliki kontrol penuh atas sistem, termasuk mengelola produk, melihat seluruh transaksi, mengakses laporan lengkap, dan mengelola pengguna lain.
  * **Kasir**: Hanya dapat melakukan transaksi penjualan, melihat seluruh transaksi serta transaksi yang mereka lakukan, dan mengakses laporan harian pribadi mereka.

-----

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

-----

## Panduan Instalasi & Menjalankan Aplikasi

Ikuti langkah-langkah di bawah ini untuk menginstal dan menjalankan aplikasi di lingkungan lokal Anda:

### 1\. Clone Repository

```bash
git clone https://github.com/GhozyIkbarF/Point-of-Sale.git
cd Point-of-Sale
```

### 2\. Instal Dependensi

Instal semua dependensi PHP dan Node.js yang diperlukan:

```bash
composer install
npm install
```

### 3\. Konfigurasi Environment

Salin file contoh environment dan sesuaikan konfigurasi database, mail, dan lainnya di file `.env`:

```bash
cp .env.example .env
```

### 4\. Generate Key & Migrasi Database

Buat kunci aplikasi dan jalankan migrasi database beserta seeder untuk mengisi data awal:

```bash
php artisan key:generate
php artisan migrate --seed
```

### 5\. Jalankan Server

Mulai server pengembangan untuk Vue (Vite) dan Laravel:

```bash
npm run dev
php artisan serve
```

### 6\. Akses Login

Anda dapat mengakses aplikasi melalui browser. Gunakan kredensial default di bawah atau buat pengguna baru melalui fitur admin.

  * **Admin**:
      * Email: `admin@pos.test`
      * Password: `password`
  * **Kasir**:
      * Email: `kasir@pos.test` / `kasir1@pos.test` / `kasir2@pos.test`
      * Password: `password`

-----

## Laravel Sponsors

Kami mengucapkan terima kasih kepada para sponsor berikut yang telah mendanai pengembangan Laravel. Jika Anda tertarik untuk menjadi sponsor, silakan kunjungi [program Laravel Partners](https://partners.laravel.com).

### Premium Partners

  * **[Vehikl](https://vehikl.com)**
  * **[Tighten Co.](https://tighten.co)**
  * **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
  * **[64 Robots](https://64robots.com)**
  * **[Curotec](https://www.curotec.com/services/technologies/laravel)**
  * **[DevSquad](https://devsquad.com/hire-laravel-developers)**
  * **[Redberry](https://redberry.international/laravel-development)**
  * **[Active Logic](https://activelogic.com)**

-----

## Kontribusi

Terima kasih atas pertimbangan Anda untuk berkontribusi pada kerangka kerja Laravel\! Panduan kontribusi dapat ditemukan di [dokumentasi Laravel](https://laravel.com/docs/contributions).

-----

## Kode Etik

Untuk memastikan komunitas Laravel menyambut semua orang, harap tinjau dan patuhi [Kode Etik](https://laravel.com/docs/contributions#code-of-conduct).

-----

## Kerentanan Keamanan

Jika Anda menemukan kerentanan keamanan dalam Laravel, silakan kirim email ke Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). Semua kerentanan keamanan akan segera ditangani.

-----

## Lisensi

Kerangka kerja Laravel adalah perangkat lunak sumber terbuka yang dilisensikan di bawah [lisensi MIT](https://opensource.org/licenses/MIT).
