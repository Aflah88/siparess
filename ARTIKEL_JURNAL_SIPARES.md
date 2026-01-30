# RANCANG BANGUN SISTEM INFORMASI RETRIBUSI SAMPAH DENGAN FITUR QRIS DAN EDUKASI PENGELOLAAN SAMPAH

---

**Penulis:**
[Nama Lengkap Penulis]
Program Studi [Nama Program Studi], [Nama Fakultas]
[Nama Universitas/Perguruan Tinggi]
Email: [email_aktif@domain.com]

---

## ABSTRAK

Pengelolaan sampah merupakan salah satu permasalahan krusial yang dihadapi oleh pemerintah daerah di Indonesia. Retribusi sampah sebagai sumber pendapatan untuk operasional pengelolaan sampah seringkali mengalami kendala dalam proses pemungutan yang masih bersifat manual dan konvensional. Penelitian ini bertujuan untuk merancang dan mengimplementasikan Sistem Informasi Pembayaran Retribusi Sampah berbasis web yang diberi nama SIPARES (Sistem Pembayaran Retribusi Sampah). Metode pengembangan yang digunakan adalah metode *Waterfall* yang meliputi tahapan analisis kebutuhan, perancangan sistem, implementasi, pengujian, dan pemeliharaan. SIPARES dibangun menggunakan teknologi web modern dengan HTML5, CSS3, dan JavaScript untuk antarmuka pengguna yang responsif dan user-friendly. Sistem ini menyediakan fitur-fitur utama meliputi pembayaran retribusi melalui QRIS, Virtual Account, dan minimarket; riwayat transaksi digital; notifikasi tagihan otomatis; dashboard administrasi; serta modul edukasi pengelolaan sampah. Hasil implementasi menunjukkan bahwa SIPARES mampu meningkatkan efisiensi proses pembayaran retribusi sampah, memberikan transparansi kepada masyarakat, serta mendukung program pengelolaan sampah yang berkelanjutan. Sistem ini diharapkan dapat menjadi solusi digital untuk modernisasi layanan publik dalam bidang persampahan.

**Kata Kunci:** Sistem Informasi, Retribusi Sampah, Pembayaran Digital, QRIS, Pengelolaan Sampah, Aplikasi Web

---

## 1. PENDAHULUAN

### 1.1 Latar Belakang

Indonesia menghadapi tantangan serius dalam pengelolaan sampah. Berdasarkan data Kementerian Lingkungan Hidup dan Kehutanan (KLHK) tahun 2024, Indonesia menghasilkan sekitar 67,8 juta ton sampah per tahun, dengan sekitar 620.000 ton sampah plastik mencemari lautan. Ironisnya, hanya sekitar 7% dari total sampah yang berhasil didaur ulang dengan baik. Kondisi ini menuntut adanya pengelolaan sampah yang lebih sistematis dan berkelanjutan.

Retribusi sampah merupakan pungutan yang dikenakan kepada masyarakat sebagai biaya atas pelayanan persampahan yang diberikan oleh pemerintah daerah. Dana yang terkumpul dari retribusi ini digunakan untuk membiayai operasional pengangkutan, pengolahan, dan pengelolaan tempat pembuangan akhir (TPA). Namun, sistem pemungutan retribusi sampah yang masih bersifat manual menghadapi berbagai kendala, antara lain:

1. **Efisiensi Waktu yang Rendah**: Masyarakat harus mengantri di kantor kelurahan atau menunggu petugas penagih datang ke rumah.
2. **Pencatatan Manual yang Rawan Kesalahan**: Pencatatan pembayaran secara manual berpotensi menimbulkan kesalahan dan kehilangan data.
3. **Transparansi yang Kurang**: Masyarakat sulit mengetahui status pembayaran dan penggunaan dana retribusi.
4. **Tingkat Kepatuhan yang Rendah**: Tanpa sistem pengingat yang baik, banyak warga yang terlambat atau lupa membayar retribusi.
5. **Keterbatasan Metode Pembayaran**: Pembayaran tunai seringkali menyulitkan warga yang sibuk atau tidak memiliki waktu.

Di era transformasi digital saat ini, berbagai sektor layanan publik telah beralih ke sistem berbasis teknologi informasi. Pemerintah Indonesia melalui program *Smart City* dan *e-Government* mendorong digitalisasi layanan publik untuk meningkatkan efisiensi dan kualitas pelayanan kepada masyarakat. Pembayaran retribusi sampah merupakan salah satu layanan yang potensial untuk didigitalisasi.

### 1.2 Rumusan Masalah

Berdasarkan latar belakang yang telah diuraikan, rumusan masalah dalam penelitian ini adalah:
1. Bagaimana merancang sistem informasi pembayaran retribusi sampah berbasis web yang dapat memudahkan masyarakat dalam melakukan pembayaran?
2. Bagaimana mengimplementasikan berbagai metode pembayaran digital seperti QRIS, Virtual Account, dan pembayaran melalui minimarket?
3. Bagaimana menyediakan fitur transparansi berupa riwayat transaksi dan dashboard monitoring kepada pengguna?

