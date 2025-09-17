<?php
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../includes/header.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}
$id = (int)$_GET['id'];

// Ambil data foto + kategori
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

if (!$photo) die("Foto tidak ditemukan atau sudah di-approve");

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);

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

<div class="container my-4">
    <h2 class="h4 fw-bold mb-3">Edit Foto Pending</h2>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3 text-center">
                    <img src="../uploads/<?= esc($photo['file_path']) ?>" class="img-thumbnail" style="max-width:200px">
                </div>

                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" value="<?= esc($photo['title']) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="4" class="form-control"><?= esc($photo['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" id="category" name="category" value="<?= esc($photo['category_name']) ?>" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="pending_items.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
