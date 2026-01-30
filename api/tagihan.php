<?php
/**
 * ========================================
 * SIPARES - Tagihan API
 * ========================================
 * Endpoint untuk mengelola tagihan
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

require_once __DIR__ . '/../config/init.php';
require_once INCLUDES_PATH . 'Tagihan.php';

$action = $_GET['action'] ?? '';
$tagihanModel = new Tagihan();

// Cek login untuk beberapa action
$requireLogin = ['list', 'detail', 'user'];
if (in_array($action, $requireLogin) && !Session::isLoggedIn()) {
  jsonResponse(['success' => false, 'message' => 'Silakan login terlebih dahulu'], 401);
}

switch ($action) {
  case 'list':
    handleList($tagihanModel);
    break;

  case 'detail':
    handleDetail($tagihanModel);
    break;

  case 'user':
    handleUserTagihan($tagihanModel);
    break;

  case 'check':
    handleCheck($tagihanModel);
    break;

  case 'statistics':
    handleStatistics($tagihanModel);
    break;

  default:
    jsonResponse(['success' => false, 'message' => 'Action tidak valid'], 400);
}

/**
 * Handle list tagihan (untuk user yang login)
 */
function handleList($tagihanModel)
{
  $userId = Session::userId();
  $status = $_GET['status'] ?? null;

  $tagihan = $tagihanModel->getByUserId($userId, $status);

  jsonResponse([
    'success' => true,
    'data' => $tagihan,
    'total' => count($tagihan)
  ]);
}

/**
 * Handle detail tagihan
 */
function handleDetail($tagihanModel)
{
  $id = (int) ($_GET['id'] ?? 0);

  if (!$id) {
    jsonResponse(['success' => false, 'message' => 'ID tagihan diperlukan'], 400);
  }

  $tagihan = $tagihanModel->getById($id);

  if (!$tagihan) {
    jsonResponse(['success' => false, 'message' => 'Tagihan tidak ditemukan'], 404);
  }

  // Pastikan tagihan milik user yang login
  if ($tagihan['user_id'] != Session::userId()) {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
  }

  jsonResponse(['success' => true, 'data' => $tagihan]);
}

/**
 * Handle tagihan user yang login
 */
function handleUserTagihan($tagihanModel)
{
  $userId = Session::userId();

  // Tagihan belum bayar
  $unpaid = $tagihanModel->getUnpaidByUserId($userId);

  // Semua tagihan
  $all = $tagihanModel->getByUserId($userId);

  // Hitung total tunggakan
  $totalTunggakan = 0;
  foreach ($unpaid as $t) {
    $totalTunggakan += $t['total_tagihan'];
  }

  jsonResponse([
    'success' => true,
    'unpaid' => $unpaid,
    'all' => $all,
    'total_unpaid' => count($unpaid),
    'total_tunggakan' => $totalTunggakan
  ]);
}

/**
 * Handle cek tagihan (public)
 */
function handleCheck($tagihanModel)
{
  $noPelanggan = sanitize($_GET['no_pelanggan'] ?? '');

  if (empty($noPelanggan)) {
    jsonResponse(['success' => false, 'message' => 'Nomor pelanggan diperlukan'], 400);
  }

  // Cari user
  require_once INCLUDES_PATH . 'User.php';
  $userModel = new User();
  $user = $userModel->getByNoPelanggan($noPelanggan);

  if (!$user) {
    jsonResponse(['success' => false, 'message' => 'Nomor pelanggan tidak ditemukan'], 404);
  }

  // Ambil tagihan belum bayar
  $tagihan = $tagihanModel->getUnpaidByUserId($user['id']);

  // Hitung total
  $totalTunggakan = 0;
  foreach ($tagihan as $t) {
    $totalTunggakan += $t['total_tagihan'];
  }

  jsonResponse([
    'success' => true,
    'user' => [
      'no_pelanggan' => $user['no_pelanggan'],
      'nama_lengkap' => $user['nama_lengkap'],
      'alamat' => $user['alamat'],
      'kategori' => kategoriLabel($user['kategori'])
    ],
    'tagihan' => $tagihan,
    'total_tagihan' => count($tagihan),
    'total_tunggakan' => $totalTunggakan
  ]);
}

/**
 * Handle statistik tagihan
 */
function handleStatistics($tagihanModel)
{
  $stats = $tagihanModel->getStatistics();
  jsonResponse(['success' => true, 'data' => $stats]);
}
?>

