<?php
require_once __DIR__ . '/functions.php';

// Ambil kategori dari URL
$category_slug = trim($_GET['category'] ?? '');

// Ambil semua kategori
$cats = [];
$res = db()->query("SELECT * FROM categories ORDER BY name ASC");
if ($res) $cats = $res->fetch_all(MYSQLI_ASSOC);

// Foto Terbaru (FILTER kategori, max 10)
if ($category_slug) {
    $stmt = db()->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM photos p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE c.slug = ?
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    $stmt->bind_param("s", $category_slug);
    $stmt->execute();
    $latest = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $res = db()->query("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM photos p
        LEFT JOIN categories c ON c.id = p.category_id
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    $latest = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

$title = 'Beranda';
include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="mb-5 position-relative hero-cinematic text-center text-light">
  <div class="hero-overlay"></div>
  <div class="position-relative py-5">
    <h1 class="display-4 fw-bold mb-3 animate-glow">Galeri Foto & Cerita</h1>
    <p class="lead mb-3 animate-fade">
      Jelajahi koleksi foto terbaik berdasarkan popularitas dan kategori
    </p>
  </div>
</section>

<!-- Filter Kategori -->
<section class="mb-5 container animate-fade">
  <form method="get" class="d-flex align-items-center gap-2">
    <label for="category" class="fw-semibold">Kategori:</label>
    <select name="category" id="category" class="form-select w-auto" onchange="this.form.submit()">
      <option value="">All</option>
      <?php foreach ($cats as $cat): ?>
        <option value="<?= esc($cat['slug']) ?>" <?= $cat['slug'] === $category_slug ? 'selected' : '' ?>>
          <?= esc($cat['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>
</section>

<!-- Foto Terbaru -->
<section class="container mb-5">
  <h2 class="h5 fw-semibold mb-4 animate-fade">
    <?= $category_slug ? "Foto Terbaru di Kategori: " . esc($cats[array_search($category_slug, array_column($cats, 'slug'))]['name'] ?? 'Tidak Ditemukan') : "Foto Terbaru" ?>
  </h2>
  <div class="masonry-grid">
    <?php foreach ($latest as $index => $p): ?>
      <div class="masonry-item animate-photo" style="animation-delay: <?= $index * 0.2 ?>s;">
        <div class="photo-wrapper">
          <a href="<?= BASE_URL ?>/detail.php?id=<?= (int)$p['id'] ?>">
            <img src="<?= BASE_URL ?>/uploads/<?= esc($p['file_path']) ?>" 
                 alt="<?= esc($p['title']) ?>">
            <div class="photo-overlay">
              <div class="overlay-text">
                <h3 class="h6 mb-1"><?= esc($p['title']) ?></h3>
                <small>👁️ <?= (int)$p['views'] ?> views</small><br>
                <small><?= esc($p['category_name'] ?? 'Tanpa Kategori') ?></small>
              </div>
            </div>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($latest)): ?>
      <div class="alert alert-info text-center py-4 shadow-sm rounded-3">Belum ada foto terbaru</div>
    <?php endif; ?>
  </div>
</section>


<!-- CSS -->
<style>
  .hero-cinematic {
    background: linear-gradient(120deg, #4e54c8, #8f94fb, #1CB5E0, #ff6a00, #ff3c8e);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    border-radius: 0 0 40px 40px;
    position: relative;
    overflow: hidden;
  }
  @keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
  .hero-overlay {
    position: absolute; inset: 0;
    background: radial-gradient(circle at top left, rgba(255,255,255,0.15), transparent 70%),
                radial-gradient(circle at bottom right, rgba(0,0,0,0.3), transparent 70%);
    animation: moveOverlay 12s infinite alternate;
  }
  @keyframes moveOverlay {
    from { transform: translate(-20px, -20px); }
    to { transform: translate(20px, 20px); }
  }
  .animate-glow {
    text-shadow: 0 0 10px #fff, 0 0 25px #4e54c8, 0 0 35px #8f94fb;
    animation: glowPulse 2s infinite alternate;
  }
  @keyframes glowPulse {
    from { text-shadow: 0 0 8px #fff, 0 0 20px #4e54c8; }
    to   { text-shadow: 0 0 20px #fff, 0 0 40px #8f94fb; }
  }
  .masonry-grid {
    columns: 2; column-gap: 1rem;
  }
  @media (min-width: 768px) { .masonry-grid { columns: 3; } }
  @media (min-width: 992px) { .masonry-grid { columns: 4; } }
  .masonry-item { break-inside: avoid; margin-bottom: 1rem; }
  .photo-wrapper { position: relative; overflow: hidden; border-radius: 14px; box-shadow: 0 6px 14px rgba(0,0,0,0.2); }
  .photo-wrapper img { width: 100%; display: block; transition: transform 0.5s ease; }
  .photo-wrapper:hover img { transform: scale(1.1); }
  .photo-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);
    opacity: 0; display: flex; align-items: flex-end;
    transition: opacity 0.4s ease; padding: 12px;
  }
  .photo-wrapper:hover .photo-overlay { opacity: 1; }
  .overlay-text { color: #fff; }
  .animate-fade { opacity: 0; animation: fadeIn 1s forwards; }
  @keyframes fadeIn { to { opacity: 1; } }
  .animate-photo { opacity: 0; transform: scale(0.9) translateY(20px); animation: photoIn 0.9s forwards; }
  @keyframes photoIn { to { opacity: 1; transform: scale(1) translateY(0); } }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
