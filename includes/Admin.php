<?php
/**
 * ========================================
 * SIPARES - Admin Model
 * ========================================
 */

require_once __DIR__ . '/../config/init.php';

class Admin
{
  private $db;

  public function __construct()
  {
    $this->db = db();
  }

  /**
   * Mendapatkan admin berdasarkan ID
   */
  public function getById($id)
  {
    $sql = "SELECT * FROM admin WHERE id = ?";
    return $this->db->fetchOne($sql, [$id]);
  }

  /**
   * Mendapatkan admin berdasarkan username
   */
  public function getByUsername($username)
  {
    $sql = "SELECT * FROM admin WHERE username = ?";
    return $this->db->fetchOne($sql, [$username]);
  }

  /**
   * Mendapatkan admin berdasarkan email
   */
  public function getByEmail($email)
  {
    $sql = "SELECT * FROM admin WHERE email = ?";
    return $this->db->fetchOne($sql, [$email]);
  }

  /**
   * Login admin
   */
  public function login($username, $password)
  {
    // Coba dengan username atau email
    $admin = $this->getByUsername($username);
    if (!$admin) {
      $admin = $this->getByEmail($username);
    }

    if (!$admin) {
      return ['success' => false, 'message' => 'Username atau email tidak ditemukan'];
    }

    if ($admin['status'] !== 'aktif') {
      return ['success' => false, 'message' => 'Akun Anda tidak aktif'];
    }

    if (!verifyPassword($password, $admin['password'])) {
      return ['success' => false, 'message' => 'Password salah'];
    }

    // Update last login
    $sql = "UPDATE admin SET last_login = NOW() WHERE id = ?";
    $this->db->query($sql, [$admin['id']]);

    // Bersihkan sesi user jika ada agar tidak tumpang tindih
    Session::remove('user_id');
    Session::remove('user_data');

    // Set session admin
    Session::set('admin_id', $admin['id']);
    Session::set('admin_data', [
      'id' => $admin['id'],
      'username' => $admin['username'],
      'nama_lengkap' => $admin['nama_lengkap'],
      'email' => $admin['email'],
      'role' => $admin['role']
    ]);
    Session::regenerate();

    logActivity('Admin login', 'admin', $admin['id'], null, $admin['id']);

    return [
      'success' => true,
      'message' => 'Login berhasil',
      'admin' => $admin
    ];
  }

  /**
   * Logout admin
   */
  public function logout()
  {
    $adminId = Session::adminId();
    logActivity('Admin logout', 'admin', $adminId, null, $adminId);
    Session::destroy();
    return ['success' => true, 'message' => 'Logout berhasil'];
  }

  /**
   * Cek apakah admin memiliki role tertentu
   */
  public function hasRole($adminId, $roles)
  {
    $admin = $this->getById($adminId);
    if (!$admin)
      return false;

    if (is_string($roles)) {
      $roles = [$roles];
    }

    return in_array($admin['role'], $roles);
  }

  /**
   * Mendapatkan semua admin
   */
  public function getAll()
  {
    $sql = "SELECT id, username, email, nama_lengkap, role, last_login, status, created_at
                FROM admin ORDER BY created_at DESC";
    return $this->db->fetchAll($sql);
  }

  /**
   * Tambah admin baru
   */
  public function create($data)
  {
    // Validasi
    if (
      empty($data['username']) || empty($data['email']) ||
      empty($data['password']) || empty($data['nama_lengkap'])
    ) {
      return ['success' => false, 'message' => 'Semua field wajib diisi'];
    }

    // Cek duplikat
    if ($this->getByUsername($data['username'])) {
      return ['success' => false, 'message' => 'Username sudah digunakan'];
    }

    if ($this->getByEmail($data['email'])) {
      return ['success' => false, 'message' => 'Email sudah terdaftar'];
    }

    try {
      $sql = "INSERT INTO admin (username, email, password, nama_lengkap, role) VALUES (?, ?, ?, ?, ?)";
      $this->db->query($sql, [
        $data['username'],
        $data['email'],
        hashPassword($data['password']),
        $data['nama_lengkap'],
        $data['role'] ?? 'operator'
      ]);

      $adminId = $this->db->lastInsertId();
      logActivity('Tambah admin baru', 'admin', $adminId, null, Session::adminId());

      return ['success' => true, 'message' => 'Admin berhasil ditambahkan', 'admin_id' => $adminId];

    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Gagal menambahkan admin'];
    }
  }

  /**
   * Update admin
   */
  public function update($id, $data)
  {
    try {
      $sql = "UPDATE admin SET nama_lengkap = ?, email = ?, role = ? WHERE id = ?";
      $this->db->query($sql, [
        $data['nama_lengkap'],
        $data['email'],
        $data['role'],
        $id
      ]);

      logActivity('Update admin', 'admin', $id, null, Session::adminId());

      return ['success' => true, 'message' => 'Admin berhasil diperbarui'];

    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Gagal memperbarui admin'];
    }
  }

  /**
   * Ganti password admin
   */
  public function changePassword($id, $newPassword)
  {
    try {
      $sql = "UPDATE admin SET password = ? WHERE id = ?";
      $this->db->query($sql, [hashPassword($newPassword), $id]);

      logActivity('Ganti password admin', 'admin', $id, null, Session::adminId());

      return ['success' => true, 'message' => 'Password berhasil diubah'];

    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Gagal mengubah password'];
    }
  }

  /**
   * Ubah status admin
   */
  public function toggleStatus($id)
  {
    try {
      $sql = "UPDATE admin SET status = IF(status = 'aktif', 'nonaktif', 'aktif') WHERE id = ?";
      $this->db->query($sql, [$id]);

      logActivity('Ubah status admin', 'admin', $id, null, Session::adminId());

      return ['success' => true, 'message' => 'Status berhasil diubah'];

    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Gagal mengubah status'];
    }
  }
}
?>

