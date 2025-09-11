<?php
require_once "functions.php";

// Ambil semua kategori
$categories_res = db()->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $categories_res->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $category_id = $_POST["category_id"] ?? null;
    $new_category_name = trim($_POST["new_category_name"]);

    $errors = [];

    // --- VALIDASI ---
    if ($title === '') {
        $errors[] = "Judul tidak boleh kosong";
    } elseif (!preg_match('/^[a-zA-Z0-9\s\.\,\!\?\-]+$/u', $title)) {
        $errors[] = "Judul hanya boleh huruf, angka, spasi, dan tanda baca dasar";
    }

    if ($description === '') {
        $errors[] = "Deskripsi tidak boleh kosong";
    }

    if ($new_category_name !== '') {
        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $new_category_name)) {
            $errors[] = "Kategori baru hanya boleh huruf, angka, dan spasi";
        }
    }

    // Validasi file upload
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
    foreach ($_FILES["images"]["name"] as $key => $name) {
        if ($_FILES["images"]["error"][$key] !== UPLOAD_ERR_OK) {
            $errors[] = "Upload gagal untuk file $name";
            continue;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $size = $_FILES["images"]["size"][$key];

        if (!in_array($ext, $allowed_ext)) {
            $errors[] = "File $name memiliki format tidak valid";
        }
        if ($size > 5 * 1024 * 1024) { // 5 MB
            $errors[] = "File $name terlalu besar (maksimal 5MB)";
        }
    }

    if (empty($errors)) {
        // --- Buat folder upload kalau belum ada ---
        $target_dir = __DIR__ . "/uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        // --- Jika user isi kategori baru ---
        if ($new_category_name) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $new_category_name));

            $stmt = db()->prepare("SELECT id FROM categories WHERE slug=? LIMIT 1");
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            $res = $stmt->get_result();
            $cat = $res->fetch_assoc();

            if ($cat) {
                $category_id = $cat['id'];
            } else {
                $stmt = db()->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                $stmt->bind_param("ss", $new_category_name, $slug);
                $stmt->execute();
                $category_id = db()->insert_id;
            }
        }

        // --- Upload file foto ---
        $uploaded_files = [];
        foreach ($_FILES["images"]["name"] as $key => $name) {
            $tmp_name = $_FILES["images"]["tmp_name"][$key];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $filename = uniqid() . "." . $ext;
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $stmt = db()->prepare("INSERT INTO photos (title, description, category_id, file_path, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->bind_param("ssis", $title, $description, $category_id, $filename);
                $stmt->execute();
                $uploaded_files[] = $filename;
            }
        }

        if (!empty($uploaded_files)) {
            $alert = "<div class='alert alert-success shadow-sm'>Request berhasil dikirim, menunggu persetujuan admin</div>";
        } else {
            $alert = "<div class='alert alert-danger shadow-sm'>Gagal upload foto</div>";
        }
    } else {
        $alert = "<div class='alert alert-danger shadow-sm'><ul><li>" . implode("</li><li>", $errors) . "</li></ul></div>";
    }
}
?>


<?php include __DIR__ . '/includes/header.php'; ?>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">
          <h3 class="mb-3 fw-bold text-center text-primary">Request Foto Baru</h3>
          <p class="text-muted text-center mb-4">
            Pilih kategori yang sesuai, atau buat kategori baru jika belum ada.
          </p>

          <?php if (!empty($alert)) echo $alert; ?>

          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label fw-semibold">Judul</label>
              <input type="text" name="title" class="form-control rounded-3" placeholder="Judul Foto" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Deskripsi</label>
              <textarea name="description" rows="3" class="form-control rounded-3" placeholder="Deskripsi" required></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Kategori (Pilih yang sudah ada)</label>
              <select name="category_id" class="form-select rounded-3">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Atau Tambah Kategori Baru</label>
              <input type="text" name="new_category_name" class="form-control rounded-3" placeholder="Kategori Baru (opsional)">
              <div class="form-text text-muted">Isi hanya jika kategori belum ada di atas</div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Upload Foto</label>
              <input type="file" name="images[]" class="form-control rounded-3" multiple required>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary rounded-3 fw-semibold">
                 Kirim
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
