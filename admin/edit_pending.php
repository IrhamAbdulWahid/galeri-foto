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

// --- Ambil semua kategori untuk dropdown ---
$catRes = db()->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catRes ? $catRes->fetch_all(MYSQLI_ASSOC) : [];

// --- Proses update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category    = trim($_POST['category']);

    // Cek apakah kategori sudah ada
    $stmt = db()->prepare("SELECT id FROM categories WHERE name=? LIMIT 1");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $res = $stmt->get_result();
    $catRow = $res->fetch_assoc();

    if ($catRow) {
        // Kalau kategori sudah ada → pakai ID yang ada
        $categoryId = $catRow['id'];
    } else {
        // Kalau belum ada → buat kategori baru
        $slug = slugify($category);
        $stmt = db()->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $category, $slug);
        $stmt->execute();
        $categoryId = db()->insert_id;
    }

    // Update photo dengan kategori baru
    $stmt = db()->prepare("UPDATE photos SET title=?, description=?, category_id=? WHERE id=?");
    $stmt->bind_param("ssii", $title, $description, $categoryId, $id);
    $stmt->execute();

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
              <select name="category_select" id="category_select" class="form-select mb-2">
                <option value="">-- Pilih kategori --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= esc($cat['name']) ?>" 
                    <?= ($photo['category_name'] === $cat['name']) ? 'selected' : '' ?>>
                    <?= esc($cat['name']) ?>
                  </option>
                <?php endforeach; ?>
                <option value="__new__">➕  Tambah kategori baru</option>
              </select>

              <!-- Input manual kategori baru -->
              <input type="text" 
                     id="category_input" 
                     name="category" 
                     class="form-control" 
                     placeholder="Tulis kategori baru">
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

<script>
  const select = document.getElementById('category_select');
  const input  = document.getElementById('category_input');

  function toggleInput() {
    if (select.value === '__new__') {
      input.style.display = 'block';
      input.required = true;
      input.value = '';
    } else if (select.value !== '') {
      input.style.display = 'none';
      input.required = false;
      input.value = select.value; // isi otomatis dengan pilihan dropdown
    } else {
      input.style.display = 'none';
      input.required = false;
      input.value = '';
    }
  }

  // jalankan saat halaman pertama kali load
  toggleInput();

  // jalankan saat dropdown berubah
  select.addEventListener('change', toggleInput);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