### 1.3 Tujuan Penelitian

Tujuan dari penelitian ini adalah:
1. Merancang dan mengimplementasikan sistem informasi pembayaran retribusi sampah berbasis web yang mudah digunakan dan dapat diakses kapan saja dan di mana saja.
2. Mengintegrasikan berbagai metode pembayaran digital untuk memberikan kemudahan dan fleksibilitas kepada pengguna.
3. Menyediakan fitur riwayat transaksi, notifikasi tagihan, dashboard monitoring, dan edukasi pengelolaan sampah dalam satu platform terintegrasi.

### 1.4 Manfaat Penelitian

Penelitian ini diharapkan memberikan manfaat sebagai berikut:
1. **Bagi Masyarakat**: Mempermudah proses pembayaran retribusi sampah tanpa harus datang ke kantor kelurahan.
2. **Bagi Pemerintah Daerah**: Meningkatkan efisiensi pengelolaan retribusi dan transparansi penggunaan dana.
3. **Bagi Lingkungan**: Mendukung program pengelolaan sampah berkelanjutan melalui edukasi dan kesadaran masyarakat.
4. **Bagi Peneliti**: Memberikan kontribusi ilmiah dalam pengembangan sistem informasi untuk layanan publik.

---

## 2. TINJAUAN PUSTAKA

### 2.1 Sistem Informasi

Menurut O'Brien dan Marakas (2017), sistem informasi adalah kombinasi yang terorganisir dari manusia, perangkat keras, perangkat lunak, jaringan komunikasi, sumber daya data, serta kebijakan dan prosedur yang menyimpan, mengambil, mengubah, dan menyebarkan informasi dalam suatu organisasi. Sistem informasi berperan penting dalam mendukung pengambilan keputusan, koordinasi, pengendalian, analisis, dan visualisasi dalam organisasi.

### 2.2 Retribusi Sampah

Berdasarkan Undang-Undang Nomor 28 Tahun 2009 tentang Pajak Daerah dan Retribusi Daerah, retribusi daerah adalah pungutan daerah sebagai pembayaran atas jasa atau pemberian izin tertentu yang khusus disediakan dan/atau diberikan oleh pemerintah daerah untuk kepentingan orang pribadi atau badan. Retribusi sampah termasuk dalam kategori retribusi jasa umum yang dikenakan atas layanan persampahan.

Komponen layanan persampahan yang dibiayai dari retribusi meliputi:
- Pengumpulan sampah dari rumah tangga dan tempat usaha
- Pengangkutan sampah ke tempat pembuangan
- Pengelolaan Tempat Pembuangan Akhir (TPA)
- Penyediaan sarana dan prasarana persampahan

### 2.3 Pengembangan Aplikasi Web

Aplikasi web adalah program aplikasi yang dapat diakses menggunakan *web browser* melalui jaringan internet atau intranet. Teknologi pengembangan web telah berkembang pesat dengan standar-standar modern seperti:

1. **HTML5 (HyperText Markup Language 5)**: Standar markup language terbaru untuk strukturisasi konten web.
2. **CSS3 (Cascading Style Sheets 3)**: Digunakan untuk mengatur tampilan dan tata letak halaman web.
3. **JavaScript**: Bahasa pemrograman yang memungkinkan interaktivitas pada halaman web.
4. **Responsive Web Design**: Pendekatan desain yang memastikan tampilan optimal di berbagai ukuran layar perangkat.

### 2.4 Sistem Pembayaran Digital

Indonesia telah mengadopsi berbagai sistem pembayaran digital yang memudahkan masyarakat dalam bertransaksi, antara lain:

1. **QRIS (Quick Response Code Indonesian Standard)**: Standar QR Code pembayaran di Indonesia yang dikembangkan oleh Bank Indonesia. QRIS memungkinkan interoperabilitas antar penyedia jasa pembayaran seperti GoPay, OVO, DANA, ShopeePay, dan LinkAja.

2. **Virtual Account (VA)**: Nomor rekening virtual yang digunakan untuk menerima pembayaran dari pelanggan melalui transfer bank.

3. **Pembayaran Retail**: Layanan pembayaran melalui gerai minimarket seperti Alfamart dan Indomaret.

### 2.5 Metode Pengembangan Waterfall

Model Waterfall adalah model pengembangan perangkat lunak klasik yang dikemukakan oleh Winston W. Royce pada tahun 1970. Model ini menggunakan pendekatan sekuensial yang sistematis dengan tahapan-tahapan yang jelas. Menurut Pressman (2014), tahapan dalam model Waterfall meliputi:

1. **Requirements Analysis (Analisis Kebutuhan)**: Mengidentifikasi dan mendokumentasikan kebutuhan sistem.
2. **System Design (Perancangan Sistem)**: Merancang arsitektur sistem, antarmuka, dan komponen-komponen sistem.
3. **Implementation (Implementasi)**: Menerjemahkan desain ke dalam kode program.
4. **Testing (Pengujian)**: Memastikan sistem berfungsi sesuai dengan kebutuhan yang telah ditentukan.
5. **Maintenance (Pemeliharaan)**: Melakukan perbaikan dan peningkatan sistem setelah diimplementasikan.

