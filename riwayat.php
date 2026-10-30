<?php
/**
 * Root riwayat router
 */
require_once __DIR__ . '/config/init.php';

if (Session::isAdminLoggedIn()) {
  redirect('admin/pembayaran.php');
} elseif (Session::isLoggedIn()) {
  redirect('user/riwayat.php');
} else {
  Session::flash('warning', 'Silakan login terlebih dahulu');
  redirect('login.php');
}
