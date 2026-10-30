<?php
/**
 * ========================================
 * SIPARES - Pembayaran API
 * ========================================
 * Endpoint untuk mengelola pembayaran
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

require_once __DIR__ . '/../config/init.php';
require_once INCLUDES_PATH . 'Pembayaran.php';
require_once INCLUDES_PATH . 'Tagihan.php';

$action = $_GET['action'] ?? '';
$pembayaranModel = new Pembayaran();

// Cek login untuk beberapa action
$requireLogin = ['create', 'history', 'detail', 'upload'];
if (in_array($action, $requireLogin) && !Session::isLoggedIn()) {
  jsonResponse(['success' => false, 'message' => 'Silakan login terlebih dahulu'], 401);
}

switch ($action) {
  case 'create':
    handleCreate($pembayaranModel);
    break;

  case 'history':
    handleHistory($pembayaranModel);
    break;

  case 'detail':
    handleDetail($pembayaranModel);
    break;

  case 'check':
    handleCheck($pembayaranModel);
    break;

  case 'upload':
    handleUpload($pembayaranModel);
    break;

  case 'methods':
    handleMethods();
    break;

  case 'statistics':
    handleStatistics($pembayaranModel);
    break;

  case 'recent':
    handleRecent($pembayaranModel);
    break;

  case 'receipt':
    handleReceipt($pembayaranModel);
    break;

  default:
    jsonResponse(['success' => false, 'message' => 'Action tidak valid'], 400);
}

/**
 * Handle buat pembayaran baru
 */
function handleCreate($pembayaranModel)
{
  if (!isPost()) {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
  }

  $data = json_decode(file_get_contents('php://input'), true);

  $tagihanId = (int) ($data['tagihan_id'] ?? 0);
  $metodePembayaran = sanitize($data['metode_pembayaran'] ?? '');

  if (!$tagihanId) {
    jsonResponse(['success' => false, 'message' => 'ID tagihan diperlukan'], 400);
  }

  if (empty($metodePembayaran)) {
    jsonResponse(['success' => false, 'message' => 'Metode pembayaran diperlukan'], 400);
  }

  // Validasi metode pembayaran
  $validMethods = ['qris', 'transfer_bca', 'transfer_bni', 'transfer_mandiri', 'transfer_bri', 'minimarket'];
  if (!in_array($metodePembayaran, $validMethods)) {
    jsonResponse(['success' => false, 'message' => 'Metode pembayaran tidak valid'], 400);
  }

  // Verifikasi tagihan milik user yang login
  $tagihanModel = new Tagihan();
  $tagihan = $tagihanModel->getById($tagihanId);

  if (!$tagihan || $tagihan['user_id'] != Session::userId()) {
    jsonResponse(['success' => false, 'message' => 'Tagihan tidak ditemukan'], 404);
  }

  $result = $pembayaranModel->create([
    'tagihan_id' => $tagihanId,
    'metode_pembayaran' => $metodePembayaran
  ]);

  if ($result['success']) {
    // Tambahkan info pembayaran berdasarkan metode
    $paymentInfo = getPaymentInfo($metodePembayaran, $result['no_transaksi'], $tagihan['total_tagihan']);
    $result['payment_info'] = $paymentInfo;
  }

  jsonResponse($result, $result['success'] ? 200 : 400);
}

/**
 * Get payment info berdasarkan metode
 */
