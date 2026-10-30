<?php
/**
 * Main Edukasi Router / View
 */
require_once __DIR__ . '/config/init.php';

if (Session::isLoggedIn()) {
  redirect('user/edukasi.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
} else {
  // Jika pengunjung publik, tampilkan halaman edukasi beranda publik
  include __DIR__ . '/edukasi.html';
}
