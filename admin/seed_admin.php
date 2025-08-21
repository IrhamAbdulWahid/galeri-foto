<?php
require_once __DIR__ . '/../functions.php';

$username = 'admin';
$password = 'admin123';

$stmt = db()->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param('s', $username);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();

if ($exists) {
    echo "Admin sudah ada.\n";
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = db()->prepare("INSERT INTO users(username, password_hash) VALUES(?, ?)");
$stmt->bind_param('ss', $username, $hash);
$stmt->execute();

echo "Admin dibuat. Username: admin, Password: admin123\n";
