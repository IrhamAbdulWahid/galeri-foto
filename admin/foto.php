<?php
require_once __DIR__ . '/../functions.php';
require_login();
verify_csrf();

$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

// Ambil kategori untuk select
$cats = [];
$res  = db()->query("SELECT * FROM categories ORDER BY name ASC");
if ($res) $cats = $res->fetch_all(MYSQLI_ASSOC);

/**
 * Handle upload gambar
 */
function handle_upload(array $file): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    $tmp  = $file['tmp_name'];
    $info = getimagesize($tmp);
    if (!$info) return null;

    $ext     = image_type_to_extension($info[2], false); 
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array(strtolower($ext), $allowed, true)) return null;

    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = __DIR__ . '/../uploads/' . $name;

    return move_uploaded_file($tmp, $dest) ? $name : null;
}

/**
 * Handle CREATE / UPDATE
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $cat         = $category_id > 0 ? $category_id : null;

    // --- VALIDASI INPUT ---
    if ($title === '') {
        set_flash('err', 'Judul tidak boleh kosong');
        redirect('/admin/foto.php' . ($action === 'update' ? "?action=edit&id=$id" : ""));
    }

    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $title)) {
        set_flash('err', 'Judul hanya boleh berisi huruf, angka, dan spasi');
        redirect('/admin/foto.php' . ($action === 'update' ? "?action=edit&id=$id" : ""));
    }

    if ($cat !== null) {
        $stmt = db()->prepare("SELECT id FROM categories WHERE id=?");
        $stmt->bind_param('i', $cat);
        $stmt->execute();
        $cekCat = $stmt->get_result()->fetch_assoc();
        if (!$cekCat) {
            set_flash('err', 'Kategori tidak valid.');
            redirect('/admin/foto.php' . ($action === 'update' ? "?action=edit&id=$id" : ""));
        }
    }
    // --- END VALIDASI ---

    if ($action === 'create') {
        $file_path = handle_upload($_FILES['image'] ?? []);
        if (!$file_path) {
            set_flash('err', 'Upload gagal / file tidak valid (jpg/png/webp)');
        } else {
            $stmt = db()->prepare("INSERT INTO photos(title, description, category_id, file_path, status) VALUES(?, ?, ?, ?, 'approved')");
            $stmt->bind_param('ssis', $title, $description, $cat, $file_path);
            $stmt->execute();
            set_flash('ok', 'Foto berhasil ditambahkan');
        }
        redirect('/admin/foto.php');
    }

    if ($action === 'update' && $id) {
        $file_path = null;
        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_path = handle_upload($_FILES['image']);
        }

        if ($file_path) {
            $old = db()->query("SELECT file_path FROM photos WHERE id=" . (int)$id)->fetch_assoc();
            if ($old && !empty($old['file_path'])) {
                @unlink(__DIR__ . '/../uploads/' . $old['file_path']);
            }

            $stmt = db()->prepare("UPDATE photos SET title=?, description=?, category_id=?, file_path=? WHERE id=?");
            $stmt->bind_param('ssisi', $title, $description, $cat, $file_path, $id);
        } else {
            $stmt = db()->prepare("UPDATE photos SET title=?, description=?, category_id=? WHERE id=?");
            $stmt->bind_param('ssii', $title, $description, $cat, $id);
        }

        $stmt->execute();
        set_flash('ok', 'Foto berhasil diperbarui');
        redirect('/admin/foto.php');
    }
}

/**
 * Handle DELETE
 */
if ($action === 'delete' && $id) {
    $old = db()->query("SELECT file_path FROM photos WHERE id=" . (int)$id)->fetch_assoc();
    if ($old && !empty($old['file_path'])) {
        @unlink(__DIR__ . '/../uploads/' . $old['file_path']);
    }

    $stmt = db()->prepare("DELETE FROM photos WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    set_flash('ok', 'Foto berhasil dihapus');
    redirect('/admin/foto.php');
}

// Ambil data untuk EDIT
$edit = null;
if ($action === 'edit' && $id) {
    $stmt = db()->prepare("SELECT * FROM photos WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

// Ambil semua foto
$photos = [];
$res = db()->query("
    SELECT p.*, c.name AS category_name 
    FROM photos p 
    LEFT JOIN categories c ON c.id = p.category_id 
    ORDER BY p.created_at ASC
");
if ($res) $photos = $res->fetch_all(MYSQLI_ASSOC);

$title = 'Kelola Foto';
include __DIR__ . '/../includes/header.php';
?>


<div class="row g-4">
  <!-- Form Upload/Edit -->
  <div class="col-md-5">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-light fw-bold">
        <?= $edit ? 'Edit Foto' : 'Upload Foto' ?>
      </div>
      <div class="card-body">
        <?php if ($m = get_flash('ok')): ?>
          <div class="alert alert-success alert-dismissible fade show small"><?= esc($m) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        <?php if ($m = get_flash('err')): ?>
          <div class="alert alert-danger alert-dismissible fade show small"><?= esc($m) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="?action=<?= $edit ? 'update&id='.(int)$edit['id'] : 'create' ?>">
          <input type="hidden" name="csrf" value="<?= esc(csrf_token()) ?>">

          <div class="mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="title" class="form-control form-control-sm" required value="<?= esc($edit['title'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control form-control-sm" rows="3"><?= esc($edit['description'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select form-select-sm">
              <option value="0">— Tanpa Kategori —</option>
              <?php foreach ($cats as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= isset($edit['category_id']) && (int)$edit['category_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                  <?= esc($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Gambar <?= $edit ? '(kosongkan jika tidak diganti)' : '' ?></label>
            <input type="file" name="image" class="form-control form-control-sm" <?= $edit ? '' : 'required' ?> accept=".jpg,.jpeg,.png,.webp">
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-primary"><?= $edit ? 'Update' : 'Upload' ?></button>
            <?php if ($edit): ?>
              <a class="btn btn-sm btn-secondary" href="foto.php">Batal</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Daftar Foto -->
  <div class="col-md-7">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-light fw-bold">Daftar Foto</div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Foto</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($photos as $i => $p): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td style="width:70px">
                    <img src="<?= BASE_URL ?>/uploads/<?= esc($p['file_path']) ?>" class="img-fluid rounded" alt="">
                  </td>
                  <td><?= esc($p['title']) ?></td>
                  <td><span class="badge bg-secondary"><?= esc($p['category_name'] ?? '—') ?></span></td>
                  <td>
                    <?php if ($p['status'] === 'approved'): ?>
                      <span class="badge bg-success">Disetujui</span>
                    <?php elseif ($p['status'] === 'pending'): ?>
                      <span class="badge bg-warning text-dark">Tertunda</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Ditolak</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-muted small"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-secondary" href="?action=edit&id=<?= (int)$p['id'] ?>"><i class="bi bi-pencil"></i></a>
                    <a class="btn btn-sm btn-outline-danger btn-delete" href="?action=delete&id=<?= (int)$p['id'] ?>"><i class="bi bi-trash"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($photos)): ?>
                <tr>
                  <td colspan="7" class="text-center text-muted">Belum ada foto.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
