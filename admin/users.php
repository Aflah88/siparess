<?php
/**
 * ========================================
 * SIPARES - Admin Manajemen Pelanggan
 * ========================================
 */
require_once __DIR__ . '/../config/init.php';

if (!Session::isAdminLoggedIn()) {
  Session::flash('warning', 'Silakan login sebagai admin');
  redirect('../login.php');
}

require_once INCLUDES_PATH . 'Admin.php';
require_once INCLUDES_PATH . 'User.php';
require_once INCLUDES_PATH . 'Pembayaran.php';

$adminData = Session::admin();
$userModel = new User();
$pembayaranModel = new Pembayaran();
$pendingPayments = $pembayaranModel->count('pending');

// Handle Form Submissions (Tambah / Edit / Toggle Status)
$message = '';
$messageType = 'success';

if (isPost()) {
  $action = $_POST['action'] ?? '';

  if ($action === 'create') {
    $res = $userModel->register([
      'nik' => sanitize($_POST['nik'] ?? ''),
      'nama_lengkap' => sanitize($_POST['nama_lengkap'] ?? ''),
      'email' => sanitize($_POST['email'] ?? ''),
      'password' => $_POST['password'] ?? 'password123',
      'no_telepon' => sanitize($_POST['no_telepon'] ?? ''),
      'alamat' => sanitize($_POST['alamat'] ?? ''),
      'rt' => sanitize($_POST['rt'] ?? ''),
      'rw' => sanitize($_POST['rw'] ?? ''),
      'kelurahan' => sanitize($_POST['kelurahan'] ?? ''),
      'kecamatan' => sanitize($_POST['kecamatan'] ?? ''),
      'kategori' => sanitize($_POST['kategori'] ?? 'rumah_tangga')
    ]);
    $message = $res['message'];
    $messageType = $res['success'] ? 'success' : 'danger';
  } elseif ($action === 'update') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $res = $userModel->updateUser($userId, [
      'nik' => sanitize($_POST['nik'] ?? ''),
      'nama_lengkap' => sanitize($_POST['nama_lengkap'] ?? ''),
      'email' => sanitize($_POST['email'] ?? ''),
      'no_telepon' => sanitize($_POST['no_telepon'] ?? ''),
      'alamat' => sanitize($_POST['alamat'] ?? ''),
      'rt' => sanitize($_POST['rt'] ?? ''),
      'rw' => sanitize($_POST['rw'] ?? ''),
      'kelurahan' => sanitize($_POST['kelurahan'] ?? ''),
      'kecamatan' => sanitize($_POST['kecamatan'] ?? ''),
      'kategori' => sanitize($_POST['kategori'] ?? 'rumah_tangga'),
      'status' => sanitize($_POST['status'] ?? 'aktif')
    ]);
    $message = $res['message'];
    $messageType = $res['success'] ? 'success' : 'danger';
  } elseif ($action === 'toggle_status') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $res = $userModel->toggleStatus($userId);
    $message = $res['message'];
    $messageType = $res['success'] ? 'success' : 'danger';
  }
}

// Filter & Search
$keyword = sanitize($_GET['q'] ?? '');
$filterKategori = sanitize($_GET['kategori'] ?? '');
$filterStatus = sanitize($_GET['status'] ?? '');

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($keyword)) {
  $sql .= " AND (nama_lengkap LIKE ? OR no_pelanggan LIKE ? OR nik LIKE ? OR email LIKE ? OR alamat LIKE ?)";
  $kw = "%$keyword%";
  $params = array_merge($params, [$kw, $kw, $kw, $kw, $kw]);
}

if (!empty($filterKategori)) {
  $sql .= " AND kategori = ?";
  $params[] = $filterKategori;
}

if (!empty($filterStatus)) {
  $sql .= " AND status = ?";
  $params[] = $filterStatus;
}

$sql .= " ORDER BY created_at DESC";
$users = db()->fetchAll($sql, $params);