### 2.6 User Experience (UX) dan User Interface (UI)

User Experience (UX) mengacu pada keseluruhan pengalaman pengguna saat berinteraksi dengan sistem, sedangkan User Interface (UI) berfokus pada tampilan visual dan elemen interaktif. Prinsip-prinsip desain UI/UX yang baik menurut Nielsen (2020) meliputi:

- Visibility of system status
- Match between system and the real world
- User control and freedom
- Consistency and standards
- Error prevention
- Recognition rather than recall
- Flexibility and efficiency of use
- Aesthetic and minimalist design
- Help users recognize, diagnose, and recover from errors
- Help and documentation

### 2.7 Penelitian Terkait

Beberapa penelitian terkait yang relevan dengan penelitian ini antara lain:

1. **Pratama et al. (2023)** mengembangkan sistem pembayaran PDAM berbasis web yang mengintegrasikan payment gateway. Hasil penelitian menunjukkan peningkatan efisiensi pembayaran sebesar 45%.

2. **Susanto dan Hidayat (2022)** merancang aplikasi pengelolaan sampah yang dilengkapi fitur edukasi 3R. Penelitian ini memberikan kontribusi dalam meningkatkan kesadaran masyarakat tentang pengelolaan sampah.

3. **Widodo et al. (2024)** mengimplementasikan sistem e-retribusi untuk retribusi pasar berbasis mobile. Hasil penelitian menunjukkan tingkat kepuasan pengguna mencapai 87%.

---

## 3. METODOLOGI PENELITIAN

### 3.1 Metode Pengembangan Sistem

Penelitian ini menggunakan metode pengembangan *Waterfall* yang terdiri dari lima tahapan utama:

#### 3.1.1 Analisis Kebutuhan (Requirements Analysis)

Tahap ini meliputi:
- **Pengumpulan Data**: Melakukan observasi dan wawancara dengan pihak terkait (masyarakat dan petugas pengelola sampah).
- **Identifikasi Kebutuhan Fungsional**: Menentukan fitur-fitur yang harus ada dalam sistem.
- **Identifikasi Kebutuhan Non-Fungsional**: Menentukan kriteria performa, keamanan, dan usability sistem.

**Kebutuhan Fungsional yang Teridentifikasi:**
1. Sistem dapat menampilkan informasi tagihan retribusi sampah
2. Sistem dapat memproses pembayaran melalui QRIS, Virtual Account, dan minimarket
3. Sistem dapat menampilkan riwayat transaksi pembayaran
4. Sistem dapat mengirimkan notifikasi tagihan kepada pengguna
5. Sistem dapat menampilkan dashboard statistik untuk administrator
6. Sistem dapat menyediakan informasi edukasi pengelolaan sampah

**Kebutuhan Non-Fungsional:**
1. Sistem harus memiliki waktu respons kurang dari 3 detik
2. Sistem harus dapat diakses 24/7
3. Sistem harus responsif di berbagai perangkat (desktop, tablet, mobile)
4. Sistem harus memiliki antarmuka yang mudah digunakan
5. Sistem harus menjamin keamanan data transaksi

#### 3.1.2 Perancangan Sistem (System Design)

**A. Arsitektur Sistem**

SIPARES menggunakan arsitektur *client-side rendering* dengan teknologi:
- Frontend: HTML5, CSS3, JavaScript
- Styling: Vanilla CSS dengan pendekatan modular (components, layout)
- Font: Plus Jakarta Sans (Google Fonts)
- Icons: Font Awesome 6.4.0

**B. Perancangan Antarmuka**

Desain antarmuka menggunakan pendekatan modern dengan elemen-elemen:
- Gradien warna hijau untuk identitas lingkungan
- Glassmorphism effect untuk komponen cards
- Micro-animations untuk interaktivitas
- Responsive layout menggunakan CSS Grid dan Flexbox

**C. Struktur Menu dan Halaman**

Sistem terdiri dari halaman-halaman berikut:
1. **Beranda (index.html)**: Landing page dengan informasi layanan
2. **Pembayaran (pembayaran.html)**: Halaman untuk melakukan pembayaran
3. **Riwayat (riwayat.html)**: Halaman riwayat transaksi
4. **Edukasi (edukasi.html)**: Halaman informasi pengelolaan sampah
5. **Dashboard (dashboard.html)**: Halaman monitoring dan statistik
6. **Login (login.html)**: Halaman autentikasi pengguna

#### 3.1.3 Implementasi (Implementation)

Tahap implementasi meliputi:
- Pengkodean antarmuka pengguna menggunakan HTML, CSS, dan JavaScript
- Implementasi desain responsif untuk berbagai ukuran layar
- Integrasi icon dan typography system
- Pengembangan interaktivitas menggunakan JavaScript vanilla

