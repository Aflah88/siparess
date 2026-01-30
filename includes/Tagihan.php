<?php
/**
 * ========================================
 * SIPARES - Tagihan Model
 * ========================================
 */

require_once __DIR__ . '/../config/init.php';

class Tagihan
{
  private $db;

  public function __construct()
  {
    $this->db = db();
  }

  /**
   * Mendapatkan semua tagihan
   */
  public function getAll($limit = 100, $offset = 0, $status = null)
  {
    $sql = "SELECT t.*, u.nama_lengkap, u.no_pelanggan, u.alamat,
                       tr.nama_tarif, tr.kategori as kategori_tarif
                FROM tagihan t
                JOIN users u ON t.user_id = u.id
                JOIN tarif tr ON t.tarif_id = tr.id";

    $params = [];

    if ($status) {
      $sql .= " WHERE t.status = ?";
      $params[] = $status;
    }

    $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    return $this->db->fetchAll($sql, $params);
  }

  /**
   * Mendapatkan tagihan berdasarkan ID
   */
  public function getById($id)
  {
    $sql = "SELECT t.*, u.nama_lengkap, u.no_pelanggan, u.alamat, u.email, u.no_telepon,
                       tr.nama_tarif, tr.kategori as kategori_tarif
                FROM tagihan t
                JOIN users u ON t.user_id = u.id
                JOIN tarif tr ON t.tarif_id = tr.id
                WHERE t.id = ?";
    return $this->db->fetchOne($sql, [$id]);
  }

  /**
   * Mendapatkan tagihan berdasarkan nomor tagihan
   */
  public function getByNoTagihan($noTagihan)
  {
    $sql = "SELECT t.*, u.nama_lengkap, u.no_pelanggan, u.alamat, u.email, u.no_telepon,
                       tr.nama_tarif, tr.kategori as kategori_tarif
                FROM tagihan t
                JOIN users u ON t.user_id = u.id
                JOIN tarif tr ON t.tarif_id = tr.id
                WHERE t.no_tagihan = ?";
    return $this->db->fetchOne($sql, [$noTagihan]);
  }

  /**
   * Mendapatkan tagihan berdasarkan user ID
   */
  public function getByUserId($userId, $status = null)
  {
    $sql = "SELECT t.*, tr.nama_tarif, tr.kategori as kategori_tarif
                FROM tagihan t
                JOIN tarif tr ON t.tarif_id = tr.id
                WHERE t.user_id = ?";

    $params = [$userId];

    if ($status) {
      $sql .= " AND t.status = ?";
      $params[] = $status;
    }

    $sql .= " ORDER BY t.periode_tahun DESC, t.periode_bulan DESC";

    return $this->db->fetchAll($sql, $params);
  }

  /**
   * Mendapatkan tagihan belum bayar user
   */
  public function getUnpaidByUserId($userId)
  {
    return $this->getByUserId($userId, 'belum_bayar');
  }

