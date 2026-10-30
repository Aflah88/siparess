<?php
/**
 * Root dashboard router
 * Automatically routes users to user/dashboard.php and admins to admin/dashboard.php
 */
require_once __DIR__ . '/config/init.php';

if (Session::isAdminLoggedIn()) {
  redirect('admin/dashboard.php');
} elseif (Session::isLoggedIn()) {
  redirect('user/dashboard.php');
} else {
  redirect('login.php');
}
