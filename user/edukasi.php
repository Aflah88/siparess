<?php
/**
 * ========================================
 * SIPARES - Halaman Edukasi Pengguna
 * ========================================
 */
require_once __DIR__ . '/../config/init.php';

$isLoggedIn = Session::isLoggedIn();
$userData = $isLoggedIn ? Session::user() : null;
$userId = $isLoggedIn ? Session::userId() : null;

$unreadNotifCount = 0;
if ($isLoggedIn) {
  require_once INCLUDES_PATH . 'Notifikasi.php';
  $notifModel = new Notifikasi();
  $notifModel->triggerAutomatedNotifications($userId);
  $unreadNotifCount = $notifModel->countUnread($userId);
}

// Filter kategori
$kategoriFilter = sanitize($_GET['kategori'] ?? '');
$searchKeyword = sanitize($_GET['q'] ?? '');

$sql = "SELECT * FROM artikel WHERE is_published = 1";
$params = [];

if (!empty($kategoriFilter)) {
  $sql .= " AND kategori = ?";
  $params[] = $kategoriFilter;
}

if (!empty($searchKeyword)) {
  $sql .= " AND (judul LIKE ? OR konten LIKE ? OR ringkasan LIKE ?)";
  $kw = "%$searchKeyword%";
  $params[] = $kw;
  $params[] = $kw;
  $params[] = $kw;
}

$sql .= " ORDER BY id ASC";
$articles = db()->fetchAll($sql, $params);

