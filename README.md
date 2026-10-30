# 🗑️ SIPARES — Sistem Pembayaran Retribusi Sampah

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

**SIPARES** adalah aplikasi web berbasis PHP untuk mengelola pembayaran retribusi sampah secara online. Sistem ini memungkinkan warga (pelanggan) membayar iuran sampah melalui berbagai metode pembayaran digital, sekaligus menyediakan panel admin untuk mengelola tagihan, pembayaran, dan pelaporan.

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Struktur Proyek](#-struktur-proyek)
- [Prasyarat](#-prasyarat)
- [Instalasi](#-instalasi)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Akun Default](#-akun-default)
- [Screenshot](#-screenshot)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

### Sisi Pengguna (Warga)
- **Pendaftaran & Login** — Registrasi mandiri dengan validasi NIK, email, dan nomor telepon
- **Dashboard Pelanggan** — Ringkasan tagihan, tunggakan, dan aksi cepat
- **Pembayaran Online** — Mendukung QRIS, transfer bank (BCA, BNI, Mandiri, BRI), dan minimarket
- **Riwayat Transaksi** — Pantau seluruh riwayat pembayaran dengan filter lengkap
- **Bukti Pembayaran Digital** — Unduh/cetak bukti pembayaran dalam format digital
- **Notifikasi Otomatis** — Pengingat tagihan dan konfirmasi pembayaran secara real-time
- **Profil Pelanggan** — Kelola data pribadi dan ubah password
- **Edukasi Sampah** — Artikel informatif seputar pengelolaan sampah (organik, anorganik, B3, residu, tips)

### Sisi Admin
- **Dashboard Admin** — Statistik pendapatan, jumlah pelanggan, dan tingkat kepatuhan
- **Manajemen Pengguna** — CRUD data pelanggan
- **Manajemen Tagihan** — Terbitkan dan kelola tagihan per periode
- **Verifikasi Pembayaran** — Konfirmasi pembayaran yang masuk
- **Laporan Keuangan** — Rekapitulasi pendapatan bulanan

### Keamanan
- Password di-hash menggunakan `bcrypt`
- Proteksi CSRF pada semua form
- Prepared Statement (PDO) untuk mencegah SQL Injection
- Session management dengan regenerasi ID

---

## 🛠️ Teknologi yang Digunakan

| Komponen       | Teknologi                                      |
|----------------|-------------------------------------------------|
| **Backend**    | PHP 8.x (Native OOP, PDO)                      |
| **Database**   | MySQL 8.x / MariaDB 10.x                       |
| **Frontend**   | HTML5, CSS3 (Custom CSS), JavaScript (Vanilla)  |
| **Font**       | Plus Jakarta Sans (Google Fonts)                |
| **Ikon**       | Font Awesome 6.4                                |
| **Web Server** | Apache (Laragon / XAMPP / MAMP)                 |

---

## 📂 Struktur Proyek

```
siparess/
├── admin/                  # Panel administrasi
│   ├── dashboard.php       # Dashboard admin
│   ├── laporan.php         # Laporan keuangan
│   ├── pembayaran.php      # Verifikasi pembayaran
│   ├── tagihan.php         # Manajemen tagihan
│   ├── users.php           # Manajemen pengguna
│   └── logout.php
├── api/                    # REST API endpoints
│   ├── admin.php           # API admin
│   ├── auth.php            # API autentikasi (login/register)
│   ├── notifikasi.php      # API notifikasi
│   ├── pembayaran.php      # API pembayaran
│   └── tagihan.php         # API tagihan
├── config/                 # Konfigurasi aplikasi
│   ├── database.php        # Koneksi database (PDO Singleton)
│   ├── init.php            # Autoloader & inisialisasi
│   └── session.php         # Session & CSRF management
├── css/                    # Stylesheet
│   ├── styles.css          # Styling utama & variabel
│   ├── components.css      # Komponen UI
│   ├── layout.css          # Layout grid & flexbox
│   ├── pages.css           # Styling per halaman
│   └── sections.css        # Styling per section
├── database/
│   └── sipares.sql         # Skema database + data awal
├── img/                    # Aset gambar
├── includes/               # Class & helper PHP
│   ├── Admin.php           # Model Admin
│   ├── functions.php       # Helper functions (rupiah, tanggal, dll.)
│   ├── Notifikasi.php      # Model Notifikasi
│   ├── Pembayaran.php      # Model Pembayaran
│   ├── Tagihan.php         # Model Tagihan
│   └── User.php            # Model User
├── js/
│   └── main.js             # JavaScript utama
├── uploads/                # File upload (bukti pembayaran)
├── user/                   # Halaman pengguna (setelah login)
│   ├── dashboard.php       # Dashboard pengguna
│   ├── edukasi.php         # Pusat edukasi
│   ├── profil.php          # Profil pengguna
│   ├── riwayat.php         # Riwayat pembayaran
│   ├── tagihan.php         # Lihat & bayar tagihan
│   ├── bukti_digital.php   # Cetak bukti digital
│   └── logout.php
├── index.php               # Landing page / entry point
├── index.html              # Landing page (HTML statis)
├── login.php               # Halaman login
├── register.php            # Halaman registrasi
├── edukasi.php             # Router halaman edukasi
├── edukasi.html            # Halaman edukasi (HTML statis)
└── README.md               # Dokumentasi proyek (file ini)
```

---

## 📦 Prasyarat

Pastikan perangkat lunak berikut telah terinstal di komputer Anda:

| Software           | Versi Minimum | Keterangan                          |
|--------------------|---------------|--------------------------------------|
| **PHP**            | 8.0+          | Dengan ekstensi `pdo_mysql`, `mbstring` |
| **MySQL/MariaDB**  | 5.7+ / 10.3+  | Sebagai database                     |
| **Apache**         | 2.4+          | Atau web server lain yang mendukung PHP |
| **Laragon / XAMPP**| Terbaru       | Sebagai paket lokal development      |
| **Git** *(opsional)* | 2.x+       | Untuk clone repository               |

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/siparess.git
```

Atau unduh sebagai ZIP dan ekstrak ke folder web server Anda.

### 2. Pindahkan ke Direktori Web Server

- **Laragon:** `C:\laragon\www\siparess`
- **XAMPP (Windows):** `C:\xampp\htdocs\siparess`
- **XAMPP (macOS):** `/Applications/XAMPP/htdocs/siparess`
- **MAMP:** `/Applications/MAMP/htdocs/siparess`

### 3. Buat Database

Buka **phpMyAdmin** atau MySQL CLI, lalu jalankan perintah berikut:

```sql
CREATE DATABASE sipares;
```

### 4. Impor Skema Database

Impor file SQL yang sudah disediakan:

**Via phpMyAdmin:**
1. Buka `http://localhost/phpmyadmin`
2. Pilih database `sipares`
3. Klik tab **Import**
4. Pilih file `database/sipares.sql`
5. Klik **Go / Kirim**

**Via CLI:**
```bash
mysql -u root -p sipares < database/sipares.sql
```

File SQL ini akan otomatis membuat:
- Semua tabel yang diperlukan (users, admin, tagihan, pembayaran, dll.)
- Data tarif retribusi default
- Akun admin default
- Data sampel pengguna dan tagihan

### 5. Konfigurasi Database

Buka file `config/database.php` dan sesuaikan pengaturan koneksi jika diperlukan:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sipares');
define('DB_USER', 'root');
define('DB_PASS', '');          // Sesuaikan dengan password MySQL Anda
define('DB_CHARSET', 'utf8mb4');
```

### 6. Pastikan Folder Upload Bisa Ditulis

```bash
chmod -R 755 uploads/
```

Pada Windows (Laragon), folder ini biasanya sudah memiliki izin yang cukup.

---

## ▶️ Menjalankan Aplikasi

### Menggunakan Laragon (Direkomendasikan)

1. Buka **Laragon**
2. Klik **Start All** untuk menjalankan Apache dan MySQL
3. Buka browser dan akses:

   ```
   http://localhost/siparess
   ```

### Menggunakan XAMPP

1. Buka **XAMPP Control Panel**
2. Klik **Start** pada modul **Apache** dan **MySQL**
3. Buka browser dan akses:

   ```
   http://localhost/siparess
   ```

### Menggunakan PHP Built-in Server

```bash
cd siparess
php -S localhost:8000
```

Lalu buka `http://localhost:8000` di browser.

---

## 🔑 Akun Default

Setelah mengimpor database, Anda dapat login menggunakan akun berikut:

### Admin
| Field     | Nilai                  |
|-----------|------------------------|
| Username  | `admin`                |
| Email     | `admin@sipares.go.id`  |
| Password  | `password`             |

### Pengguna (Warga)
| Field         | Nilai                   |
|---------------|-------------------------|
| Email         | `ahmad@email.com`       |
| No. Pelanggan | `SPR-2026-001234`       |
| Password      | `password`              |

| Field         | Nilai                   |
|---------------|-------------------------|
| Email         | `siti@email.com`        |
| No. Pelanggan | `SPR-2026-001235`       |
| Password      | `password`              |

> **⚠️ Penting:** Segera ganti password default setelah pertama kali login!

---

## 📸 Screenshot

| Halaman              | Deskripsi                                       |
|----------------------|-------------------------------------------------|
| Landing Page         | Halaman utama dengan informasi layanan & tarif   |
| Login                | Form login untuk pengguna dan admin              |
| Dashboard Pengguna   | Ringkasan tagihan, tunggakan, dan aksi cepat     |
| Pembayaran           | Pilih metode pembayaran (QRIS, transfer, dll.)   |
| Riwayat              | Daftar riwayat transaksi dengan filter           |
| Edukasi              | Artikel pengelolaan sampah (organik, B3, dll.)   |
| Dashboard Admin      | Statistik dan grafik pendapatan retribusi        |

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademis / pembelajaran.

© 2026 SIPARES. Hak Cipta Dilindungi.

#   s i p a r e s s  
 #   s i p a r e s s  
 