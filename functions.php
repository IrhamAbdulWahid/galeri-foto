<?php
// Common functions
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config.php';

function esc(string $s) : string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path) : never {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function slugify(string $text) : string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    if (empty($text)) return 'n-a';
    return $text;
}

function is_logged_in() : bool {
    return isset($_SESSION['admin_id']);
}

// 🔑 Tambahan: cek apakah user adalah admin
function is_admin() : bool {
    // Untuk sekarang sama saja dengan is_logged_in()
    // Kalau nanti ada role lain (user biasa, moderator), bisa diatur di sini
    return isset($_SESSION['admin_id']);
}

function require_login() : void {
    if (!is_logged_in()) redirect('/admin/login.php');
}

function set_flash(string $key, string $msg) : void {
    $_SESSION['flash'][$key] = $msg;
}

function get_flash(string $key) : ?string {
    if (!empty($_SESSION['flash'][$key])) {
        $m = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    return null;
}

function csrf_token() : string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function verify_csrf() : void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = $_POST['csrf'] ?? '';
        if (!$t || $t !== ($_SESSION['csrf'] ?? '')) {
            http_response_code(419);
            die('CSRF validation failed.');
        }
    }
}

// Ensure uploads dir exists
if (!is_dir(__DIR__ . '/uploads')) {
    @mkdir(__DIR__ . '/uploads', 0775, true);
}
?>
