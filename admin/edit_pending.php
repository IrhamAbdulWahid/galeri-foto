<?php
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../includes/header.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = (int)$_GET['id'];

// --- Ambil data foto + kategori ---
$stmt = db()->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.id AS category_id 
    FROM photos p 
    LEFT JOIN categories c ON c.id=p.category_id 
    WHERE p.id=? AND p.status='pending'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$photo = $res->fetch_assoc();

if (!$photo) {
    die("Foto tidak ditemukan atau sudah disetujui");
}

// --- Proses update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category    = trim($_POST['category']);

    // Generate slug otomatis dari kategori
    $slug = slugify($category);

    // Update photo
    $stmt = db()->prepare("UPDATE photos SET title=?, description=? WHERE id=?");
    $stmt->bind_param("ssi", $title, $description, $id);
    $stmt->execute();

    // Update kategori
    if ($photo['category_id']) {
        $stmt = db()->prepare("UPDATE categories SET name=?, slug=? WHERE id=?");
        $stmt->bind_param("ssi", $category, $slug, $photo['category_id']);
        $stmt->execute();
    }

    header("Location: pending_items.php?success=" . urlencode("Foto berhasil diperbarui"));
    exit;
}
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Foto Pending</h4>
        </div>
        <div class="card-body p-4">
          <form method="POST">
            
            <!-- Preview Gambar -->
            <div class="mb-4 text-center">
              <img src="../uploads/<?= esc($photo['file_path']) ?>" 
                   class="img-fluid rounded shadow-sm" 
                   style="max-width:220px; border: 3px solid #eee;">
              <p class="text-muted small mt-2">Preview foto yang sedang diedit</p>
            </div>

            <!-- Judul -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Judul</label>
              <input type="text" 
                     name="title" 
                     value="<?= esc($photo['title']) ?>" 
                     class="form-control" 
                     placeholder="Masukkan judul foto" 
                     required>
            </div>

            <!-- Deskripsi -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Deskripsi</label>
              <textarea name="description" 
                        rows="4" 
                        class="form-control" 
                        placeholder="Tambahkan deskripsi singkat"><?= esc($photo['description']) ?></textarea>
            </div>

            <!-- Kategori -->
            <div class="mb-4">
              <label class="form-label fw-semibold">Kategori</label>
              <input type="text" 
                     id="category" 
                     name="category" 
                     value="<?= esc($photo['category_name']) ?>" 
                     class="form-control" 
                     placeholder="Contoh: Alam, Hewan, Wisata">
            </div>

            <!-- Tombol Aksi -->
            <div class="d-flex justify-content-between">
              <a href="pending_items.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Batal
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
