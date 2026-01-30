<?php
require_once __DIR__ . '/../config/init.php';
if (!Session::isAdminLoggedIn()) {
  Session::flash('warning', 'Silakan login sebagai admin');
  redirect('../login.php');
}

require_once INCLUDES_PATH . 'Admin.php';
require_once INCLUDES_PATH . 'User.php';
require_once INCLUDES_PATH . 'Tagihan.php';
require_once INCLUDES_PATH . 'Pembayaran.php';

$adminData = Session::admin();
$totalUsers = (new User())->count();
$tagihanStats = (new Tagihan())->getStatistics();
$pembayaranStats = (new Pembayaran())->getStatistics();
$pendingPayments = (new Pembayaran())->count('pending');
$recentPayments = (new Pembayaran())->getRecent(8);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - SIPARES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    body {
      background: var(--gray-100);
    }

    .admin-layout {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: var(--gray-900);
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
      color: var(--white);
      margin-bottom: 2rem;
    }

    .sidebar-logo i {
      color: var(--primary);
    }

    .sidebar-menu {
      list-style: none;
    }

    .sidebar-menu a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      color: var(--gray-400);
      border-radius: var(--radius-lg);
      margin-bottom: 0.25rem;
      font-size: 0.9rem;
      transition: var(--transition);
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
      background: var(--primary);
      color: var(--white);
    }

    .sidebar-menu .badge-count {
      margin-left: auto;
      background: var(--danger);
      color: white;
      font-size: 0.7rem;
      padding: 0.15rem 0.5rem;
      border-radius: var(--radius-full);
    }

    .sidebar-section {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--gray-500);
      margin: 1.5rem 0 0.75rem;
      padding-left: 1rem;
    }

    .sidebar-footer {
      position: absolute;
      bottom: 1.5rem;
      left: 1.5rem;
      right: 1.5rem;
    }

    .admin-info {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem;
      background: rgba(255, 255, 255, 0.05);
      border-radius: var(--radius-lg);
      color: var(--white);
      margin-bottom: 0.75rem;
    }

    .admin-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }

    .admin-info h4 {
      font-size: 0.85rem;
    }

    .admin-info p {
      font-size: 0.75rem;
      color: var(--gray-400);
    }

    /* Main */
    .main {
      flex: 1;
      margin-left: 240px;
      padding: 2rem;
    }

    .page-header {
      margin-bottom: 2rem;
    }

    .page-header h1 {
      font-size: 1.5rem;
      margin-bottom: 0.25rem;
    }

    .page-header p {
      color: var(--text-secondary);
    }

    /* Stats */
    .stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: var(--white);
      padding: 1.5rem;
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-sm);
    }

    .stat-card .header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 0.5rem;
    }

    .stat-card .icon {
      width: 42px;
      height: 42px;
      border-radius: var(--radius-lg);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .stat-card h3 {
      font-size: 1.5rem;
    }

    .stat-card p {
      color: var(--text-secondary);
      font-size: 0.85rem;
    }

    /* Table */
    .data-card {
      background: var(--white);
      border-radius: var(--radius-xl);
      padding: 1.5rem;
      box-shadow: var(--shadow-sm);
    }

    .data-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .data-card-header h2 {
      font-size: 1rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      padding: 0.75rem;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }

    th {
      font-weight: 600;
      color: var(--text-secondary);
      font-size: 0.8rem;
      text-transform: uppercase;
    }

    tbody tr:hover {
      background: var(--gray-50);
    }

    .user-cell {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .user-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--primary);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 0.8rem;
    }

    .user-cell h4 {
      font-size: 0.9rem;
    }

    .user-cell small {
      color: var(--text-secondary);
      font-size: 0.75rem;
    }

    .action-btn {
      width: 28px;
      height: 28px;
      border-radius: var(--radius-md);
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-right: 0.25rem;
      transition: var(--transition);
    }

    .action-btn.success {
      background: #d1fae5;
      color: #059669;
    }

    .action-btn.danger {
      background: #fee2e2;
      color: #dc2626;
    }

    .action-btn:hover {
      opacity: 0.8;
    }

    @media (max-width: 1200px) {
      .stats {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }

      .main {
        margin-left: 0;
      }

      .stats {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo"><i class="fas fa-recycle"></i> SIPARES</div>
      <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
        <li class="sidebar-section">Manajemen</li>
        <li><a href="users.php"><i class="fas fa-users"></i> Pelanggan</a></li>
        <li><a href="tagihan.php"><i class="fas fa-file-invoice"></i> Tagihan</a></li>
        <li>
          <a href="pembayaran.php"><i class="fas fa-credit-card"></i> Pembayaran
            <?php if ($pendingPayments > 0): ?><span
                class="badge-count"><?php echo $pendingPayments; ?></span><?php endif; ?>
          </a>
        </li>
        <li class="sidebar-section">Laporan</li>
        <li><a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
      </ul>
      <div class="sidebar-footer">
        <div class="admin-info">
          <div class="admin-avatar"><?php echo strtoupper(substr($adminData['nama_lengkap'], 0, 1)); ?></div>
          <div>
            <h4><?php echo e($adminData['nama_lengkap']); ?></h4>
            <p><?php echo ucfirst($adminData['role']); ?></p>
          </div>
        </div>
        <a href="logout.php" class="btn btn-outline btn-block" style="border-color:rgba(255,255,255,0.2);color:white;">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main">
      <div class="page-header">
        <h1>Dashboard</h1>
        <p><?php echo tanggal_indo(date('Y-m-d'), 'full'); ?></p>
      </div>

      <!-- Stats -->
      <div class="stats">
        <div class="stat-card">
          <div class="header">
            <div>
              <h3><?php echo $totalUsers; ?></h3>
              <p>Total Pelanggan</p>
            </div>
            <div class="icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-users"></i></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="header">
            <div>
              <h3><?php echo rupiah($pembayaranStats['bulan_ini']['nominal_total'] ?? 0); ?></h3>
              <p>Pendapatan Bulan Ini</p>
            </div>
            <div class="icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-wallet"></i></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="header">
            <div>
              <h3><?php echo $pendingPayments; ?></h3>
              <p>Pending Konfirmasi</p>
            </div>
            <div class="icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-clock"></i></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="header">
            <div>
              <h3><?php echo rupiah($tagihanStats['belum_bayar']['nominal_total'] ?? 0); ?></h3>
              <p>Total Tunggakan</p>
            </div>
            <div class="icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-exclamation-circle"></i></div>
          </div>
        </div>
      </div>

      <!-- Recent Payments -->
      <div class="data-card">
        <div class="data-card-header">
          <h2><i class="fas fa-credit-card"></i> Pembayaran Terbaru</h2>
          <a href="pembayaran.php" class="btn btn-sm">Lihat Semua</a>
        </div>
        <table>
          <thead>
            <tr>
              <th>Pelanggan</th>
              <th>No. Transaksi</th>
              <th>Nominal</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentPayments)): ?>
              <tr>
                <td colspan="5" style="text-align:center;padding:2rem;">Belum ada data</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentPayments as $p): ?>
                <tr>
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar"><?php echo strtoupper(substr($p['nama_lengkap'], 0, 1)); ?></div>
                      <div>
                        <h4><?php echo e($p['nama_lengkap']); ?></h4>
                        <small><?php echo e($p['no_pelanggan']); ?></small>
                      </div>
                    </div>
                  </td>
                  <td><?php echo e($p['no_transaksi']); ?></td>
                  <td><strong><?php echo rupiah($p['nominal_bayar']); ?></strong></td>
                  <td>
                    <span
                      class="badge badge-<?php echo $p['status'] === 'berhasil' ? 'success' : ($p['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                      <?php echo ucfirst($p['status']); ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($p['status'] === 'pending'): ?>
                      <button class="action-btn success" onclick="confirmPay(<?php echo $p['id']; ?>)" title="Konfirmasi"><i
                          class="fas fa-check"></i></button>
                      <button class="action-btn danger" onclick="rejectPay(<?php echo $p['id']; ?>)" title="Tolak"><i
                          class="fas fa-times"></i></button>
                    <?php else: ?>
                      <span style="color:var(--text-secondary);font-size:0.8rem;">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <script>
    function confirmPay(id) {
      if (confirm('Konfirmasi pembayaran ini?')) {
        fetch('../api/admin.php?action=confirm_payment', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id })
        }).then(r => r.json()).then(d => { alert(d.message); if (d.success) location.reload(); });
      }
    }
    function rejectPay(id) {
      const alasan = prompt('Alasan penolakan:');
      if (alasan) {
        fetch('../api/admin.php?action=reject_payment', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, alasan: alasan })
        }).then(r => r.json()).then(d => { alert(d.message); if (d.success) location.reload(); });
      }
    }
  </script>
</body>

</html>
