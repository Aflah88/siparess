<?php
/**
 * Root index file
 */
require_once __DIR__ . '/config/init.php';

// Jika pengguna sudah login, arahkan langsung ke dashboard yang sesuai
if (Session::isLoggedIn()) {
  redirect('user/dashboard.php');
} elseif (Session::isAdminLoggedIn()) {
  redirect('admin/dashboard.php');
}

// Jika belum login, tampilkan landing page index.html
include __DIR__ . '/index.html';
