<?php
require_once __DIR__ . '/functions.php';

$id = (int)($_GET['id'] ?? 0);

// 🔹 Naikkan jumlah views setiap kali halaman dibuka
$update = db()->prepare("UPDATE photos SET views = views + 1 WHERE id = ?");
$update->bind_param('i', $id);
$update->execute();

// 🔹 Ambil data foto
$stmt = db()->prepare("
    SELECT p.*, c.name AS category_name 
    FROM photos p 
    LEFT JOIN categories c ON c.id=p.category_id 
    WHERE p.id=? LIMIT 1
");
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

<style>
  /* 🔹 Atur ukuran foto preview di halaman detail */
  .detail-photo {
    max-width: 400px;   /* preview lebih kecil */
    width: 100%;
    height: auto;
    cursor: pointer;    /* kasih cursor pointer biar kelihatan bisa diklik */
    transition: transform 0.2s ease-in-out;
  }
  .detail-photo:hover {
    transform: scale(1.02);
  }
  /* Modal gambar full */
  .modal-img {
    max-width: 100%;
    height: auto;
  }
</style>

<div class="row g-4">
  <div class="col-12 col-md-7 text-center">
    <!-- 🔹 Foto preview kecil, klik untuk buka modal -->
    <img class="detail-photo rounded shadow" 
         src="<?= BASE_URL ?>/uploads/<?= esc($photo['file_path']) ?>" 
         alt="<?= esc($photo['title']) ?>"
         data-bs-toggle="modal" 
         data-bs-target="#photoModal">
  </div>
  <div class="col-12 col-md-5">
    <h1 class="h4"><?= esc($photo['title']) ?></h1>
    <div class="text-muted mb-2 small">
      Diunggah pada <?= date('d M Y H:i', strtotime($photo['created_at'])) ?>
    </div>
    <div class="mb-3">
      <span class="badge text-bg-secondary"><?= esc($photo['category_name'] ?? 'Tanpa Kategori') ?></span>
    </div>
    <p><?= nl2br(esc($photo['description'] ?? '')) ?></p>
    
    <!-- 🔹 Tampilkan jumlah views -->
    <div class="mb-3 text-muted small">
      👁️ <?= (int)$photo['views'] ?> kali dilihat
    </div>

    <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>/galeri.php">Kembali</a>
    <a href="download.php?id=<?= $photo['id'] ?>" class="btn btn-success">Download PDF</a>
  </div>
</div>

<!-- 🔹 Modal Bootstrap untuk tampilkan foto full -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-center">
      <div class="modal-body p-2">
        <img src="<?= BASE_URL ?>/uploads/<?= esc($photo['file_path']) ?>" 
             alt="<?= esc($photo['title']) ?>" 
             class="modal-img rounded">
      </div>
      <div class="modal-footer border-0 justify-content-between">
        <span class="text-white small"><?= esc($photo['title']) ?></span>
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
