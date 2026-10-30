<?php
/**
 * ========================================
 * SIPARES - Notifikasi API
 * ========================================
 * Endpoint untuk memproses notifikasi otomatis dan interaksi pengguna
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/init.php';
require_once INCLUDES_PATH . 'Notifikasi.php';

$action = $_GET['action'] ?? 'list';
$notifModel = new Notifikasi();

// Tentukan user ID (dari session atau parameter default untuk demo jika belum login)
$userId = Session::userId();
if (!$userId && isset($_GET['demo_user_id'])) {
    $userId = (int) $_GET['demo_user_id'];
} elseif (!$userId && (isset($_GET['user_id']) || isPost())) {
    $postData = json_decode(file_get_contents('php://input'), true);
    $userId = (int) ($_GET['user_id'] ?? $postData['user_id'] ?? 0);
}

// Jika belum login dan tidak ada demo user, default ke user 1 agar antarmuka HTML tetap interaktif
if (!$userId) {
    $userId = 1; // Default pelanggan Ahmad Susanto untuk preview/demo
}

switch ($action) {
    case 'list':
        handleList($notifModel, $userId);
        break;

    case 'count':
        handleCount($notifModel, $userId);
        break;

    case 'mark_read':
        handleMarkRead($notifModel, $userId);
        break;

    case 'mark_all':
        handleMarkAll($notifModel, $userId);
        break;

    case 'run_check':
        handleRunCheck($notifModel, $userId);
        break;

    case 'cron':
        handleCron($notifModel);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Action tidak valid'], 400);
}

/**
 * Mengambil daftar notifikasi dan menjalankan pengecekan otomatis
 */
function handleList($notifModel, $userId)
{
    // Jalankan pengecekan otomatis sebelum menampilkan list
    $notifModel->triggerAutomatedNotifications($userId);

    $limit = (int) ($_GET['limit'] ?? 15);
    $items = $notifModel->getByUserId($userId, $limit);
    $unreadCount = $notifModel->countUnread($userId);

    // Format waktu ramah pengguna
    foreach ($items as &$item) {
        $timestamp = strtotime($item['created_at']);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            $item['time_ago'] = 'Baru saja';
        } elseif ($diff < 3600) {
            $item['time_ago'] = floor($diff / 60) . ' menit lalu';
        } elseif ($diff < 86400) {
            $item['time_ago'] = floor($diff / 3600) . ' jam lalu';
        } elseif ($diff < 604800) {
            $item['time_ago'] = floor($diff / 86400) . ' hari lalu';
        } else {
            $item['time_ago'] = date('d M Y', $timestamp);
        }
    }

    jsonResponse([
        'success' => true,
        'unread_count' => $unreadCount,
        'total' => count($items),
        'data' => $items
    ]);
}

/**
 * Mengambil jumlah unread notifikasi
 */
function handleCount($notifModel, $userId)
{
    $notifModel->triggerAutomatedNotifications($userId);
    $unreadCount = $notifModel->countUnread($userId);

    jsonResponse([
        'success' => true,
        'unread_count' => $unreadCount
    ]);
}

/**
 * Tandai satu notifikasi sebagai sudah dibaca
 */
function handleMarkRead($notifModel, $userId)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int) ($data['id'] ?? $_GET['id'] ?? 0);

    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID notifikasi diperlukan'], 400);
    }

    $notifModel->markAsRead($id, $userId);
    $newCount = $notifModel->countUnread($userId);

    jsonResponse([
        'success' => true,
        'message' => 'Notifikasi ditandai telah dibaca',
        'unread_count' => $newCount
    ]);
}

/**
 * Tandai seluruh notifikasi sebagai sudah dibaca
 */
function handleMarkAll($notifModel, $userId)
{
    $notifModel->markAllAsRead($userId);

    jsonResponse([
        'success' => true,
        'message' => 'Semua notifikasi ditandai telah dibaca',
        'unread_count' => 0
    ]);
}

/**
 * Menjalankan pengecekan notifikasi otomatis untuk user tertentu
 */
function handleRunCheck($notifModel, $userId)
{
    $created = $notifModel->triggerAutomatedNotifications($userId);
    $unreadCount = $notifModel->countUnread($userId);

    jsonResponse([
        'success' => true,
        'message' => "Pengecekan selesai. Dihasilkan {$created} notifikasi otomatis baru.",
        'created' => $created,
        'unread_count' => $unreadCount
    ]);
}

/**
 * Menjalankan cron bulk check untuk semua user
 */
function handleCron($notifModel)
{
    $created = $notifModel->triggerBulkAutomatedNotifications();

    jsonResponse([
        'success' => true,
        'message' => "Bulk check berhasil. Dihasilkan {$created} notifikasi otomatis baru.",
        'created' => $created
    ]);
}

