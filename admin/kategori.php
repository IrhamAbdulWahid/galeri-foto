<?php
require_once __DIR__ . '/../functions.php';
require_login();
verify_csrf();

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = slugify($name);

    // --- VALIDASI ---
    if ($name === '') {
        set_flash('error', 'Nama kategori tidak boleh kosong');
        redirect('/admin/kategori.php' . ($action === 'update' ? "?action=edit&id=$id" : ""));
    }

    // hanya huruf, angka, spasi diperbolehkan
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $name)) {
        set_flash('error', 'Nama kategori hanya boleh berisi huruf, angka, dan spasi');
        redirect('/admin/kategori.php' . ($action === 'update' ? "?action=edit&id=$id" : ""));
    }

    // Cek duplikat kategori (slug unik)
    $stmt = db()->prepare("SELECT id FROM categories WHERE slug=? AND id!=?");
    $stmt->bind_param('si', $slug, $id);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    if ($exists) {
        set_flash('error', 'Kategori dengan nama ini sudah ada');
        redirect('/admin/kategori.php' . ($action === 'update' ? "?action=edit&id=$id" : ""));
    }
    // --- END VALIDASI ---

    if ($action === 'create') {
        $stmt = db()->prepare("INSERT INTO categories(name, slug) VALUES(?, ?)");
        $stmt->bind_param('ss', $name, $slug);
        $stmt->execute();
        set_flash('ok', 'Kategori berhasil ditambahkan');
    } elseif ($action === 'update' && $id) {
        $stmt = db()->prepare("UPDATE categories SET name=?, slug=? WHERE id=?");
        $stmt->bind_param('ssi', $name, $slug, $id);
        $stmt->execute();
        set_flash('ok', 'Kategori berhasil diperbarui');
    }
    redirect('/admin/kategori.php');
}

// Handle Delete
if ($action === 'delete' && $id) {
    $stmt = db()->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    set_flash('ok', 'Kategori berhasil dihapus');
    redirect('/admin/kategori.php');
}

// Ambil semua kategori
$categories = [];
$res = db()->query("SELECT * FROM categories ORDER BY name ASC");
if ($res) $categories = $res->fetch_all(MYSQLI_ASSOC);

// Ambil data edit jika ada
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
  <!-- Form Tambah / Edit -->
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h6 mb-3 fw-bold"><?= $edit ? 'Edit' : 'Tambah' ?> Kategori</h2>

        <?php if ($m = get_flash('ok')): ?>
          <div class="alert alert-success alert-dismissible fade show small" role="alert">
            <?= esc($m) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php if ($m = get_flash('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show small" role="alert">
            <?= esc($m) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="post" action="?action=<?= $edit ? 'update&id='.(int)$edit['id'] : 'create' ?>">
          <input type="hidden" name="csrf" value="<?= esc(csrf_token()) ?>">

          <div class="mb-3">
            <label class="form-label">Nama Kategori</label>
            <input type="text" name="name" class="form-control" required value="<?= esc($edit['name'] ?? '') ?>">
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary"><?= $edit ? 'Update' : 'Tambah' ?></button>
            <?php if ($edit): ?>
              <a class="btn btn-secondary" href="kategori.php">Batal</a>
            <?php endif; ?>
            <a class="btn btn-outline-dark" href="dashboard.php">Kembali</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Tabel Kategori -->
  <div class="col-md-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h6 mb-3 fw-bold">Daftar Kategori</h2>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:5%">No</th>
                <th>Nama</th>
                <th>Slug</th>
                <th style="width:15%" class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $i => $c): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= esc($c['name']) ?></td>
                <td><span class="badge bg-secondary"><?= esc($c['slug']) ?></span></td>
                <td class="text-end">
                  <a href="?action=edit&id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="?action=delete&id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-danger btn-delete">
                    <i class="bi bi-trash"></i>
                  </a>
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
