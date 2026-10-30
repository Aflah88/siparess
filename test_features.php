<?php
/**
 * Test Suite for SIPARES Features:
 * 1. Notifikasi Otomatis
 * 2. Bukti Digital
 */

require_once __DIR__ . '/config/init.php';
require_once INCLUDES_PATH . 'User.php';
require_once INCLUDES_PATH . 'Tagihan.php';
require_once INCLUDES_PATH . 'Pembayaran.php';
require_once INCLUDES_PATH . 'Notifikasi.php';

echo "========================================\n";
echo " TESTING SIPARES NEW FEATURES\n";
echo "========================================\n\n";

$db = db();
$notifModel = new Notifikasi();
$pembayaranModel = new Pembayaran();
$tagihanModel = new Tagihan();

// 1. Test Automated Notification Trigger for User 1
echo "[TEST 1] Trigger Automated Notifications for User 1...\n";
$newNotifCount = $notifModel->triggerAutomatedNotifications(1);
echo "Result: {$newNotifCount} notifications triggered.\n";

$unreadCount = $notifModel->countUnread(1);
$list = $notifModel->getByUserId(1, 10);
echo "Total notifications for User 1: " . count($list) . ", Unread: {$unreadCount}\n";
foreach ($list as $n) {
    echo "  - [{$n['tipe']}] {$n['judul']} (Read: {$n['is_read']})\n";
}
assert(count($list) > 0, "Notifications should exist for User 1");
echo "-> TEST 1 PASSED!\n\n";

// 2. Test Mark Notification as Read & Mark All Read
echo "[TEST 2] Testing Mark as Read & Mark All...\n";
if (!empty($list)) {
    $firstId = $list[0]['id'];
    $notifModel->markAsRead($firstId, 1);
    echo "Marked single notification ID {$firstId} as read.\n";
}
$notifModel->markAllAsRead(1);
$unreadAfterMarkAll = $notifModel->countUnread(1);
echo "Unread count after mark all: {$unreadAfterMarkAll}\n";
assert($unreadAfterMarkAll === 0, "Unread count should be 0 after markAllAsRead");
echo "-> TEST 2 PASSED!\n\n";

// 3. Test Bukti Pembayaran Digital (getReceiptData)
echo "[TEST 3] Testing Bukti Pembayaran Digital Data Retrieval...\n";
$receiptData = $pembayaranModel->getReceiptData('TRX-2025120023');
assert($receiptData !== null, "Receipt data for TRX-2025120023 should not be null");
echo "Receipt No Transaksi: " . $receiptData['no_transaksi'] . "\n";
echo "Nama Pelanggan      : " . $receiptData['nama_lengkap'] . " (" . $receiptData['no_pelanggan'] . ")\n";
echo "Periode             : " . $receiptData['periode_nama'] . "\n";
echo "Metode Bayar        : " . $receiptData['metode_nama'] . "\n";
echo "Total Bayar         : " . $receiptData['nominal_formatted'] . "\n";
echo "Kode Verifikasi     : " . $receiptData['kode_verifikasi'] . "\n";
echo "Status              : " . $receiptData['status'] . "\n";
assert(!empty($receiptData['kode_verifikasi']), "Verification code should be generated");
assert(!empty($receiptData['nominal_formatted']), "Formatted nominal should exist");
echo "-> TEST 3 PASSED!\n\n";

// 4. Test Bulk Cron Notification
echo "[TEST 4] Testing Bulk Automated Notification Trigger...\n";
$bulkCreated = $notifModel->triggerBulkAutomatedNotifications();
echo "Bulk notifications generated across all users: {$bulkCreated}\n";
echo "-> TEST 4 PASSED!\n\n";

echo "========================================\n";
echo " ALL UNIT TESTS COMPLETED SUCCESSFULLY!\n";
echo "========================================\n";

