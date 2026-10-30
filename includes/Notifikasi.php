<?php
/**
 * ========================================
 * SIPARES - Notifikasi Model
 * ========================================
 * Menangani notifikasi otomatis dan manajemen pesan pengguna
 */

require_once __DIR__ . '/../config/init.php';

class Notifikasi
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Mengambil daftar notifikasi untuk user
     */
    public function getByUserId($userId, $limit = 20, $unreadOnly = false)
    {
        $sql = "SELECT * FROM notifikasi WHERE user_id = ?";
        $params = [$userId];

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = (int) $limit;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Menghitung notifikasi belum dibaca
     */
    public function countUnread($userId)
    {
        $sql = "SELECT COUNT(*) as total FROM notifikasi WHERE user_id = ? AND is_read = 0";
        $row = $this->db->fetchOne($sql, [$userId]);
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca
     */
    public function markAsRead($id, $userId)
    {
        $sql = "UPDATE notifikasi SET is_read = 1 WHERE id = ? AND user_id = ?";
        $this->db->query($sql, [$id, $userId]);
        return true;
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca
     */
    public function markAllAsRead($userId)
    {
        $sql = "UPDATE notifikasi SET is_read = 1 WHERE user_id = ?";
        $this->db->query($sql, [$userId]);
        return true;
    }

    /**
     * Buat notifikasi baru
     */
    public function create($userId, $judul, $pesan, $tipe = 'info', $link = null)
    {
        $sql = "INSERT INTO notifikasi (user_id, judul, pesan, tipe, link, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW())";
        $this->db->query($sql, [$userId, $judul, $pesan, $tipe, $link]);
        return $this->db->lastInsertId();
    }

    /**
     * Pemicu Notifikasi Otomatis (Automated Notification Trigger)
     * Otomatis mendeteksi:
     * 1. Tagihan baru belum dibayar yang belum dinotifikasi
     * 2. Tagihan mendekati jatuh tempo (H-3 atau kurang)
     * 3. Tagihan yang telah melewati jatuh tempo (keterlambatan/denda)
     */
    public function triggerAutomatedNotifications($userId)
    {
        $generatedCount = 0;
        $today = date('Y-m-d');

        // Ambil tagihan aktif milik user
        $sql = "SELECT t.*, tr.nama_tarif
                FROM tagihan t
                JOIN tarif tr ON t.tarif_id = tr.id
                WHERE t.user_id = ? AND t.status IN ('belum_bayar', 'terlambat')";
        $tagihanList = $this->db->fetchAll($sql, [$userId]);

        foreach ($tagihanList as $tagihan) {
            $namaBulan = namaBulan($tagihan['periode_bulan']);
            $periodeStr = $namaBulan . ' ' . $tagihan['periode_tahun'];
            $jatuhTempo = $tagihan['tanggal_jatuh_tempo'];
            $diffDays = (int) ((strtotime($jatuhTempo) - strtotime($today)) / 86400);

            // 1. Notifikasi Tagihan Baru (jika belum pernah ada notifikasi tagihan untuk periode ini)
            $sqlCheck = "SELECT id FROM notifikasi 
                         WHERE user_id = ? AND tipe = 'tagihan' AND judul LIKE ?";
            $existsNew = $this->db->fetchOne($sqlCheck, [$userId, "%$periodeStr%"]);
            if (!$existsNew) {
                $this->create(
                    $userId,
                    "Tagihan Retribusi $periodeStr Terbit",
                    "Tagihan retribusi sampah bulan $periodeStr sebesar " . rupiah($tagihan['total_tagihan']) . " telah terbit. Silakan lakukan pembayaran.",
                    'tagihan',
                    "tagihan.php?id=" . $tagihan['id']
                );
                $generatedCount++;
            }

            // 2. Notifikasi Peringatan Mendekati Jatuh Tempo (antara 0 sampai 3 hari sebelum jatuh tempo)
            if ($diffDays >= 0 && $diffDays <= 3) {
                $sqlCheckWarning = "SELECT id FROM notifikasi 
                                    WHERE user_id = ? AND tipe = 'warning' AND judul LIKE ?";
                $existsWarning = $this->db->fetchOne($sqlCheckWarning, [$userId, "%Jatuh Tempo $periodeStr%"]);
                if (!$existsWarning) {
                    $pesanJatuhTempo = ($diffDays === 0)
                        ? "Hari ini adalah batas akhir jatuh tempo pembayaran tagihan $periodeStr. Segera bayar untuk menghindari denda."
                        : "Tagihan bulan $periodeStr akan jatuh tempo dalam $diffDays hari (" . tanggal_indo($jatuhTempo, 'short') . "). Segera lakukan pembayaran.";

                    $this->create(
                        $userId,
                        "Pengingat Jatuh Tempo $periodeStr",
                        $pesanJatuhTempo,
                        'warning',
                        "tagihan.php?id=" . $tagihan['id']
                    );
                    $generatedCount++;
                }
            }

            // 3. Notifikasi Keterlambatan Pembayaran (jika sudah melewati jatuh tempo)
            if ($diffDays < 0) {
                $sqlCheckOverdue = "SELECT id FROM notifikasi 
                                    WHERE user_id = ? AND tipe = 'warning' AND judul LIKE ?";
                $existsOverdue = $this->db->fetchOne($sqlCheckOverdue, [$userId, "%Keterlambatan $periodeStr%"]);
                if (!$existsOverdue) {
                    // Update status tagihan jika belum 'terlambat'
                    if ($tagihan['status'] !== 'terlambat') {
                        $this->db->query("UPDATE tagihan SET status = 'terlambat' WHERE id = ?", [$tagihan['id']]);
                    }

                    $this->create(
                        $userId,
                        "Pemberitahuan Keterlambatan $periodeStr",
                        "Tagihan retribusi sampah periode $periodeStr telah melewati jatuh tempo sejak " . tanggal_indo($jatuhTempo, 'short') . ". Mohon segera melunasi tagihan Anda.",
                        'warning',
                        "tagihan.php?id=" . $tagihan['id']
                    );
                    $generatedCount++;
                }
            }
        }

        return $generatedCount;
    }

    /**
     * Trigger bulk notifications untuk seluruh pelanggan (misal via cron/admin)
     */
    public function triggerBulkAutomatedNotifications()
    {
        $sql = "SELECT id FROM users WHERE status = 'aktif'";
        $users = $this->db->fetchAll($sql);
        $totalCreated = 0;

        foreach ($users as $user) {
            $totalCreated += $this->triggerAutomatedNotifications($user['id']);
        }

        return $totalCreated;
    }
}

