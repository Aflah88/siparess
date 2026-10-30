<?php
/**
 * ========================================
 * SIPARES - Profil Pengguna
 * ========================================
 */
require_once __DIR__ . '/../config/init.php';

if (!Session::isLoggedIn()) {
  Session::flash('warning', 'Silakan login terlebih dahulu');
  redirect('../login.php');
}

require_once INCLUDES_PATH . 'User.php';
require_once INCLUDES_PATH . 'Notifikasi.php';

$userId = Session::userId();

$userModel = new User();
$userData = $userModel->getById($userId);
if (!$userData) {
  $userData = Session::user();
}

$notifModel = new Notifikasi();
$notifModel->triggerAutomatedNotifications($userId);
$unreadNotifCount = $notifModel->countUnread($userId);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Pelanggan - SIPARES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    body { background: var(--bg-primary); }
    .dashboard { display: flex; min-height: 100vh; }
    .sidebar {
      width: 250px; background: var(--white);
      border-right: 1px solid var(--border-color);
      padding: 1.5rem; position: fixed; height: 100vh;
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
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .profile-card {
      background: var(--white); border-radius: var(--radius-xl);
      padding: 2rem; border: 1px solid var(--border-color); max-width: 650px;
    }
    .profile-row {
      display: flex; justify-content: space-between; padding: 0.75rem 0;
      border-bottom: 1px solid var(--border-color); font-size: 0.95rem;
    }
    .profile-row:last-child { border-bottom: none; }
    .label { color: var(--text-secondary); }
    .val { font-weight: 600; color: var(--text-primary); }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main { margin-left: 0; }
    }
  </style>
</head>

<body>
  <div class="dashboard">
    <aside class="sidebar">
      <div class="sidebar-logo"><i class="fas fa-recycle"></i> SIPARES</div>
      <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="tagihan.php"><i class="fas fa-file-invoice"></i> Tagihan</a></li>
        <li><a href="riwayat.php"><i class="fas fa-history"></i> Riwayat</a></li>
        <li><a href="profil.php" class="active"><i class="fas fa-user"></i> Profil</a></li>
        <li><a href="edukasi.php"><i class="fas fa-book"></i> Edukasi</a></li>
      </ul>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline btn-block"><i class="fas fa-sign-out-alt"></i> Keluar</a>
      </div>
    </aside>

    <main class="main">
      <div class="page-header">
        <div>
          <h1>Profil Pelanggan</h1>
          <p>Informasi identitas akun dan objek retribusi sampah Anda</p>
        </div>
        <div>
          <button class="btn-notification" id="btn-notification" title="Notifikasi">
            <i class="fas fa-bell"></i>
            <?php if ($unreadNotifCount > 0): ?>
              <span class="notification-badge"><?php echo $unreadNotifCount; ?></span>
            <?php endif; ?>
          </button>
        </div>
      </div>

      <div class="profile-card">
        <h3 style="margin-top:0;margin-bottom:1.5rem;font-size:1.15rem;"><i class="fas fa-id-card" style="color:var(--primary);margin-right:0.5rem;"></i> Rincian Identitas Wajib Retribusi</h3>
        <div class="profile-row">
          <span class="label">Nama Lengkap</span>
          <span class="val"><?php echo e($userData['nama_lengkap']); ?></span>
        </div>
        <div class="profile-row">
          <span class="label">No. Pelanggan</span>
          <span class="val"><?php echo e($userData['no_pelanggan']); ?></span>
        </div>
        <div class="profile-row">
          <span class="label">NIK</span>
          <span class="val"><?php echo e($userData['nik']); ?></span>
        </div>
        <div class="profile-row">
          <span class="label">Email</span>
          <span class="val"><?php echo e($userData['email']); ?></span>
        </div>
        <div class="profile-row">
          <span class="label">No. Telepon</span>
          <span class="val"><?php echo e($userData['no_telepon']); ?></span>
        </div>
        <div class="profile-row">
          <span class="label">Kategori Wajib Retribusi</span>
          <span class="val"><?php echo kategoriLabel($userData['kategori']); ?></span>
        </div>
        <div class="profile-row">
          <span class="label">Alamat Lengkap</span>
          <span class="val"><?php echo e($userData['alamat']); ?> RT <?php echo e($userData['rt']); ?>/RW <?php echo e($userData['rw']); ?></span>
        </div>
        <div class="profile-row">
          <span class="label">Kelurahan / Kecamatan</span>
          <span class="val"><?php echo e($userData['kelurahan']); ?>, <?php echo e($userData['kecamatan']); ?></span>
        </div>
        <div class="profile-row">
          <span class="label">Status Akun</span>
          <span class="val"><span class="badge badge-success">&#10004; <?php echo ucfirst($userData['status']); ?></span></span>
        </div>
      </div>
    </main>
  </div>

  <script src="../js/main.js"></script>
</body>

</html>

