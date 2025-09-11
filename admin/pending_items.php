<?php
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../includes/header.php';

// --- FOTO: Approve / Reject ---
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = db()->prepare("UPDATE photos SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($action === 'reject') {
        $res = db()->query("SELECT file_path FROM photos WHERE id=$id");
        $photo = $res->fetch_assoc();
        if ($photo && file_exists(__DIR__ . '/../uploads/' . $photo['file_path'])) {
            unlink(__DIR__ . '/../uploads/' . $photo['file_path']);
        }
        $stmt = db()->prepare("DELETE FROM photos WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    redirect('/admin/pending_items.php');
}

// Ambil data foto pending
$photos = [];
$res = db()->query("
    SELECT p.*, c.name AS category_name 
    FROM photos p 
    LEFT JOIN categories c ON c.id=p.category_id 
    WHERE p.status='pending' 
    ORDER BY p.created_at DESC
");
if ($res) $photos = $res->fetch_all(MYSQLI_ASSOC);

$title = 'Foto Pending';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h5 fw-bold">Foto Menunggu Persetujuan</h2>
  <a href="dashboard.php" class="btn btn-outline-dark btn-sm">Kembali ke Dashboard</a>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:100px">Preview</th>
            <th>Judul</th>
            <th>Deskripsi</th>
            <th>Kategori</th>
            <th style="width:160px" class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($photos as $p): ?>
          <tr>
            <td>
              <img src="../uploads/<?= esc($p['file_path']) ?>" class="img-thumbnail" style="max-width:90px">
            </td>
            <td><?= esc($p['title']) ?></td>
            <td class="small text-muted"><?= esc($p['description']) ?></td>
            <td>
              <?php if ($p['category_name']): ?>
                <span class="badge bg-secondary"><?= esc($p['category_name']) ?></span>
              <?php else: ?>
                <span class="text-muted">Tanpa Kategori</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <a href="?action=approve&id=<?= $p['id'] ?>" class="btn btn-sm btn-success">
                Setuju
              </a>
              <a href="?action=reject&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger btn-delete"
                 onclick="return confirm('Yakin tolak dan hapus foto ini?')">
                Tolak
              </a>
            </td>
          </tr>
          <?php endforeach; ?>

          <?php if (empty($photos)): ?>
          <tr>
            <td colspan="5" class="text-center text-muted">Tidak ada foto pending.</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
