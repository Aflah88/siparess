<?php
/**
 * ========================================
 * SIPARES - Admin Manajemen Tagihan
 * ========================================
 */
require_once __DIR__ . '/../config/init.php';

if (!Session::isAdminLoggedIn()) {
  Session::flash('warning', 'Silakan login sebagai admin');
  redirect('../login.php');
}

require_once INCLUDES_PATH . 'Admin.php';
require_once INCLUDES_PATH . 'Tagihan.php';
require_once INCLUDES_PATH . 'Pembayaran.php';
require_once INCLUDES_PATH . 'Notifikasi.php';

$adminData = Session::admin();
$tagihanModel = new Tagihan();
$pembayaranModel = new Pembayaran();
$pendingPayments = $pembayaranModel->count('pending');

// Handle Action (Bulk Create, Calculate Late Fees, Update Status)
$message = '';
$messageType = 'success';

if (isPost()) {
  $action = $_POST['action'] ?? '';

  if ($action === 'create_bulk') {
    $bulan = (int) ($_POST['bulan'] ?? date('n'));
    $tahun = (int) ($_POST['tahun'] ?? date('Y'));
    $res = $tagihanModel->createBulk($bulan, $tahun);
    $message = $res['message'];
    $messageType = 'success';
  } elseif ($action === 'calculate_late_fees') {
    $res = $tagihanModel->calculateLateFees();
    $message = $res['message'];
    $messageType = 'success';
  } elseif ($action === 'update_status') {
    $id = (int) ($_POST['tagihan_id'] ?? 0);
    $newStatus = sanitize($_POST['status'] ?? '');
    $res = $tagihanModel->updateStatus($id, $newStatus);
    $message = $res['message'];
    $messageType = $res['success'] ? 'success' : 'danger';
  }
}

// Filter & Search
$keyword = sanitize($_GET['q'] ?? '');
$filterStatus = sanitize($_GET['status'] ?? '');
$filterBulan = (int) ($_GET['bulan'] ?? 0);
$filterTahun = (int) ($_GET['tahun'] ?? 0);

$sql = "SELECT t.*, u.nama_lengkap, u.no_pelanggan, u.kategori as kategori_user,
               tr.nama_tarif, tr.kategori as kategori_tarif,
               p.no_transaksi as trx_code
        FROM tagihan t
        JOIN users u ON t.user_id = u.id
        JOIN tarif tr ON t.tarif_id = tr.id
        LEFT JOIN pembayaran p ON p.tagihan_id = t.id AND p.status = 'berhasil'
        WHERE 1=1";
$params = [];

if (!empty($keyword)) {
  $sql .= " AND (u.nama_lengkap LIKE ? OR u.no_pelanggan LIKE ? OR t.no_tagihan LIKE ?)";
  $kw = "%$keyword%";
  $params = array_merge($params, [$kw, $kw, $kw]);
}

if (!empty($filterStatus)) {
  $sql .= " AND t.status = ?";
  $params[] = $filterStatus;
}

if ($filterBulan > 0) {
  $sql .= " AND t.periode_bulan = ?";
  $params[] = $filterBulan;
}

if ($filterTahun > 0) {
  $sql .= " AND t.periode_tahun = ?";
  $params[] = $filterTahun;
}

$sql .= " ORDER BY t.periode_tahun DESC, t.periode_bulan DESC, t.id DESC";
$tagihanList = db()->fetchAll($sql, $params);