function getPaymentInfo($metode, $noTransaksi, $nominal)
{
  $info = [
    'no_transaksi' => $noTransaksi,
    'nominal' => $nominal,
    'nominal_formatted' => rupiah($nominal),
    'expired' => date('Y-m-d H:i:s', strtotime('+24 hours'))
  ];

  switch ($metode) {
    case 'qris':
      $info['type'] = 'qris';
      $info['qr_image'] = '../img/qris.jpg';
      $info['merchant_name'] = 'SIPARES - Retribusi Kebersihan';
      $info['account_info'] = '1140027199481 a/n MUHAMMAD ROIF IMAM A (Bank Mandiri)';
      $info['instructions'] = [
        'Buka aplikasi e-wallet atau mobile banking (GoPay, OVO, DANA, ShopeePay, Livin by Mandiri, BCA Mobile, dll)',
        'Pilih menu Scan QRIS',
        'Arahkan kamera ke kode QRIS di layar atau transfer ke Mandiri: 1140027199481 a/n MUHAMMAD ROIF IMAM A',
        'Pastikan nominal transfer sesuai: ' . rupiah($nominal),
        'Selesaikan pembayaran dan upload bukti transfer'
      ];
      break;

    case 'transfer_mandiri':
      $info['type'] = 'transfer';
      $info['bank'] = 'Mandiri';
      $info['no_rekening'] = '1140027199481';
      $info['nama_rekening'] = 'MUHAMMAD ROIF IMAM A';
      $info['instructions'] = [
        'Login ke Livin\' by Mandiri atau ATM Mandiri',
        'Pilih menu Transfer > Rekening Mandiri',
        'Masukkan nomor rekening: 1140027199481',
        'Nama penerima: MUHAMMAD ROIF IMAM A',
        'Masukkan nominal: ' . rupiah($nominal),
        'Masukkan keterangan: ' . $noTransaksi,
        'Konfirmasi transfer dan upload bukti pembayaran'
      ];
      break;

    case 'transfer_bca':
      $info['type'] = 'transfer';
      $info['bank'] = 'BCA';
      $info['no_rekening'] = '1140027199481';
      $info['nama_rekening'] = 'MUHAMMAD ROIF IMAM A';
      $info['instructions'] = [
        'Login ke BCA Mobile atau KlikBCA',
        'Pilih menu Transfer Antar Bank ke Bank Mandiri',
        'Masukkan nomor rekening Mandiri: 1140027199481 a/n MUHAMMAD ROIF IMAM A',
        'Masukkan nominal: ' . rupiah($nominal),
        'Masukkan berita: ' . $noTransaksi,
        'Konfirmasi dan simpan bukti transfer'
      ];
      break;

    case 'transfer_bni':
      $info['type'] = 'transfer';
      $info['bank'] = 'BNI';
      $info['no_rekening'] = '1140027199481';
      $info['nama_rekening'] = 'MUHAMMAD ROIF IMAM A (Bank Mandiri)';
      $info['instructions'] = [
        'Login ke BNI Mobile Banking',
        'Pilih menu Transfer Antar Bank > Bank Mandiri',
        'Masukkan nomor rekening: 1140027199481',
        'Masukkan nominal: ' . rupiah($nominal),
        'Masukkan berita: ' . $noTransaksi,
        'Konfirmasi dan simpan bukti transfer'
      ];
      break;

    case 'transfer_bri':
      $info['type'] = 'transfer';
      $info['bank'] = 'BRI';
      $info['no_rekening'] = '5544332211';
      $info['nama_rekening'] = 'SIPARES PEMKOT';
      $info['instructions'] = [
        'Login ke BRImo',
        'Pilih menu Transfer',
        'Masukkan nomor rekening: 5544332211',
        'Masukkan nominal: ' . rupiah($nominal),
        'Masukkan berita: ' . $noTransaksi,
        'Konfirmasi dan simpan bukti transfer'
      ];
      break;

    case 'minimarket':
      $info['type'] = 'minimarket';
      $info['kode_pembayaran'] = strtoupper(substr($noTransaksi, -8));
      $info['instructions'] = [
        'Kunjungi Indomaret/Alfamart terdekat',
        'Katakan kepada kasir: bayar SIPARES',
        'Sebutkan kode pembayaran: ' . strtoupper(substr($noTransaksi, -8)),
        'Bayar sesuai nominal',
        'Simpan struk sebagai bukti'
      ];
      break;
  }

  return $info;
}

/**
 * Handle riwayat pembayaran user
 */
function handleHistory($pembayaranModel)
{
  $userId = Session::userId();
  $limit = (int) ($_GET['limit'] ?? 50);

  $history = $pembayaranModel->getByUserId($userId, $limit);

  // Format data
  foreach ($history as &$item) {
    $item['metode_label'] = metodePembayaranLabel($item['metode_pembayaran']);
    $item['nominal_formatted'] = rupiah($item['nominal_bayar']);
    $item['tanggal_formatted'] = $item['tanggal_bayar'] ? tanggal_indo($item['tanggal_bayar'], 'datetime') : '-';
    $item['periode'] = namaBulan($item['periode_bulan']) . ' ' . $item['periode_tahun'];
  }

  jsonResponse([
    'success' => true,
    'data' => $history,
    'total' => count($history)
  ]);
}

/**
 * Handle detail pembayaran
 */
function handleDetail($pembayaranModel)
{
  $id = (int) ($_GET['id'] ?? 0);

  if (!$id) {
    jsonResponse(['success' => false, 'message' => 'ID pembayaran diperlukan'], 400);
  }

  $pembayaran = $pembayaranModel->getById($id);

  if (!$pembayaran) {
    jsonResponse(['success' => false, 'message' => 'Pembayaran tidak ditemukan'], 404);
  }

  // Pastikan milik user yang login
  if ($pembayaran['user_id'] != Session::userId()) {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
  }

  // Format data
  $pembayaran['metode_label'] = metodePembayaranLabel($pembayaran['metode_pembayaran']);
  $pembayaran['nominal_formatted'] = rupiah($pembayaran['nominal_bayar']);
  $pembayaran['tanggal_formatted'] = $pembayaran['tanggal_bayar'] ? tanggal_indo($pembayaran['tanggal_bayar'], 'datetime') : '-';

  jsonResponse(['success' => true, 'data' => $pembayaran]);
}

