<?php
require_once __DIR__ . '/../functions.php';
header('Content-Type: application/json');

// Ambil semua foto yang statusnya approved
$res = db()->query("
    SELECT id, title, description, file_path, created_at 
    FROM photos 
    WHERE status='approved' 
    ORDER BY created_at DESC
");

$photos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// Output JSON
echo json_encode([
    "status" => "success",
    "data" => $photos
], JSON_PRETTY_PRINT);
