<?php
/**
 * ========================================
 * SIPARES - Auth API
 * ========================================
 * Endpoint untuk autentikasi user
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

require_once __DIR__ . '/../config/init.php';
require_once INCLUDES_PATH . 'User.php';

$action = $_GET['action'] ?? '';
$userModel = new User();

switch ($action) {
  case 'login':
    handleLogin($userModel);
    break;

  case 'register':
    handleRegister($userModel);
    break;

  case 'logout':
    handleLogout($userModel);
    break;

  case 'check':
    handleCheck();
    break;

  case 'profile':
    handleProfile($userModel);
    break;

  default:
    jsonResponse(['success' => false, 'message' => 'Action tidak valid'], 400);
}

/**
 * Handle login
 */
function handleLogin($userModel)
{
  if (!isPost()) {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
  }

  $data = json_decode(file_get_contents('php://input'), true);

  $email = sanitize($data['email'] ?? '');
  $password = $data['password'] ?? '';

  if (empty($email) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Email dan password wajib diisi'], 400);
  }

  $result = $userModel->login($email, $password);

  if ($result['success']) {
    jsonResponse([
      'success' => true,
      'message' => 'Login berhasil',
      'user' => [
        'id' => $result['user']['id'],
        'no_pelanggan' => $result['user']['no_pelanggan'],
        'nama_lengkap' => $result['user']['nama_lengkap'],
        'email' => $result['user']['email'],
        'kategori' => $result['user']['kategori']
      ]
    ]);
  } else {
    jsonResponse($result, 401);
  }
}

/**
 * Handle register
 */
function handleRegister($userModel)
{
  if (!isPost()) {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
  }

  $data = json_decode(file_get_contents('php://input'), true);

  $registerData = [
    'nik' => sanitize($data['nik'] ?? ''),
    'nama_lengkap' => sanitize($data['nama_lengkap'] ?? ''),
    'email' => sanitize($data['email'] ?? ''),
    'password' => $data['password'] ?? '',
    'no_telepon' => sanitize($data['no_telepon'] ?? ''),
    'alamat' => sanitize($data['alamat'] ?? ''),
    'rt' => sanitize($data['rt'] ?? ''),
    'rw' => sanitize($data['rw'] ?? ''),
    'kelurahan' => sanitize($data['kelurahan'] ?? ''),
    'kecamatan' => sanitize($data['kecamatan'] ?? ''),
    'kategori' => sanitize($data['kategori'] ?? 'rumah_tangga')
  ];

  // Validasi password
  if (strlen($registerData['password']) < 8) {
    jsonResponse(['success' => false, 'message' => 'Password minimal 8 karakter'], 400);
  }

  $result = $userModel->register($registerData);

  if ($result['success']) {
    jsonResponse($result);
  } else {
    jsonResponse($result, 400);
  }
}

/**
 * Handle logout
 */
function handleLogout($userModel)
{
  $result = $userModel->logout();
  jsonResponse($result);
}

/**
 * Handle check session
 */
function handleCheck()
{
  if (Session::isLoggedIn()) {
    jsonResponse([
      'success' => true,
      'logged_in' => true,
      'user' => Session::user()
    ]);
  } else {
    jsonResponse([
      'success' => true,
      'logged_in' => false
    ]);
  }
}

/**
 * Handle get/update profile
 */
function handleProfile($userModel)
{
  if (!Session::isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Silakan login terlebih dahulu'], 401);
  }

  $userId = Session::userId();

  if (isGet()) {
    // Get profile
    $user = $userModel->getById($userId);
    if ($user) {
      unset($user['password']);
      jsonResponse(['success' => true, 'user' => $user]);
    } else {
      jsonResponse(['success' => false, 'message' => 'User tidak ditemukan'], 404);
    }
  } elseif (isPost()) {
    // Update profile
    $data = json_decode(file_get_contents('php://input'), true);

    $updateData = [
      'nama_lengkap' => sanitize($data['nama_lengkap'] ?? ''),
      'no_telepon' => sanitize($data['no_telepon'] ?? ''),
      'alamat' => sanitize($data['alamat'] ?? ''),
      'rt' => sanitize($data['rt'] ?? ''),
      'rw' => sanitize($data['rw'] ?? ''),
      'kelurahan' => sanitize($data['kelurahan'] ?? ''),
      'kecamatan' => sanitize($data['kecamatan'] ?? '')
    ];

    $result = $userModel->updateProfile($userId, $updateData);
    jsonResponse($result);
  }
}
?>

