<?php
require_once __DIR__ . '/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT p.*, c.name AS category_name FROM photos p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$photo = $stmt->get_result()->fetch_assoc();

if (!$photo) {
    http_response_code(404);
    die('Foto tidak ditemukan');
}

$title = $photo['title'];
include __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
  <div class="col-12 col-md-7">
    <img class="w-100 rounded" src="<?= BASE_URL ?>/uploads/<?= esc($photo['file_path']) ?>" alt="<?= esc($photo['title']) ?>">
  </div>
  <div class="col-12 col-md-5">
    <h1 class="h4"><?= esc($photo['title']) ?></h1>
    <div class="text-muted mb-2 small">Diunggah pada <?= date('d M Y H:i', strtotime($photo['created_at'])) ?></div>
    <div class="mb-3"><span class="badge text-bg-secondary"><?= esc($photo['category_name'] ?? 'Tanpa Kategori') ?></span></div>
    <p><?= nl2br(esc($photo['description'] ?? '')) ?></p>
    <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>/galeri.php">Kembali ke Galeri</a>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