  /**
   * Buat tagihan baru
   */
  public function create($data)
  {
    try {
      // Ambil tarif berdasarkan kategori user
      $sql = "SELECT t.id as tarif_id, t.nominal
                    FROM tarif t
                    JOIN users u ON t.kategori = u.kategori
                    WHERE u.id = ? AND t.is_active = 1";
      $tarif = $this->db->fetchOne($sql, [$data['user_id']]);

      if (!$tarif) {
        return ['success' => false, 'message' => 'Tarif tidak ditemukan'];
      }

      // Generate nomor tagihan
      $noTagihan = generateNoTagihan($data['user_id'], $data['periode_bulan'], $data['periode_tahun']);

      // Cek apakah tagihan periode ini sudah ada
      $existing = $this->getByNoTagihan($noTagihan);
      if ($existing) {
        return ['success' => false, 'message' => 'Tagihan periode ini sudah ada'];
      }

      $biayaAdmin = $data['biaya_admin'] ?? 5000;
      $totalTagihan = $tarif['nominal'] + $biayaAdmin;

      $tanggalTerbit = $data['tanggal_terbit'] ?? date('Y-m-d');
      $tanggalJatuhTempo = $data['tanggal_jatuh_tempo'] ?? date('Y-m-20');

      $sql = "INSERT INTO tagihan (user_id, no_tagihan, periode_bulan, periode_tahun, tarif_id,
                    nominal_tagihan, biaya_admin, denda, total_tagihan, tanggal_terbit, tanggal_jatuh_tempo, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, 'belum_bayar')";

      $this->db->query($sql, [
        $data['user_id'],
        $noTagihan,
        $data['periode_bulan'],
        $data['periode_tahun'],
        $tarif['tarif_id'],
        $tarif['nominal'],
        $biayaAdmin,
        $totalTagihan,
        $tanggalTerbit,
        $tanggalJatuhTempo
      ]);

      $tagihanId = $this->db->lastInsertId();

      // Buat notifikasi
      $this->createNotification($data['user_id'], $data['periode_bulan'], $data['periode_tahun'], $totalTagihan);

      logActivity('Buat tagihan baru', 'tagihan', $tagihanId, $data['user_id']);

      return [
        'success' => true,
        'message' => 'Tagihan berhasil dibuat',
        'tagihan_id' => $tagihanId,
        'no_tagihan' => $noTagihan
      ];

    } catch (Exception $e) {
      error_log("Create Tagihan Error: " . $e->getMessage());
      return ['success' => false, 'message' => 'Gagal membuat tagihan'];
    }
  }

  /**
   * Buat tagihan bulk untuk semua user aktif
   */
  public function createBulk($bulan, $tahun)
  {
    $sql = "SELECT id FROM users WHERE status = 'aktif'";
    $users = $this->db->fetchAll($sql);

    $created = 0;
    $failed = 0;

    foreach ($users as $user) {
      $result = $this->create([
        'user_id' => $user['id'],
        'periode_bulan' => $bulan,
        'periode_tahun' => $tahun
      ]);

      if ($result['success']) {
        $created++;
      } else {
        $failed++;
      }
    }

    return [
      'success' => true,
      'message' => "Berhasil membuat $created tagihan, $failed gagal",
      'created' => $created,
      'failed' => $failed
    ];
  }

  /**
   * Update status tagihan
   */
  public function updateStatus($id, $status)
  {
    try {
      $sql = "UPDATE tagihan SET status = ? WHERE id = ?";
      $this->db->query($sql, [$status, $id]);

      logActivity('Update status tagihan', 'tagihan', $id);

      return ['success' => true, 'message' => 'Status tagihan diperbarui'];

    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Gagal memperbarui status'];
    }
  }

  /**
   * Hitung denda tagihan terlambat
   */
  public function calculateLateFees()
  {
    try {
      $sql = "SELECT id, nominal_tagihan, tanggal_jatuh_tempo
                    FROM tagihan
                    WHERE status = 'belum_bayar' AND tanggal_jatuh_tempo < CURDATE()";
      $tagihanList = $this->db->fetchAll($sql);

      foreach ($tagihanList as $tagihan) {
        $denda = hitungDenda($tagihan['nominal_tagihan'], $tagihan['tanggal_jatuh_tempo']);

        $sql = "UPDATE tagihan SET denda = ?, total_tagihan = nominal_tagihan + biaya_admin + ?, status = 'terlambat' WHERE id = ?";
        $this->db->query($sql, [$denda, $denda, $tagihan['id']]);
      }

      return ['success' => true, 'message' => count($tagihanList) . ' tagihan diperbarui'];

    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Gagal menghitung denda'];
    }
  }

  /**
   * Statistik tagihan
   */
  public function getStatistics()
  {
    $stats = [];

    // Total semua tagihan bulan ini
    $sql = "SELECT COUNT(*) as total,
                       SUM(total_tagihan) as nominal_total
                FROM tagihan
                WHERE periode_bulan = MONTH(NOW()) AND periode_tahun = YEAR(NOW())";
    $stats['bulan_ini'] = $this->db->fetchOne($sql);

    // Tagihan belum dibayar
    $sql = "SELECT COUNT(*) as total,
                       SUM(total_tagihan) as nominal_total
                FROM tagihan
                WHERE status IN ('belum_bayar', 'terlambat')";
    $stats['belum_bayar'] = $this->db->fetchOne($sql);

    // Tagihan lunas bulan ini
    $sql = "SELECT COUNT(*) as total,
                       SUM(total_tagihan) as nominal_total
                FROM tagihan
                WHERE status = 'lunas'
                AND periode_bulan = MONTH(NOW()) AND periode_tahun = YEAR(NOW())";
    $stats['lunas_bulan_ini'] = $this->db->fetchOne($sql);

    return $stats;
  }

  /**
   * Hitung total tagihan
   */
  public function count($status = null)
  {
    $sql = "SELECT COUNT(*) as total FROM tagihan";
    $params = [];

    if ($status) {
      $sql .= " WHERE status = ?";
      $params[] = $status;
    }

    $result = $this->db->fetchOne($sql, $params);
    return $result['total'];
  }

  /**
   * Buat notifikasi tagihan
   */
  private function createNotification($userId, $bulan, $tahun, $nominal)
  {
    $namaBulan = namaBulan($bulan);
    $sql = "INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, ?, ?, 'tagihan')";
    $this->db->query($sql, [
      $userId,
      "Tagihan Bulan $namaBulan $tahun",
      "Tagihan retribusi sampah Anda sebesar " . rupiah($nominal) . " telah terbit. Silakan lakukan pembayaran sebelum jatuh tempo."
    ]);
  }
}
?>

