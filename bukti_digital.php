<?php
/**
 * ========================================
 * SIPARES - Bukti Pembayaran Digital Resmi
 * ========================================
 * Menampilkan e-Kwitansi resmi dengan fitur cetak & unduh PDF
 */

require_once __DIR__ . '/config/init.php';
require_once INCLUDES_PATH . 'Pembayaran.php';

$pembayaranModel = new Pembayaran();
$identifier = $_GET['trx'] ?? $_GET['id'] ?? '';

// Jika tidak diberikan parameter, cari pembayaran terakhir user yang login atau pembayaran sampel 1
if (empty($identifier)) {
    if (Session::isLoggedIn()) {
        $userHistory = $pembayaranModel->getByUserId(Session::userId(), 1);
        if (!empty($userHistory)) {
            $identifier = $userHistory[0]['no_transaksi'];
        }
    }
    if (empty($identifier)) {
        $identifier = 'TRX-2025120023'; // Sampel default
    }
}

$data = $pembayaranModel->getReceiptData($identifier);

if (!$data) {
    die("<h3>Bukti pembayaran digital tidak ditemukan.</h3><p><a href='riwayat.html'>Kembali ke Riwayat</a></p>");
}

$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=" . urlencode(APP_URL . "/bukti_digital.php?trx=" . $data['no_transaksi']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bukti Pembayaran Digital - <?php echo e($data['no_transaksi']); ?> - SIPARES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    :root {
      --primary-color: #059669;
      --primary-dark: #065f46;
      --text-dark: #1f2937;
      --text-muted: #6b7280;
      --bg-receipt: #ffffff;
      --border-receipt: #e5e7eb;
    }

    body {
      background: #f3f4f6;
      color: var(--text-dark);
      font-family: 'Plus Jakarta Sans', sans-serif;
      margin: 0;
      padding: 2rem 1rem;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .toolbar {
      width: 100%;
      max-width: 800px;
      margin-bottom: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .toolbar-left, .toolbar-right {
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }

    .btn-action {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.65rem 1.25rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.9rem;
      text-decoration: none;
      cursor: pointer;
      border: none;
      transition: all 0.2s ease;
    }

    .btn-action.btn-back {
      background: #ffffff;
      color: #374151;
      border: 1px solid #d1d5db;
    }

    .btn-action.btn-back:hover {
      background: #f9fafb;
    }

    .btn-action.btn-print {
      background: #059669;
      color: #ffffff;
    }

    .btn-action.btn-print:hover {
      background: #047857;
      box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    .receipt-container {
      width: 100%;
      max-width: 800px;
      background: var(--bg-receipt);
      border-radius: 16px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
      border: 1px solid var(--border-receipt);
      padding: 2.5rem;
      position: relative;
      overflow: hidden;
      box-sizing: border-box;
    }

    /* Watermark Lunas */
    .receipt-watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-30deg);
      font-size: 8rem;
      font-weight: 900;
      color: rgba(5, 150, 105, 0.04);
      letter-spacing: 0.5rem;
      pointer-events: none;
      user-select: none;
      z-index: 1;
    }

    /* Header */
    .receipt-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 2px dashed #d1d5db;
      padding-bottom: 1.75rem;
      margin-bottom: 1.75rem;
      position: relative;
      z-index: 2;
    }

    .receipt-brand {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .receipt-logo {
      width: 52px;
      height: 52px;
      background: linear-gradient(135deg, #10b981 0%, #047857 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-size: 1.6rem;
    }

    .receipt-brand-text h2 {
      margin: 0;
      font-size: 1.4rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      color: #065f46;
    }

    .receipt-brand-text p {
      margin: 0.25rem 0 0;
      font-size: 0.85rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    .receipt-title-box {
      text-align: right;
    }

    .receipt-badge-status {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      background: #d1fae5;
      color: #047857;
      padding: 0.35rem 0.85rem;
      border-radius: 9999px;
      font-weight: 700;
      font-size: 0.8rem;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      margin-bottom: 0.5rem;
    }

    .receipt-no {
      font-family: 'Space Mono', monospace;
      font-weight: 700;
      font-size: 1.05rem;
      color: #111827;
      margin: 0;
    }

    .receipt-date {
      margin: 0.2rem 0 0;
      font-size: 0.8rem;
      color: var(--text-muted);
    }

    /* Grid Info */
    .receipt-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      margin-bottom: 2rem;
      position: relative;
      z-index: 2;
    }

    .info-card {
      background: #f9fafb;
      border: 1px solid #f3f4f6;
      border-radius: 12px;
      padding: 1.25rem;
    }

    .info-card h4 {
      margin: 0 0 0.85rem;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .info-card h4 i {
      color: var(--primary-color);
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.5rem;
      font-size: 0.88rem;
    }

    .info-row:last-child {
      margin-bottom: 0;
    }

    .info-label {
      color: var(--text-muted);
    }

    .info-value {
      font-weight: 600;
      color: #1f2937;
      text-align: right;
    }

    /* Detail Table */
    .table-section {
      margin-bottom: 2rem;
      position: relative;
      z-index: 2;
    }

    .table-section table {
      width: 100%;
      border-collapse: collapse;
    }

    .table-section th {
      background: #f9fafb;
      padding: 0.85rem 1rem;
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--text-muted);
      border-top: 1px solid #e5e7eb;
      border-bottom: 1px solid #e5e7eb;
      text-align: left;
    }

    .table-section th:last-child {
      text-align: right;
    }

    .table-section td {
      padding: 0.9rem 1rem;
      font-size: 0.9rem;
      border-bottom: 1px solid #f3f4f6;
    }

    .table-section td:last-child {
      text-align: right;
      font-weight: 600;
    }

    .table-section .total-row td {
      border-top: 2px solid #e5e7eb;
      border-bottom: none;
      padding-top: 1.25rem;
      font-size: 1.15rem;
      font-weight: 800;
      color: #065f46;
    }

    /* Footer & Verification */
    .receipt-footer {
      border-top: 1px solid #e5e7eb;
      padding-top: 1.75rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1.5rem;
      position: relative;
      z-index: 2;
    }

    .verification-box {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .qr-img {
      width: 84px;
      height: 84px;
      border-radius: 8px;
      border: 1px solid #e5e7eb;
      padding: 3px;
      background: #ffffff;
    }

    .verification-text h5 {
      margin: 0 0 0.25rem;
      font-size: 0.85rem;
      color: #111827;
      font-weight: 700;
    }

    .verification-text p {
      margin: 0;
      font-size: 0.75rem;
      color: var(--text-muted);
      line-height: 1.4;
      max-width: 320px;
    }

    .verification-code {
      font-family: 'Space Mono', monospace;
      font-size: 0.75rem;
      background: #f3f4f6;
      padding: 0.2rem 0.4rem;
      border-radius: 4px;
      display: inline-block;
      margin-top: 0.35rem;
      color: #374151;
      font-weight: 600;
    }

    .stamp-box {
      text-align: center;
      border: 2px dashed #059669;
      border-radius: 12px;
      padding: 0.75rem 1.25rem;
      background: rgba(16, 185, 129, 0.03);
    }

    .stamp-box .stamp-title {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #059669;
      margin-bottom: 0.2rem;
    }

    .stamp-box .stamp-time {
      font-size: 0.75rem;
      color: #4b5563;
      font-weight: 600;
    }

    .stamp-box .stamp-valid {
      font-size: 0.7rem;
      color: #059669;
      font-weight: 700;
    }

    /* Print Styles */
    @media print {
      body {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
      }

      .toolbar {
        display: none !important;
      }

      .receipt-container {
        border: none !important;
        box-shadow: none !important;
        max-width: 100% !important;
        padding: 1.5cm !important;
      }

      .receipt-watermark {
        opacity: 0.15;
      }
    }

    @media (max-width: 680px) {
      .receipt-header {
        flex-direction: column;
        gap: 1rem;
      }
      .receipt-title-box {
        text-align: left;
      }
      .receipt-grid {
        grid-template-columns: 1fr;
      }
      .receipt-footer {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>

<body>

  <!-- Top Toolbar -->
  <div class="toolbar">
    <div class="toolbar-left">
      <?php if (Session::isLoggedIn()): ?>
        <a href="user/dashboard.php" class="btn-action btn-back"><i class="fas fa-arrow-left"></i> Dashboard</a>
        <a href="user/riwayat.php" class="btn-action btn-back"><i class="fas fa-history"></i> Riwayat Pembayaran</a>
      <?php else: ?>
        <a href="riwayat.html" class="btn-action btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Riwayat</a>
      <?php endif; ?>
    </div>
    <div class="toolbar-right">
      <button onclick="window.print()" class="btn-action btn-print">
        <i class="fas fa-print"></i> Cetak / Simpan PDF
      </button>
    </div>
  </div>

  <!-- Receipt Card -->
  <div class="receipt-container" id="receipt">
    <div class="receipt-watermark">LUNAS</div>

    <!-- Header -->
    <div class="receipt-header">
      <div class="receipt-brand">
        <div class="receipt-logo"><i class="fas fa-recycle"></i></div>
        <div class="receipt-brand-text">
          <h2>SIPARES</h2>
          <p>Sistem Pembayaran Retribusi Sampah &bull; DLH Kota</p>
        </div>
      </div>
      <div class="receipt-title-box">
        <div class="receipt-badge-status">
          <i class="fas fa-check-circle"></i> Pembayaran Berhasil
        </div>
        <div class="receipt-no"><?php echo e($data['no_transaksi']); ?></div>
        <div class="receipt-date"><?php echo e($data['tanggal_bayar_formatted']); ?></div>
      </div>
    </div>

    <!-- Info Grid -->
    <div class="receipt-grid">
      <!-- Data Wajib Retribusi -->
      <div class="info-card">
        <h4><i class="fas fa-user-circle"></i> Identitas Wajib Retribusi</h4>
        <div class="info-row">
          <span class="info-label">Nama Pelanggan</span>
          <span class="info-value"><?php echo e($data['nama_lengkap']); ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">No. Pelanggan</span>
          <span class="info-value"><?php echo e($data['no_pelanggan']); ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Kategori / Tarif</span>
          <span class="info-value"><?php echo kategoriLabel($data['kategori']); ?> (<?php echo e($data['nama_tarif']); ?>)</span>
        </div>
        <div class="info-row">
          <span class="info-label">Alamat</span>
          <span class="info-value"><?php echo e($data['alamat']); ?> RT <?php echo e($data['rt']); ?>/RW <?php echo e($data['rw']); ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Kel. / Kec.</span>
          <span class="info-value"><?php echo e($data['kelurahan']); ?>, <?php echo e($data['kecamatan']); ?></span>
        </div>
      </div>

      <!-- Data Tagihan & Transaksi -->
      <div class="info-card">
        <h4><i class="fas fa-receipt"></i> Rincian Transaksi</h4>
        <div class="info-row">
          <span class="info-label">No. Tagihan</span>
          <span class="info-value"><?php echo e($data['no_tagihan']); ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Periode Retribusi</span>
          <span class="info-value"><?php echo e($data['periode_nama']); ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Kanal Pembayaran</span>
          <span class="info-value"><?php echo e($data['metode_nama']); ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Jatuh Tempo</span>
          <span class="info-value"><?php echo e($data['tanggal_jatuh_tempo_formatted']); ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Status Verifikasi</span>
          <span class="info-value" style="color: #059669;">Tervalidasi Otomatis</span>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="table-section">
      <table>
        <thead>
          <tr>
            <th>Deskripsi Layanan</th>
            <th>Frekuensi Layanan</th>
            <th>Jumlah</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <strong>Retribusi Pengangkutan & Pengelolaan Sampah</strong><br>
              <small style="color:var(--text-muted);">Periode <?php echo e($data['periode_nama']); ?></small>
            </td>
            <td><?php echo e($data['frekuensi_angkut'] ?? '2x seminggu'); ?></td>
            <td><?php echo rupiah($data['nominal_tagihan']); ?></td>
          </tr>
          <tr>
            <td>
              <strong>Biaya Administrasi & Transaksi Digital</strong><br>
              <small style="color:var(--text-muted);">Biaya pemeliharaan sistem & kanal pembayaran</small>
            </td>
            <td>1x</td>
            <td><?php echo rupiah($data['biaya_admin']); ?></td>
          </tr>
          <?php if (($data['denda'] ?? 0) > 0): ?>
          <tr>
            <td>
              <strong style="color:#dc2626;">Denda Keterlambatan</strong><br>
              <small style="color:var(--text-muted);">Keterlambatan pembayaran tagihan</small>
            </td>
            <td>-</td>
            <td style="color:#dc2626;"><?php echo rupiah($data['denda']); ?></td>
          </tr>
          <?php endif; ?>
          <tr class="total-row">
            <td colspan="2">TOTAL DIBAYARKAN</td>
            <td><?php echo rupiah($data['nominal_bayar']); ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer & Verification -->
    <div class="receipt-footer">
      <div class="verification-box">
        <img src="<?php echo $qrUrl; ?>" alt="QR Validasi" class="qr-img">
        <div class="verification-text">
          <h5>Validasi Dokumen Digital Resmi</h5>
          <p>Dokumen ini diterbitkan sah oleh Sistem Pembayaran Retribusi Sampah (SIPARES) dan diakui sebagai bukti pelunasan retribusi resmi.</p>
          <div class="verification-code">KODE: <?php echo e($data['kode_verifikasi']); ?></div>
        </div>
      </div>

      <div class="stamp-box">
        <div class="stamp-title"><i class="fas fa-stamp"></i> BADAN RETRIBUSI DLH</div>
        <div class="stamp-valid">&#10004; LUNAS / VERIFIED</div>
        <div class="stamp-time"><?php echo e($data['tanggal_bayar_formatted']); ?></div>
      </div>
    </div>
  </div>

</body>
</html>

