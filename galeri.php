<?php
require_once __DIR__ . '/functions.php';

$per_page = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// Hitung total foto approved
$total = 0;
$res = db()->query("SELECT COUNT(*) AS c FROM photos WHERE status='approved'");
if ($res) $total = (int)$res->fetch_assoc()['c'];

// Ambil foto
$photos = [];
$res = db()->query("SELECT * FROM photos WHERE status='approved'
                    ORDER BY created_at DESC
                    LIMIT $per_page OFFSET $offset");
if ($res) $photos = $res->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<style>
/* Card animasi hover */
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

/* Fade-in */
.fade-in {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeInUp 0.8s forwards;
}
@keyframes fadeInUp {
  to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="container my-4">
  <h1 class="h4 mb-4 text-center fw-bold">Galeri Foto</h1>

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
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (empty($photos)): ?>
      <div class="col-12">
        <div class="alert alert-info text-center">❗ Belum ada foto di galeri</div>
      </div>
    <?php endif; ?>
  </div>

  <?php
  $total_pages = max(1, (int)ceil($total / $per_page));
  if ($total_pages > 1): ?>
  <nav aria-label="Pagination" class="mt-4">
    <ul class="pagination justify-content-center flex-wrap">
      <?php for ($i=1; $i <= $total_pages; $i++): 
        $qs = http_build_query(['page'=>$i]);
        $active = $i === $page ? ' active' : '';
      ?>
        <li class="page-item<?= $active ?>">
          <a class="page-link" href="<?= BASE_URL ?>/galeri.php?<?= $qs ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".fade-in").forEach(el => {
    el.style.animationPlayState = "running";
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
