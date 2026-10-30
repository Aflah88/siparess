<?php
/**
 * ========================================
 * SIPARES - Database Configuration
 * ========================================
 * File ini berisi konfigurasi koneksi ke database MySQL
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'sipares');
define('DB_USER', 'root');
define('DB_PASS', '');  // Laragon default password kosong
define('DB_CHARSET', 'utf8mb4');

// Konfigurasi Aplikasi
define('APP_NAME', 'SIPARES');
define('APP_URL', 'http://localhost/sipares');
define('APP_VERSION', '1.0.0');

// Konfigurasi Session
define('SESSION_LIFETIME', 3600); // 1 jam

// Konfigurasi Upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Timezone
date_default_timezone_set('Asia/Jakarta');

/**
 * Class Database
 * Menangani koneksi ke database menggunakan PDO
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            if (file_exists('/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock')) {
                $dsn = "mysql:unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            } else {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            }
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            // Log error (jangan tampilkan detail di production)
            error_log("Database Connection Error: " . $e->getMessage());
            die("Koneksi database gagal. Silakan hubungi administrator.");
        }
    }

    /**
     * Singleton pattern - mendapatkan instance database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Mendapatkan koneksi PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Menjalankan query dengan prepared statement
     *
     * @param string $sql - Query SQL
     * @param array $params - Parameter untuk binding
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Mengambil satu baris data
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Mengambil semua baris data
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Mendapatkan ID terakhir yang di-insert
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Memulai transaksi
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaksi
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback transaksi
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function untuk mendapatkan instance database
 */
function db() {
    return Database::getInstance();
}
?>
