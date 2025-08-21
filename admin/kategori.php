<?php
require_once __DIR__ . '/../functions.php';
require_login();
verify_csrf();

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = slugify($name);
    if ($action === 'create') {
        $stmt = db()->prepare("INSERT INTO categories(name, slug) VALUES(?, ?)");
        $stmt->bind_param('ss', $name, $slug);
        $stmt->execute();
        set_flash('ok', 'Kategori ditambahkan.');
        redirect('/admin/kategori.php');
    } elseif ($action === 'update' && $id) {
        $stmt = db()->prepare("UPDATE categories SET name=?, slug=? WHERE id=?");
        $stmt->bind_param('ssi', $name, $slug, $id);
        $stmt->execute();
        set_flash('ok', 'Kategori diperbarui.');
        redirect('/admin/kategori.php');
    }
}

if ($action === 'delete' && $id) {
    $stmt = db()->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    set_flash('ok', 'Kategori dihapus.');
    redirect('/admin/kategori.php');
}

$categories = [];
$res = db()->query("SELECT * FROM categories ORDER BY name ASC");
if ($res) $categories = $res->fetch_all(MYSQLI_ASSOC);

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = db()->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

$title = 'Kelola Kategori';
include __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
  <div class="col-md-5">
    <div class="card">
      <div class="card-body">
        <h2 class="h6 mb-3"><?= $edit ? 'Edit' : 'Tambah' ?> Kategori</h2>
        <?php if ($m = get_flash('ok')): ?><div class="alert alert-success"><?= esc($m) ?></div><?php endif; ?>
        <form method="post" action="?action=<?= $edit ? 'update&id='.(int)$edit['id'] : 'create' ?>">
          <input type="hidden" name="csrf" value="<?= esc(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">Nama Kategori</label>
            <input type="text" name="name" class="form-control" required value="<?= esc($edit['name'] ?? '') ?>">
          </div>
          <button class="btn btn-primary"><?= $edit ? 'Update' : 'Tambah' ?></button>
          <?php if ($edit): ?>
            <a class="btn btn-secondary" href="kategori.php">Batal</a>
          <?php endif; ?>

          <!-- Tombol kembali selalu muncul -->
          <a class="btn btn-outline-dark" href="dashboard.php">Kembali</a>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card">
      <div class="card-body">
        <h2 class="h6 mb-3">Daftar Kategori</h2>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead><tr><th>#</th><th>Nama</th><th>Slug</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($categories as $i=>$c): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= esc($c['name']) ?></td>
                <td class="text-muted"><?= esc($c['slug']) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary" href="?action=edit&id=<?= (int)$c['id'] ?>">Edit</a>
                  <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$c['id'] ?>" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($categories)): ?>
                <tr><td colspan="4" class="text-center text-muted">Belum ada kategori.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
