<?php
/**
 * ========================================
 * SIPARES - Admin API
 * ========================================
 * Endpoint untuk admin panel
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

require_once __DIR__ . '/../config/init.php';
require_once INCLUDES_PATH . 'Admin.php';
require_once INCLUDES_PATH . 'User.php';
require_once INCLUDES_PATH . 'Tagihan.php';
require_once INCLUDES_PATH . 'Pembayaran.php';

$action = $_GET['action'] ?? '';
$adminModel = new Admin();

// Action yang tidak perlu login admin
$publicActions = ['login'];

// Cek login admin
if (!in_array($action, $publicActions) && !Session::isAdminLoggedIn()) {
  jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai admin.'], 401);
}

switch ($action) {
  case 'login':
    handleLogin($adminModel);
    break;

  case 'logout':
    handleLogout($adminModel);
    break;

  case 'dashboard':
    handleDashboard();
    break;

  case 'users':
    handleUsers();
    break;

  case 'tagihan':
    handleTagihan();
    break;

  case 'pembayaran':
    handlePembayaran();
    break;

  case 'confirm_payment':
    handleConfirmPayment();
    break;

  case 'reject_payment':
    handleRejectPayment();
    break;

  case 'create_tagihan_bulk':
    handleCreateTagihanBulk();
    break;

  case 'reports':
    handleReports();
    break;

  default:
    jsonResponse(['success' => false, 'message' => 'Action tidak valid'], 400);
}

/**
 * Handle admin login
 */
function handleLogin($adminModel)
{
  if (!isPost()) {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
  }

  $data = json_decode(file_get_contents('php://input'), true);

  $username = sanitize($data['username'] ?? '');
  $password = $data['password'] ?? '';

  if (empty($username) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Username dan password wajib diisi'], 400);
  }

  $result = $adminModel->login($username, $password);

  if ($result['success']) {
    jsonResponse([
      'success' => true,
      'message' => 'Login berhasil',
      'admin' => [
        'id' => $result['admin']['id'],
        'username' => $result['admin']['username'],
        'nama_lengkap' => $result['admin']['nama_lengkap'],
        'role' => $result['admin']['role']
      ]
    ]);
  } else {
    jsonResponse($result, 401);
  }
}

/**
 * Handle admin logout
 */
function handleLogout($adminModel)
{
  $result = $adminModel->logout();
  jsonResponse($result);
}

/**
 * Handle dashboard data
 */
function handleDashboard()
{
  $userModel = new User();
  $tagihanModel = new Tagihan();
  $pembayaranModel = new Pembayaran();

  $data = [
    // Statistik user
    'total_users' => $userModel->count(),
    'users_aktif' => $userModel->countActive(),
    'users_per_kategori' => $userModel->countByKategori(),

    // Statistik tagihan
    'tagihan_stats' => $tagihanModel->getStatistics(),

    // Statistik pembayaran
    'pembayaran_stats' => $pembayaranModel->getStatistics(),

    // Pembayaran pending (perlu konfirmasi)
    'pending_payments' => $pembayaranModel->count('pending'),

    // Pembayaran terbaru
    'recent_payments' => $pembayaranModel->getRecent(5)
  ];

  jsonResponse(['success' => true, 'data' => $data]);
}

/**
 * Handle users management
 */
function handleUsers()
{
  $userModel = new User();

  if (isGet()) {
    $limit = (int) ($_GET['limit'] ?? 100);
    $offset = (int) ($_GET['offset'] ?? 0);

    $users = $userModel->getAll($limit, $offset);

    // Remove password dari response
    foreach ($users as &$user) {
      unset($user['password']);
      $user['kategori_label'] = kategoriLabel($user['kategori']);
    }

    jsonResponse([
      'success' => true,
      'data' => $users,
      'total' => $userModel->count()
    ]);
  }
}

/**
 * Handle tagihan management
 */
function handleTagihan()
{
  $tagihanModel = new Tagihan();

  if (isGet()) {
    $limit = (int) ($_GET['limit'] ?? 100);
    $offset = (int) ($_GET['offset'] ?? 0);
    $status = $_GET['status'] ?? null;

    $tagihan = $tagihanModel->getAll($limit, $offset, $status);

    // Format data
    foreach ($tagihan as &$item) {
      $item['nominal_formatted'] = rupiah($item['total_tagihan']);
      $item['periode'] = namaBulan($item['periode_bulan']) . ' ' . $item['periode_tahun'];
      $item['tanggal_terbit_formatted'] = tanggal_indo($item['tanggal_terbit'], 'short');
      $item['jatuh_tempo_formatted'] = tanggal_indo($item['tanggal_jatuh_tempo'], 'short');
    }

    jsonResponse([
      'success' => true,
      'data' => $tagihan,
      'total' => $tagihanModel->count($status)
    ]);
  }
}

