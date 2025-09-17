<?php
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../includes/header.php';

$error = $success = "";

// --- ACTION FOTO ---
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = db()->prepare("UPDATE photos SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // jika kategori masih pending, approve juga
        $res = db()->query("SELECT category_id FROM photos WHERE id=$id");
        $photo = $res->fetch_assoc();
        if ($photo) {
            $cat_id = (int)$photo['category_id'];
            db()->query("UPDATE categories SET status='approved' WHERE id=$cat_id AND status='pending'");
        }

        $success = "Foto berhasil disetujui";
    } elseif ($action === 'reject') {
        $res = db()->query("SELECT * FROM photos WHERE id=$id");
        $photo = $res->fetch_assoc();

        if ($photo) {
            $cat_id = (int)$photo['category_id'];
            // hapus file fisik jika ada
            if ($photo['file_path'] && file_exists(__DIR__ . '/../uploads/' . $photo['file_path'])) {
                unlink(__DIR__ . '/../uploads/' . $photo['file_path']);
            }

            $stmt = db()->prepare("DELETE FROM photos WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // hapus kategori jika sudah tidak ada foto lain di kategori itu
            $check = db()->query("SELECT COUNT(*) c FROM photos WHERE category_id=$cat_id")->fetch_assoc();
            if ($check && $check['c'] == 0) {
                db()->query("DELETE FROM categories WHERE id=$cat_id AND status='pending'");
            }

            $success = "Foto berhasil ditolak dan dihapus";
        }
    }

    // redirect agar query string hilang
    header("Location: pending_items.php?success=" . urlencode($success));
    exit;
}

// --- Ambil data foto pending ---
$photos = [];
$res = db()->query("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM photos p 
    LEFT JOIN categories c ON c.id=p.category_id 
    WHERE p.status='pending' 
    ORDER BY p.created_at DESC
");
if ($res) $photos = $res->fetch_all(MYSQLI_ASSOC);

$title = 'Foto Pending';
$success = $_GET['success'] ?? '';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 fw-bold">Foto Menunggu Persetujuan</h2>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= esc($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:100px">Preview</th>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Slug</th>
                        <th style="width:220px" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($photos): ?>
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
                        <td class="small text-muted"><?= $p['category_slug'] ? esc($p['category_slug']) : '-' ?></td>
                        <td class="text-end">
                            <a href="?action=approve&id=<?= $p['id'] ?>" class="btn btn-sm btn-success me-1">
                                <i class="bi bi-check-circle"></i> 
                            </a>
                            <a href="?action=reject&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger me-1">
                                <i class="bi bi-x-circle"></i> 
                            </a>
                            <a href="edit_pending.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil-square"></i> 
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Tidak ada foto pending</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
