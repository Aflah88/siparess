# 📚 Tutorial Lengkap: Membuat Website SIPARES dari Nol

## Daftar Isi
1. [Persiapan](#1-persiapan)
2. [Membuat Struktur Folder](#2-membuat-struktur-folder)
3. [Membuat File CSS Dasar](#3-membuat-file-css-dasar)
4. [Membuat Halaman HTML](#4-membuat-halaman-html)
5. [Membuat JavaScript](#5-membuat-javascript)
6. [Tips & Penjelasan](#6-tips--penjelasan)

---

## 1. Persiapan

### Apa yang Anda butuhkan:
- **Text Editor**: VS Code, Notepad++, atau Sublime Text
- **Browser**: Chrome, Firefox, atau Edge
- **Pengetahuan Dasar**: HTML, CSS, JavaScript (sedikit)

### Konsep yang akan dipelajari:
- HTML5 semantic elements
- CSS Variables (Custom Properties)
- CSS Flexbox & Grid
- CSS Animations
- Responsive Design
- JavaScript DOM manipulation

---

## 2. Membuat Struktur Folder

Buat folder dengan struktur seperti ini:

```
SIPARES/
├── css/
│   └── styles.css
├── js/
│   └── main.js
└── index.html
```

**Cara membuat:**
1. Buat folder baru bernama `SIPARES`
2. Di dalam folder SIPARES, buat folder `css` dan `js`
3. Di dalam folder css, buat file `styles.css`
4. Di dalam folder js, buat file `main.js`
5. Di folder utama SIPARES, buat file `index.html`

---

## 3. Membuat File CSS Dasar

### LANGKAH 3.1: Buat Design System dengan CSS Variables

Buka file `css/styles.css` dan ketik kode berikut:

```css
/* ========================================
   LANGKAH 1: CSS Variables (Design System)
   Variables memudahkan kita mengubah warna/ukuran
   di satu tempat saja
======================================== */

:root {
    /* Warna Utama - Tema Hijau */
    --primary-500: #10b981;
    --primary-600: #059669;
    --primary-700: #047857;

    /* Warna Abu-abu untuk teks */
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-800: #1f2937;
    --gray-900: #111827;

    /* Gradient */
    --gradient-primary: linear-gradient(135deg, #10b981 0%, #047857 100%);
    --gradient-hero: linear-gradient(135deg, #065f46 0%, #047857 50%, #10b981 100%);

    /* Shadow */
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);

    /* Font */
    --font-family: 'Plus Jakarta Sans', sans-serif;

    /* Spacing */
    --spacing-4: 1rem;
    --spacing-6: 1.5rem;
    --spacing-8: 2rem;

    /* Border Radius */
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;
    --radius-full: 9999px;

    /* Transition */
    --transition-base: 300ms ease;
}
```

**PENJELASAN:**
- `:root` adalah pseudo-class untuk elemen root (html)
- `--nama-variable` adalah cara membuat CSS variable
- Variable dipakai dengan `var(--nama-variable)`
- Keuntungan: ubah warna di satu tempat, otomatis berubah semua

---

### LANGKAH 3.2: CSS Reset & Base Styles

Tambahkan kode berikut di bawah variables:

```css
/* ========================================
   LANGKAH 2: CSS Reset
   Menghilangkan default styling browser
======================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: var(--font-family);
    font-size: 1rem;
    line-height: 1.6;
    color: var(--gray-800);
    background-color: var(--gray-50);
}

a {
    text-decoration: none;
    color: inherit;
}

ul {
    list-style: none;
}

button {
    font-family: inherit;
    cursor: pointer;
    border: none;
    background: none;
}

.container {
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 var(--spacing-6);
}
```

**PENJELASAN:**
- `*` adalah selector untuk SEMUA elemen
- `box-sizing: border-box` membuat padding tidak menambah lebar
- `scroll-behavior: smooth` membuat scroll halus
- `.container` adalah class untuk membatasi lebar konten

---

### LANGKAH 3.3: Styling Navigation

Tambahkan kode berikut:

```css
/* ========================================
   LANGKAH 3: Navigation Bar
======================================== */

.navbar {
    position: fixed;          /* Tetap di atas saat scroll */
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;            /* Di atas elemen lain */
    padding: var(--spacing-4) 0;
    transition: var(--transition-base);
}

/* Saat di-scroll, navbar berubah */
.navbar.scrolled {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    box-shadow: var(--shadow-lg);
}

.nav-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 var(--spacing-6);
}

/* Logo */
.nav-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-icon {
    width: 45px;
    height: 45px;
    background: var(--gradient-primary);
    border-radius: var(--radius-xl);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.logo-text {
    font-size: 1.25rem;
    font-weight: 800;
    color: white;
}

.navbar.scrolled .logo-text {
    color: var(--primary-700);
}

/* Menu */
.nav-menu {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
    border-radius: var(--radius-lg);
    transition: var(--transition-base);
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.2);
}

.navbar.scrolled .nav-link {
    color: var(--gray-600);
}

.navbar.scrolled .nav-link:hover {
    background: rgba(16, 185, 129, 0.1);
    color: var(--primary-700);
}
```

**PENJELASAN:**
- `position: fixed` membuat navbar tetap saat scroll
- `z-index` mengatur urutan tumpukan elemen
- `backdrop-filter: blur()` membuat efek blur di belakang
- `display: flex` untuk mengatur layout horizontal
- `gap` adalah jarak antar elemen flex

---

### LANGKAH 3.4: Styling Buttons

```css
/* ========================================
   LANGKAH 4: Buttons
======================================== */

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: var(--radius-xl);
    transition: var(--transition-base);
    cursor: pointer;
    border: 2px solid transparent;
}

.btn-primary {
    background: var(--gradient-primary);
    color: white;
    box-shadow: var(--shadow-md);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.125rem;
}
```

**PENJELASAN:**
- `inline-flex` seperti flex tapi inline
- `transform: translateY(-2px)` menggerakkan ke atas saat hover
- Transisi membuat gerakan halus

---

### LANGKAH 3.5: Hero Section

```css
/* ========================================
   LANGKAH 5: Hero Section
======================================== */

.hero {
    position: relative;
    min-height: 100vh;         /* Tinggi minimal = tinggi layar */
    display: flex;
    align-items: center;
    background: var(--gradient-hero);
    overflow: hidden;
    padding-top: 100px;
}

/* Background shapes untuk dekorasi */
.hero-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
}

.hero-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.05);
}

.shape-1 {
    width: 600px;
    height: 600px;
    top: -200px;
    right: -100px;
    animation: float 20s ease-in-out infinite;
}

.shape-2 {
    width: 400px;
    height: 400px;
    bottom: -100px;
    left: -100px;
    animation: float 15s ease-in-out infinite reverse;
}

/* Animasi mengambang */
@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-30px) rotate(10deg);
    }
}

/* Konten hero */
.hero-container {
    display: grid;
    grid-template-columns: 1fr 1fr;  /* 2 kolom sama lebar */
    gap: 4rem;
    align-items: center;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 var(--spacing-6);
    position: relative;
    z-index: 1;
}

.hero-title {
    font-size: 3rem;
    font-weight: 800;
    color: white;
    line-height: 1.1;
    margin-bottom: var(--spacing-6);
}

/* Teks dengan gradient */
.gradient-text {
    background: linear-gradient(135deg, #a7f3d0, #6ee7b7, #34d399);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-description {
    font-size: 1.125rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: var(--spacing-8);
    max-width: 540px;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
}
```

**PENJELASAN:**
- `min-height: 100vh` = minimal setinggi viewport (layar)
- `@keyframes` mendefinisikan animasi
- `animation` menerapkan keyframes ke elemen
- `grid-template-columns: 1fr 1fr` membuat 2 kolom sama besar
- `-webkit-background-clip: text` membuat gradient di teks

---

### LANGKAH 3.6: Section Styling

```css
/* ========================================
   LANGKAH 6: Section Umum
======================================== */

.section-header {
    text-align: center;
    margin-bottom: 4rem;
}

.section-badge {
    display: inline-block;
    background: rgba(16, 185, 129, 0.1);
    color: var(--primary-700);
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.section-title {
    font-size: 2.25rem;
    font-weight: 800;
    color: var(--gray-900);
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.125rem;
    color: var(--gray-600);
    max-width: 600px;
    margin: 0 auto;
}

/* Services Section */
.services {
    padding: 5rem 0;
    background: var(--gray-50);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);  /* 3 kolom */
    gap: 2rem;
}

.service-card {
    background: white;
    padding: 2rem;
    border-radius: 1.5rem;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
}

.service-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.service-icon {
    width: 70px;
    height: 70px;
    background: rgba(16, 185, 129, 0.1);
    border-radius: var(--radius-xl);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary-600);
    margin-bottom: var(--spacing-6);
}

.service-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 0.75rem;
}

.service-desc {
    color: var(--gray-600);
    line-height: 1.7;
}
```

---

### LANGKAH 3.7: Footer

```css
/* ========================================
   LANGKAH 7: Footer
======================================== */

.footer {
    background: var(--gray-900);
    color: white;
    padding: 4rem 0 2rem;
}

.footer-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
    gap: 3rem;
    margin-bottom: 3rem;
}

.footer-brand .logo-text {
    color: white;
}

.footer-desc {
    color: #9ca3af;
    margin: 1rem 0;
    line-height: 1.8;
}

.footer-links h4 {
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.footer-links ul {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.footer-links a {
    color: #9ca3af;
    transition: var(--transition-base);
}

.footer-links a:hover {
    color: var(--primary-500);
}

.footer-bottom {
    display: flex;
    justify-content: space-between;
    padding-top: 2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-bottom p {
    color: #6b7280;
    font-size: 0.875rem;
}
```

---

### LANGKAH 3.8: Responsive Design

```css
/* ========================================
   LANGKAH 8: Responsive Design
   Membuat tampilan bagus di semua ukuran layar
======================================== */

/* Tablet dan lebih kecil */
@media (max-width: 1024px) {
    .hero-container {
        grid-template-columns: 1fr;  /* 1 kolom */
        text-align: center;
    }

    .services-grid {
        grid-template-columns: repeat(2, 1fr);  /* 2 kolom */
    }

    .footer-grid {
        grid-template-columns: 1fr 1fr;
    }
}

/* Mobile */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }

    .section-title {
        font-size: 1.5rem;
    }

    .services-grid {
        grid-template-columns: 1fr;  /* 1 kolom */
    }

    .nav-menu {
        display: none;  /* Sembunyikan menu di mobile */
    }

    .footer-grid {
        grid-template-columns: 1fr;
    }
}
```

**PENJELASAN:**
- `@media` adalah media query untuk responsive
- `max-width: 768px` berarti "jika lebar layar maksimal 768px"
- Di dalamnya kita ubah layout untuk layar kecil

---

## 4. Membuat Halaman HTML

### LANGKAH 4.1: Struktur Dasar HTML

Buka file `index.html` dan ketik:

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIPARES - Sistem Pembayaran Retribusi Sampah">
    <title>SIPARES - Sistem Pembayaran Retribusi Sampah</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Kita -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <!-- Konten akan ditambahkan di sini -->

    <!-- JavaScript -->
    <script src="js/main.js"></script>
</body>
</html>
```

**PENJELASAN:**
- `<!DOCTYPE html>` memberitahu browser ini HTML5
- `<meta viewport>` penting untuk responsive
- `<link>` untuk menghubungkan CSS
- `<script>` untuk menghubungkan JavaScript

---

### LANGKAH 4.2: Navigation Bar

Tambahkan di dalam `<body>`:

```html
<!-- Navigation -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <!-- Logo -->
        <a href="index.html" class="nav-logo">
            <div class="logo-icon">
                <i class="fas fa-recycle"></i>
            </div>
            <span class="logo-text">SIPARES</span>
        </a>

        <!-- Menu -->
        <div class="nav-menu">
            <a href="index.html" class="nav-link active">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>
            <a href="pembayaran.html" class="nav-link">
                <i class="fas fa-credit-card"></i>
                <span>Pembayaran</span>
            </a>
            <a href="riwayat.html" class="nav-link">
                <i class="fas fa-history"></i>
                <span>Riwayat</span>
            </a>
            <a href="edukasi.html" class="nav-link">
                <i class="fas fa-book-open"></i>
                <span>Edukasi</span>
            </a>
        </div>

        <!-- Login Button -->
        <div class="nav-actions">
            <a href="login.html" class="btn btn-primary">
                <i class="fas fa-user"></i>
                <span>Masuk</span>
            </a>
        </div>
    </div>
</nav>
```

**PENJELASAN:**
- `<nav>` adalah elemen semantik untuk navigasi
- `<i class="fas fa-icon">` adalah ikon dari Font Awesome
- `class="active"` untuk menandai halaman aktif

---

### LANGKAH 4.3: Hero Section

```html
<!-- Hero Section -->
<section class="hero" id="hero">
    <!-- Background Shapes -->
    <div class="hero-bg">
        <div class="hero-shape shape-1"></div>
        <div class="hero-shape shape-2"></div>
    </div>

    <!-- Content -->
    <div class="hero-container">
        <!-- Kiri: Teks -->
        <div class="hero-content">
            <h1 class="hero-title">
                Sistem Pembayaran
                <br>
                <span class="gradient-text">Retribusi Sampah</span>
            </h1>

            <p class="hero-description">
                Platform digital terintegrasi untuk pembayaran
                retribusi sampah yang mudah, cepat, dan transparan.
            </p>

            <div class="hero-buttons">
                <a href="pembayaran.html" class="btn btn-primary btn-lg">
                    <i class="fas fa-credit-card"></i>
                    <span>Bayar Sekarang</span>
                </a>
            </div>
        </div>

        <!-- Kanan: Visual (opsional) -->
        <div class="hero-visual">
            <!-- Bisa tambahkan gambar atau ilustrasi -->
        </div>
    </div>
</section>
```

---

### LANGKAH 4.4: Services Section

```html
<!-- Services Section -->
<section class="services" id="layanan">
    <div class="container">
        <!-- Header -->
        <div class="section-header">
            <span class="section-badge">Layanan Kami</span>
            <h2 class="section-title">Fitur Unggulan Platform</h2>
            <p class="section-subtitle">
                Nikmati kemudahan layanan retribusi sampah
                dengan berbagai fitur modern
            </p>
        </div>

        <!-- Grid Cards -->
        <div class="services-grid">
            <!-- Card 1 -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h3 class="service-title">Pembayaran QRIS</h3>
                <p class="service-desc">
                    Bayar retribusi sampah dengan mudah
                    menggunakan QRIS dari berbagai e-wallet.
                </p>
            </div>

            <!-- Card 2 -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="service-title">Riwayat Transaksi</h3>
                <p class="service-desc">
                    Lacak dan kelola semua riwayat pembayaran
                    Anda dengan mudah.
                </p>
            </div>

            <!-- Card 3 -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="service-title">Notifikasi Tagihan</h3>
                <p class="service-desc">
                    Dapatkan pengingat otomatis untuk tagihan
                    retribusi Anda.
                </p>
            </div>
        </div>
    </div>
</section>
```

---

### LANGKAH 4.5: Footer

```html
<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <a href="index.html" class="nav-logo">
                    <div class="logo-icon">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <span class="logo-text">SIPARES</span>
                </a>
                <p class="footer-desc">
                    Sistem Pembayaran Retribusi Sampah -
                    Platform digital untuk layanan persampahan.
                </p>
            </div>

            <!-- Links 1 -->
            <div class="footer-links">
                <h4>Layanan</h4>
                <ul>
                    <li><a href="pembayaran.html">Pembayaran</a></li>
                    <li><a href="riwayat.html">Riwayat</a></li>
                    <li><a href="edukasi.html">Edukasi</a></li>
                </ul>
            </div>

            <!-- Links 2 -->
            <div class="footer-links">
                <h4>Informasi</h4>
                <ul>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Kebijakan</a></li>
                    <li><a href="#">Kontak</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="footer-links">
                <h4>Hubungi Kami</h4>
                <ul>
                    <li><i class="fas fa-phone"></i> (021) 1234-5678</li>
                    <li><i class="fas fa-envelope"></i> info@sipares.go.id</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 SIPARES. Hak Cipta Dilindungi.</p>
        </div>
    </div>
</footer>
```

---

## 5. Membuat JavaScript

### LANGKAH 5.1: Navbar Scroll Effect

Buka file `js/main.js` dan ketik:

```javascript
// ========================================
// LANGKAH 1: Navbar Scroll Effect
// Mengubah tampilan navbar saat scroll
// ========================================

// Ambil elemen navbar
const navbar = document.getElementById('navbar');

// Fungsi untuk cek scroll
function handleScroll() {
    if (window.scrollY > 50) {
        // Jika scroll lebih dari 50px
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
}

// Pasang event listener
window.addEventListener('scroll', handleScroll);

// Jalankan sekali saat halaman load
handleScroll();
```

**PENJELASAN:**
- `document.getElementById()` mengambil elemen berdasarkan ID
- `window.scrollY` adalah posisi scroll vertikal
- `classList.add()` menambahkan class
- `addEventListener()` mendengarkan event

---

### LANGKAH 5.2: Smooth Scroll

```javascript
// ========================================
// LANGKAH 2: Smooth Scroll
// Scroll halus saat klik link anchor
// ========================================

// Ambil semua link yang dimulai dengan #
const anchorLinks = document.querySelectorAll('a[href^="#"]');

anchorLinks.forEach(link => {
    link.addEventListener('click', function(e) {
        // Ambil href (contoh: #layanan)
        const href = this.getAttribute('href');

        // Jika hanya # saja, skip
        if (href === '#') return;

        // Cari elemen target
        const target = document.querySelector(href);

        if (target) {
            // Cegah default behavior
            e.preventDefault();

            // Hitung posisi (kurangi tinggi navbar)
            const offsetTop = target.offsetTop - 80;

            // Scroll ke posisi
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    });
});
```

---

### LANGKAH 5.3: FAQ Accordion (Bonus)

```javascript
// ========================================
// LANGKAH 3: FAQ Accordion
// Buka/tutup jawaban FAQ
// ========================================

// Ambil semua item FAQ
const faqItems = document.querySelectorAll('.faq-item');

faqItems.forEach(item => {
    // Ambil tombol pertanyaan
    const question = item.querySelector('.faq-question');

    question.addEventListener('click', () => {
        // Tutup item lain
        faqItems.forEach(otherItem => {
            if (otherItem !== item) {
                otherItem.classList.remove('active');
            }
        });

        // Toggle item ini
        item.classList.toggle('active');
    });
});
```

---

## 6. Tips & Penjelasan

### 💡 Tips untuk Pemula

1. **Mulai dari sederhana**
   - Buat struktur HTML dulu
   - Tambahkan CSS sedikit demi sedikit
   - Test di browser setiap perubahan

2. **Gunakan Developer Tools**
   - Klik kanan → Inspect
   - Lihat console untuk error
   - Edit CSS langsung di browser untuk eksperimen

3. **Pelajari Flexbox & Grid**
   - Flexbox: untuk layout 1 dimensi (baris ATAU kolom)
   - Grid: untuk layout 2 dimensi (baris DAN kolom)

4. **Mobile First**
   - Desain untuk mobile dulu
   - Tambahkan style untuk layar besar dengan media query

### 🔑 Konsep Penting

| Konsep | Penjelasan |
|--------|------------|
| Selector | Cara memilih elemen (class, id, tag) |
| Box Model | margin, border, padding, content |
| Flexbox | Layout flexible 1D |
| Grid | Layout 2D |
| Position | static, relative, absolute, fixed |
| Z-index | Urutan tumpukan |
| Transition | Animasi perubahan properti |
| Animation | Keyframe animation |

### 📚 Sumber Belajar

- [MDN Web Docs](https://developer.mozilla.org) - Dokumentasi lengkap
- [CSS-Tricks](https://css-tricks.com) - Tutorial CSS
- [Flexbox Froggy](https://flexboxfroggy.com) - Game belajar Flexbox
- [Grid Garden](https://cssgridgarden.com) - Game belajar Grid

---

## Selamat Belajar! 🎉

Jika ada pertanyaan, jangan ragu untuk bertanya!