/**
 * Handle pembayaran management
 */
function handlePembayaran()
{
  $pembayaranModel = new Pembayaran();

  if (isGet()) {
    $limit = (int) ($_GET['limit'] ?? 100);
    $offset = (int) ($_GET['offset'] ?? 0);
    $status = $_GET['status'] ?? null;

    $pembayaran = $pembayaranModel->getAll($limit, $offset, $status);

    // Format data
    foreach ($pembayaran as &$item) {
      $item['nominal_formatted'] = rupiah($item['nominal_bayar']);
      $item['metode_label'] = metodePembayaranLabel($item['metode_pembayaran']);
      $item['periode'] = namaBulan($item['periode_bulan']) . ' ' . $item['periode_tahun'];
      $item['tanggal_formatted'] = $item['tanggal_bayar'] ? tanggal_indo($item['tanggal_bayar'], 'datetime') : '-';
      $item['created_formatted'] = tanggal_indo($item['created_at'], 'datetime');
    }

    jsonResponse([
      'success' => true,
      'data' => $pembayaran,
      'total' => $pembayaranModel->count($status)
    ]);
  }
}

/**
 * Handle konfirmasi pembayaran
 */
function handleConfirmPayment()
{
  if (!isPost()) {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
  }

  $data = json_decode(file_get_contents('php://input'), true);
  $id = (int) ($data['id'] ?? 0);

  if (!$id) {
    jsonResponse(['success' => false, 'message' => 'ID pembayaran diperlukan'], 400);
  }

  $pembayaranModel = new Pembayaran();
  $result = $pembayaranModel->confirm($id);

  jsonResponse($result, $result['success'] ? 200 : 400);
}

/**
 * Handle tolak pembayaran
 */
function handleRejectPayment()
{
  if (!isPost()) {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
  }

  $data = json_decode(file_get_contents('php://input'), true);
  $id = (int) ($data['id'] ?? 0);
  $alasan = sanitize($data['alasan'] ?? '');

  if (!$id) {
    jsonResponse(['success' => false, 'message' => 'ID pembayaran diperlukan'], 400);
  }

  $pembayaranModel = new Pembayaran();
  $result = $pembayaranModel->reject($id, $alasan);

  jsonResponse($result, $result['success'] ? 200 : 400);
}

/**
 * Handle buat tagihan bulk
 */
function handleCreateTagihanBulk()
{
  if (!isPost()) {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
  }

  // Cek role admin
  $adminData = Session::admin();
  if (!in_array($adminData['role'], ['super_admin', 'admin'])) {
    jsonResponse(['success' => false, 'message' => 'Anda tidak memiliki akses'], 403);
  }

  $data = json_decode(file_get_contents('php://input'), true);
  $bulan = (int) ($data['bulan'] ?? date('n'));
  $tahun = (int) ($data['tahun'] ?? date('Y'));

  if ($bulan < 1 || $bulan > 12) {
    jsonResponse(['success' => false, 'message' => 'Bulan tidak valid'], 400);
  }

  $tagihanModel = new Tagihan();
  $result = $tagihanModel->createBulk($bulan, $tahun);

  jsonResponse($result);
}

/**
 * Handle laporan
 */
function handleReports()
{
  $type = $_GET['type'] ?? 'monthly';
  $tahun = (int) ($_GET['tahun'] ?? date('Y'));

  $pembayaranModel = new Pembayaran();

  switch ($type) {
    case 'monthly':
      $data = $pembayaranModel->getMonthlyReport($tahun);

      // Format data
      foreach ($data as &$item) {
        $item['bulan_nama'] = namaBulan($item['bulan']);
        $item['total_formatted'] = rupiah($item['total_pendapatan']);
      }
      break;

    case 'statistics':
      $data = $pembayaranModel->getStatistics();
      break;

    default:
      jsonResponse(['success' => false, 'message' => 'Tipe laporan tidak valid'], 400);
  }

  jsonResponse(['success' => true, 'data' => $data]);
}
?>