// Hitung Statistik
$stats = $tagihanModel->getStatistics();
$countBelumBayar = $tagihanModel->count('belum_bayar');
$countTerlambat = $tagihanModel->count('terlambat');
$countLunas = $tagihanModel->count('lunas');
$countPending = $tagihanModel->count('pending');
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Tagihan - SIPARES Admin</title>
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
      display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;
    }
    .header-btns { display: flex; gap: 0.75rem; }

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

    /* Modal Styles */
    .modal-overlay {
      position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
      display: none; align-items: center; justify-content: center;
      z-index: 1050; padding: 1rem;
    }
    .modal-overlay.active { display: flex; }
    .modal-card {
      background: #fff; border-radius: var(--radius-xl); max-width: 480px;
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
        <li><a href="tagihan.php" class="active"><i class="fas fa-file-invoice"></i> Tagihan</a></li>
        <li>
          <a href="pembayaran.php"><i class="fas fa-credit-card"></i> Pembayaran
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
          <h1 style="margin:0 0 0.25rem;">Manajemen Tagihan Retribusi</h1>
          <p style="margin:0;color:var(--text-secondary);">Kelola penerbitan tagihan bulanan dan monitoring status pelunasan</p>
        </div>
        <div class="header-btns">
          <form method="POST" onsubmit="return confirm('Jalankan perhitungan denda untuk tagihan melewati jatuh tempo?');" style="display:inline;">
            <input type="hidden" name="action" value="calculate_late_fees">
            <button type="submit" class="btn btn-outline" style="background:#fff;">
              <i class="fas fa-calculator"></i> Hitung Denda Keterlambatan
            </button>
          </form>
          <button onclick="openBulkModal()" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Terbitkan Tagihan Masal
          </button>
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
            <h3><?php echo count($tagihanList); ?></h3>
            <p>Total Tagihan Ditampilkan</p>
          </div>
          <div class="icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-file-invoice"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $countBelumBayar; ?></h3>
            <p>Belum Bayar</p>
          </div>
          <div class="icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-clock"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $countLunas; ?></h3>
            <p>Lunas Terbayar</p>
          </div>
          <div class="icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $countTerlambat; ?></h3>
            <p>Terlambat / Denda</p>
          </div>
          <div class="icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
      </div>

      <!-- Tagihan Table Card -->
      <div class="data-card">
        <form method="GET" class="filter-bar">
          <input type="text" name="q" value="<?php echo e($keyword); ?>" placeholder="Cari nama, No. Tagihan, No. Pelanggan..." class="search-input">
          <select name="status" class="select-filter">
            <option value="">Semua Status</option>
            <option value="belum_bayar" <?php echo $filterStatus === 'belum_bayar' ? 'selected' : ''; ?>>Belum Bayar</option>
            <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="lunas" <?php echo $filterStatus === 'lunas' ? 'selected' : ''; ?>>Lunas</option>
            <option value="terlambat" <?php echo $filterStatus === 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
          </select>
          <select name="bulan" class="select-filter">
            <option value="0">Semua Bulan</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
              <option value="<?php echo $m; ?>" <?php echo $filterBulan === $m ? 'selected' : ''; ?>><?php echo namaBulan($m); ?></option>
            <?php endfor; ?>
          </select>
          <select name="tahun" class="select-filter">
            <option value="0">Semua Tahun</option>
            <option value="2026" <?php echo $filterTahun === 2026 ? 'selected' : ''; ?>>2026</option>
            <option value="2025" <?php echo $filterTahun === 2025 ? 'selected' : ''; ?>>2025</option>
          </select>
          <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-filter"></i> Filter</button>
          <?php if (!empty($keyword) || !empty($filterStatus) || $filterBulan > 0 || $filterTahun > 0): ?>
            <a href="tagihan.php" class="btn btn-sm" style="color:var(--text-secondary);">Reset</a>
          <?php endif; ?>
        </form>

        <div style="overflow-x:auto;">
          <table>
            <thead>
              <tr>
                <th>No. Tagihan</th>
                <th>Pelanggan</th>
                <th>Periode</th>
                <th>Jatuh Tempo</th>
                <th>Pokok</th>
                <th>Admin</th>
                <th>Denda</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($tagihanList)): ?>
                <tr>
                  <td colspan="10" style="text-align:center;padding:2.5rem;color:var(--text-secondary);">
                    Tidak ada tagihan yang sesuai filter.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($tagihanList as $t): ?>
                  <tr>
                    <td><strong><?php echo e($t['no_tagihan']); ?></strong></td>
                    <td>
                      <strong><?php echo e($t['nama_lengkap']); ?></strong><br>
                      <small style="color:var(--text-secondary);"><?php echo e($t['no_pelanggan']); ?> &bull; <?php echo kategoriLabel($t['kategori_user']); ?></small>
                    </td>
                    <td><?php echo namaBulan($t['periode_bulan']) . ' ' . $t['periode_tahun']; ?></td>
                    <td>
                      <small><?php echo tanggal_indo($t['tanggal_jatuh_tempo'], 'short'); ?></small>
                    </td>
                    <td><?php echo rupiah($t['nominal_tagihan']); ?></td>
                    <td><?php echo rupiah($t['biaya_admin']); ?></td>
                    <td><?php echo ($t['denda'] > 0) ? '<span style="color:#dc2626;">' . rupiah($t['denda']) . '</span>' : '-'; ?></td>
                    <td><strong><?php echo rupiah($t['total_tagihan']); ?></strong></td>
                    <td>
                      <?php if ($t['status'] === 'lunas'): ?>
                        <span class="badge badge-success">&#10004; Lunas</span>
                      <?php elseif ($t['status'] === 'pending'): ?>
                        <span class="badge badge-warning">Pending</span>
                      <?php elseif ($t['status'] === 'terlambat'): ?>
                        <span class="badge badge-danger">&#9888; Terlambat</span>
                      <?php else: ?>
                        <span class="badge badge-warning">Belum Bayar</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div style="display:flex;gap:0.35rem;">
                        <?php if ($t['status'] === 'lunas' && !empty($t['trx_code'])): ?>
                          <a href="../bukti_digital.php?trx=<?php echo urlencode($t['trx_code']); ?>" target="_blank" 
                             class="action-btn" style="background:#ecfdf5;color:#047857;text-decoration:none;" title="Lihat Bukti Digital">
                            <i class="fas fa-file-invoice"></i>
                          </a>
                        <?php endif; ?>
                        <button type="button" class="action-btn" style="background:#f3f4f6;color:#374151;"
                                onclick="openStatusModal(<?php echo $t['id']; ?>, '<?php echo e($t['no_tagihan']); ?>', '<?php echo $t['status']; ?>')" title="Ubah Status">
                          <i class="fas fa-pencil-alt"></i>
                        </button>
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

  <!-- Modal Terbitkan Tagihan Masal (Bulk) -->
  <div class="modal-overlay" id="bulkModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin:0;"><i class="fas fa-file-invoice-dollar" style="color:var(--primary);"></i> Terbitkan Tagihan Masal</h3>
        <button type="button" onclick="closeBulkModal()" style="border:none;background:none;font-size:1.3rem;cursor:pointer;">&times;</button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="create_bulk">
        <p style="font-size:0.9rem;color:var(--text-secondary);margin-top:0;">
          Sistem akan membuat tagihan secara otomatis untuk <strong>seluruh pelanggan aktif</strong> sesuai tarif kategorinya, serta mengirimkan notifikasi terbit tagihan baru ke setiap akun.
        </p>
        <div style="margin-bottom:1rem;">
          <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:0.35rem;">Periode Bulan *</label>
          <select name="bulan" required class="form-control" style="width:100%;padding:0.65rem;border:1px solid var(--border-color);border-radius:var(--radius-md);">
            <?php for ($m = 1; $m <= 12; $m++): ?>
              <option value="<?php echo $m; ?>" <?php echo $m == date('n') ? 'selected' : ''; ?>><?php echo namaBulan($m); ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div style="margin-bottom:1.5rem;">
          <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:0.35rem;">Periode Tahun *</label>
          <select name="tahun" required class="form-control" style="width:100%;padding:0.65rem;border:1px solid var(--border-color);border-radius:var(--radius-md);">
            <option value="2026" selected>2026</option>
            <option value="2025">2025</option>
          </select>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:0.75rem;">
          <button type="button" onclick="closeBulkModal()" class="btn btn-outline">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Terbitkan Sekarang</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Ubah Status Tagihan -->
  <div class="modal-overlay" id="statusModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin:0;">Ubah Status Tagihan</h3>
        <button type="button" onclick="closeStatusModal()" style="border:none;background:none;font-size:1.3rem;cursor:pointer;">&times;</button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="tagihan_id" id="statusTagihanId">
        <p style="font-size:0.9rem;color:var(--text-secondary);">No. Tagihan: <strong id="statusTagihanNo">-</strong></p>
        <div style="margin-bottom:1.5rem;">
          <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:0.35rem;">Pilih Status Baru</label>
          <select name="status" id="statusSelect" class="form-control" style="width:100%;padding:0.65rem;border:1px solid var(--border-color);border-radius:var(--radius-md);">
            <option value="belum_bayar">Belum Bayar</option>
            <option value="pending">Pending</option>
            <option value="lunas">Lunas</option>
            <option value="terlambat">Terlambat</option>
          </select>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:0.75rem;">
          <button type="button" onclick="closeStatusModal()" class="btn btn-outline">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui Status</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openBulkModal() {
      document.getElementById('bulkModal').classList.add('active');
    }
    function closeBulkModal() {
      document.getElementById('bulkModal').classList.remove('active');
    }

    function openStatusModal(id, noTagihan, currentStatus) {
      document.getElementById('statusTagihanId').value = id;
      document.getElementById('statusTagihanNo').textContent = noTagihan;
      document.getElementById('statusSelect').value = currentStatus;
      document.getElementById('statusModal').classList.add('active');
    }
    function closeStatusModal() {
      document.getElementById('statusModal').classList.remove('active');
    }
  </script>
</body>

</html>

