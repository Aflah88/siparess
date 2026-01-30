-- ========================================
-- DATABASE SIPARES
-- Sistem Pembayaran Retribusi Sampah
-- ========================================

-- Buat database
CREATE DATABASE IF NOT EXISTS sipares;
USE sipares;

-- ========================================
-- 1. TABEL USERS (Pengguna/Warga)
-- ========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_pelanggan VARCHAR(20) UNIQUE NOT NULL,
    nik VARCHAR(16) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telepon VARCHAR(15),
    alamat TEXT,
    rt VARCHAR(3),
    rw VARCHAR(3),
    kelurahan VARCHAR(50),
    kecamatan VARCHAR(50),
    kategori ENUM('rumah_tangga', 'usaha', 'industri') DEFAULT 'rumah_tangga',
    foto_profil VARCHAR(255) DEFAULT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- 2. TABEL ADMIN (Administrator)
-- ========================================
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'operator') DEFAULT 'operator',
    foto_profil VARCHAR(255) DEFAULT NULL,
    last_login TIMESTAMP NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- 3. TABEL TARIF RETRIBUSI
-- ========================================
CREATE TABLE tarif (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori ENUM('rumah_tangga', 'usaha', 'industri') NOT NULL,
    nama_tarif VARCHAR(100) NOT NULL,
    nominal DECIMAL(10,2) NOT NULL,
    deskripsi TEXT,
    frekuensi_angkut VARCHAR(50),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- 4. TABEL TAGIHAN
-- ========================================
CREATE TABLE tagihan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    no_tagihan VARCHAR(30) UNIQUE NOT NULL,
    periode_bulan INT NOT NULL,
    periode_tahun INT NOT NULL,
    tarif_id INT NOT NULL,
    nominal_tagihan DECIMAL(10,2) NOT NULL,
    biaya_admin DECIMAL(10,2) DEFAULT 5000,
    denda DECIMAL(10,2) DEFAULT 0,
    total_tagihan DECIMAL(10,2) NOT NULL,
    tanggal_terbit DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    status ENUM('belum_bayar', 'pending', 'lunas', 'terlambat') DEFAULT 'belum_bayar',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tarif_id) REFERENCES tarif(id)
);

-- ========================================
-- 5. TABEL PEMBAYARAN
-- ========================================
CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tagihan_id INT NOT NULL,
    user_id INT NOT NULL,
    no_transaksi VARCHAR(30) UNIQUE NOT NULL,
    metode_pembayaran ENUM('qris', 'transfer_bca', 'transfer_bni', 'transfer_mandiri', 'transfer_bri', 'minimarket') NOT NULL,
    nominal_bayar DECIMAL(10,2) NOT NULL,
    bukti_pembayaran VARCHAR(255) DEFAULT NULL,
    tanggal_bayar TIMESTAMP NULL,
    status ENUM('pending', 'berhasil', 'gagal', 'expired') DEFAULT 'pending',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tagihan_id) REFERENCES tagihan(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ========================================
-- 6. TABEL NOTIFIKASI
-- ========================================
CREATE TABLE notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(100) NOT NULL,
    pesan TEXT NOT NULL,
    tipe ENUM('tagihan', 'pembayaran', 'info', 'warning') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- 7. TABEL ARTIKEL EDUKASI
-- ========================================
CREATE TABLE artikel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    konten TEXT NOT NULL,
    ringkasan TEXT,
    gambar VARCHAR(255) DEFAULT NULL,
    kategori ENUM('organik', 'anorganik', 'b3', 'residu', 'tips', 'umum') DEFAULT 'umum',
    author_id INT,
    views INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES admin(id)
);

-- ========================================
-- 8. TABEL LOG AKTIVITAS
-- ========================================
CREATE TABLE log_aktivitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    aksi VARCHAR(100) NOT NULL,
    tabel_terkait VARCHAR(50),
    data_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- 9. TABEL PENGATURAN
-- ========================================
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kunci VARCHAR(50) UNIQUE NOT NULL,
    nilai TEXT,
    deskripsi VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- INSERT DATA AWAL
-- ========================================

-- Insert Tarif Default
INSERT INTO tarif (kategori, nama_tarif, nominal, deskripsi, frekuensi_angkut) VALUES
('rumah_tangga', 'Rumah Tangga', 25000.00, 'Tarif retribusi untuk rumah tangga', '2x seminggu'),
('usaha', 'Usaha/Toko', 50000.00, 'Tarif retribusi untuk usaha dan toko', '3x seminggu'),
('industri', 'Industri', 150000.00, 'Tarif retribusi untuk industri', 'Setiap hari');

