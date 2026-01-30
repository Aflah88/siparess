<?php
require_once __DIR__ . '/../config/init.php';
if (!Session::isLoggedIn()) {
  Session::flash('warning', 'Silakan login');
  redirect('../login.php');
}

require_once INCLUDES_PATH . 'User.php';
require_once INCLUDES_PATH . 'Tagihan.php';
require_once INCLUDES_PATH . 'Pembayaran.php';

$userData = Session::user();
$tagihanModel = new Tagihan();
$pembayaranModel = new Pembayaran();

$userId = Session::userId();
$unpaid = $tagihanModel->getUnpaidByUserId($userId);
$history = $pembayaranModel->getByUserId($userId, 5);
$totalTunggakan = array_sum(array_column($unpaid, 'total_tagihan'));
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - SIPARES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    body {
      background: var(--bg-primary);
    }

    .dashboard {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background: var(--white);
      border-right: 1px solid var(--border-color);
      padding: 1.5rem;
      position: fixed;
      height: 100vh;
    }

    .sidebar-logo {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 2rem;
    }

    .sidebar-menu {
      list-style: none;
    }

    .sidebar-menu a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      color: var(--text-secondary);
      border-radius: var(--radius-lg);
      margin-bottom: 0.25rem;
      transition: var(--transition);
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
      background: var(--primary);
      color: var(--white);
    }

    .sidebar-footer {
      position: absolute;
      bottom: 1.5rem;
      left: 1.5rem;
      right: 1.5rem;
    }

    /* Main */
    .main {
      flex: 1;
      margin-left: 250px;
      padding: 2rem;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .page-header h1 {
      font-size: 1.5rem;
    }

    .page-header p {
      color: var(--text-secondary);
    }

    /* Stats */
    .stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: var(--white);
      padding: 1.5rem;
      border-radius: var(--radius-xl);
      border: 1px solid var(--border-color);
    }

    .stat-card .icon {
      width: 48px;
      height: 48px;
      border-radius: var(--radius-lg);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      margin-bottom: 1rem;
    }

    .stat-card .icon.primary {
      background: var(--primary-light);
      color: var(--primary);
    }

    .stat-card .icon.warning {
      background: #fef3c7;
      color: #d97706;
    }

    .stat-card .icon.success {
      background: #d1fae5;
      color: #059669;
    }

    .stat-card h3 {
      font-size: 1.75rem;
      margin-bottom: 0.25rem;
    }

    .stat-card p {
      color: var(--text-secondary);
      font-size: 0.875rem;
    }

    /* Grid */
    .content-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }

    .content-card {
      background: var(--white);
      border-radius: var(--radius-xl);
      padding: 1.5rem;
      border: 1px solid var(--border-color);
    }

    .card-title {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    /* List items */
    .list-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      background: var(--gray-50);
      border-radius: var(--radius-lg);
      margin-bottom: 0.75rem;
    }

    .list-item:last-child {
      margin-bottom: 0;
    }

    .list-item h4 {
      font-size: 0.9rem;
      margin-bottom: 0.25rem;
    }

    .list-item small {
      color: var(--text-secondary);
      font-size: 0.8rem;
    }

    .list-item .amount {
      font-weight: 600;
      color: var(--primary);
    }

    /* Quick actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }

    .quick-btn {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
      padding: 1.5rem;
      background: var(--gray-50);
      border-radius: var(--radius-lg);
      color: var(--text-primary);
      transition: var(--transition);
    }

    .quick-btn:hover {
      background: var(--primary);
      color: var(--white);
    }

    .quick-btn i {
      font-size: 1.5rem;
    }

    .quick-btn span {
      font-size: 0.85rem;
      font-weight: 500;
    }

    .empty {
      text-align: center;
      padding: 2rem;
      color: var(--text-secondary);
    }

    .empty i {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      opacity: 0.5;
    }

    @media (max-width: 992px) {
      .stats {
        grid-template-columns: 1fr;
      }

      .content-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }

      .main {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <i class="fas fa-recycle"></i> SIPARES
      </div>
      <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="tagihan.php"><i class="fas fa-file-invoice"></i> Tagihan</a></li>
        <li><a href="riwayat.php"><i class="fas fa-history"></i> Riwayat</a></li>
        <li><a href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
        <li><a href="../edukasi.html"><i class="fas fa-book"></i> Edukasi</a></li>
      </ul>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline btn-block"><i class="fas fa-sign-out-alt"></i> Keluar</a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main">
      <div class="page-header">
        <div>
          <h1>Halo, <?php echo e($userData['nama_lengkap']); ?>! 👋</h1>
          <p>No. Pelanggan: <strong><?php echo e($userData['no_pelanggan']); ?></strong></p>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats">
        <div class="stat-card">
          <div class="icon warning"><i class="fas fa-file-invoice-dollar"></i></div>
          <h3><?php echo count($unpaid); ?></h3>
          <p>Tagihan Belum Bayar</p>
        </div>
        <div class="stat-card">
          <div class="icon primary"><i class="fas fa-money-bill-wave"></i></div>
          <h3><?php echo rupiah($totalTunggakan); ?></h3>
          <p>Total Tunggakan</p>
        </div>
        <div class="stat-card">
          <div class="icon success"><i class="fas fa-tag"></i></div>
          <h3><?php echo kategoriLabel($userData['kategori']); ?></h3>
          <p>Kategori Pelanggan</p>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="content-grid">
        <!-- Tagihan -->
        <div class="content-card">
          <h2 class="card-title"><i class="fas fa-file-invoice"></i> Tagihan Terbaru</h2>
          <?php if (empty($unpaid)): ?>
            <div class="empty">
              <i class="fas fa-check-circle"></i>
              <p>Tidak ada tagihan belum dibayar</p>
            </div>
          <?php else: ?>
            <?php foreach (array_slice($unpaid, 0, 3) as $t): ?>
              <div class="list-item">
                <div>
                  <h4><?php echo namaBulan($t['periode_bulan']) . ' ' . $t['periode_tahun']; ?></h4>
                  <small>Jatuh tempo: <?php echo tanggal_indo($t['tanggal_jatuh_tempo'], 'short'); ?></small>
                </div>
                <span class="amount"><?php echo rupiah($t['total_tagihan']); ?></span>
              </div>
            <?php endforeach; ?>
            <a href="tagihan.php" class="btn btn-primary btn-block mt-2"><i class="fas fa-credit-card"></i> Bayar
              Sekarang</a>
          <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="content-card">
          <h2 class="card-title"><i class="fas fa-bolt"></i> Aksi Cepat</h2>
          <div class="quick-actions">
            <a href="tagihan.php" class="quick-btn">
              <i class="fas fa-credit-card"></i>
              <span>Bayar Tagihan</span>
            </a>
            <a href="riwayat.php" class="quick-btn">
              <i class="fas fa-history"></i>
              <span>Riwayat</span>
            </a>
            <a href="profil.php" class="quick-btn">
              <i class="fas fa-user-edit"></i>
              <span>Edit Profil</span>
            </a>
            <a href="../edukasi.html" class="quick-btn">
              <i class="fas fa-book"></i>
              <span>Edukasi</span>
            </a>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>

</html>
