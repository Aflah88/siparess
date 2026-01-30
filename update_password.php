<?php
/**
 * Script untuk update password sample users
 * Jalankan sekali saja kemudian hapus file ini
 */

require_once __DIR__ . '/config/init.php';

$db = db();

// Hash untuk password "password123"
$newHash = password_hash('password123', PASSWORD_DEFAULT);

// Update users
$sql = "UPDATE users SET password = ?";
$db->query($sql, [$newHash]);
echo "Users password updated!\n";

// Hash untuk password "admin123"
$adminHash = password_hash('admin123', PASSWORD_DEFAULT);

// Update admin
$sql = "UPDATE admin SET password = ?";
$db->query($sql, [$adminHash]);
echo "Admin password updated!\n";

echo "\n=== LOGIN CREDENTIALS ===\n";
echo "User: ahmad@email.com / password123\n";
echo "User: siti@email.com / password123\n";
echo "User: tokomakmur@email.com / password123\n";
echo "Admin: admin / admin123\n";
echo "\nSekarang Anda bisa login!\n";
?>