/**
 * Handle cek status pembayaran
 */
function handleCheck($pembayaranModel)
{
  $noTransaksi = sanitize($_GET['no_transaksi'] ?? '');

  if (empty($noTransaksi)) {
    jsonResponse(['success' => false, 'message' => 'Nomor transaksi diperlukan'], 400);
  }

  $pembayaran = $pembayaranModel->getByNoTransaksi($noTransaksi);

  if (!$pembayaran) {
    jsonResponse(['success' => false, 'message' => 'Transaksi tidak ditemukan'], 404);
  }

  jsonResponse([
    'success' => true,
    'data' => [
      'no_transaksi' => $pembayaran['no_transaksi'],
      'status' => $pembayaran['status'],
      'nominal' => rupiah($pembayaran['nominal_bayar']),
      'metode' => metodePembayaranLabel($pembayaran['metode_pembayaran']),
      'tanggal_bayar' => $pembayaran['tanggal_bayar'] ? tanggal_indo($pembayaran['tanggal_bayar'], 'datetime') : null
    ]
  ]);
}

/**
 * Handle upload bukti pembayaran
 */
function handleUpload($pembayaranModel)
{
  if (!isPost()) {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
  }

  $id = (int) ($_POST['id'] ?? 0);

  if (!$id) {
    jsonResponse(['success' => false, 'message' => 'ID pembayaran diperlukan'], 400);
  }

  // Verifikasi pembayaran
  $pembayaran = $pembayaranModel->getById($id);
  if (!$pembayaran || $pembayaran['user_id'] != Session::userId()) {
    jsonResponse(['success' => false, 'message' => 'Pembayaran tidak ditemukan'], 404);
  }

  if (!isset($_FILES['bukti'])) {
    jsonResponse(['success' => false, 'message' => 'File bukti pembayaran diperlukan'], 400);
  }

  $result = $pembayaranModel->uploadBukti($id, $_FILES['bukti']);
  jsonResponse($result, $result['success'] ? 200 : 400);
}

/**
 * Handle list metode pembayaran
 */
function handleMethods()
{
  $methods = [
    [
      'id' => 'qris',
      'name' => 'QRIS',
      'description' => 'Bayar dengan scan QR (GoPay, OVO, DANA, dll)',
      'icon' => 'fas fa-qrcode'
    ],
    [
      'id' => 'transfer_bca',
      'name' => 'Transfer BCA',
      'description' => 'Transfer via m-BCA atau KlikBCA',
      'icon' => 'fas fa-university'
    ],
    [
      'id' => 'transfer_bni',
      'name' => 'Transfer BNI',
      'description' => 'Transfer via BNI Mobile Banking',
      'icon' => 'fas fa-university'
    ],
    [
      'id' => 'transfer_mandiri',
      'name' => 'Transfer Mandiri',
      'description' => 'Transfer via Livin\' by Mandiri',
      'icon' => 'fas fa-university'
    ],
    [
      'id' => 'transfer_bri',
      'name' => 'Transfer BRI',
      'description' => 'Transfer via BRImo',
      'icon' => 'fas fa-university'
    ],
    [
      'id' => 'minimarket',
      'name' => 'Minimarket',
      'description' => 'Bayar di Indomaret/Alfamart',
      'icon' => 'fas fa-store'
    ]
  ];

  jsonResponse(['success' => true, 'data' => $methods]);
}

/**
 * Handle statistik pembayaran
 */
function handleStatistics($pembayaranModel)
{
  $stats = $pembayaranModel->getStatistics();
  jsonResponse(['success' => true, 'data' => $stats]);
}

/**
 * Handle pembayaran terbaru
 */
function handleRecent($pembayaranModel)
{
  $limit = (int) ($_GET['limit'] ?? 10);
  $recent = $pembayaranModel->getRecent($limit);

  // Format data
  foreach ($recent as &$item) {
    $item['metode_label'] = metodePembayaranLabel($item['metode_pembayaran']);
    $item['nominal_formatted'] = rupiah($item['nominal_bayar']);
    $item['created_formatted'] = tanggal_indo($item['created_at'], 'datetime');
  }

  jsonResponse(['success' => true, 'data' => $recent]);
}
/**
 * Handle data bukti pembayaran digital (e-Kwitansi)
 */
function handleReceipt($pembayaranModel)
{
  $identifier = $_GET['id'] ?? $_GET['trx'] ?? $_GET['no_transaksi'] ?? '';

  if (empty($identifier)) {
    jsonResponse(['success' => false, 'message' => 'Parameter ID atau nomor transaksi diperlukan'], 400);
  }

  $receipt = $pembayaranModel->getReceiptData($identifier);

  if (!$receipt) {
    jsonResponse(['success' => false, 'message' => 'Data bukti digital tidak ditemukan'], 404);
  }

  jsonResponse([
    'success' => true,
    'data' => $receipt
  ]);
}
?>