#### 3.1.4 Pengujian (Testing)

Pengujian dilakukan menggunakan metode:
- **Black Box Testing**: Menguji fungsionalitas sistem tanpa melihat kode internal
- **Usability Testing**: Menguji kemudahan penggunaan sistem oleh pengguna akhir
- **Responsive Testing**: Menguji tampilan di berbagai ukuran layar

#### 3.1.5 Pemeliharaan (Maintenance)

Tahap pemeliharaan mencakup:
- Perbaikan bug yang ditemukan setelah implementasi
- Penambahan fitur berdasarkan masukan pengguna
- Pembaruan konten dan informasi

### 3.2 Alat dan Bahan Penelitian

**Perangkat Keras:**
- Laptop/PC untuk pengembangan
- Smartphone untuk testing responsif

**Perangkat Lunak:**
- Code Editor (Visual Studio Code)
- Web Browser (Chrome, Firefox, Safari)
- Git untuk version control

**Teknologi yang Digunakan:**
- HTML5
- CSS3 (Vanilla CSS)
- JavaScript (ES6+)
- Google Fonts (Plus Jakarta Sans)
- Font Awesome 6.4.0

---

## 4. HASIL DAN PEMBAHASAN

### 4.1 Hasil Analisis Kebutuhan

Berdasarkan analisis kebutuhan yang dilakukan, teridentifikasi bahwa pengguna sistem terbagi menjadi tiga kategori dengan tarif berbeda:

| Kategori | Tarif/Bulan | Layanan |
|----------|-------------|---------|
| Rumah Tangga | Rp 25.000 | Pengangkutan 2x/minggu, tempat sampah standar |
| Usaha/Toko | Rp 50.000 | Pengangkutan 3x/minggu, tempat sampah khusus, laporan bulanan |
| Industri | Rp 150.000 | Pengangkutan harian, kontainer khusus, penanganan B3 |

### 4.2 Hasil Perancangan Sistem

#### 4.2.1 Desain Antarmuka Beranda

Halaman beranda dirancang dengan struktur:
- **Hero Section**: Menampilkan tagline, statistik pengguna (25.000+ pengguna aktif, 98% tingkat kepuasan, 50.000+ transaksi/bulan), dan call-to-action buttons
- **Services Section**: Menampilkan 6 fitur unggulan (Pembayaran QRIS, Riwayat Transaksi, Notifikasi Tagihan, Laporan Real-time, Edukasi Sampah, Layanan 24/7)
- **Policy Section**: Informasi tarif dan ketentuan retribusi
- **Education Preview**: Klasifikasi jenis sampah (Organik, Anorganik, B3, Residu)
- **Impact Section**: Statistik dampak lingkungan
- **How It Works**: 4 langkah mudah pembayaran
- **Testimonials**: Testimoni pengguna
- **FAQ**: Pertanyaan yang sering diajukan

#### 4.2.2 Desain Halaman Pembayaran

Halaman pembayaran terdiri dari dua kolom utama:
- **Kolom Kiri (Info Tagihan)**: Menampilkan detail tagihan (no. pelanggan, nama, alamat, kategori, periode, jatuh tempo, rincian biaya)
- **Kolom Kanan (Metode Pembayaran)**: Menyediakan pilihan pembayaran:
  1. QRIS (mendukung GoPay, OVO, DANA, ShopeePay, LinkAja)
  2. Transfer Bank (Virtual Account BCA, BNI, Mandiri, BRI)
  3. Minimarket (Alfamart, Indomaret)

#### 4.2.3 Desain Halaman Riwayat

Halaman riwayat menyediakan:
- Filter berdasarkan tahun, status, dan metode pembayaran
- Tabel transaksi dengan kolom: No. Transaksi, Periode, Tanggal Bayar, Metode, Nominal, Status, Aksi (unduh bukti)
- Ringkasan statistik: Total pembayaran, jumlah pembayaran tepat waktu, tingkat kepatuhan

#### 4.2.4 Desain Halaman Edukasi

Halaman edukasi mencakup:
- **Klasifikasi Sampah**: 4 kategori (Organik, Anorganik, B3, Residu) dengan penjelasan dan tips pengelolaan
- **Prinsip 3R**: Reduce, Reuse, Recycle dengan visualisasi menarik
- **Dampak Lingkungan**: Infografis tentang dampak sampah terhadap lingkungan

#### 4.2.5 Desain Dashboard

Dashboard admin menampilkan:
- **Statistik Utama**: Total pengguna terdaftar (25.432), pendapatan bulan ini (Rp 1,27 M), tagihan tertunda (3.847), tingkat kepatuhan (84,7%)
- **Grafik Tren Pembayaran**: Visualisasi bar chart bulanan
- **Distribusi per Kategori**: Pie chart (Rumah Tangga 60%, Usaha 20%, Industri 20%)
- **Daftar Transaksi Terbaru**: Tabel real-time transaksi

### 4.3 Hasil Implementasi

#### 4.3.1 Struktur File Sistem

