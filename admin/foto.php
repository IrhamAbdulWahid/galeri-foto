<?php
require_once __DIR__ . '/../functions.php';
require_login();
verify_csrf();

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

// Fetch categories for select
$cats = [];
$res = db()->query("SELECT * FROM categories ORDER BY name ASC");
if ($res) $cats = $res->fetch_all(MYSQLI_ASSOC);

function handle_upload(array $file) : ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $tmp = $file['tmp_name'];
    $info = getimagesize($tmp);
    if (!$info) return null;
    $ext = image_type_to_extension($info[2], false); // jpg, png, webp, etc.
    $allowed = ['jpg','jpeg','png','webp'];
    if (!in_array(strtolower($ext), $allowed, true)) return null;
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = __DIR__ . '/../uploads/' . $name;
    if (move_uploaded_file($tmp, $dest)) return $name;
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $cat_id      = $category_id ?: null; // ✅ penting: harus variabel

    if ($action === 'create') {
        $file_path = handle_upload($_FILES['image'] ?? []);
        if (!$file_path) {
            set_flash('err', 'Upload gagal / file tidak valid (jpg/png/webp).');
        } else {
            $stmt = db()->prepare("INSERT INTO photos(title, description, category_id, file_path) VALUES(?, ?, ?, ?)");
            $stmt->bind_param('ssis', $title, $description, $cat_id, $file_path);
            $stmt->execute();
            $stmt->close();
            set_flash('ok', 'Foto ditambahkan.');
        }
        redirect('/admin/foto.php');

    } elseif ($action === 'update' && $id) {
        $file_path = null;
        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_path = handle_upload($_FILES['image']);
        }

        if ($file_path) {
            // hapus file lama
            $old = db()->query("SELECT file_path FROM photos WHERE id=".(int)$id)->fetch_assoc();
            if ($old && !empty($old['file_path'])) {
                @unlink(__DIR__ . '/../uploads/' . $old['file_path']);
            }

            $stmt = db()->prepare("UPDATE photos SET title=?, description=?, category_id=?, file_path=? WHERE id=?");
            $stmt->bind_param('ssisi', $title, $description, $cat_id, $file_path, $id);
        } else {
            $stmt = db()->prepare("UPDATE photos SET title=?, description=?, category_id=? WHERE id=?");
            $stmt->bind_param('ssii', $title, $description, $cat_id, $id);
        }
        $stmt->execute();
        $stmt->close();
        set_flash('ok', 'Foto diperbarui.');
        redirect('/admin/foto.php');
    }
}

if ($action === 'delete' && $id) {
    $old = db()->query("SELECT file_path FROM photos WHERE id=".(int)$id)->fetch_assoc();
    if ($old && !empty($old['file_path'])) {
        @unlink(__DIR__ . '/../uploads/' . $old['file_path']);
    }
    $stmt = db()->prepare("DELETE FROM photos WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    set_flash('ok', 'Foto dihapus.');
    redirect('/admin/foto.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = db()->prepare("SELECT * FROM photos WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$photos = [];
$res = db()->query("SELECT p.*, c.name AS category_name 
                    FROM photos p 
                    LEFT JOIN categories c ON c.id=p.category_id 
                    ORDER BY p.created_at DESC");
if ($res) $photos = $res->fetch_all(MYSQLI_ASSOC);

$title = 'Kelola Foto';
include __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
  <div class="col-md-5">
    <div class="card">
      <div class="card-body">
        <h2 class="h6 mb-3"><?= $edit ? 'Edit' : 'Upload' ?> Foto</h2>
        <?php if ($m = get_flash('ok')): ?><div class="alert alert-success"><?= esc($m) ?></div><?php endif; ?>
        <?php if ($m = get_flash('err')): ?><div class="alert alert-danger"><?= esc($m) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" action="?action=<?= $edit ? 'update&id='.(int)$edit['id'] : 'create' ?>">
          <input type="hidden" name="csrf" value="<?= esc(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="title" class="form-control" required value="<?= esc($edit['title'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="3"><?= esc($edit['description'] ?? '') ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select">
              <option value="0">— Tanpa Kategori —</option>
              <?php foreach ($cats as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= isset($edit['category_id']) && (int)$edit['category_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                  <?= esc($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Gambar <?= $edit ? '(biarkan kosong jika tidak ganti)' : '' ?></label>
            <input type="file" name="image" class="form-control" <?= $edit ? '' : 'required' ?> accept=".jpg,.jpeg,.png,.webp">
          </div>
          <button class="btn btn-primary"><?= $edit ? 'Update' : 'Upload' ?></button>
          <a class="btn btn-secondary" href="foto.php">Kembali</a>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card">
      <div class="card-body">
        <h2 class="h6 mb-3">Daftar Foto</h2>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>#</th><th>Thumbnail</th><th>Judul</th><th>Kategori</th><th>Tanggal</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($photos as $i=>$p): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td style="width:90px"><img src="<?= BASE_URL ?>/uploads/<?= esc($p['file_path']) ?>" class="img-thumbnail" alt=""></td>
                <td><?= esc($p['title']) ?></td>
                <td class="text-muted"><?= esc($p['category_name'] ?? '—') ?></td>
                <td class="text-muted small"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary" href="?action=edit&id=<?= (int)$p['id'] ?>">Edit</a>
                  <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$p['id'] ?>" onclick="return confirm('Hapus foto ini?')">Hapus</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($photos)): ?>
                <tr><td colspan="6" class="text-center text-muted">Belum ada foto.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
