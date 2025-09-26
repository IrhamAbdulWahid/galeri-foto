<?php
require_once __DIR__ . '/functions.php';

$per_page = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// kategori filter
$category = $_GET['category'] ?? '';

// ambil kategori yang disetujui
$cats = [];
$res2 = db()->query("SELECT * FROM categories WHERE status='approved' ORDER BY name ASC");
if ($res2) $cats = $res2->fetch_all(MYSQLI_ASSOC);

// Hitung total foto approved
$total = 0;
$sql_total = "SELECT COUNT(*) AS c FROM photos WHERE status='approved'";
if (!empty($category)) {
  $sql_total .= " AND category_id = " . (int)$category;
}
$res = db()->query($sql_total);
if ($res) $total = (int)$res->fetch_assoc()['c'];

// Ambil foto approved dengan limit
$photos = [];
$sql = "SELECT p.*, c.name AS category_name 
        FROM photos p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.status='approved'";
if (!empty($category)) {
  $sql .= " AND p.category_id = " . (int)$category;
}
$sql .= " ORDER BY p.created_at DESC
          LIMIT $per_page OFFSET $offset";

$res = db()->query($sql);
if ($res) $photos = $res->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
  <h1 class="h4 mb-4 text-center fw-bold">Galeri Foto</h1>

  <!-- Dropdown kategori -->
  <div class="row justify-content-center mb-4">
    <div class="col-md-4">
      <select name="category" class="form-select"
              onchange="location.href='galeri.php?category='+this.value">
        <option value="">Semua Kategori</option>
        <?php foreach ($cats as $cat): ?>
          <option value="<?= $cat['id']; ?>" <?= ($category == $cat['id']) ? 'selected' : ''; ?>>
            <?= htmlspecialchars($cat['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="row g-4">
    <?php foreach ($photos as $index => $p): ?>
      <div class="col-6 col-md-4 col-lg-3 fade-in" style="animation-delay: <?= $index * 0.08 ?>s">
        <div class="card h-100 shadow-sm border-0">
          <a href="<?= BASE_URL ?>/detail.php?id=<?= (int)$p['id'] ?>">
            <img class="card-img-top" src="<?= BASE_URL ?>/uploads/<?= esc($p['file_path']) ?>" alt="<?= esc($p['title']) ?>">
          </a>
          <div class="card-body text-center p-2">
            <h3 class="h6 card-title mb-1 text-truncate" title="<?= esc($p['title']) ?>">
              <?= esc($p['title']) ?>
            </h3>
            <p class="text-muted small"><?= esc($p['category_name']) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (empty($photos)): ?>
      <div class="col-12">
        <div class="alert alert-info text-center shadow-sm rounded-3">
          Belum ada foto di galeri
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php
  $total_pages = max(1, (int)ceil($total / $per_page));
  if ($total_pages > 1): ?>
    <nav aria-label="Pagination" class="mt-4">
      <ul class="pagination justify-content-center flex-wrap">
        <?php for ($i = 1; $i <= $total_pages; $i++): 
          $qs = ['page' => $i];
          if (!empty($category)) $qs['category'] = $category;
          $qs_str = http_build_query($qs);
          $active = $i === $page ? ' active' : '';
        ?>
          <li class="page-item<?= $active ?>">
            <a class="page-link" href="<?= BASE_URL ?>/galeri.php?<?= $qs_str ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<style>
/* Card hover animasi */
.card {
  transition: all 0.3s ease;
  border-radius: 0.75rem;
  overflow: hidden;
}
.card:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 24px rgba(0,0,0,0.2);
}
.card-img-top {
  object-fit: cover;
  height: 190px;
  transition: transform 0.3s ease;
}
.card:hover .card-img-top {
  transform: scale(1.05);
}

/* Animasi fade-in */
.fade-in {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeInUp 0.8s forwards;
}
@keyframes fadeInUp {
  to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".fade-in").forEach(el => {
    el.style.animationPlayState = "running";
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
