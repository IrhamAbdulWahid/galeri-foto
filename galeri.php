<?php
require_once __DIR__ . '/functions.php';

$per_page = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;
$category_slug = trim($_GET['category'] ?? '');
$where = "WHERE p.status='approved'"; // default filter hanya approved
$title = 'Semua Foto ';

if ($category_slug) {
    $stmt = db()->prepare("SELECT id, name FROM categories WHERE slug=? AND status='approved' LIMIT 1");
    $stmt->bind_param('s', $category_slug);
    $stmt->execute();
    $cat = $stmt->get_result()->fetch_assoc();
    if ($cat) {
        $where .= " AND p.category_id=" . (int)$cat['id'];
        $title = 'Kategori: ' . $cat['name'];
    }
}

$total = 0;
$res = db()->query("SELECT COUNT(*) AS c FROM photos p $where");
if ($res) $total = (int)$res->fetch_assoc()['c'];

$photos = [];
$res = db()->query("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                    FROM photos p
                    LEFT JOIN categories c ON c.id=p.category_id
                    $where
                    ORDER BY p.created_at DESC
                    LIMIT $per_page OFFSET $offset");
if ($res) $photos = $res->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<style>
/* Animasi hover untuk card */
.card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
  transform: translateY(-6px) scale(1.03);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* Animasi fade-in */
.fade-in {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeInUp 0.8s forwards;
}
@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive image agar rapi */
.card-img-top {
  object-fit: cover;
  height: 180px;
  border-radius: 0.5rem;
}
</style>

<h1 class="h4 mb-3 text-center fw-bold"><?= esc($title) ?></h1>

<div class="row g-3">
  <?php foreach ($photos as $index => $p): ?>
    <div class="col-6 col-md-4 col-lg-3 fade-in" style="animation-delay: <?= $index * 0.1 ?>s">
      <div class="card h-100 shadow-sm border-0">
        <a href="<?= BASE_URL ?>/detail.php?id=<?= (int)$p['id'] ?>">
          <img class="card-img-top" src="<?= BASE_URL ?>/uploads/<?= esc($p['file_path']) ?>" alt="<?= esc($p['title']) ?>">
        </a>
        <div class="card-body text-center">
          <h3 class="h6 card-title mb-1"><?= esc($p['title']) ?></h3>
          <div class="small text-muted"><?= esc($p['category_name'] ?? 'Tanpa Kategori') ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (empty($photos)): ?>
    <div class="col-12">
      <div class="alert alert-info text-center">Tidak ada foto pada kategori ini.</div>
    </div>
  <?php endif; ?>
</div>

<?php
$total_pages = max(1, (int)ceil($total / $per_page));
if ($total_pages > 1): ?>
<nav aria-label="Pagination" class="mt-4">
  <ul class="pagination justify-content-center flex-wrap">
    <?php for ($i=1; $i <= $total_pages; $i++): 
      $qs = http_build_query(array_filter(['category'=>$category_slug ?: null, 'page'=>$i]));
      $active = $i === $page ? ' active' : '';
    ?>
      <li class="page-item<?= $active ?>">
        <a class="page-link" href="<?= BASE_URL ?>/galeri.php?<?= $qs ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<script>
// Tambahkan efek smooth saat load
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".fade-in").forEach(el => {
    el.style.animationPlayState = "running";
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
