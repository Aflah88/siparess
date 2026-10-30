<?php
/**
 * ========================================
 * SIPARES - Halaman Tagihan User
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

$tagihanModel = new Tagihan();
$pembayaranModel = new Pembayaran();
$notifModel = new Notifikasi();

// Pemicu otomatis notifikasi jatuh tempo
$notifModel->triggerAutomatedNotifications($userId);
$unreadNotifCount = $notifModel->countUnread($userId);

// Ambil semua tagihan user
$allTagihan = $tagihanModel->getByUserId($userId);

// Ambil pembayaran user untuk mapping bukti digital & status
$allPayments = $pembayaranModel->getByUserId($userId, 100);
$paymentsByTagihan = [];
foreach ($allPayments as $p) {
  $paymentsByTagihan[$p['tagihan_id']] = $p;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tagihan Retribusi - SIPARES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
      z-index: 100;
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

    .card-table {
      background: var(--white);
      border-radius: var(--radius-xl);
      border: 1px solid var(--border-color);
      padding: 1.5rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.35rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 600;
    }

    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }

    .btn-receipt {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.4rem 0.85rem;
      background: #ecfdf5;
      color: #059669;
      border: 1px solid #a7f3d0;
      border-radius: var(--radius-md);
      font-size: 0.82rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }

    .btn-receipt:hover {
      background: #059669;
      color: #ffffff;
    }

    /* Modal styling */
    .modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.55);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1050;
      padding: 1rem;
    }

    .modal-overlay.active {
      display: flex;
    }

    .modal-card {
      background: #ffffff;
      border-radius: var(--radius-xl);
      max-width: 520px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      padding: 2rem;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .modal-header h3 {
      margin: 0;
      font-size: 1.25rem;
      font-weight: 700;
    }

    .btn-close {
      background: none;
      border: none;
      font-size: 1.4rem;
      cursor: pointer;
      color: var(--text-secondary);
      line-height: 1;
    }

    .payment-option-grid {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      margin-bottom: 1.5rem;
    }

    .payment-option {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.9rem 1rem;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-lg);
      cursor: pointer;
      transition: all 0.2s;
    }

    .payment-option:hover, .payment-option:has(input:checked) {
      border-color: var(--primary);
      background: #f0fdf4;
    }

    .qris-box {
      text-align: center;
      background: #f8fafc;
      border: 2px dashed #cbd5e1;
      border-radius: var(--radius-lg);
      padding: 1.25rem;
      margin-bottom: 1.25rem;
    }

    .qris-img {
      max-width: 230px;
      width: 100%;
      height: auto;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      margin: 0.5rem auto;
      display: block;
    }

    .bank-card {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: var(--radius-lg);
      padding: 1.25rem;
      margin-bottom: 1.25rem;
    }

    .copy-btn {
      background: #10b981;
      color: white;
      border: none;
      padding: 0.35rem 0.75rem;
      border-radius: 6px;
      font-size: 0.8rem;
      cursor: pointer;
      margin-left: 0.5rem;
      font-weight: 600;
    }

    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main { margin-left: 0; padding: 1.25rem; }
      .table-responsive { overflow-x: auto; }
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
        <li><a href="tagihan.php" class="active"><i class="fas fa-file-invoice"></i> Tagihan</a></li>
        <li><a href="riwayat.php"><i class="fas fa-history"></i> Riwayat</a></li>
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
          <h1>Tagihan Retribusi Sampah</h1>
          <p>Kelola dan bayar tagihan retribusi sampah Anda dengan metode QRIS & Transfer Bank</p>
        </div>
        <div>
          <!-- Notification Bell -->
          <button class="btn-notification" id="btn-notification" title="Notifikasi">
            <i class="fas fa-bell"></i>
            <?php if ($unreadNotifCount > 0): ?>
              <span class="notification-badge"><?php echo $unreadNotifCount; ?></span>
            <?php endif; ?>
          </button>
        </div>
      </div>

      <!-- Tagihan List -->
      <div class="card-table">
        <h2 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Semua Tagihan</h2>
        
        <?php if (empty($allTagihan)): ?>
          <div class="empty" style="text-align:center;padding:3rem 1rem;">
            <i class="fas fa-check-circle" style="font-size:2.5rem;color:var(--primary);margin-bottom:0.75rem;"></i>
            <p>Tidak ada tagihan yang terdaftar.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>No. Tagihan</th>
                  <th>Periode</th>
                  <th>Jatuh Tempo</th>
                  <th>Nominal</th>
                  <th>Biaya Admin</th>
                  <th>Denda</th>
                  <th>Total Tagihan</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($allTagihan as $t): ?>
                  <tr>
                    <td><strong><?php echo e($t['no_tagihan']); ?></strong></td>
                    <td><?php echo namaBulan($t['periode_bulan']) . ' ' . $t['periode_tahun']; ?></td>
                    <td>
                      <?php 
                        $isOverdue = strtotime($t['tanggal_jatuh_tempo']) < time() && $t['status'] !== 'lunas';
                      ?>
                      <span style="<?php echo $isOverdue ? 'color:#dc2626;font-weight:600;' : ''; ?>">
                        <?php echo tanggal_indo($t['tanggal_jatuh_tempo'], 'short'); ?>
                      </span>
                    </td>
                    <td><?php echo rupiah($t['nominal_tagihan']); ?></td>
                    <td><?php echo rupiah($t['biaya_admin']); ?></td>
                    <td><?php echo rupiah($t['denda']); ?></td>
                    <td><strong><?php echo rupiah($t['total_tagihan']); ?></strong></td>
                    <td>
                      <?php if ($t['status'] === 'lunas'): ?>
                        <span class="badge badge-success"><i class="fas fa-check"></i> Lunas</span>
                      <?php elseif ($t['status'] === 'pending'): ?>
                        <span class="badge badge-warning"><i class="fas fa-clock"></i> Verifikasi</span>
                      <?php elseif ($t['status'] === 'terlambat'): ?>
                        <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Terlambat</span>
                      <?php else: ?>
                        <span class="badge badge-warning">Belum Bayar</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($t['status'] === 'lunas'): ?>
                        <?php 
                          $payInfo = $paymentsByTagihan[$t['id']] ?? null;
                          $trxCode = $payInfo ? $payInfo['no_transaksi'] : '';
                        ?>
                        <a href="../bukti_digital.php?<?php echo $trxCode ? 'trx=' . urlencode($trxCode) : 'id=' . $t['id']; ?>" target="_blank" class="btn-receipt">
                          <i class="fas fa-file-invoice"></i> Bukti Digital
                        </a>
                      <?php elseif ($t['status'] === 'pending'): ?>
                        <?php 
                          $payInfo = $paymentsByTagihan[$t['id']] ?? null;
                          $pId = $payInfo ? $payInfo['id'] : 0;
                          $trxCode = $payInfo ? $payInfo['no_transaksi'] : '';
                          $hasBukti = !empty($payInfo['bukti_pembayaran']);
                        ?>
                        <div style="display:flex;gap:0.4rem;align-items:center;">
                          <button type="button" onclick="openUploadModal(<?php echo $pId; ?>, '<?php echo e($trxCode); ?>', <?php echo $hasBukti ? 'true' : 'false'; ?>)" class="btn btn-sm btn-outline" style="border-color:#10b981;color:#059669;font-size:0.8rem;">
                            <i class="fas fa-upload"></i> <?php echo $hasBukti ? 'Bukti Terunggah' : 'Upload Bukti'; ?>
                          </button>
                        </div>
                      <?php else: ?>
                        <button onclick="openPayModal(<?php echo $t['id']; ?>, '<?php echo e($t['no_tagihan']); ?>', '<?php echo rupiah($t['total_tagihan']); ?>')" class="btn btn-primary btn-sm">
                          <i class="fas fa-credit-card"></i> Bayar
                        </button>
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

  <!-- Pay Modal (Step 1: Pilih Metode) -->
  <div class="modal-overlay" id="payModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Bayar Tagihan Retribusi</h3>
        <button type="button" class="btn-close" onclick="closePayModal()">&times;</button>
      </div>
      <div>
        <p style="margin-bottom:0.4rem;color:var(--text-secondary);font-size:0.9rem;">No. Tagihan: <strong id="modalNoTagihan" style="color:var(--text-primary);">-</strong></p>
        <p style="margin-bottom:1.25rem;font-size:1.15rem;font-weight:700;color:var(--primary);">Total: <span id="modalTotal">-</span></p>

        <label style="display:block;margin-bottom:0.75rem;font-weight:600;font-size:0.9rem;">Pilih Saluran Pembayaran Resmi:</label>
        <div class="payment-option-grid">
          <label class="payment-option">
            <input type="radio" name="payMethod" value="qris" checked>
            <i class="fas fa-qrcode" style="font-size:1.5rem;color:#059669;"></i>
            <div>
              <strong style="display:block;font-size:0.92rem;">QRIS Real-Time (ShopeePay / All E-Wallet)</strong>
              <small style="color:var(--text-secondary);">Bisa scan via GoPay, OVO, DANA, BCA, Mandiri & ShopeePay</small>
            </div>
          </label>
          <label class="payment-option">
            <input type="radio" name="payMethod" value="transfer_mandiri">
            <i class="fas fa-university" style="font-size:1.5rem;color:#2563eb;"></i>
            <div>
              <strong style="display:block;font-size:0.92rem;">Transfer Bank Mandiri Langsung</strong>
              <small style="color:var(--text-secondary);">No. Rek: 1140027199481 a/n MUHAMMAD ROIF IMAM A</small>
            </div>
          </label>
          <label class="payment-option">
            <input type="radio" name="payMethod" value="transfer_bca">
            <i class="fas fa-university" style="font-size:1.5rem;color:#0284c7;"></i>
            <div>
              <strong style="display:block;font-size:0.92rem;">Transfer Bank Lain / BCA</strong>
              <small style="color:var(--text-secondary);">Transfer ke rekening Mandiri penampung retribusi</small>
            </div>
          </label>
        </div>

        <button type="button" id="btnConfirmPay" onclick="submitPayment()" class="btn btn-primary btn-block">
          <i class="fas fa-arrow-right"></i> Lanjutkan Pembayaran
        </button>
      </div>
    </div>
  </div>

  <!-- Instruction Modal (Step 2: Menampilkan QRIS Asli & Info Transfer) -->
  <div class="modal-overlay" id="instructionModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 id="instTitle"><i class="fas fa-qrcode" style="color:var(--primary);"></i> Pembayaran Retribusi</h3>
        <button type="button" class="btn-close" onclick="closeInstructionModal()">&times;</button>
      </div>
      <div>
        <div style="background:var(--primary-light);padding:0.75rem 1rem;border-radius:var(--radius-md);margin-bottom:1rem;font-size:0.85rem;color:var(--gray-800);">
          <strong>Nomor Transaksi:</strong> <span id="instTrx" style="font-family:monospace;font-weight:700;">-</span><br>
          <strong>Nominal Tagihan:</strong> <span id="instNominal" style="font-weight:700;color:var(--primary);">-</span>
        </div>

        <!-- Section QRIS -->
        <div id="instQrisSection" class="qris-box" style="display:none;">
          <h4 style="margin:0 0 0.5rem;font-size:1rem;color:var(--gray-900);">Scan QRIS Pembayaran</h4>
          <p style="margin:0 0 0.5rem;font-size:0.82rem;color:var(--text-secondary);">Gunakan aplikasi GoPay, OVO, DANA, ShopeePay, Livin' by Mandiri, atau BCA</p>
          <img src="../img/qris.jpg" alt="QRIS SIPARES" class="qris-img">
          <p style="margin:0.5rem 0 0;font-size:0.85rem;font-weight:700;color:var(--primary);">SIPARES - REKENING PENAMPUNG</p>
          <small style="color:var(--text-secondary);font-size:0.8rem;">Bank Mandiri: <strong>1140027199481</strong> a/n <strong>MUHAMMAD ROIF IMAM A</strong></small>
        </div>

        <!-- Section Bank Transfer -->
        <div id="instBankSection" class="bank-card" style="display:none;">
          <h4 style="margin:0 0 0.5rem;font-size:0.95rem;color:#065f46;"><i class="fas fa-university"></i> Rekening Resmi Bank Mandiri</h4>
          <div style="display:flex;align-items:center;justify-content:space-between;background:white;padding:0.75rem;border-radius:8px;border:1px solid #bbf7d0;margin:0.5rem 0;">
            <div>
              <span style="font-size:0.75rem;color:var(--text-secondary);display:block;">Nomor Rekening Mandiri:</span>
              <strong id="mandiriAcc" style="font-size:1.1rem;letter-spacing:1px;color:#1e293b;">1140027199481</strong>
            </div>
            <button type="button" class="copy-btn" onclick="copyText('1140027199481')">Salin</button>
          </div>
          <p style="margin:0.35rem 0 0;font-size:0.85rem;color:#065f46;">
            Atas Nama: <strong>MUHAMMAD ROIF IMAM A</strong>
          </p>
        </div>

        <!-- Panduan Langkah -->
        <div style="margin-bottom:1.25rem;">
          <strong style="font-size:0.85rem;display:block;margin-bottom:0.5rem;">Langkah Pembayaran:</strong>
          <ol id="instSteps" style="padding-left:1.25rem;font-size:0.82rem;color:var(--text-secondary);line-height:1.6;margin:0;"></ol>
        </div>

        <div style="display:flex;gap:0.75rem;">
          <button type="button" onclick="goToUploadAfterInst()" class="btn btn-primary btn-block">
            <i class="fas fa-upload"></i> Upload Bukti Sekarang
          </button>
          <button type="button" onclick="closeInstructionModal(true)" class="btn btn-outline btn-block">
            Bayar Nanti
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Upload Bukti Modal -->
  <div class="modal-overlay" id="uploadModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3><i class="fas fa-file-upload" style="color:var(--primary);"></i> Upload Bukti Pembayaran</h3>
        <button type="button" class="btn-close" onclick="closeUploadModal()">&times;</button>
      </div>
      <form id="uploadBuktiForm" onsubmit="submitUploadBukti(event)">
        <input type="hidden" name="id" id="uploadPayId" value="">
        <p style="margin-bottom:0.5rem;font-size:0.9rem;color:var(--text-secondary);">No. Transaksi: <strong id="uploadTrxNo" style="color:var(--text-primary);">-</strong></p>
        
        <div id="alreadyUploadedNotice" style="display:none;background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:0.65rem 0.9rem;border-radius:var(--radius-md);font-size:0.85rem;margin-bottom:1rem;">
          <i class="fas fa-check-circle"></i> Bukti pembayaran telah diunggah sebelumnya. Anda dapat mengunggah file baru untuk menggantinya jika diperlukan.
        </div>

        <div style="margin-bottom:1.25rem;">
          <label style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.9rem;">Pilih File Bukti (Foto/Struk/Screenshot transfer):</label>
          <input type="file" name="bukti" id="inputBuktiFile" accept="image/*,.pdf" required style="width:100%;padding:0.6rem;border:1px solid var(--border-color);border-radius:var(--radius-lg);font-size:0.9rem;">
          <small style="display:block;color:var(--text-secondary);margin-top:0.35rem;font-size:0.8rem;">Format didukung: JPG, PNG, PDF (Maks. 5MB)</small>
        </div>

        <button type="submit" id="btnUploadSubmit" class="btn btn-primary btn-block">
          <i class="fas fa-cloud-upload-alt"></i> Kirim Bukti Pembayaran
        </button>
      </form>
    </div>
  </div>

  <script src="../js/main.js"></script>
  <script>
    let activeTagihanId = null;
    let currentCreatedPayId = null;
    let currentCreatedTrx = null;

    function openPayModal(tagihanId, noTagihan, totalStr) {
      activeTagihanId = tagihanId;
      document.getElementById('modalNoTagihan').textContent = noTagihan;
      document.getElementById('modalTotal').textContent = totalStr;
      document.getElementById('payModal').classList.add('active');
    }

    function closePayModal() {
      document.getElementById('payModal').classList.remove('active');
      activeTagihanId = null;
    }

    function submitPayment() {
      if (!activeTagihanId) return;

      const method = document.querySelector('input[name="payMethod"]:checked').value;
      const btn = document.getElementById('btnConfirmPay');
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

      fetch('../api/pembayaran.php?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          tagihan_id: activeTagihanId,
          metode_pembayaran: method
        })
      })
      .then(res => res.json())
      .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-arrow-right"></i> Lanjutkan Pembayaran';

        if (res.success) {
          closePayModal();
          showInstructions(res);
        } else {
          alert('Gagal: ' + (res.message || 'Terjadi kesalahan'));
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-arrow-right"></i> Lanjutkan Pembayaran';
        alert('Terjadi kesalahan jaringan.');
      });
    }

    function showInstructions(res) {
      currentCreatedPayId = res.pembayaran_id || res.id;
      currentCreatedTrx = res.no_transaksi;

      document.getElementById('instTrx').textContent = res.no_transaksi;
      document.getElementById('instNominal').textContent = res.payment_info ? res.payment_info.nominal_formatted : '-';

      const pInfo = res.payment_info || {};
      const qrisBox = document.getElementById('instQrisSection');
      const bankBox = document.getElementById('instBankSection');
      const stepsList = document.getElementById('instSteps');
      stepsList.innerHTML = '';

      if (pInfo.type === 'qris') {
        qrisBox.style.display = 'block';
        bankBox.style.display = 'none';
      } else {
        qrisBox.style.display = 'none';
        bankBox.style.display = 'block';
      }

      if (pInfo.instructions && Array.isArray(pInfo.instructions)) {
        pInfo.instructions.forEach(step => {
          const li = document.createElement('li');
          li.textContent = step;
          stepsList.appendChild(li);
        });
      }

      document.getElementById('instructionModal').classList.add('active');
    }

    function closeInstructionModal(reload = false) {
      document.getElementById('instructionModal').classList.remove('active');
      if (reload) {
        window.location.reload();
      }
    }

    function goToUploadAfterInst() {
      const pId = currentCreatedPayId;
      const trx = currentCreatedTrx;
      closeInstructionModal(false);
      openUploadModal(pId, trx, false);
    }

    function openUploadModal(payId, trxNo, hasBukti) {
      document.getElementById('uploadPayId').value = payId;
      document.getElementById('uploadTrxNo').textContent = trxNo;
      document.getElementById('alreadyUploadedNotice').style.display = hasBukti ? 'block' : 'none';
      document.getElementById('uploadModal').classList.add('active');
    }

    function closeUploadModal() {
      document.getElementById('uploadModal').classList.remove('active');
    }

    function submitUploadBukti(e) {
      e.preventDefault();
      const form = document.getElementById('uploadBuktiForm');
      const formData = new FormData(form);
      const btn = document.getElementById('btnUploadSubmit');

      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengunggah...';

      fetch('../api/pembayaran.php?action=upload', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Kirim Bukti Pembayaran';

        if (res.success) {
          alert('Bukti pembayaran berhasil diunggah! Petugas/Admin akan memverifikasi pembayaran Anda.');
          closeUploadModal();
          window.location.reload();
        } else {
          alert('Gagal unggah: ' + (res.message || 'Terjadi kesalahan'));
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Kirim Bukti Pembayaran';
        alert('Terjadi kesalahan jaringan.');
      });
    }

    function copyText(txt) {
      navigator.clipboard.writeText(txt).then(() => {
        alert('Nomor rekening ' + txt + ' berhasil disalin!');
      });
    }
  </script>
</body>

</html>
