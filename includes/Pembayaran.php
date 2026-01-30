<?php
/**
 * ========================================
 * SIPARES - Pembayaran Model
 * ========================================
 */

require_once __DIR__ . '/../config/init.php';

class Pembayaran
{
  private $db;

  public function __construct()
  {
    $this->db = db();
  }

  /**
   * Mendapatkan semua pembayaran
   */
  public function getAll($limit = 100, $offset = 0, $status = null)
  {
    $sql = "SELECT p.*, t.no_tagihan, t.periode_bulan, t.periode_tahun,
                       u.nama_lengkap, u.no_pelanggan
                FROM pembayaran p
                JOIN tagihan t ON p.tagihan_id = t.id
                JOIN users u ON p.user_id = u.id";

    $params = [];

    if ($status) {
      $sql .= " WHERE p.status = ?";
      $params[] = $status;
    }

    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    return $this->db->fetchAll($sql, $params);
  }

  /**
   * Mendapatkan pembayaran berdasarkan ID
   */
  public function getById($id)
  {
    $sql = "SELECT p.*, t.no_tagihan, t.periode_bulan, t.periode_tahun, t.total_tagihan,
                       u.nama_lengkap, u.no_pelanggan, u.alamat, u.email
                FROM pembayaran p
                JOIN tagihan t ON p.tagihan_id = t.id
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?";
    return $this->db->fetchOne($sql, [$id]);
  }

  /**
   * Mendapatkan pembayaran berdasarkan nomor transaksi
   */
  public function getByNoTransaksi($noTransaksi)
  {
    $sql = "SELECT p.*, t.no_tagihan, t.periode_bulan, t.periode_tahun, t.total_tagihan,
                       u.nama_lengkap, u.no_pelanggan
                FROM pembayaran p
                JOIN tagihan t ON p.tagihan_id = t.id
                JOIN users u ON p.user_id = u.id
                WHERE p.no_transaksi = ?";
    return $this->db->fetchOne($sql, [$noTransaksi]);
  }

  /**
   * Mendapatkan pembayaran berdasarkan user ID
   */
  public function getByUserId($userId, $limit = 50)
  {
    $sql = "SELECT p.*, t.no_tagihan, t.periode_bulan, t.periode_tahun
                FROM pembayaran p
                JOIN tagihan t ON p.tagihan_id = t.id
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC
                LIMIT ?";
    return $this->db->fetchAll($sql, [$userId, $limit]);
  }

  /**
   * Buat pembayaran baru
   */
  public function create($data)
  {
    try {
      // Validasi tagihan
      $sql = "SELECT * FROM tagihan WHERE id = ?";
      $tagihan = $this->db->fetchOne($sql, [$data['tagihan_id']]);

      if (!$tagihan) {
        return ['success' => false, 'message' => 'Tagihan tidak ditemukan'];
      }

      if ($tagihan['status'] === 'lunas') {
        return ['success' => false, 'message' => 'Tagihan sudah lunas'];
      }

      // Cek apakah ada pembayaran pending
      $sql = "SELECT * FROM pembayaran WHERE tagihan_id = ? AND status = 'pending'";
      $existing = $this->db->fetchOne($sql, [$data['tagihan_id']]);
      if ($existing) {
        return ['success' => false, 'message' => 'Sudah ada pembayaran menunggu konfirmasi'];
      }

      $noTransaksi = generateNoTransaksi();

      $sql = "INSERT INTO pembayaran (tagihan_id, user_id, no_transaksi, metode_pembayaran, nominal_bayar, status)
                    VALUES (?, ?, ?, ?, ?, 'pending')";

      $this->db->query($sql, [
        $data['tagihan_id'],
        $tagihan['user_id'],
        $noTransaksi,
        $data['metode_pembayaran'],
        $tagihan['total_tagihan']
      ]);

      $pembayaranId = $this->db->lastInsertId();

      // Update status tagihan ke pending
      $sql = "UPDATE tagihan SET status = 'pending' WHERE id = ?";
      $this->db->query($sql, [$data['tagihan_id']]);

      logActivity('Buat pembayaran baru', 'pembayaran', $pembayaranId, $tagihan['user_id']);

      return [
        'success' => true,
        'message' => 'Pembayaran berhasil dibuat',
        'pembayaran_id' => $pembayaranId,
        'no_transaksi' => $noTransaksi,
        'nominal' => $tagihan['total_tagihan']
      ];

    } catch (Exception $e) {
      error_log("Create Pembayaran Error: " . $e->getMessage());
      return ['success' => false, 'message' => 'Gagal membuat pembayaran'];
    }
  }

  /**
   * Konfirmasi pembayaran (admin)
   */
  public function confirm($id)
  {
    try {
      $this->db->beginTransaction();

      $pembayaran = $this->getById($id);
      if (!$pembayaran) {
        return ['success' => false, 'message' => 'Pembayaran tidak ditemukan'];
      }

      if ($pembayaran['status'] !== 'pending') {
        return ['success' => false, 'message' => 'Status pembayaran tidak valid'];
      }

      // Update pembayaran
      $sql = "UPDATE pembayaran SET status = 'berhasil', tanggal_bayar = NOW() WHERE id = ?";
      $this->db->query($sql, [$id]);

      // Update tagihan
      $sql = "UPDATE tagihan SET status = 'lunas' WHERE id = ?";
      $this->db->query($sql, [$pembayaran['tagihan_id']]);

      // Buat notifikasi
      $this->createNotification($pembayaran['user_id'], $pembayaran['periode_bulan'], $pembayaran['periode_tahun']);

      $this->db->commit();

      logActivity('Konfirmasi pembayaran', 'pembayaran', $id, $pembayaran['user_id'], Session::adminId());

      return ['success' => true, 'message' => 'Pembayaran berhasil dikonfirmasi'];

    } catch (Exception $e) {
      $this->db->rollback();
      error_log("Confirm Pembayaran Error: " . $e->getMessage());
      return ['success' => false, 'message' => 'Gagal mengkonfirmasi pembayaran'];
    }
  }

