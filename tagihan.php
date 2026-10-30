<?php
/**
 * Root tagihan router
 */
require_once __DIR__ . '/config/init.php';

if (Session::isAdminLoggedIn()) {
  redirect('admin/tagihan.php');
} elseif (Session::isLoggedIn()) {
  redirect('user/tagihan.php');
} else {
  Session::flash('warning', 'Silakan login terlebih dahulu');
  redirect('login.php');
}
