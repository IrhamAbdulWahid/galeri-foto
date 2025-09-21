<?php
require_once "functions.php";

// --- Ambil kategori approved ---
$categories_res = db()->query("SELECT id, name FROM categories WHERE status='approved' ORDER BY name ASC");
$categories = $categories_res ? $categories_res->fetch_all(MYSQLI_ASSOC) : [];

$error = $success = "";

// --- Proses form ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $category_id = $_POST["category_id"] ?? null;
    $new_category_name = trim($_POST["new_category_name"]);

    $errors = [];

    // Validasi dasar
    if ($title === "" || $description === "") {
        $errors[] = "Judul dan deskripsi wajib diisi.";
    }
    if (!$category_id && $new_category_name === "") {
        $errors[] = "Pilih kategori atau buat kategori baru.";
    }
    if (!isset($_FILES["photo"]) || $_FILES["photo"]["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload foto gagal.";
    }

    // Validasi file
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
        $allowed_ext = ["jpg", "jpeg", "png", "webp"];
        $ext  = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
        $size = $_FILES["photo"]["size"];

        if (!in_array($ext, $allowed_ext)) {
            $errors[] = "Format file tidak valid (hanya jpg, jpeg, png, webp).";
        }
        if ($size > 5 * 1024 * 1024) {
            $errors[] = "Ukuran file maksimal 5MB.";
        }
    }

    if (empty($errors)) {
        // Jika kategori baru dipilih
        if ($category_id === "new" && $new_category_name !== "") {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $new_category_name));
            $stmt = db()->prepare("SELECT id FROM categories WHERE slug=? LIMIT 1");
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            $res = $stmt->get_result();
            $cat = $res->fetch_assoc();

            if ($cat) {
                $category_id = $cat['id'];
            } else {
                $stmt = db()->prepare("INSERT INTO categories (name, slug, status) VALUES (?, ?, 'pending')");
                $stmt->bind_param("ss", $new_category_name, $slug);
                $stmt->execute();
                $category_id = db()->insert_id;
            }
        }

        // Upload foto
        $target_dir = __DIR__ . "/uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $filename = uniqid() . "." . $ext;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $stmt = db()->prepare("INSERT INTO photos (title, description, category_id, file_path, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("ssis", $title, $description, $category_id, $filename);
            $stmt->execute();
            $success = "Foto berhasil diajukan, menunggu persetujuan admin, mohon ditung 1x24 jam.";
        } else {
            $errors[] = "Gagal menyimpan file foto";
        }
    }

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container mt-5">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="mb-0">Request Upload Foto</h4>
        </div>
        <div class="card-body">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-control" placeholder="Masukkan judul foto" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Tuliskan deskripsi foto" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select" onchange="toggleNewCategory(this.value)">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                        <option value="new">➕ Tambah Kategori Baru</option>
                    </select>
                </div>
                <div class="mb-3" id="newCategoryBox" style="display:none;">
                    <label class="form-label">Kategori Baru</label>
                    <input type="text" name="new_category_name" class="form-control" placeholder="Masukkan nama kategori baru">
                    <div class="form-text text-muted">Kategori baru harus disetujui admin sebelum tampil di publik</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Foto</label>
                    <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                    <div class="form-text">Format: JPG, JPEG, PNG, WEBP (maksimal 5MB)</div>
                </div>
                <button type="submit" class="btn btn-success w-100">Kirim</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleNewCategory(val) {
    document.getElementById('newCategoryBox').style.display = (val === 'new') ? 'block' : 'none';
}
</script>

<?php include "includes/footer.php"; ?>