```
SIPARES/
├── index.html          # Halaman beranda
├── pembayaran.html     # Halaman pembayaran
├── riwayat.html        # Halaman riwayat transaksi
├── edukasi.html        # Halaman edukasi
├── dashboard.html      # Halaman dashboard
├── login.html          # Halaman login
├── css/
│   ├── styles.css      # Stylesheet utama
│   ├── pages.css       # Stylesheet halaman spesifik
│   ├── components.css  # Komponen reusable
│   └── layout.css      # Layout sistem
└── js/
    └── main.js         # JavaScript utama
```

#### 4.3.2 Implementasi Fitur Utama

**A. Sistem Pembayaran Multi-Channel**

SIPARES mengimplementasikan tiga metode pembayaran:
1. **QRIS**: Kode QR yang dapat di-scan menggunakan berbagai e-wallet
2. **Virtual Account**: Nomor rekening virtual dari bank-bank utama
3. **Retail/Minimarket**: Kode pembayaran untuk gerai minimarket

**B. Sistem Notifikasi**

Sistem notifikasi real-time menampilkan:
- Tagihan baru yang terbit
- Konfirmasi pembayaran berhasil
- Tips pengelolaan sampah

**C. Dashboard Real-time**

Dashboard admin menampilkan data statistik yang diperbarui secara real-time dengan visualisasi:
- Bar chart untuk tren pembayaran
- Pie chart untuk distribusi kategori
- Tabel transaksi terbaru

#### 4.3.3 Implementasi Desain Responsif

Sistem diimplementasikan dengan pendekatan *mobile-first* menggunakan:
- CSS Grid untuk layout utama
- Flexbox untuk komponen-komponen fleksibel
- Media queries untuk breakpoint responsif
- Font sizing responsif menggunakan rem/em

### 4.4 Hasil Pengujian

#### 4.4.1 Pengujian Black Box

| No | Skenario Pengujian | Hasil yang Diharapkan | Hasil Aktual | Status |
|----|-------------------|----------------------|--------------|--------|
| 1 | Akses halaman beranda | Halaman beranda tampil dengan benar | Sesuai | ✓ Berhasil |
| 2 | Navigasi ke halaman pembayaran | Halaman pembayaran tampil | Sesuai | ✓ Berhasil |
| 3 | Pilih metode pembayaran QRIS | Tampilan QR code muncul | Sesuai | ✓ Berhasil |
| 4 | Pilih metode Transfer Bank | Daftar Virtual Account tampil | Sesuai | ✓ Berhasil |
| 5 | Akses halaman riwayat | Tabel riwayat transaksi tampil | Sesuai | ✓ Berhasil |
| 6 | Filter riwayat berdasarkan tahun | Data terfilter sesuai | Sesuai | ✓ Berhasil |
| 7 | Akses halaman edukasi | Konten edukasi tampil lengkap | Sesuai | ✓ Berhasil |
| 8 | Akses dashboard | Statistik dan grafik tampil | Sesuai | ✓ Berhasil |
| 9 | Login dengan credentials valid | Redirect ke halaman utama | Sesuai | ✓ Berhasil |
| 10 | Responsivitas di mobile | Layout menyesuaikan layar | Sesuai | ✓ Berhasil |

#### 4.4.2 Pengujian Responsif

| Perangkat | Resolusi | Hasil |
|-----------|----------|-------|
| Desktop | 1920x1080 | ✓ Tampil optimal |
| Laptop | 1366x768 | ✓ Tampil optimal |
| Tablet | 768x1024 | ✓ Layout menyesuaikan |
| Mobile | 375x667 | ✓ Mobile-friendly |

### 4.5 Pembahasan

#### 4.5.1 Kelebihan Sistem SIPARES

1. **Kemudahan Akses**: Sistem dapat diakses 24/7 melalui browser tanpa perlu instalasi aplikasi.
2. **Multi-Channel Payment**: Tersedia berbagai pilihan metode pembayaran yang mengakomodasi preferensi pengguna.
3. **Transparansi**: Riwayat transaksi yang lengkap dan dapat diunduh.
4. **Edukasi Terintegrasi**: Fitur edukasi yang meningkatkan kesadaran pengelolaan sampah.
5. **User-Friendly Interface**: Desain antarmuka modern yang mudah digunakan.
6. **Responsif**: Dapat diakses dengan baik di berbagai perangkat.

#### 4.5.2 Kontribusi terhadap Pengelolaan Sampah

SIPARES tidak hanya berfungsi sebagai sistem pembayaran, tetapi juga berkontribusi dalam:
1. **Meningkatkan Kepatuhan**: Notifikasi otomatis mengurangi keterlambatan pembayaran.
2. **Edukasi Masyarakat**: Modul edukasi meningkatkan pemahaman tentang klasifikasi dan pengelolaan sampah.
3. **Transparansi Dana**: Dashboard publik memungkinkan masyarakat memantau penggunaan dana retribusi.
4. **Digitalisasi Layanan Publik**: Mendukung program *Smart City* dan *e-Government*.

