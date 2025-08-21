<?php
require_once __DIR__ . '/../functions.php';
require_login();

$stat = ['categories'=>0, 'photos'=>0];
$res = db()->query("SELECT COUNT(*) c FROM categories"); if ($res) $stat['categories'] = (int)$res->fetch_assoc()['c'];
$res = db()->query("SELECT COUNT(*) c FROM photos"); if ($res) $stat['photos'] = (int)$res->fetch_assoc()['c'];

$title = 'Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<h1 class="h4 mb-4">Dashboard</h1>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small">Total Kategori</div>
            <div class="display-6"><?= (int)$stat['categories'] ?></div>
          </div>
          <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/admin/kategori.php">Kelola</a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small">Total Foto</div>
            <div class="display-6"><?= (int)$stat['photos'] ?></div>
          </div>
          <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/admin/foto.php">Kelola</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
