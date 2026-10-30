<?php
/**
 * ========================================
 * SIPARES - Admin Manajemen Pembayaran
 * ========================================
 */
require_once __DIR__ . '/../config/init.php';

if (!Session::isAdminLoggedIn()) {
  Session::flash('warning', 'Silakan login sebagai admin');
  redirect('../login.php');
}

require_once INCLUDES_PATH . 'Admin.php';
require_once INCLUDES_PATH . 'Pembayaran.php';
require_once INCLUDES_PATH . 'Tagihan.php';

$adminData = Session::admin();
$pembayaranModel = new Pembayaran();

// Handle Aksi Konfirmasi / Tolak
$message = '';
$messageType = 'success';

if (isPost()) {
  $action = $_POST['action'] ?? '';
  $payId = (int) ($_POST['pembayaran_id'] ?? 0);

  if ($action === 'confirm' && $payId > 0) {
    $res = $pembayaranModel->confirm($payId);
    $message = $res['message'];
    $messageType = $res['success'] ? 'success' : 'danger';
  } elseif ($action === 'reject' && $payId > 0) {
    $alasan = sanitize($_POST['alasan'] ?? 'Pembayaran tidak sesuai atau bukti tidak valid');
    $res = $pembayaranModel->reject($payId, $alasan);
    $message = $res['message'];
    $messageType = $res['success'] ? 'success' : 'danger';
  }
}

// Filter & Search
$keyword = sanitize($_GET['q'] ?? '');
$filterStatus = sanitize($_GET['status'] ?? '');
$filterMetode = sanitize($_GET['metode'] ?? '');

$sql = "SELECT p.*, t.no_tagihan, t.periode_bulan, t.periode_tahun,
               u.nama_lengkap, u.no_pelanggan, u.email
        FROM pembayaran p
        JOIN tagihan t ON p.tagihan_id = t.id
        JOIN users u ON p.user_id = u.id
        WHERE 1=1";
$params = [];

if (!empty($keyword)) {
  $sql .= " AND (u.nama_lengkap LIKE ? OR u.no_pelanggan LIKE ? OR p.no_transaksi LIKE ? OR t.no_tagihan LIKE ?)";
  $kw = "%$keyword%";
  $params = array_merge($params, [$kw, $kw, $kw, $kw]);
}

if (!empty($filterStatus)) {
  $sql .= " AND p.status = ?";
  $params[] = $filterStatus;
}

if (!empty($filterMetode)) {
  $sql .= " AND p.metode_pembayaran = ?";
  $params[] = $filterMetode;
}

$sql .= " ORDER BY p.created_at DESC";
$payments = db()->fetchAll($sql, $params);