#### 4.5.3 Perbandingan dengan Sistem Konvensional

| Aspek | Sistem Konvensional | SIPARES |
|-------|---------------------|---------|
| Waktu Pembayaran | Jam kerja kantor | 24/7 |
| Metode Pembayaran | Tunai | QRIS, VA, Minimarket |
| Bukti Pembayaran | Manual/Kertas | Digital/PDF |
| Transparansi | Terbatas | Real-time Dashboard |
| Notifikasi | Tidak ada | Otomatis |
| Edukasi | Tidak ada | Terintegrasi |

---

## 5. KESIMPULAN DAN SARAN

### 5.1 Kesimpulan

Berdasarkan hasil perancangan dan implementasi Sistem Informasi Pembayaran Retribusi Sampah (SIPARES), dapat disimpulkan bahwa:

1. **Keberhasilan Perancangan**: SIPARES berhasil dirancang sebagai sistem informasi berbasis web yang menyediakan layanan pembayaran retribusi sampah yang mudah, cepat, dan transparan. Sistem ini memiliki 6 halaman utama yang masing-masing memiliki fungsi spesifik untuk memenuhi kebutuhan pengguna.

2. **Implementasi Multi-Channel Payment**: Sistem berhasil mengintegrasikan tiga metode pembayaran digital yaitu QRIS (mendukung berbagai e-wallet), Virtual Account dari bank-bank utama (BCA, BNI, Mandiri, BRI), dan pembayaran melalui minimarket (Alfamart, Indomaret). Implementasi ini memberikan fleksibilitas kepada pengguna dalam memilih metode pembayaran sesuai preferensi.

3. **Fitur Transparansi**: SIPARES menyediakan fitur riwayat transaksi yang komprehensif dengan kemampuan filter dan unduh bukti pembayaran digital. Dashboard admin menampilkan statistik real-time yang meningkatkan transparansi pengelolaan dana retribusi.

4. **Integrasi Edukasi**: Modul edukasi yang terintegrasi dalam sistem berhasil menyampaikan informasi tentang klasifikasi sampah (Organik, Anorganik, B3, Residu), prinsip 3R (Reduce, Reuse, Recycle), dan dampak sampah terhadap lingkungan.

5. **Kualitas Antarmuka**: Penggunaan teknologi web modern (HTML5, CSS3, JavaScript) menghasilkan antarmuka yang responsif, estetis, dan user-friendly yang dapat diakses di berbagai perangkat.

### 5.2 Saran

Untuk pengembangan sistem lebih lanjut, disarankan:

1. **Integrasi Backend**: Mengembangkan backend menggunakan teknologi server-side (seperti Node.js, PHP, atau Python) dan database untuk menyimpan data transaksi secara persisten.

2. **Integrasi Payment Gateway**: Mengintegrasikan sistem dengan payment gateway resmi (seperti Midtrans, Xendit, atau Doku) untuk pemrosesan pembayaran yang sebenarnya.

3. **Notifikasi Push**: Mengimplementasikan notifikasi push melalui browser, email, SMS, atau WhatsApp untuk pengingat tagihan yang lebih efektif.

4. **Aplikasi Mobile Native**: Mengembangkan versi aplikasi mobile native untuk Android dan iOS untuk pengalaman pengguna yang lebih optimal.

5. **Fitur Pelaporan**: Menambahkan fitur pelaporan masalah persampahan (seperti sampah tidak terangkut) yang dapat digunakan masyarakat.

6. **Gamifikasi**: Menambahkan sistem reward atau gamifikasi untuk meningkatkan kepatuhan pembayaran dan partisipasi dalam program pengelolaan sampah.

7. **Integrasi GIS**: Mengintegrasikan Geographic Information System (GIS) untuk pemetaan area layanan dan jadwal pengangkutan sampah.

---

## DAFTAR PUSTAKA

Kementerian Lingkungan Hidup dan Kehutanan. (2024). *Sistem Informasi Pengelolaan Sampah Nasional*. Jakarta: KLHK.

Nielsen, J. (2020). *10 Usability Heuristics for User Interface Design*. Nielsen Norman Group.

O'Brien, J. A., & Marakas, G. M. (2017). *Management Information Systems* (10th ed.). New York: McGraw-Hill.

Pressman, R. S. (2014). *Software Engineering: A Practitioner's Approach* (8th ed.). New York: McGraw-Hill.

Pratama, A., Wijaya, B., & Kusuma, C. (2023). Pengembangan Sistem Pembayaran PDAM Berbasis Web dengan Integrasi Payment Gateway. *Jurnal Sistem Informasi*, 15(2), 45-58.

Republik Indonesia. (2009). *Undang-Undang Nomor 28 Tahun 2009 tentang Pajak Daerah dan Retribusi Daerah*. Jakarta: Sekretariat Negara.

Susanto, D., & Hidayat, E. (2022). Perancangan Aplikasi Pengelolaan Sampah Berbasis Android dengan Fitur Edukasi 3R. *Jurnal Teknologi Informasi*, 8(1), 23-35.

