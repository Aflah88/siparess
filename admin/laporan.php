<?php
/**
 * ========================================
 * SIPARES - Admin Laporan Retribusi
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
$pendingPayments = $pembayaranModel->count('pending');

// Filter Tahun
$tahunSelected = (int) ($_GET['tahun'] ?? date('Y'));

// Ambil Data Laporan Bulanan dari View
$sqlMonthly = "SELECT * FROM v_laporan_bulanan WHERE tahun = ? ORDER BY bulan ASC";
$monthlyData = db()->fetchAll($sqlMonthly, [$tahunSelected]);

// Jika tahun terpilih tidak ada data di view (misal 2026), coba hitung total tahun ini
$totalTahun = 0;
$totalTrx = 0;
foreach ($monthlyData as $m) {
  $totalTahun += $m['total_pendapatan'];
  $totalTrx += $m['jumlah_transaksi'];
}

// Ambil Ringkasan Tunggakan per Pelanggan
$sqlRingkasan = "SELECT * FROM v_ringkasan_tagihan ORDER BY total_tunggakan DESC";
$ringkasanPelanggan = db()->fetchAll($sqlRingkasan);

$totalTunggakanKeseluruhan = 0;
$totalTagihanLunasKeseluruhan = 0;
$totalTagihanDiterbitkanKeseluruhan = 0;

foreach ($ringkasanPelanggan as $rp) {
  $totalTunggakanKeseluruhan += $rp['total_tunggakan'];
  $totalTagihanLunasKeseluruhan += $rp['tagihan_lunas'];
  $totalTagihanDiterbitkanKeseluruhan += $rp['total_tagihan'];
}

$kepatuhanRate = ($totalTagihanDiterbitkanKeseluruhan > 0)
  ? round(($totalTagihanLunasKeseluruhan / $totalTagihanDiterbitkanKeseluruhan) * 100, 1)
  : 0;

// Statistik per Metode Pembayaran
$sqlMetode = "SELECT metode_pembayaran, COUNT(*) as jumlah, SUM(nominal_bayar) as total
              FROM pembayaran
              WHERE status = 'berhasil'
              GROUP BY metode_pembayaran";
$metodeStats = db()->fetchAll($sqlMetode);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Keuangan Retribusi - SIPARES Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap"
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
      padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 2rem;
    }
    .data-card-header {
      display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;
    }
    .data-card-header h2 { margin: 0; font-size: 1.1rem; }

    table { width: 100%; border-collapse: collapse; }
    th, td {
      padding: 0.85rem 1rem; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.88rem;
    }
    th {
      font-weight: 600; color: var(--text-secondary); background: var(--gray-50);
      text-transform: uppercase; font-size: 0.75rem;
    }
    tbody tr:hover { background: var(--gray-50); }

    .print-only-header { display: none; }

    /* Print Styles */
    @media print {
      body { background: #fff !important; }
      .sidebar, .page-header .btn, form.filter-form, .btn-print-box { display: none !important; }
      .main { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
      .data-card { box-shadow: none !important; border: 1px solid #ddd !important; }
      .print-only-header {
        display: block !important;
        text-align: center;
        border-bottom: 2px solid #000;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
      }
      .print-only-header h2 { margin: 0; font-size: 1.3rem; }
      .print-only-header p { margin: 0.25rem 0 0; font-size: 0.85rem; color: #555; }
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
          <a href="pembayaran.php"><i class="fas fa-credit-card"></i> Pembayaran
            <?php if ($pendingPayments > 0): ?><span class="badge-count"><?php echo $pendingPayments; ?></span><?php endif; ?>
          </a>
        </li>
        <li class="sidebar-section">Laporan</li>
        <li><a href="laporan.php" class="active"><i class="fas fa-chart-bar"></i> Laporan</a></li>
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
      <!-- Print Header (Hanya tampil saat dicetak/PDF) -->
      <div class="print-only-header">
        <h2>PEMERINTAH KOTA &bull; DINAS LINGKUNGAN HIDUP</h2>
        <p>LAPORAN REKAPITULASI PENERIMAAN RETRIBUSI PELAYANAN PERSAMPAHAN (SIPARES)</p>
        <p>Periode Tahun Anggaran: <strong><?php echo $tahunSelected; ?></strong> &bull; Dicetak pada: <?php echo tanggal_indo(date('Y-m-d'), 'full'); ?></p>
      </div>

      <div class="page-header">
        <div>
          <h1 style="margin:0 0 0.25rem;">Laporan & Rekapitulasi Keuangan</h1>
          <p style="margin:0;color:var(--text-secondary);">Statistik penerimaan retribusi, efektivitas kanal pembayaran, dan rekap tunggakan</p>
        </div>
        <div style="display:flex;gap:0.75rem;align-items:center;">
          <form method="GET" class="filter-form" style="display:flex;gap:0.5rem;align-items:center;">
            <label style="font-size:0.85rem;font-weight:600;">Tahun:</label>
            <select name="tahun" onchange="this.form.submit()" style="padding:0.5rem 0.75rem;border-radius:var(--radius-md);border:1px solid var(--border-color);">
              <option value="2026" <?php echo $tahunSelected === 2026 ? 'selected' : ''; ?>>2026</option>
              <option value="2025" <?php echo $tahunSelected === 2025 ? 'selected' : ''; ?>>2025</option>
              <option value="2024" <?php echo $tahunSelected === 2024 ? 'selected' : ''; ?>>2024</option>
            </select>
          </form>
          <button onclick="window.print()" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;">
            <i class="fas fa-print"></i> Cetak / Simpan PDF
          </button>
        </div>
      </div>

      <!-- Executive Summary Cards -->
      <div class="stats">
        <div class="stat-card">
          <div>
            <h3><?php echo rupiah($totalTahun); ?></h3>
            <p>Penerimaan Tahun <?php echo $tahunSelected; ?></p>
          </div>
          <div class="icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-money-bill-wave"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo rupiah($totalTunggakanKeseluruhan); ?></h3>
            <p>Total Tunggakan Berjalan</p>
          </div>
          <div class="icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-exclamation-circle"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $totalTrx; ?></h3>
            <p>Transaksi Lunas (<?php echo $tahunSelected; ?>)</p>
          </div>
          <div class="icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-check-double"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <h3><?php echo $kepatuhanRate; ?>%</h3>
            <p>Tingkat Kepatuhan</p>
          </div>
          <div class="icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-chart-line"></i></div>
        </div>
      </div>

      <!-- Tabel 1: Penerimaan Bulanan -->
      <div class="data-card">
        <div class="data-card-header">
          <h2><i class="fas fa-calendar-alt" style="color:var(--primary);"></i> Rekapitulasi Penerimaan Retribusi Per Bulan (Tahun <?php echo $tahunSelected; ?>)</h2>
        </div>
        <div style="overflow-x:auto;">
          <table>
            <thead>
              <tr>
                <th>Bulan</th>
                <th>Transaksi</th>
                <th>Via QRIS</th>
                <th>Via Transfer Bank</th>
                <th>Via Minimarket</th>
                <th>Total Penerimaan</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($monthlyData)): ?>
                <tr>
                  <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-secondary);">
                    Belum ada transaksi pembayaran lunas pada tahun <?php echo $tahunSelected; ?>.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($monthlyData as $row): ?>
                  <tr>
                    <td><strong><?php echo namaBulan($row['bulan']); ?></strong></td>
                    <td><?php echo $row['jumlah_transaksi']; ?> transaksi</td>
                    <td><span class="badge badge-success"><?php echo $row['via_qris']; ?>x</span></td>
                    <td><span class="badge badge-primary"><?php echo $row['via_transfer']; ?>x</span></td>
                    <td><span class="badge badge-warning"><?php echo $row['via_minimarket']; ?>x</span></td>
                    <td><strong><?php echo rupiah($row['total_pendapatan']); ?></strong></td>
                  </tr>
                <?php endforeach; ?>
                <tr style="background:#f9fafb;font-weight:700;">
                  <td>TOTAL <?php echo $tahunSelected; ?></td>
                  <td><?php echo $totalTrx; ?> transaksi</td>
                  <td colspan="3">-</td>
                  <td style="color:#059669;font-size:1.05rem;"><?php echo rupiah($totalTahun); ?></td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tabel 2: Rekapitulasi Status & Tunggakan per Pelanggan -->
      <div class="data-card">
        <div class="data-card-header">
          <h2><i class="fas fa-users" style="color:var(--primary);"></i> Ringkasan Status Tagihan & Tunggakan Wajib Retribusi</h2>
        </div>
        <div style="overflow-x:auto;">
          <table>
            <thead>
              <tr>
                <th>No. Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Kategori</th>
                <th>Total Tagihan Terbit</th>
                <th>Lunas</th>
                <th>Belum Lunas</th>
                <th>Sisa Tunggakan</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($ringkasanPelanggan)): ?>
                <tr>
                  <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-secondary);">
                    Belum ada data pelanggan.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($ringkasanPelanggan as $rp): ?>
                  <tr>
                    <td><code><?php echo e($rp['no_pelanggan']); ?></code></td>
                    <td><strong><?php echo e($rp['nama_lengkap']); ?></strong></td>
                    <td><span class="badge badge-primary"><?php echo kategoriLabel($rp['kategori']); ?></span></td>
                    <td><?php echo $rp['total_tagihan']; ?> bulan</td>
                    <td><span style="color:#059669;font-weight:600;"><?php echo $rp['tagihan_lunas']; ?> lunas</span></td>
                    <td><span style="color:#dc2626;font-weight:600;"><?php echo $rp['tagihan_belum_lunas']; ?> tertunggak</span></td>
                    <td>
                      <?php if ($rp['total_tunggakan'] > 0): ?>
                        <strong style="color:#dc2626;"><?php echo rupiah($rp['total_tunggakan']); ?></strong>
                      <?php else: ?>
                        <span style="color:#059669;font-weight:600;">&#10004; Nihil (Lunas)</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <tr style="background:#f9fafb;font-weight:700;">
                  <td colspan="3">TOTAL KESELURUHAN</td>
                  <td><?php echo $totalTagihanDiterbitkanKeseluruhan; ?> tagihan</td>
                  <td style="color:#059669;"><?php echo $totalTagihanLunasKeseluruhan; ?> lunas</td>
                  <td style="color:#dc2626;"><?php echo ($totalTagihanDiterbitkanKeseluruhan - $totalTagihanLunasKeseluruhan); ?> belum lunas</td>
                  <td style="color:#dc2626;font-size:1.05rem;"><?php echo rupiah($totalTunggakanKeseluruhan); ?></td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>

</html>

