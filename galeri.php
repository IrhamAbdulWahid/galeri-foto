<?php
require_once __DIR__ . '/functions.php';

$per_page = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;
$category_slug = trim($_GET['category'] ?? '');
$where = '';
$title = 'Galeri';

if ($category_slug) {
    $stmt = db()->prepare("SELECT id, name FROM categories WHERE slug=? LIMIT 1");
    $stmt->bind_param('s', $category_slug);
    $stmt->execute();
    $cat = $stmt->get_result()->fetch_assoc();
    if ($cat) {
        $where = "WHERE p.category_id=" . (int)$cat['id'];
        $title = 'Galeri: ' . $cat['name'];
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
<h1 class="h4 mb-3"><?= esc($title) ?></h1>

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
    <div class="col-12"><div class="alert alert-info">Tidak ada foto pada kategori ini.</div></div>
  <?php endif; ?>
</div>

<?php
$total_pages = max(1, (int)ceil($total / $per_page));
if ($total_pages > 1): ?>
<nav aria-label="Pagination" class="mt-4">
  <ul class="pagination">
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

<?php include __DIR__ . '/includes/footer.php'; ?>