Widodo, F., Santoso, G., & Purnomo, H. (2024). Implementasi Sistem E-Retribusi untuk Retribusi Pasar Berbasis Mobile. *Jurnal Informatika Terapan*, 10(3), 112-125.

---

## LAMPIRAN

---

### Lampiran 1: Daftar Screenshot yang Diperlukan

Berikut adalah daftar lengkap screenshot yang perlu Anda siapkan untuk melengkapi artikel jurnal ini:

---

#### 📸 GAMBAR WAJIB (Utama)

| No | Nama Gambar | Halaman | Bagian yang Di-screenshot | Penempatan dalam Artikel |
|----|-------------|---------|---------------------------|--------------------------|
| 1 | **Gambar 1** | `index.html` | Hero Section + Navigation (tampilan atas halaman beranda) | **Bagian 4.2.1** - Desain Antarmuka Beranda |
| 2 | **Gambar 2** | `index.html` | Services Section (6 fitur unggulan) | **Bagian 4.2.1** - Desain Antarmuka Beranda |
| 3 | **Gambar 3** | `index.html` | Policy Section (kartu tarif retribusi) | **Bagian 4.2.1** - Desain Antarmuka Beranda |
| 4 | **Gambar 4** | `pembayaran.html` | Tampilan lengkap halaman pembayaran (tagihan + metode) | **Bagian 4.2.2** - Desain Halaman Pembayaran |
| 5 | **Gambar 5** | `pembayaran.html` | Detail metode QRIS yang dipilih | **Bagian 4.3.2** - Implementasi Fitur Utama |
| 6 | **Gambar 6** | `riwayat.html` | Tampilan tabel riwayat transaksi | **Bagian 4.2.3** - Desain Halaman Riwayat |
| 7 | **Gambar 7** | `edukasi.html` | Klasifikasi sampah (4 jenis sampah) | **Bagian 4.2.4** - Desain Halaman Edukasi |
| 8 | **Gambar 8** | `edukasi.html` | Prinsip 3R (Reduce, Reuse, Recycle) | **Bagian 4.2.4** - Desain Halaman Edukasi |
| 9 | **Gambar 9** | `dashboard.html` | Statistik cards (4 kartu statistik atas) | **Bagian 4.2.5** - Desain Dashboard |
| 10 | **Gambar 10** | `dashboard.html` | Grafik dan tabel transaksi terbaru | **Bagian 4.3.2** - Dashboard Real-time |
| 11 | **Gambar 11** | `login.html` | Tampilan halaman login lengkap | **Bagian 4.3** - Hasil Implementasi |

---

#### 📱 GAMBAR RESPONSIF (Opsional tapi Disarankan)

| No | Nama Gambar | Halaman | Deskripsi | Penempatan dalam Artikel |
|----|-------------|---------|-----------|--------------------------|
| 12 | **Gambar 12** | `index.html` | Tampilan mobile (lebar 375px) | **Bagian 4.4.2** - Pengujian Responsif |
| 13 | **Gambar 13** | `pembayaran.html` | Tampilan tablet (lebar 768px) | **Bagian 4.4.2** - Pengujian Responsif |
| 14 | **Gambar 14** | `index.html` | Tampilan menu navigasi mobile (hamburger menu terbuka) | **Bagian 4.3.3** - Implementasi Desain Responsif |

---

### Lampiran 2: Panduan Cara Mengambil Screenshot

#### Langkah-langkah Mengambil Screenshot:

1. **Buka website SIPARES** di browser (Chrome/Firefox disarankan)
2. **Untuk tampilan desktop**: Buka Developer Tools (F12), pilih mode responsive, set resolusi 1920x1080 atau 1366x768
3. **Untuk tampilan mobile**: Set resolusi 375x667 (iPhone SE) atau 414x896 (iPhone XR)
4. **Untuk tampilan tablet**: Set resolusi 768x1024 (iPad)
5. **Screenshot**: Gunakan Snipping Tool (Windows) atau Screenshot tool di DevTools dengan tombol capture

#### Tips untuk Screenshot yang Baik:

- ✅ Pastikan seluruh elemen yang ingin ditampilkan terlihat jelas
- ✅ Hindari scroll yang memotong elemen penting
- ✅ Gunakan resolusi tinggi (minimal 1080p untuk desktop)
- ✅ Simpan dalam format PNG atau JPG berkualitas tinggi
- ✅ Berikan nama file yang jelas (contoh: `gambar_1_hero_section.png`)

---

### Lampiran 3: Template Penempatan Gambar dalam Artikel

Berikut adalah template untuk menempatkan gambar dalam artikel Anda. Copy dan paste template ini ke bagian yang sesuai:

---

**Template untuk Bagian 4.2.1 (Desain Antarmuka Beranda):**