-- Insert Admin Default (password: admin123)
INSERT INTO admin (username, email, password, nama_lengkap, role) VALUES
('admin', 'admin@sipares.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'super_admin');

-- Insert Pengaturan Default
INSERT INTO pengaturan (kunci, nilai, deskripsi) VALUES
('nama_aplikasi', 'SIPARES', 'Nama aplikasi'),
('tagline', 'Sistem Pembayaran Retribusi Sampah', 'Tagline aplikasi'),
('email_admin', 'admin@sipares.go.id', 'Email administrator'),
('no_telepon', '(021) 1234-5678', 'Nomor telepon kantor'),
('alamat', 'Jl. Lingkungan Hijau No. 123, Kota Bersih', 'Alamat kantor'),
('denda_persen', '2', 'Persentase denda keterlambatan per bulan'),
('tanggal_jatuh_tempo', '20', 'Tanggal jatuh tempo setiap bulan');

-- Insert Sample Users (password: password123)
INSERT INTO users (no_pelanggan, nik, nama_lengkap, email, password, no_telepon, alamat, rt, rw, kelurahan, kecamatan, kategori) VALUES
('SPR-2026-001234', '3201234567890001', 'Ahmad Susanto', 'ahmad@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'Jl. Merdeka No. 45', '03', '05', 'Kelurahan Merdeka', 'Kecamatan Bersih', 'rumah_tangga'),
('SPR-2026-001235', '3201234567890002', 'Siti Rahayu', 'siti@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567891', 'Jl. Mawar No. 12', '02', '04', 'Kelurahan Mawar', 'Kecamatan Bersih', 'rumah_tangga'),
('SPR-2026-001567', '3201234567890003', 'Toko Makmur Jaya', 'tokomakmur@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567892', 'Jl. Pasar No. 88', '01', '01', 'Kelurahan Pasar', 'Kecamatan Bersih', 'usaha');

-- Insert Sample Tagihan
INSERT INTO tagihan (user_id, no_tagihan, periode_bulan, periode_tahun, tarif_id, nominal_tagihan, biaya_admin, denda, total_tagihan, tanggal_terbit, tanggal_jatuh_tempo, status) VALUES
(1, 'TGH-202601-001234', 1, 2026, 1, 25000.00, 5000.00, 0, 30000.00, '2026-01-01', '2026-01-20', 'belum_bayar'),
(1, 'TGH-202512-001234', 12, 2025, 1, 25000.00, 5000.00, 0, 30000.00, '2025-12-01', '2025-12-20', 'lunas'),
(1, 'TGH-202511-001234', 11, 2025, 1, 25000.00, 5000.00, 0, 30000.00, '2025-11-01', '2025-11-20', 'lunas'),
(2, 'TGH-202601-001235', 1, 2026, 1, 25000.00, 5000.00, 0, 30000.00, '2026-01-01', '2026-01-20', 'belum_bayar'),
(3, 'TGH-202601-001567', 1, 2026, 2, 50000.00, 5000.00, 0, 55000.00, '2026-01-01', '2026-01-20', 'belum_bayar');

-- Insert Sample Pembayaran
INSERT INTO pembayaran (tagihan_id, user_id, no_transaksi, metode_pembayaran, nominal_bayar, tanggal_bayar, status) VALUES
(2, 1, 'TRX-2025120023', 'qris', 30000.00, '2025-12-15 14:32:00', 'berhasil'),
(3, 1, 'TRX-2025110019', 'transfer_bca', 30000.00, '2025-11-12 09:15:00', 'berhasil');

-- Insert Sample Notifikasi
INSERT INTO notifikasi (user_id, judul, pesan, tipe, is_read) VALUES
(1, 'Tagihan Bulan Januari 2026', 'Tagihan retribusi sampah Anda sebesar Rp 30.000 telah terbit.', 'tagihan', 0),
(1, 'Pembayaran Berhasil', 'Pembayaran bulan Desember 2025 telah dikonfirmasi.', 'pembayaran', 0),
(1, 'Tips Pengelolaan Sampah', 'Pelajari cara memilah sampah organik dan anorganik dengan benar.', 'info', 0);

-- Insert Sample Artikel
INSERT INTO artikel (judul, slug, konten, ringkasan, kategori, author_id, is_published, published_at) VALUES
('Mengenal Sampah Organik dan Cara Pengolahannya', 'mengenal-sampah-organik', 'Sampah organik adalah sampah yang berasal dari makhluk hidup...', 'Pelajari tentang sampah organik dan bagaimana cara mengolahnya menjadi kompos.', 'organik', 1, 1, NOW()),
('Bahaya Sampah Plastik bagi Lingkungan', 'bahaya-sampah-plastik', 'Sampah plastik membutuhkan ratusan tahun untuk terurai...', 'Ketahui dampak negatif sampah plastik dan langkah yang bisa kita lakukan.', 'anorganik', 1, 1, NOW()),
('Cara Mengelola Sampah B3 di Rumah', 'mengelola-sampah-b3', 'Sampah B3 atau Bahan Berbahaya dan Beracun...', 'Panduan aman untuk menangani sampah berbahaya di rumah tangga.', 'b3', 1, 1, NOW());

-- ========================================
-- VIEWS UNTUK REPORTING
-- ========================================

-- View Ringkasan Tagihan per User
CREATE VIEW v_ringkasan_tagihan AS
SELECT
    u.id as user_id,
    u.no_pelanggan,
    u.nama_lengkap,
    u.kategori,
    COUNT(t.id) as total_tagihan,
    SUM(CASE WHEN t.status = 'lunas' THEN 1 ELSE 0 END) as tagihan_lunas,
    SUM(CASE WHEN t.status != 'lunas' THEN 1 ELSE 0 END) as tagihan_belum_lunas,
    SUM(CASE WHEN t.status != 'lunas' THEN t.total_tagihan ELSE 0 END) as total_tunggakan
FROM users u
LEFT JOIN tagihan t ON u.id = t.user_id
GROUP BY u.id;

-- View Laporan Pembayaran Bulanan
CREATE VIEW v_laporan_bulanan AS
SELECT
    YEAR(p.tanggal_bayar) as tahun,
    MONTH(p.tanggal_bayar) as bulan,
    COUNT(p.id) as jumlah_transaksi,
    SUM(p.nominal_bayar) as total_pendapatan,
    SUM(CASE WHEN p.metode_pembayaran = 'qris' THEN 1 ELSE 0 END) as via_qris,
    SUM(CASE WHEN p.metode_pembayaran LIKE 'transfer%' THEN 1 ELSE 0 END) as via_transfer,
    SUM(CASE WHEN p.metode_pembayaran = 'minimarket' THEN 1 ELSE 0 END) as via_minimarket
FROM pembayaran p
WHERE p.status = 'berhasil'
GROUP BY YEAR(p.tanggal_bayar), MONTH(p.tanggal_bayar);
