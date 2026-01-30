<?php
/**
 * ========================================
 * SIPARES - Helper Functions
 * ========================================
 */

/**
 * Redirect ke halaman lain
 */
function redirect($url)
{
  header("Location: " . $url);
  exit;
}

/**
 * Sanitize input
 */
function sanitize($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
  return $data;
}

/**
 * Escape output untuk HTML
 */
function e($string)
{
  return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka ke format Rupiah
 */
function rupiah($angka, $prefix = 'Rp')
{
  return $prefix . ' ' . number_format($angka, 0, ',', '.');
}

/**
 * Format tanggal ke bahasa Indonesia
 */
function tanggal_indo($tanggal, $format = 'full')
{
  $hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
  ];

  $bulan = [
    1 => 'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
  ];

  $timestamp = strtotime($tanggal);
  $dayName = date('l', $timestamp);
  $day = date('j', $timestamp);
  $month = date('n', $timestamp);
  $year = date('Y', $timestamp);

  switch ($format) {
    case 'short':
      return $day . ' ' . $bulan[$month] . ' ' . $year;
    case 'full':
      return $hari[$dayName] . ', ' . $day . ' ' . $bulan[$month] . ' ' . $year;
    case 'datetime':
      return $day . ' ' . $bulan[$month] . ' ' . $year . ' ' . date('H:i', $timestamp);
    default:
      return $day . ' ' . $bulan[$month] . ' ' . $year;
  }
}

/**
 * Generate nomor pelanggan
 */
function generateNoPelanggan()
{
  $prefix = 'SPR';
  $year = date('Y');
  $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
  return "{$prefix}-{$year}-{$random}";
}

/**
 * Generate nomor tagihan
 */
function generateNoTagihan($userId, $bulan, $tahun)
{
  $prefix = 'TGH';
  $periode = $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT);
  $userPart = str_pad($userId, 6, '0', STR_PAD_LEFT);
  return "{$prefix}-{$periode}-{$userPart}";
}

/**
 * Generate nomor transaksi
 */
function generateNoTransaksi()
{
  $prefix = 'TRX';
  $date = date('Ymd');
  $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
  return "{$prefix}{$date}{$random}";
}

/**
 * Validasi email
 */
function isValidEmail($email)
{
  return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validasi NIK (16 digit)
 */
function isValidNIK($nik)
{
  return preg_match('/^[0-9]{16}$/', $nik);
}

/**
 * Validasi nomor telepon Indonesia
 */
function isValidPhone($phone)
{
  return preg_match('/^(08|628)[0-9]{8,11}$/', $phone);
}

/**
 * Hash password
 */
function hashPassword($password)
{
  return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash)
{
  return password_verify($password, $hash);
}

/**
 * Get nama bulan
 */
function namaBulan($bulan)
{
  $nama = [
    1 => 'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
  ];
  return $nama[$bulan] ?? '';
}

/**
 * Get status badge HTML
 */
function statusBadge($status)
{
  $badges = [
    'lunas' => '<span class="badge badge-success">Lunas</span>',
    'belum_bayar' => '<span class="badge badge-warning">Belum Dibayar</span>',
    'pending' => '<span class="badge badge-info">Pending</span>',
    'terlambat' => '<span class="badge badge-danger">Terlambat</span>',
    'berhasil' => '<span class="badge badge-success">Berhasil</span>',
    'gagal' => '<span class="badge badge-danger">Gagal</span>',
    'expired' => '<span class="badge badge-secondary">Kadaluarsa</span>',
    'aktif' => '<span class="badge badge-success">Aktif</span>',
    'nonaktif' => '<span class="badge badge-danger">Nonaktif</span>',
  ];
  return $badges[$status] ?? '<span class="badge">' . $status . '</span>';
}

/**
 * Get kategori label
 */
function kategoriLabel($kategori)
{
  $labels = [
    'rumah_tangga' => 'Rumah Tangga',
    'usaha' => 'Usaha/Toko',
    'industri' => 'Industri'
  ];
  return $labels[$kategori] ?? $kategori;
}

/**
 * Get metode pembayaran label
 */
function metodePembayaranLabel($metode)
{
  $labels = [
    'qris' => 'QRIS',
    'transfer_bca' => 'Transfer BCA',
    'transfer_bni' => 'Transfer BNI',
    'transfer_mandiri' => 'Transfer Mandiri',
    'transfer_bri' => 'Transfer BRI',
    'minimarket' => 'Minimarket'
  ];
  return $labels[$metode] ?? $metode;
}

/**
 * Hitung denda keterlambatan
 */
function hitungDenda($nominal, $tanggalJatuhTempo, $persenDenda = 2)
{
  $today = new DateTime();
  $jatuhTempo = new DateTime($tanggalJatuhTempo);

  if ($today <= $jatuhTempo) {
    return 0;
  }

  $interval = $today->diff($jatuhTempo);
  $bulanTerlambat = ($interval->y * 12) + $interval->m + ($interval->d > 0 ? 1 : 0);

  return $nominal * ($persenDenda / 100) * $bulanTerlambat;
}

/**
 * Log aktivitas
 */
function logActivity($aksi, $tabelTerkait = null, $dataId = null, $userId = null, $adminId = null)
{
  try {
    $db = db();
    $sql = "INSERT INTO log_aktivitas (user_id, admin_id, aksi, tabel_terkait, data_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
    $db->query($sql, [
      $userId,
      $adminId,
      $aksi,
      $tabelTerkait,
      $dataId,
      $_SERVER['REMOTE_ADDR'] ?? null,
      $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
  } catch (Exception $e) {
    error_log("Log Activity Error: " . $e->getMessage());
  }
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200)
{
  http_response_code($statusCode);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

/**
 * Get input dari POST/GET
 */
function input($key, $default = null)
{
  return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/**
 * Check if request is AJAX
 */
function isAjax()
{
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Check if request method is POST
 */
function isPost()
{
  return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request method is GET
 */
function isGet()
{
  return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...')
{
  if (strlen($text) <= $length) {
    return $text;
  }
  return substr($text, 0, $length) . $suffix;
}

/**
 * Create slug from text
 */
function createSlug($text)
{
  $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
  $text = strtolower(trim($text));
  $text = preg_replace('/\s+/', '-', $text);
  return $text;
}

/**
 * Upload file
 */
function uploadFile($file, $directory, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'])
{
  if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
    return ['success' => false, 'message' => 'Tidak ada file yang diupload'];
  }

  $fileName = $file['name'];
  $fileSize = $file['size'];
  $fileTmp = $file['tmp_name'];
  $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

  // Validasi ekstensi
  if (!in_array($fileExt, $allowedTypes)) {
    return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
  }

  // Validasi ukuran (5MB)
  if ($fileSize > MAX_UPLOAD_SIZE) {
    return ['success' => false, 'message' => 'Ukuran file terlalu besar (max 5MB)'];
  }

  // Generate nama file unik
  $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
  $uploadPath = UPLOAD_PATH . $directory . '/';

  // Buat direktori jika belum ada
  if (!is_dir($uploadPath)) {
    mkdir($uploadPath, 0755, true);
  }

  // Pindahkan file
  if (move_uploaded_file($fileTmp, $uploadPath . $newFileName)) {
    return [
      'success' => true,
      'filename' => $newFileName,
      'path' => $directory . '/' . $newFileName
    ];
  }

  return ['success' => false, 'message' => 'Gagal mengupload file'];
}
?>

