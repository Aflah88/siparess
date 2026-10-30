<?php
/**
 * ========================================
 * SIPARES - Halaman Riwayat Transaksi User
 * ========================================
 */
require_once __DIR__ . '/../config/init.php';

if (!Session::isLoggedIn()) {
  Session::flash('warning', 'Silakan login terlebih dahulu');
  redirect('../login.php');
}

require_once INCLUDES_PATH . 'User.php';
require_once INCLUDES_PATH . 'Tagihan.php';
require_once INCLUDES_PATH . 'Pembayaran.php';
require_once INCLUDES_PATH . 'Notifikasi.php';

$userData = Session::user();
$userId = Session::userId();

$pembayaranModel = new Pembayaran();
$notifModel = new Notifikasi();

// Pengecekan otomatis notifikasi tagihan/jatuh tempo
$notifModel->triggerAutomatedNotifications($userId);
$unreadNotifCount = $notifModel->countUnread($userId);

// Ambil riwayat pembayaran user
$payments = $pembayaranModel->getByUserId($userId, 50);

// Hitung total bayar
$totalBayar = 0;
foreach ($payments as $p) {
  if ($p['status'] === 'berhasil') {
    $totalBayar += $p['nominal_bayar'];
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Pembayaran - SIPARES</title>
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
      padding: 0;
      margin: 0;
    }

    .sidebar-menu a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      color: var(--text-secondary);
      border-radius: var(--radius-lg);
      margin-bottom: 0.25rem;
      text-decoration: none;
      font-weight: 500;
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

    .header-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .stats-row {
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
      display: flex;
      align-items: center;
      gap: 1.25rem;
    }

    .stat-card .icon {
      width: 48px;
      height: 48px;
      border-radius: var(--radius-lg);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
    }

    .stat-card .icon.success {
      background: #d1fae5;
      color: #059669;
    }

    .stat-card .icon.primary {
      background: var(--primary-light);
      color: var(--primary);
    }

    .stat-card .icon.warning {
      background: #fef3c7;
      color: #d97706;
    }

    .card-table {
      background: var(--white);
      border-radius: var(--radius-xl);
      padding: 1.5rem;
      border: 1px solid var(--border-color);
    }

    .card-title {
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 0.9rem 1rem;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
      font-size: 0.88rem;
    }

    th {
      font-weight: 600;
      color: var(--text-secondary);
      background: var(--gray-50);
      text-transform: uppercase;
      font-size: 0.78rem;
    }

    tbody tr:hover {
      background: var(--gray-50);
    }

    .empty {
      text-align: center;
      padding: 3rem 1rem;
      color: var(--text-secondary);
    }

    .empty i {
      font-size: 2.5rem;
      margin-bottom: 0.75rem;
      opacity: 0.4;
    }

    @media (max-width: 992px) {
      .stats-row {
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
      .table-responsive {
        overflow-x: auto;
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
        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="tagihan.php"><i class="fas fa-file-invoice"></i> Tagihan</a></li>
        <li><a href="riwayat.php" class="active"><i class="fas fa-history"></i> Riwayat</a></li>
        <li><a href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
        <li><a href="edukasi.php"><i class="fas fa-book"></i> Edukasi</a></li>
      </ul>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline btn-block"><i class="fas fa-sign-out-alt"></i> Keluar</a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main">
      <div class="page-header">
        <div>
          <h1>Riwayat Pembayaran</h1>
          <p>Daftar transaksi dan unduh bukti digital resmi retribusi Anda</p>
        </div>
        <div class="header-actions">
          <!-- Notification Bell -->
          <button class="btn-notification" id="btn-notification" title="Notifikasi">
            <i class="fas fa-bell"></i>
            <?php if ($unreadNotifCount > 0): ?>
              <span class="notification-badge"><?php echo $unreadNotifCount; ?></span>
            <?php endif; ?>
          </button>
        </div>
      </div>

      <!-- Stats Summary -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="icon success"><i class="fas fa-check-circle"></i></div>
          <div>
            <h3 style="margin:0;"><?php echo count($payments); ?></h3>
            <p style="margin:0.25rem 0 0;color:var(--text-secondary);font-size:0.85rem;">Total Transaksi</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="icon primary"><i class="fas fa-money-bill-wave"></i></div>
          <div>
            <h3 style="margin:0;"><?php echo rupiah($totalBayar); ?></h3>
            <p style="margin:0.25rem 0 0;color:var(--text-secondary);font-size:0.85rem;">Total Dana Terbayar</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="icon warning"><i class="fas fa-certificate"></i></div>
          <div>
            <h3 style="margin:0;">100% Sah</h3>
            <p style="margin:0.25rem 0 0;color:var(--text-secondary);font-size:0.85rem;">Validitas e-Kwitansi</p>
          </div>
        </div>
      </div>

      <!-- Payment History Table -->
      <div class="card-table">
        <h2 class="card-title"><i class="fas fa-history"></i> Daftar Transaksi & Bukti Digital</h2>
        
        <?php if (empty($payments)): ?>
          <div class="empty">
            <i class="fas fa-receipt"></i>
            <p>Belum ada riwayat transaksi pembayaran.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>No. Transaksi</th>
                  <th>Periode</th>
                  <th>Tanggal Bayar</th>
                  <th>Metode</th>
                  <th>Nominal</th>
                  <th>Status</th>
                  <th>Bukti Digital</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($payments as $p): ?>
                  <tr>
                    <td><strong><?php echo e($p['no_transaksi']); ?></strong></td>
                    <td><?php echo namaBulan($p['periode_bulan']) . ' ' . $p['periode_tahun']; ?></td>
                    <td><?php echo $p['tanggal_bayar'] ? tanggal_indo($p['tanggal_bayar'], 'datetime') : '-'; ?></td>
                    <td>
                      <span style="display:inline-flex;align-items:center;gap:0.35rem;">
                        <i class="fas <?php echo $p['metode_pembayaran'] === 'qris' ? 'fa-qrcode' : 'fa-university'; ?>"></i>
                        <?php echo metodePembayaranLabel($p['metode_pembayaran']); ?>
                      </span>
                    </td>
                    <td><strong><?php echo rupiah($p['nominal_bayar']); ?></strong></td>
                    <td>
                      <?php if ($p['status'] === 'berhasil'): ?>
                        <span class="badge badge-success"><i class="fas fa-check"></i> Lunas</span>
                      <?php elseif ($p['status'] === 'pending'): ?>
                        <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                      <?php else: ?>
                        <span class="badge badge-danger"><?php echo ucfirst($p['status']); ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($p['status'] === 'berhasil'): ?>
                        <a href="../bukti_digital.php?trx=<?php echo urlencode($p['no_transaksi']); ?>" target="_blank" class="btn-receipt">
                          <i class="fas fa-file-invoice"></i> Lihat & Cetak Bukti
                        </a>
                      <?php else: ?>
                        <span style="color:var(--text-secondary);font-size:0.8rem;">Menunggu lunas</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script src="../js/main.js"></script>
</body>

</html>

