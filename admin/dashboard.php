<?php
require_once __DIR__ . '/../functions.php';
require_login();

// Ambil semua kategori
$categories_res = db()->query("SELECT id, name, slug FROM categories ORDER BY name ASC");
$categories = $categories_res->fetch_all(MYSQLI_ASSOC);

// Hitung total kategori
$res = db()->query("SELECT COUNT(*) c FROM categories WHERE status='approved'");
$total_categories = $res ? (int)$res->fetch_assoc()['c'] : 0;

// Hitung total foto (approved)
$res = db()->query("SELECT COUNT(*) c FROM photos WHERE status='approved'");
$photos_total = $res ? (int)$res->fetch_assoc()['c'] : 0;

// Hitung foto pending
$res = db()->query("SELECT COUNT(*) c FROM photos WHERE status='pending'");
$photos_pending = $res ? (int)$res->fetch_assoc()['c'] : 0;

$title = 'Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h5 fw-bold mb-0">Dashboard Admin</h1>
</div>

<div class="row g-4">
  <!-- Total Kategori -->
  <div class="col-md-4">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body d-flex flex-column justify-content-between">
        <div>
          <div class="text-muted small">Total Kategori</div>
          <div class="display-6 fw-bold"><?= $total_categories ?></div>
        </div>
        <a href="<?= BASE_URL ?>/admin/kategori.php" class="btn btn-outline-primary btn-sm mt-3">
          Kelola Kategori
        </a>
      </div>
    </div>
  </div>

  <!-- Total Foto -->
  <div class="col-md-4">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body d-flex flex-column justify-content-between">
        <div>
          <div class="text-muted small">Total Foto</div>
          <div class="display-6 fw-bold"><?= $photos_total ?></div>
        </div>
        <a href="<?= BASE_URL ?>/admin/foto.php" class="btn btn-outline-primary btn-sm mt-3">
          Kelola Foto
        </a>
      </div>
    </div>
  </div>

  <!-- Foto Pending -->
  <div class="col-md-4">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body d-flex flex-column justify-content-between">
        <div>
          <div class="text-muted small">Menunggu Persetujuan</div>
          <div class="display-6 fw-bold"><?= $photos_pending ?></div>
        </div>
        <a href="<?= BASE_URL ?>/admin/pending_items.php" class="btn btn-outline-warning btn-sm mt-3">
          Lihat Pending
        </a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
