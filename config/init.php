<?php
/**
 * ========================================
 * SIPARES - Initialization File
 * ========================================
 * File ini harus di-include di setiap halaman PHP
 */

// Definisikan root path
define('ROOT_PATH', dirname(__DIR__) . '/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('API_PATH', ROOT_PATH . 'api/');

// Load konfigurasi
require_once CONFIG_PATH . 'database.php';
require_once CONFIG_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

// Error reporting (ubah ke 0 di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>

