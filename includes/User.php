<?php
/**
 * ========================================
 * SIPARES - User Model
 * ========================================
 */

require_once __DIR__ . '/../config/init.php';

class User
{
  private $db;

  public function __construct()
  {
    $this->db = db();
  }

  /**
   * Mendapatkan semua user
   */
  public function getAll($limit = 100, $offset = 0)
  {
    $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
    return $this->db->fetchAll($sql, [$limit, $offset]);
  }

  /**
   * Mendapatkan user berdasarkan ID
   */
  public function getById($id)
  {
    $sql = "SELECT * FROM users WHERE id = ?";
    return $this->db->fetchOne($sql, [$id]);
  }

  /**
   * Mendapatkan user berdasarkan email
   */
  public function getByEmail($email)
  {
    $sql = "SELECT * FROM users WHERE email = ?";
    return $this->db->fetchOne($sql, [$email]);
  }

  /**
   * Mendapatkan user berdasarkan nomor pelanggan
   */
  public function getByNoPelanggan($noPelanggan)
  {
    $sql = "SELECT * FROM users WHERE no_pelanggan = ?";
    return $this->db->fetchOne($sql, [$noPelanggan]);
  }

  /**
   * Mendapatkan user berdasarkan NIK
   */
  public function getByNIK($nik)
  {
    $sql = "SELECT * FROM users WHERE nik = ?";
    return $this->db->fetchOne($sql, [$nik]);
  }

  /**
   * Registrasi user baru
   */
  public function register($data)
  {
    // Validasi
    if (
      empty($data['nik']) || empty($data['nama_lengkap']) ||
      empty($data['email']) || empty($data['password'])
    ) {
      return ['success' => false, 'message' => 'Semua field wajib diisi'];
    }

    if (!isValidNIK($data['nik'])) {
      return ['success' => false, 'message' => 'NIK tidak valid (harus 16 digit)'];
    }

    if (!isValidEmail($data['email'])) {
      return ['success' => false, 'message' => 'Email tidak valid'];
    }

    // Cek duplikat
    if ($this->getByEmail($data['email'])) {
      return ['success' => false, 'message' => 'Email sudah terdaftar'];
    }

    if ($this->getByNIK($data['nik'])) {
      return ['success' => false, 'message' => 'NIK sudah terdaftar'];
    }

    try {
      $sql = "INSERT INTO users (no_pelanggan, nik, nama_lengkap, email, password, no_telepon, alamat, rt, rw, kelurahan, kecamatan, kategori)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      $noPelanggan = generateNoPelanggan();
      $hashedPassword = hashPassword($data['password']);

      $this->db->query($sql, [
        $noPelanggan,
        $data['nik'],
        $data['nama_lengkap'],
        $data['email'],
        $hashedPassword,
        $data['no_telepon'] ?? null,
        $data['alamat'] ?? null,
        $data['rt'] ?? null,
        $data['rw'] ?? null,
        $data['kelurahan'] ?? null,
        $data['kecamatan'] ?? null,
        $data['kategori'] ?? 'rumah_tangga'
      ]);

      $userId = $this->db->lastInsertId();

      logActivity('Registrasi user baru', 'users', $userId, $userId);

      return [
        'success' => true,
        'message' => 'Registrasi berhasil',
        'user_id' => $userId,
        'no_pelanggan' => $noPelanggan
      ];

    } catch (Exception $e) {
      error_log("Register Error: " . $e->getMessage());
      return ['success' => false, 'message' => 'Gagal melakukan registrasi'];
    }
  }

  /**
   * Login user
   */
  public function login($email, $password)
  {
    $user = $this->getByEmail($email);

    if (!$user) {
      return ['success' => false, 'message' => 'Email tidak ditemukan'];
    }

    if ($user['status'] !== 'aktif') {
      return ['success' => false, 'message' => 'Akun Anda tidak aktif'];
    }

    if (!verifyPassword($password, $user['password'])) {
      return ['success' => false, 'message' => 'Password salah'];
    }

    // Set session
    Session::set('user_id', $user['id']);
    Session::set('user_data', [
      'id' => $user['id'],
      'no_pelanggan' => $user['no_pelanggan'],
      'nama_lengkap' => $user['nama_lengkap'],
      'email' => $user['email'],
      'kategori' => $user['kategori']
    ]);
    Session::regenerate();

    logActivity('User login', 'users', $user['id'], $user['id']);

    return [
      'success' => true,
      'message' => 'Login berhasil',
      'user' => $user
    ];
  }

  /**
   * Logout user
   */
  public function logout()
  {
    $userId = Session::userId();
    logActivity('User logout', 'users', $userId, $userId);
    Session::destroy();
    return ['success' => true, 'message' => 'Logout berhasil'];
  }

  /**
   * Update profil user
   */
  public function updateProfile($id, $data)
  {
    try {
      $sql = "UPDATE users SET
                    nama_lengkap = ?,
                    no_telepon = ?,
                    alamat = ?,
                    rt = ?,
                    rw = ?,
                    kelurahan = ?,
                    kecamatan = ?
                    WHERE id = ?";

      $this->db->query($sql, [
        $data['nama_lengkap'],
        $data['no_telepon'],
        $data['alamat'],
        $data['rt'],
        $data['rw'],
        $data['kelurahan'],
        $data['kecamatan'],
        $id
      ]);

      logActivity('Update profil user', 'users', $id, $id);

      return ['success' => true, 'message' => 'Profil berhasil diperbarui'];

    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Gagal memperbarui profil'];
    }
  }

  /**
   * Ganti password
   */
  public function changePassword($id, $oldPassword, $newPassword)
  {
    $user = $this->getById($id);

    if (!$user) {
      return ['success' => false, 'message' => 'User tidak ditemukan'];
    }

    if (!verifyPassword($oldPassword, $user['password'])) {
      return ['success' => false, 'message' => 'Password lama salah'];
    }

    if (strlen($newPassword) < 8) {
      return ['success' => false, 'message' => 'Password baru minimal 8 karakter'];
    }

    try {
      $sql = "UPDATE users SET password = ? WHERE id = ?";
      $this->db->query($sql, [hashPassword($newPassword), $id]);

      logActivity('Ganti password', 'users', $id, $id);

      return ['success' => true, 'message' => 'Password berhasil diubah'];

    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Gagal mengubah password'];
    }
  }

  /**
   * Hitung total user
   */
  public function count()
  {
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = $this->db->fetchOne($sql);
    return $result['total'];
  }

  /**
   * Hitung user aktif
   */
  public function countActive()
  {
    $sql = "SELECT COUNT(*) as total FROM users WHERE status = 'aktif'";
    $result = $this->db->fetchOne($sql);
    return $result['total'];
  }

  /**
   * Statistik user per kategori
   */
  public function countByKategori()
  {
    $sql = "SELECT kategori, COUNT(*) as total FROM users GROUP BY kategori";
    return $this->db->fetchAll($sql);
  }
}
?>

