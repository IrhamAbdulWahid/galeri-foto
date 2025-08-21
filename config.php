<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'galeri_foto');

// Base URL (adjust if not at webroot). Example: '/galeri-foto'
define('BASE_URL', 'http://localhost/galeri-foto');

// Timezone
date_default_timezone_set('Asia/Jakarta');

function db() : mysqli {
    static $conn;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('Database connection failed: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
?>