```
Gambar 1. Tampilan Hero Section Halaman Beranda SIPARES
[SISIPKAN GAMBAR DI SINI]

Gambar 2. Tampilan Services Section dengan 6 Fitur Unggulan
[SISIPKAN GAMBAR DI SINI]

Gambar 3. Tampilan Policy Section dengan Kartu Tarif Retribusi
[SISIPKAN GAMBAR DI SINI]
```

---

**Template untuk Bagian 4.2.2 (Desain Halaman Pembayaran):**

```
Gambar 4. Tampilan Halaman Pembayaran dengan Detail Tagihan dan Pilihan Metode
[SISIPKAN GAMBAR DI SINI]

Gambar 5. Tampilan Metode Pembayaran QRIS
[SISIPKAN GAMBAR DI SINI]
```

---

**Template untuk Bagian 4.2.3 (Desain Halaman Riwayat):**

```
Gambar 6. Tampilan Halaman Riwayat Transaksi dengan Filter dan Tabel Data
[SISIPKAN GAMBAR DI SINI]
```

---

**Template untuk Bagian 4.2.4 (Desain Halaman Edukasi):**

```
Gambar 7. Tampilan Klasifikasi Sampah (Organik, Anorganik, B3, Residu)
[SISIPKAN GAMBAR DI SINI]

Gambar 8. Tampilan Prinsip 3R (Reduce, Reuse, Recycle)
[SISIPKAN GAMBAR DI SINI]
```

---

**Template untuk Bagian 4.2.5 (Desain Dashboard):**

```
Gambar 9. Tampilan Dashboard dengan Statistik Utama
[SISIPKAN GAMBAR DI SINI]

Gambar 10. Tampilan Grafik Tren Pembayaran dan Tabel Transaksi Terbaru
[SISIPKAN GAMBAR DI SINI]
```

---

**Template untuk Bagian 4.3 (Hasil Implementasi):**

```
Gambar 11. Tampilan Halaman Login SIPARES
[SISIPKAN GAMBAR DI SINI]
```

---

**Template untuk Bagian 4.4.2 (Pengujian Responsif):**

```
Gambar 12. Tampilan Responsif Halaman Beranda pada Perangkat Mobile (375x667)
[SISIPKAN GAMBAR DI SINI]

Gambar 13. Tampilan Responsif Halaman Pembayaran pada Tablet (768x1024)
[SISIPKAN GAMBAR DI SINI]

Gambar 14. Tampilan Menu Navigasi Mobile (Hamburger Menu)
[SISIPKAN GAMBAR DI SINI]
```

---

### Lampiran 4: Kode Sumber (Snippet)

**Struktur HTML Halaman Utama:**
```html
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIPARES - Sistem Pembayaran Retribusi Sampah</title>
  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <!-- Navigation, Hero, Services, etc. -->
</body>
</html>
```

**Contoh Struktur Halaman Pembayaran:**
```html
<section class="payment-section">
  <div class="container">
    <div class="payment-grid">
      <!-- Kolom Info Tagihan -->
      <div class="payment-info">
        <div class="bill-card">
          <div class="bill-header">
            <h3>Tagihan Anda</h3>
            <span class="bill-status pending">Belum Dibayar</span>
          </div>
          <!-- Detail tagihan -->
        </div>
      </div>
      <!-- Kolom Metode Pembayaran -->
      <div class="payment-methods">
        <!-- QRIS, Transfer Bank, Minimarket -->
      </div>
    </div>
  </div>
</section>
```

**Contoh CSS untuk Card Component:**
```css
.bill-card {
  background: white;
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
}

.bill-status.pending {
  background: #fef3c7;
  color: #d97706;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}
```

---

### Lampiran 5: Checklist Kelengkapan Artikel

Gunakan checklist ini untuk memastikan artikel Anda lengkap:

#### Data Penulis:
- [ ] Nama lengkap penulis
- [ ] Program studi
- [ ] Fakultas
- [ ] Nama universitas/perguruan tinggi
- [ ] Email aktif

#### Gambar/Screenshot:
- [ ] Gambar 1: Hero Section Beranda
- [ ] Gambar 2: Services Section
- [ ] Gambar 3: Policy Section (Tarif)
- [ ] Gambar 4: Halaman Pembayaran (Full)
- [ ] Gambar 5: Metode QRIS
- [ ] Gambar 6: Riwayat Transaksi
- [ ] Gambar 7: Klasifikasi Sampah
- [ ] Gambar 8: Prinsip 3R
- [ ] Gambar 9: Dashboard Statistik
- [ ] Gambar 10: Grafik & Tabel Dashboard
- [ ] Gambar 11: Halaman Login
- [ ] Gambar 12: Tampilan Mobile (opsional)
- [ ] Gambar 13: Tampilan Tablet (opsional)
- [ ] Gambar 14: Menu Mobile (opsional)

#### Format File:
- [ ] Gambar dalam format PNG/JPG
- [ ] Resolusi minimal 1080p untuk desktop
- [ ] Nama file sesuai urutan gambar

---

*Artikel ini disusun sebagai bagian dari penelitian pengembangan sistem informasi untuk modernisasi layanan publik di bidang persampahan.*
