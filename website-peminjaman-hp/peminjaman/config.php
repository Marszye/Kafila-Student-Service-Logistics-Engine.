<?php
// Error Handling Configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/logs/php_errors.log');

// Database Credentials (Prevent Redefinition)
if (!defined('DB_SERVER')) {
    define('DB_SERVER', 'localhost');
}
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', 'peminjaman_user');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', 'password1234');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'peminjaman_hp');
}

// Create database connection using PDO
try {
    $conn = new PDO(
        "mysql:host=" . DB_SERVER . 
        ";dbname=" . DB_NAME . 
        ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION] // Enable exceptions
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