// Hitung Statistik
$statKategori = $userModel->countByKategori();
$kategoriMap = [];
foreach ($statKategori as $sk) {
  $kategoriMap[$sk['kategori']] = $sk['total'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pelanggan - SIPARES Admin</title>
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
      display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;
      justify-content: space-between; align-items: center;
    }
    .filter-inputs { display: flex; gap: 0.75rem; flex-wrap: wrap; flex: 1; }
    .search-input, .select-filter {
      padding: 0.6rem 0.9rem; border: 1px solid var(--border-color);
      border-radius: var(--radius-md); font-size: 0.88rem; outline: none;
    }
    .search-input { min-width: 220px; }

    table { width: 100%; border-collapse: collapse; }
    th, td {
      padding: 0.85rem 1rem; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.88rem;
    }
    th {
      font-weight: 600; color: var(--text-secondary); background: var(--gray-50);
      text-transform: uppercase; font-size: 0.75rem;
    }
    tbody tr:hover { background: var(--gray-50); }

    .user-cell { display: flex; align-items: center; gap: 0.75rem; }
    .user-avatar {
      width: 36px; height: 36px; border-radius: 50%; background: var(--primary);
      color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;
    }

    /* Modal Styles */
    .modal-overlay {
      position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
      display: none; align-items: center; justify-content: center;
      z-index: 1050; padding: 1rem;
    }
    .modal-overlay.active { display: flex; }
    .modal-card {
      background: #fff; border-radius: var(--radius-xl); max-width: 600px;
      width: 100%; padding: 2rem; max-height: 90vh; overflow-y: auto;
    }
    .modal-header {
      display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;
    }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group.full { grid-column: span 2; }
    .form-group label {
      display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem;
    }
    .form-control {
      width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color);
      border-radius: var(--radius-md); font-size: 0.88rem; box-sizing: border-box;
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
        <li><a href="users.php" class="active"><i class="fas fa-users"></i> Pelanggan</a></li>
        <li><a href="tagihan.php"><i class="fas fa-file-invoice"></i> Tagihan</a></li>
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
          <h1 style="margin:0 0 0.25rem;">Data Pelanggan (Wajib Retribusi)</h1>
          <p style="margin:0;color:var(--text-secondary);">Kelola data akun warga dan kategori retribusi sampah</p>
        </div>
        <button onclick="openAddModal()" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;">
          <i class="fas fa-user-plus"></i> Tambah Pelanggan
        </button>
      </div>

      <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>" style="padding:0.9rem 1.25rem;border-radius:var(--radius-lg);margin-bottom:1.5rem;background:<?php echo $messageType === 'success' ? '#d1fae5' : '#fee2e2'; ?>;color:<?php echo $messageType === 'success' ? '#065f46' : '#991b1b'; ?>;">
          <?php echo e($message); ?>
        </div>
      <?php endif; ?>

      <!-- Statistics Cards -->
      <div class="stats">
        <div class="stat-card">
          <div>
            <h3><?php echo count($users); ?></h3>
            <p>Total Pelanggan</p>
          </div>
          <div class="icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-users"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $kategoriMap['rumah_tangga'] ?? 0; ?></h3>
            <p>Rumah Tangga</p>
          </div>
          <div class="icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-home"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $kategoriMap['usaha'] ?? 0; ?></h3>
            <p>Usaha / Toko</p>
          </div>
          <div class="icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-store"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $kategoriMap['industri'] ?? 0; ?></h3>
            <p>Industri</p>
          </div>
          <div class="icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-industry"></i></div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="data-card">
        <!-- Search & Filter Form -->
        <form method="GET" class="filter-bar">
          <div class="filter-inputs">
            <input type="text" name="q" value="<?php echo e($keyword); ?>" placeholder="Cari nama, NIK, No. Pelanggan..." class="search-input">
            <select name="kategori" class="select-filter">
              <option value="">Semua Kategori</option>
              <option value="rumah_tangga" <?php echo $filterKategori === 'rumah_tangga' ? 'selected' : ''; ?>>Rumah Tangga</option>
              <option value="usaha" <?php echo $filterKategori === 'usaha' ? 'selected' : ''; ?>>Usaha / Toko</option>
              <option value="industri" <?php echo $filterKategori === 'industri' ? 'selected' : ''; ?>>Industri</option>
            </select>
            <select name="status" class="select-filter">
              <option value="">Semua Status</option>
              <option value="aktif" <?php echo $filterStatus === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
              <option value="nonaktif" <?php echo $filterStatus === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-filter"></i> Filter</button>
            <?php if (!empty($keyword) || !empty($filterKategori) || !empty($filterStatus)): ?>
              <a href="users.php" class="btn btn-sm" style="color:var(--text-secondary);">Reset</a>
            <?php endif; ?>
          </div>
        </form>

        <!-- Users Table -->
        <div style="overflow-x:auto;">
          <table>
            <thead>
              <tr>
                <th>Pelanggan</th>
                <th>NIK</th>
                <th>Kontak</th>
                <th>Kategori</th>
                <th>Alamat Domisili</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($users)): ?>
                <tr>
                  <td colspan="7" style="text-align:center;padding:2.5rem;color:var(--text-secondary);">
                    Tidak ada data pelanggan yang sesuai kriteria.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($users as $u): ?>
                  <tr>
                    <td>
                      <div class="user-cell">
                        <div class="user-avatar"><?php echo strtoupper(substr($u['nama_lengkap'], 0, 1)); ?></div>
                        <div>
                          <strong><?php echo e($u['nama_lengkap']); ?></strong>
                          <br><small style="color:var(--text-secondary);"><?php echo e($u['no_pelanggan']); ?></small>
                        </div>
                      </div>
                    </td>
                    <td><code><?php echo e($u['nik']); ?></code></td>
                    <td>
                      <div><?php echo e($u['email']); ?></div>
                      <small style="color:var(--text-secondary);"><?php echo e($u['no_telepon']); ?></small>
                    </td>
                    <td>
                      <span class="badge badge-primary"><?php echo kategoriLabel($u['kategori']); ?></span>
                    </td>
                    <td>
                      <small><?php echo e($u['alamat']); ?> RT <?php echo e($u['rt']); ?>/RW <?php echo e($u['rw']); ?><br>
                      <?php echo e($u['kelurahan']); ?>, <?php echo e($u['kecamatan']); ?></small>
                    </td>
                    <td>
                      <span class="badge badge-<?php echo $u['status'] === 'aktif' ? 'success' : 'danger'; ?>">
                        <?php echo ucfirst($u['status']); ?>
                      </span>
                    </td>
                    <td>
                      <div style="display:flex;gap:0.35rem;">
                        <button type="button" class="action-btn" style="background:#e0f2fe;color:#0284c7;" 
                                onclick='openEditModal(<?php echo json_encode($u); ?>)' title="Edit Pelanggan">
                          <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" onsubmit="return confirm('Ubah status pelanggan ini?');" style="display:inline;">
                          <input type="hidden" name="action" value="toggle_status">
                          <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                          <button type="submit" class="action-btn <?php echo $u['status'] === 'aktif' ? 'danger' : 'success'; ?>" 
                                  title="<?php echo $u['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                            <i class="fas <?php echo $u['status'] === 'aktif' ? 'fa-ban' : 'fa-check'; ?>"></i>
                          </button>
                        </form>
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

  <!-- Modal Tambah Pelanggan -->
  <div class="modal-overlay" id="addModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin:0;"><i class="fas fa-user-plus" style="color:var(--primary);"></i> Tambah Pelanggan Baru</h3>
        <button type="button" onclick="closeAddModal()" style="border:none;background:none;font-size:1.3rem;cursor:pointer;">&times;</button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
          <div class="form-group">
            <label>NIK (16 Digit) *</label>
            <input type="text" name="nik" required maxlength="16" pattern="[0-9]{16}" class="form-control" placeholder="Contoh: 3201...">
          </div>
          <div class="form-group">
            <label>Nama Lengkap *</label>
            <input type="text" name="nama_lengkap" required class="form-control" placeholder="Nama warga / pemilik usaha">
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required class="form-control" placeholder="email@contoh.com">
          </div>
          <div class="form-group">
            <label>Kata Sandi Default</label>
            <input type="password" name="password" class="form-control" value="password123">
          </div>
          <div class="form-group">
            <label>Nomor Telepon</label>
            <input type="text" name="no_telepon" class="form-control" placeholder="0812...">
          </div>
          <div class="form-group">
            <label>Kategori Retribusi *</label>
            <select name="kategori" required class="form-control">
              <option value="rumah_tangga">Rumah Tangga (Rp 25.000)</option>
              <option value="usaha">Usaha / Toko (Rp 50.000)</option>
              <option value="industri">Industri (Rp 150.000)</option>
            </select>
          </div>
          <div class="form-group full">
            <label>Alamat Lengkap</label>
            <textarea name="alamat" rows="2" class="form-control" placeholder="Nama jalan, nomor rumah..."></textarea>
          </div>
          <div class="form-group">
            <label>RT / RW</label>
            <div style="display:flex;gap:0.5rem;">
              <input type="text" name="rt" placeholder="RT" class="form-control">
              <input type="text" name="rw" placeholder="RW" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label>Kelurahan / Kecamatan</label>
            <div style="display:flex;gap:0.5rem;">
              <input type="text" name="kelurahan" placeholder="Kelurahan" class="form-control">
              <input type="text" name="kecamatan" placeholder="Kecamatan" class="form-control">
            </div>
          </div>
        </div>
        <div style="margin-top:1.5rem;display:flex;justify-content:flex-end;gap:0.75rem;">
          <button type="button" onclick="closeAddModal()" class="btn btn-outline">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pelanggan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Edit Pelanggan -->
  <div class="modal-overlay" id="editModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin:0;"><i class="fas fa-user-edit" style="color:var(--primary);"></i> Edit Pelanggan</h3>
        <button type="button" onclick="closeEditModal()" style="border:none;background:none;font-size:1.3rem;cursor:pointer;">&times;</button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="user_id" id="editUserId">
        <div class="form-grid">
          <div class="form-group">
            <label>NIK *</label>
            <input type="text" name="nik" id="editNik" required maxlength="16" class="form-control">
          </div>
          <div class="form-group">
            <label>Nama Lengkap *</label>
            <input type="text" name="nama_lengkap" id="editNama" required class="form-control">
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" id="editEmail" required class="form-control">
          </div>
          <div class="form-group">
            <label>Nomor Telepon</label>
            <input type="text" name="no_telepon" id="editTelepon" class="form-control">
          </div>
          <div class="form-group">
            <label>Kategori Retribusi *</label>
            <select name="kategori" id="editKategori" required class="form-control">
              <option value="rumah_tangga">Rumah Tangga</option>
              <option value="usaha">Usaha / Toko</option>
              <option value="industri">Industri</option>
            </select>
          </div>
          <div class="form-group">
            <label>Status Akun *</label>
            <select name="status" id="editStatus" required class="form-control">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
          <div class="form-group full">
            <label>Alamat Lengkap</label>
            <textarea name="alamat" id="editAlamat" rows="2" class="form-control"></textarea>
          </div>
          <div class="form-group">
            <label>RT / RW</label>
            <div style="display:flex;gap:0.5rem;">
              <input type="text" name="rt" id="editRt" placeholder="RT" class="form-control">
              <input type="text" name="rw" id="editRw" placeholder="RW" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label>Kelurahan / Kecamatan</label>
            <div style="display:flex;gap:0.5rem;">
              <input type="text" name="kelurahan" id="editKelurahan" placeholder="Kelurahan" class="form-control">
              <input type="text" name="kecamatan" id="editKecamatan" placeholder="Kecamatan" class="form-control">
            </div>
          </div>
        </div>
        <div style="margin-top:1.5rem;display:flex;justify-content:flex-end;gap:0.75rem;">
          <button type="button" onclick="closeEditModal()" class="btn btn-outline">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui Data</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openAddModal() {
      document.getElementById('addModal').classList.add('active');
    }
    function closeAddModal() {
      document.getElementById('addModal').classList.remove('active');
    }

    function openEditModal(user) {
      document.getElementById('editUserId').value = user.id;
      document.getElementById('editNik').value = user.nik;
      document.getElementById('editNama').value = user.nama_lengkap;
      document.getElementById('editEmail').value = user.email;
      document.getElementById('editTelepon').value = user.no_telepon || '';
      document.getElementById('editKategori').value = user.kategori;
      document.getElementById('editStatus').value = user.status;
      document.getElementById('editAlamat').value = user.alamat || '';
      document.getElementById('editRt').value = user.rt || '';
      document.getElementById('editRw').value = user.rw || '';
      document.getElementById('editKelurahan').value = user.kelurahan || '';
      document.getElementById('editKecamatan').value = user.kecamatan || '';
      document.getElementById('editModal').classList.add('active');
    }
    function closeEditModal() {
      document.getElementById('editModal').classList.remove('active');
    }
  </script>
</body>

</html>