  /**
   * Tolak pembayaran (admin)
   */
  public function reject($id, $alasan = '')
  {
    try {
      $this->db->beginTransaction();

      $pembayaran = $this->getById($id);
      if (!$pembayaran) {
        return ['success' => false, 'message' => 'Pembayaran tidak ditemukan'];
      }

      // Update pembayaran
      $sql = "UPDATE pembayaran SET status = 'gagal', keterangan = ? WHERE id = ?";
      $this->db->query($sql, [$alasan, $id]);

      // Update tagihan kembali ke belum_bayar
      $sql = "UPDATE tagihan SET status = 'belum_bayar' WHERE id = ?";
      $this->db->query($sql, [$pembayaran['tagihan_id']]);

      $this->db->commit();

      logActivity('Tolak pembayaran', 'pembayaran', $id, $pembayaran['user_id'], Session::adminId());

      return ['success' => true, 'message' => 'Pembayaran ditolak'];

    } catch (Exception $e) {
      $this->db->rollback();
      return ['success' => false, 'message' => 'Gagal menolak pembayaran'];
    }
  }

  /**
   * Upload bukti pembayaran
   */
  public function uploadBukti($id, $file)
  {
    try {
      $result = uploadFile($file, 'bukti_pembayaran', ['jpg', 'jpeg', 'png', 'pdf']);

      if (!$result['success']) {
        return $result;
      }

      $sql = "UPDATE pembayaran SET bukti_pembayaran = ? WHERE id = ?";
      $this->db->query($sql, [$result['path'], $id]);

      logActivity('Upload bukti pembayaran', 'pembayaran', $id);

      return ['success' => true, 'message' => 'Bukti pembayaran berhasil diupload'];

    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Gagal mengupload bukti pembayaran'];
    }
  }

  /**
   * Statistik pembayaran
   */
  public function getStatistics()
  {
    $stats = [];

    // Total pembayaran berhasil bulan ini
    $sql = "SELECT COUNT(*) as total,
                       COALESCE(SUM(nominal_bayar), 0) as nominal_total
                FROM pembayaran
                WHERE status = 'berhasil'
                AND MONTH(tanggal_bayar) = MONTH(NOW())
                AND YEAR(tanggal_bayar) = YEAR(NOW())";
    $stats['bulan_ini'] = $this->db->fetchOne($sql);

    // Total pembayaran pending
    $sql = "SELECT COUNT(*) as total,
                       COALESCE(SUM(nominal_bayar), 0) as nominal_total
                FROM pembayaran
                WHERE status = 'pending'";
    $stats['pending'] = $this->db->fetchOne($sql);

    // Total pendapatan tahun ini
    $sql = "SELECT COALESCE(SUM(nominal_bayar), 0) as total
                FROM pembayaran
                WHERE status = 'berhasil'
                AND YEAR(tanggal_bayar) = YEAR(NOW())";
    $result = $this->db->fetchOne($sql);
    $stats['pendapatan_tahun'] = $result['total'];

    // Statistik per metode pembayaran
    $sql = "SELECT metode_pembayaran, COUNT(*) as total, SUM(nominal_bayar) as nominal
                FROM pembayaran
                WHERE status = 'berhasil'
                GROUP BY metode_pembayaran";
    $stats['per_metode'] = $this->db->fetchAll($sql);

    return $stats;
  }

  /**
   * Laporan bulanan
   */
  public function getMonthlyReport($tahun)
  {
    $sql = "SELECT
                    MONTH(tanggal_bayar) as bulan,
                    COUNT(*) as jumlah_transaksi,
                    SUM(nominal_bayar) as total_pendapatan
                FROM pembayaran
                WHERE status = 'berhasil'
                AND YEAR(tanggal_bayar) = ?
                GROUP BY MONTH(tanggal_bayar)
                ORDER BY bulan";
    return $this->db->fetchAll($sql, [$tahun]);
  }

  /**
   * Pembayaran terbaru
   */
  public function getRecent($limit = 10)
  {
    $sql = "SELECT p.*, t.no_tagihan, u.nama_lengkap, u.no_pelanggan
                FROM pembayaran p
                JOIN tagihan t ON p.tagihan_id = t.id
                JOIN users u ON p.user_id = u.id
                ORDER BY p.created_at DESC
                LIMIT ?";
    return $this->db->fetchAll($sql, [$limit]);
  }

  /**
   * Hitung total pembayaran
   */
  public function count($status = null)
  {
    $sql = "SELECT COUNT(*) as total FROM pembayaran";
    $params = [];

    if ($status) {
      $sql .= " WHERE status = ?";
      $params[] = $status;
    }

    $result = $this->db->fetchOne($sql, $params);
    return $result['total'];
  }

  /**
   * Buat notifikasi pembayaran berhasil
   */
  private function createNotification($userId, $bulan, $tahun)
  {
    $namaBulan = namaBulan($bulan);
    $sql = "INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, ?, ?, 'pembayaran')";
    $this->db->query($sql, [
      $userId,
      "Pembayaran Berhasil",
      "Pembayaran tagihan bulan $namaBulan $tahun telah dikonfirmasi. Terima kasih telah membayar tepat waktu."
    ]);
  }
}
?>