// Hitung Statistik
$pendingPayments = $pembayaranModel->count('pending');
$successPayments = $pembayaranModel->count('berhasil');
$failedPayments = $pembayaranModel->count('gagal');
$totalPayments = $pembayaranModel->count();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi Pembayaran - SIPARES Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    body { background: var(--gray-100); }
    .admin-layout { display: flex; min-height: 100vh; }
    .sidebar {
      width: 240px; background: var(--gray-900); padding: 1.5rem;
      position: fixed; height: 100vh; z-index: 100;
    }
    .sidebar-logo {
      display: flex; align-items: center; gap: 0.5rem;
      font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 2rem;
    }
    .sidebar-logo i { color: var(--primary); }
    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .sidebar-menu a {
      display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem;
      color: var(--gray-400); border-radius: var(--radius-lg); margin-bottom: 0.25rem;
      font-size: 0.9rem; text-decoration: none; transition: var(--transition);
    }
    .sidebar-menu a:hover, .sidebar-menu a.active { background: var(--primary); color: var(--white); }
    .sidebar-menu .badge-count {
      margin-left: auto; background: var(--danger); color: white;
      font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: var(--radius-full);
    }
    .sidebar-section {
      font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em;
      color: var(--gray-500); margin: 1.5rem 0 0.75rem; padding-left: 1rem;
    }
    .sidebar-footer { position: absolute; bottom: 1.5rem; left: 1.5rem; right: 1.5rem; }
    .admin-info {
      display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem;
      background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-lg);
      color: var(--white); margin-bottom: 0.75rem;
    }
    .admin-avatar {
      width: 36px; height: 36px; border-radius: 50%; background: var(--primary);
      display: flex; align-items: center; justify-content: center; font-weight: 600;
    }
    .main { flex: 1; margin-left: 240px; padding: 2rem; }
    .page-header {
      display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;
    }

    .stats {
      display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 2rem;
    }
    .stat-card {
      background: var(--white); padding: 1.25rem; border-radius: var(--radius-xl);
      box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between;
    }
    .stat-card h3 { margin: 0; font-size: 1.5rem; }
    .stat-card p { margin: 0.25rem 0 0; color: var(--text-secondary); font-size: 0.85rem; }
    .stat-card .icon {
      width: 44px; height: 44px; border-radius: var(--radius-lg);
      display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
    }

    .data-card {
      background: var(--white); border-radius: var(--radius-xl);
      padding: 1.5rem; box-shadow: var(--shadow-sm);
    }
    .filter-bar {
      display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; align-items: center;
    }
    .search-input, .select-filter {
      padding: 0.6rem 0.9rem; border: 1px solid var(--border-color);
      border-radius: var(--radius-md); font-size: 0.88rem; outline: none;
    }

    table { width: 100%; border-collapse: collapse; }
    th, td {
      padding: 0.85rem 1rem; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.88rem;
    }
    th {
      font-weight: 600; color: var(--text-secondary); background: var(--gray-50);
      text-transform: uppercase; font-size: 0.75rem;
    }
    tbody tr:hover { background: var(--gray-50); }

    .action-btn {
      width: 32px; height: 32px; border-radius: var(--radius-md); border: none;
      cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
      transition: var(--transition); text-decoration: none; font-size: 0.85rem;
    }
    .action-btn.success { background: #d1fae5; color: #059669; }
    .action-btn.danger { background: #fee2e2; color: #dc2626; }
    .action-btn.receipt { background: #e0f2fe; color: #0284c7; }
    .action-btn:hover { opacity: 0.85; }

    /* Modal */
    .modal-overlay {
      position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
      display: none; align-items: center; justify-content: center;
      z-index: 1050; padding: 1rem;
    }
    .modal-overlay.active { display: flex; }
    .modal-card {
      background: #fff; border-radius: var(--radius-xl); max-width: 460px;
      width: 100%; padding: 2rem;
    }
    .modal-header {
      display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;
    }

    @media (max-width: 992px) {
      .stats { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main { margin-left: 0; }
      .stats { grid-template-columns: 1fr; }
    }
  </style>
</head>

<body>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo"><i class="fas fa-recycle"></i> SIPARES</div>
      <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
        <li class="sidebar-section">Manajemen</li>
        <li><a href="users.php"><i class="fas fa-users"></i> Pelanggan</a></li>
        <li><a href="tagihan.php"><i class="fas fa-file-invoice"></i> Tagihan</a></li>
        <li>
          <a href="pembayaran.php" class="active"><i class="fas fa-credit-card"></i> Pembayaran
            <?php if ($pendingPayments > 0): ?><span class="badge-count"><?php echo $pendingPayments; ?></span><?php endif; ?>
          </a>
        </li>
        <li class="sidebar-section">Laporan</li>
        <li><a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
      </ul>
      <div class="sidebar-footer">
        <div class="admin-info">
          <div class="admin-avatar"><?php echo strtoupper(substr($adminData['nama_lengkap'], 0, 1)); ?></div>
          <div>
            <h4 style="margin:0;font-size:0.85rem;"><?php echo e($adminData['nama_lengkap']); ?></h4>
            <p style="margin:0;font-size:0.75rem;color:var(--gray-400);"><?php echo ucfirst($adminData['role']); ?></p>
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
        <div>
          <h1 style="margin:0 0 0.25rem;">Verifikasi & Log Pembayaran</h1>
          <p style="margin:0;color:var(--text-secondary);">Konfirmasi transaksi pembayaran retribusi dan cetak bukti digital resmi</p>
        </div>
      </div>

      <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>" style="padding:0.9rem 1.25rem;border-radius:var(--radius-lg);margin-bottom:1.5rem;background:<?php echo $messageType === 'success' ? '#d1fae5' : '#fee2e2'; ?>;color:<?php echo $messageType === 'success' ? '#065f46' : '#991b1b'; ?>;">
          <?php echo e($message); ?>
        </div>
      <?php endif; ?>

      <!-- Stats Cards -->
      <div class="stats">
        <div class="stat-card">
          <div>
            <h3><?php echo $totalPayments; ?></h3>
            <p>Total Transaksi</p>
          </div>
          <div class="icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-receipt"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $pendingPayments; ?></h3>
            <p>Perlu Konfirmasi</p>
          </div>
          <div class="icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-clock"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $successPayments; ?></h3>
            <p>Berhasil / Lunas</p>
          </div>
          <div class="icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $failedPayments; ?></h3>
            <p>Ditolak / Gagal</p>
          </div>
          <div class="icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-times-circle"></i></div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="data-card">
        <form method="GET" class="filter-bar">
          <input type="text" name="q" value="<?php echo e($keyword); ?>" placeholder="Cari nama, No. Transaksi, No. Tagihan..." class="search-input">
          <select name="status" class="select-filter">
            <option value="">Semua Status</option>
            <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Pending (Perlu Konfirmasi)</option>
            <option value="berhasil" <?php echo $filterStatus === 'berhasil' ? 'selected' : ''; ?>>Berhasil / Lunas</option>
            <option value="gagal" <?php echo $filterStatus === 'gagal' ? 'selected' : ''; ?>>Ditolak / Gagal</option>
          </select>
          <select name="metode" class="select-filter">
            <option value="">Semua Metode</option>
            <option value="qris" <?php echo $filterMetode === 'qris' ? 'selected' : ''; ?>>QRIS</option>
            <option value="transfer_bca" <?php echo $filterMetode === 'transfer_bca' ? 'selected' : ''; ?>>BCA VA</option>
            <option value="transfer_bni" <?php echo $filterMetode === 'transfer_bni' ? 'selected' : ''; ?>>BNI VA</option>
            <option value="transfer_mandiri" <?php echo $filterMetode === 'transfer_mandiri' ? 'selected' : ''; ?>>Mandiri VA</option>
            <option value="transfer_bri" <?php echo $filterMetode === 'transfer_bri' ? 'selected' : ''; ?>>BRI VA</option>
            <option value="minimarket" <?php echo $filterMetode === 'minimarket' ? 'selected' : ''; ?>>Minimarket</option>
          </select>
          <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-filter"></i> Filter</button>
          <?php if (!empty($keyword) || !empty($filterStatus) || !empty($filterMetode)): ?>
            <a href="pembayaran.php" class="btn btn-sm" style="color:var(--text-secondary);">Reset</a>
          <?php endif; ?>
        </form>

        <div style="overflow-x:auto;">
          <table>
            <thead>
              <tr>
                <th>No. Transaksi</th>
                <th>Pelanggan</th>
                <th>No. Tagihan & Periode</th>
                <th>Tanggal Bayar</th>
                <th>Metode</th>
                <th>Nominal</th>
                <th>Bukti Transfer</th>
                <th>Status</th>
                <th>Aksi Verifikasi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($payments)): ?>
                <tr>
                  <td colspan="9" style="text-align:center;padding:2.5rem;color:var(--text-secondary);">
                    Tidak ada transaksi pembayaran yang ditemukan.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($payments as $p): ?>
                  <tr>
                    <td><strong><?php echo e($p['no_transaksi']); ?></strong></td>
                    <td>
                      <strong><?php echo e($p['nama_lengkap']); ?></strong><br>
                      <small style="color:var(--text-secondary);"><?php echo e($p['no_pelanggan']); ?></small>
                    </td>
                    <td>
                      <div><?php echo e($p['no_tagihan']); ?></div>
                      <small style="color:var(--text-secondary);"><?php echo namaBulan($p['periode_bulan']) . ' ' . $p['periode_tahun']; ?></small>
                    </td>
                    <td>
                      <small><?php echo $p['tanggal_bayar'] ? tanggal_indo($p['tanggal_bayar'], 'datetime') : '-'; ?></small>
                    </td>
                    <td>
                      <span style="display:inline-flex;align-items:center;gap:0.35rem;">
                        <i class="fas <?php echo $p['metode_pembayaran'] === 'qris' ? 'fa-qrcode' : ($p['metode_pembayaran'] === 'minimarket' ? 'fa-store' : 'fa-university'); ?>"></i>
                        <?php echo metodePembayaranLabel($p['metode_pembayaran']); ?>
                      </span>
                    </td>
                    <td><strong><?php echo rupiah($p['nominal_bayar']); ?></strong></td>
                    <td>
                      <?php if (!empty($p['bukti_pembayaran'])): ?>
                        <a href="../uploads/<?php echo e($p['bukti_pembayaran']); ?>" target="_blank" class="action-btn" style="background:#eff6ff;color:#2563eb;border-color:#bfdbfe;font-size:0.75rem;padding:0.3rem 0.6rem;text-decoration:none;" title="Buka Bukti Transfer">
                          <i class="fas fa-file-image"></i> Lihat Bukti
                        </a>
                      <?php else: ?>
                        <span style="color:var(--text-secondary);font-size:0.75rem;">Belum ada</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($p['status'] === 'berhasil'): ?>
                        <span class="badge badge-success">&#10004; Berhasil</span>
                      <?php elseif ($p['status'] === 'pending'): ?>
                        <span class="badge badge-warning">&#9203; Pending</span>
                      <?php else: ?>
                        <span class="badge badge-danger">&#10008; <?php echo ucfirst($p['status']); ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div style="display:flex;gap:0.35rem;">
                        <?php if ($p['status'] === 'pending'): ?>
                          <form method="POST" onsubmit="return confirm('Konfirmasi pembayaran ini sebagai BERHASIL / LUNAS?');" style="display:inline;">
                            <input type="hidden" name="action" value="confirm">
                            <input type="hidden" name="pembayaran_id" value="<?php echo $p['id']; ?>">
                            <button type="submit" class="action-btn success" title="Konfirmasi & Setujui">
                              <i class="fas fa-check"></i>
                            </button>
                          </form>
                          <button type="button" class="action-btn danger" onclick="openRejectModal(<?php echo $p['id']; ?>, '<?php echo e($p['no_transaksi']); ?>')" title="Tolak Pembayaran">
                            <i class="fas fa-times"></i>
                          </button>
                        <?php elseif ($p['status'] === 'berhasil'): ?>
                          <a href="../bukti_digital.php?trx=<?php echo urlencode($p['no_transaksi']); ?>" target="_blank" class="action-btn receipt" title="Lihat & Cetak Bukti Digital Resmi">
                            <i class="fas fa-file-invoice"></i>
                          </a>
                        <?php else: ?>
                          <span style="color:var(--text-secondary);font-size:0.75rem;" title="<?php echo e($p['keterangan'] ?? ''); ?>">Ditolak</span>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- Modal Tolak Pembayaran -->
  <div class="modal-overlay" id="rejectModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin:0;"><i class="fas fa-times-circle" style="color:var(--danger);"></i> Tolak Pembayaran</h3>
        <button type="button" onclick="closeRejectModal()" style="border:none;background:none;font-size:1.3rem;cursor:pointer;">&times;</button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="reject">
        <input type="hidden" name="pembayaran_id" id="rejectPaymentId">
        <p style="font-size:0.9rem;color:var(--text-secondary);margin-top:0;">
          Transaksi: <strong id="rejectTrxNo">-</strong>
        </p>
        <div style="margin-bottom:1.5rem;">
          <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:0.35rem;">Alasan Penolakan *</label>
          <textarea name="alasan" required rows="3" class="form-control" style="width:100%;padding:0.65rem;border:1px solid var(--border-color);border-radius:var(--radius-md);" placeholder="Tuliskan alasan penolakan untuk pelanggan..."></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:0.75rem;">
          <button type="button" onclick="closeRejectModal()" class="btn btn-outline">Batal</button>
          <button type="submit" class="btn btn-danger" style="background:#dc2626;color:#fff;"><i class="fas fa-ban"></i> Tolak Pembayaran</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openRejectModal(id, trxNo) {
      document.getElementById('rejectPaymentId').value = id;
      document.getElementById('rejectTrxNo').textContent = trxNo;
      document.getElementById('rejectModal').classList.add('active');
    }
    function closeRejectModal() {
      document.getElementById('rejectModal').classList.remove('active');
    }
  </script>
</body>

</html>

