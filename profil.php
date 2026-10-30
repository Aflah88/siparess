<?php
/**
 * Root profil router
 */
require_once __DIR__ . '/config/init.php';

if (Session::isLoggedIn()) {
  redirect('user/profil.php');
} elseif (Session::isAdminLoggedIn()) {
  redirect('admin/dashboard.php');
} else {
  Session::flash('warning', 'Silakan login terlebih dahulu');
  redirect('login.php');
}
