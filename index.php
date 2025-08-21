<?php
require_once __DIR__ . '/functions.php';

// Fetch latest photos
$photos = [];
$res = db()->query("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                    FROM photos p
                    LEFT JOIN categories c ON c.id = p.category_id
                    ORDER BY p.created_at DESC
                    LIMIT 12");
if ($res) $photos = $res->fetch_all(MYSQLI_ASSOC);

// Fetch categories
$cats = [];
$res = db()->query("SELECT * FROM categories ORDER BY name ASC");
if ($res) $cats = $res->fetch_all(MYSQLI_ASSOC);

$title = 'Beranda';
include __DIR__ . '/includes/header.php';
?>
<section class="mb-4">
  <div class="p-4 p-md-5 rounded-3 bg-light">
    <h1 class="display-6 fw-bold">Galeri Foto</h1>
    <p class="lead mb-0">Lihat koleksi foto terbaru dan jelajahi berdasarkan kategori.</p>
  </div>
</section>

<section class="mb-5">
  <h2 class="h5 mb-3">Kategori</h2>
  <div class="d-flex flex-wrap gap-2">
    <?php foreach ($cats as $cat): ?>
      <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>/galeri.php?category=<?= esc($cat['slug']) ?>"><?= esc($cat['name']) ?></a>
    <?php endforeach; ?>
  </div>
</section>

<section>
  <h2 class="h5 mb-3">Foto Terbaru</h2>
  <div class="row g-3">
    <?php foreach ($photos as $p): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100">
          <a href="<?= BASE_URL ?>/detail.php?id=<?= (int)$p['id'] ?>">
            <img class="card-img-top" src="<?= BASE_URL ?>/uploads/<?= esc($p['file_path']) ?>" alt="<?= esc($p['title']) ?>">
          </a>
          <div class="card-body">
            <h3 class="h6 card-title mb-1"><?= esc($p['title']) ?></h3>
            <div class="small text-muted"><?= esc($p['category_name'] ?? 'Tanpa Kategori') ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($photos)): ?>
      <div class="col-12"><div class="alert alert-info">Belum ada foto.</div></div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