// Detail artikel tunggal jika dipilih
$detailArticle = null;
$articleId = (int) ($_GET['id'] ?? 0);
if ($articleId > 0) {
  $detailArticle = db()->fetchOne("SELECT * FROM artikel WHERE id = ? AND is_published = 1", [$articleId]);
  if ($detailArticle) {
    db()->query("UPDATE artikel SET views = views + 1 WHERE id = ?", [$articleId]);
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $detailArticle ? e($detailArticle['judul']) . ' - Edukasi' : 'Pusat Edukasi Sampah - SIPARES'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    body { background: var(--bg-primary); }
    .dashboard { display: flex; min-height: 100vh; }
    
    .sidebar {
      width: 250px; background: var(--white);
      border-right: 1px solid var(--border-color);
      padding: 1.5rem; position: fixed; height: 100vh; z-index: 100;
    }
    .sidebar-logo {
      display: flex; align-items: center; gap: 0.5rem;
      font-size: 1.25rem; font-weight: 700; color: var(--primary);
      margin-bottom: 2rem;
    }
    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .sidebar-menu a {
      display: flex; align-items: center; gap: 0.75rem;
      padding: 0.75rem 1rem; color: var(--text-secondary);
      border-radius: var(--radius-lg); margin-bottom: 0.25rem;
      text-decoration: none; font-weight: 500; transition: var(--transition);
    }
    .sidebar-menu a:hover, .sidebar-menu a.active {
      background: var(--primary); color: var(--white);
    }
    .sidebar-footer { position: absolute; bottom: 1.5rem; left: 1.5rem; right: 1.5rem; }

    .main { flex: 1; margin-left: 250px; padding: 2rem; }

    .page-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 2rem;
    }

    /* Kategori Badges */
    .category-filter {
      display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;
    }
    .cat-pill {
      padding: 0.5rem 1.2rem; border-radius: 999px;
      font-size: 0.85rem; font-weight: 600; text-decoration: none;
      background: var(--white); border: 1px solid var(--border-color);
      color: var(--text-secondary); transition: all 0.2s;
    }
    .cat-pill:hover, .cat-pill.active {
      background: var(--primary); color: var(--white);
      border-color: var(--primary);
    }

    /* Grid Artikel */
    .articles-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 1.5rem;
    }
    .article-card {
      background: var(--white); border-radius: var(--radius-xl);
      border: 1px solid var(--border-color); overflow: hidden;
      display: flex; flex-direction: column; transition: all 0.25s ease;
      box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .article-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px -10px rgba(0,0,0,0.1);
      border-color: var(--primary-light);
    }
    .article-header {
      padding: 1.25rem; background: var(--gray-50);
      border-bottom: 1px solid var(--border-color);
      display: flex; align-items: center; justify-content: space-between;
    }
    .badge-cat {
      display: inline-block; padding: 0.25rem 0.75rem;
      border-radius: 999px; font-size: 0.75rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.5px;
    }
    .cat-organik { background: #dcfce7; color: #15803d; }
    .cat-anorganik { background: #dbeafe; color: #1e40af; }
    .cat-b3 { background: #fee2e2; color: #b91c1c; }
    .cat-residu { background: #fef3c7; color: #b45309; }
    .cat-tips { background: #f3e8ff; color: #7e22ce; }
    .cat-umum { background: #e2e8f0; color: #334155; }

    .article-body {
      padding: 1.5rem; flex: 1; display: flex; flex-direction: column;
    }
    .article-title {
      font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;
      color: var(--text-primary); line-height: 1.4;
    }
    .article-summary {
      color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6;
      flex: 1; margin-bottom: 1.25rem;
    }
    .article-footer {
      display: flex; justify-content: space-between; align-items: center;
      padding-top: 1rem; border-top: 1px solid var(--gray-100);
      font-size: 0.8rem; color: var(--text-secondary);
    }

    /* Detail View */
    .detail-container {
      background: var(--white); border-radius: var(--radius-xl);
      border: 1px solid var(--border-color); padding: 2.5rem;
      max-width: 860px; margin: 0 auto;
    }
    .detail-content {
      font-size: 1.05rem; line-height: 1.8; color: var(--text-primary);
      margin-top: 2rem;
    }
    .detail-content h1, .detail-content h2, .detail-content h3 {
      margin-top: 1.5rem; margin-bottom: 0.75rem; color: var(--text-primary);
    }
    .detail-content ul, .detail-content ol {
      padding-left: 1.5rem; margin-bottom: 1.25rem;
    }
    .detail-content li { margin-bottom: 0.5rem; }

    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main { margin-left: 0; padding: 1.25rem; }
      .articles-grid { grid-template-columns: 1fr; }
      .detail-container { padding: 1.5rem; }
    }
  </style>
</head>

<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <a href="../index.php" style="display:flex;align-items:center;gap:0.5rem;color:var(--primary);text-decoration:none;">
          <i class="fas fa-recycle"></i> SIPARES
        </a>
      </div>
      <ul class="sidebar-menu">
        <li><a href="../index.php"><i class="fas fa-globe"></i> Beranda Utama</a></li>
        <?php if ($isLoggedIn): ?>
          <li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
          <li><a href="tagihan.php"><i class="fas fa-file-invoice"></i> Tagihan</a></li>
          <li><a href="riwayat.php"><i class="fas fa-history"></i> Riwayat</a></li>
          <li><a href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
        <?php endif; ?>
        <li><a href="edukasi.php" class="active"><i class="fas fa-book"></i> Edukasi</a></li>
      </ul>
      <div class="sidebar-footer">
        <?php if ($isLoggedIn): ?>
          <a href="logout.php" class="btn btn-outline btn-block"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        <?php else: ?>
          <a href="../login.php" class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt"></i> Masuk</a>
        <?php endif; ?>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main">
      <div class="page-header">
        <div>
          <h1><i class="fas fa-leaf" style="color:var(--primary);"></i> Pusat Edukasi & Pengelolaan Sampah</h1>
          <p>Tingkatkan wawasan memilah, mendaur ulang, dan merawat lingkungan demi kota yang asri dan bersih</p>
        </div>
        <div>
          <?php if ($isLoggedIn): ?>
            <button class="btn-notification" id="btn-notification" title="Notifikasi">
              <i class="fas fa-bell"></i>
              <?php if ($unreadNotifCount > 0): ?>
                <span class="notification-badge"><?php echo $unreadNotifCount; ?></span>
              <?php endif; ?>
            </button>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($detailArticle): ?>
        <!-- Tampilan Detail Artikel -->
        <div class="detail-container">
          <a href="edukasi.php" class="btn btn-sm btn-outline" style="margin-bottom:1.5rem;">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Artikel
          </a>
          <div style="margin-bottom:1rem;">
            <span class="badge-cat cat-<?php echo e($detailArticle['kategori']); ?>">
              <?php echo strtoupper(e($detailArticle['kategori'])); ?>
            </span>
            <span style="color:var(--text-secondary);font-size:0.85rem;margin-left:0.75rem;">
              <i class="far fa-calendar-alt"></i> <?php echo tanggal_indo($detailArticle['created_at'], 'short'); ?>
            </span>
            <span style="color:var(--text-secondary);font-size:0.85rem;margin-left:0.75rem;">
              <i class="far fa-eye"></i> <?php echo (int)$detailArticle['views']; ?>x dibaca
            </span>
          </div>

          <h1 style="font-size:1.8rem;line-height:1.3;margin-bottom:1rem;color:var(--text-primary);">
            <?php echo e($detailArticle['judul']); ?>
          </h1>

          <div style="background:var(--primary-light);padding:1rem 1.25rem;border-radius:var(--radius-lg);border-left:4px solid var(--primary);margin-bottom:1.5rem;font-size:0.95rem;color:var(--gray-800);">
            <strong>Ringkasan:</strong> <?php echo nl2br(e($detailArticle['ringkasan'])); ?>
          </div>

          <div class="detail-content">
            <?php echo nl2br(e($detailArticle['konten'])); ?>
          </div>

          <div style="margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;">
            <a href="edukasi.php" class="btn btn-primary">
              <i class="fas fa-arrow-left"></i> Lihat Panduan Lainnya
            </a>
            <a href="tagihan.php" class="btn btn-outline">
              <i class="fas fa-file-invoice"></i> Cek Tagihan Saya
            </a>
          </div>
        </div>

      <?php else: ?>
        <!-- Filter & Search Bar -->
        <div style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
          <div class="category-filter" style="margin-bottom:0;">
            <a href="edukasi.php" class="cat-pill <?php echo empty($kategoriFilter) ? 'active' : ''; ?>">Semua Kategori</a>
            <a href="edukasi.php?kategori=organik" class="cat-pill <?php echo $kategoriFilter === 'organik' ? 'active' : ''; ?>"><i class="fas fa-apple-alt"></i> Organik</a>
            <a href="edukasi.php?kategori=anorganik" class="cat-pill <?php echo $kategoriFilter === 'anorganik' ? 'active' : ''; ?>"><i class="fas fa-bottle-water"></i> Anorganik</a>
            <a href="edukasi.php?kategori=b3" class="cat-pill <?php echo $kategoriFilter === 'b3' ? 'active' : ''; ?>"><i class="fas fa-biohazard"></i> B3</a>
            <a href="edukasi.php?kategori=residu" class="cat-pill <?php echo $kategoriFilter === 'residu' ? 'active' : ''; ?>"><i class="fas fa-trash-alt"></i> Residu</a>
            <a href="edukasi.php?kategori=tips" class="cat-pill <?php echo $kategoriFilter === 'tips' ? 'active' : ''; ?>"><i class="fas fa-lightbulb"></i> Tips & Bank Sampah</a>
          </div>

          <form method="GET" action="edukasi.php" style="display:flex;gap:0.5rem;">
            <?php if (!empty($kategoriFilter)): ?>
              <input type="hidden" name="kategori" value="<?php echo e($kategoriFilter); ?>">
            <?php endif; ?>
            <input type="text" name="q" value="<?php echo e($searchKeyword); ?>" placeholder="Cari materi edukasi..." style="padding:0.5rem 1rem;border:1px solid var(--border-color);border-radius:var(--radius-lg);font-size:0.9rem;">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
          </form>
        </div>

        <!-- Banner Kampanye Lingkungan -->
        <div style="background:linear-gradient(135deg, #059669, #10b981);color:white;padding:1.5rem 2rem;border-radius:var(--radius-xl);margin-bottom:2rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
          <div>
            <h3 style="margin:0 0 0.5rem;font-size:1.3rem;">Gerakan Pilah Sampah dari Rumah</h3>
            <p style="margin:0;opacity:0.9;font-size:0.95rem;">Kurangi sampah residu ke TPA dengan memilah sampah organik dan mendaur ulang botol plastik Anda hari ini.</p>
          </div>
          <a href="tagihan.php" class="btn btn-outline" style="background:white;color:#059669;border-color:white;font-weight:700;">
            <i class="fas fa-file-invoice"></i> Bayar Retribusi
          </a>
        </div>

        <!-- Articles Grid -->
        <?php if (empty($articles)): ?>
          <div style="background:white;border-radius:var(--radius-xl);padding:3rem 1rem;text-align:center;border:1px solid var(--border-color);">
            <i class="fas fa-book-open" style="font-size:3rem;color:var(--text-secondary);opacity:0.4;margin-bottom:1rem;"></i>
            <h3>Tidak Ada Artikel yang Ditemukan</h3>
            <p style="color:var(--text-secondary);">Silakan pilih kategori lain atau hapus kata kunci pencarian.</p>
            <a href="edukasi.php" class="btn btn-outline btn-sm" style="margin-top:1rem;">Reset Filter</a>
          </div>
        <?php else: ?>
          <div class="articles-grid">
            <?php foreach ($articles as $art): ?>
              <article class="article-card">
                <div class="article-header">
                  <span class="badge-cat cat-<?php echo e($art['kategori']); ?>">
                    <?php echo strtoupper(e($art['kategori'])); ?>
                  </span>
                  <span style="font-size:0.8rem;color:var(--text-secondary);">
                    <i class="far fa-eye"></i> <?php echo (int)$art['views']; ?> views
                  </span>
                </div>
                <div class="article-body">
                  <h2 class="article-title"><?php echo e($art['judul']); ?></h2>
                  <p class="article-summary"><?php echo e($art['ringkasan']); ?></p>
                  <div class="article-footer">
                    <span><i class="far fa-calendar-alt"></i> <?php echo tanggal_indo($art['created_at'], 'short'); ?></span>
                    <a href="edukasi.php?id=<?php echo $art['id']; ?>" class="btn btn-sm btn-outline">
                      Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                    </a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    </main>
  </div>

  <script src="../js/main.js"></script>
</body>

</html>
