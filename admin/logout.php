<?php
/**
 * SIPARES - Admin Logout
 */
require_once __DIR__ . '/../config/init.php';
require_once INCLUDES_PATH . 'Admin.php';

$adminModel = new Admin();
$adminModel->logout();

Session::flash('success', 'Anda telah berhasil logout');
redirect('../login.php');
?>

