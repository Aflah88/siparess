<?php
/**
 * SIPARES - User Logout
 */
require_once __DIR__ . '/../config/init.php';
require_once INCLUDES_PATH . 'User.php';

$userModel = new User();
$userModel->logout();

Session::flash('success', 'Anda telah berhasil logout');
redirect('../login.php');
?>

