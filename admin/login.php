<?php
require_once __DIR__ . '/../functions.php';

if (is_logged_in()) redirect('/admin/dashboard.php');

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare("SELECT id, username, password_hash FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_id'] = (int)$user['id'];
        $_SESSION['admin_name'] = $user['username'];
        redirect('/admin/dashboard.php');
    } else {
        $error = 'Username atau password salah.';
    }
}

$title = 'Login Admin';
include __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-12 col-md-5 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h5 mb-3">Login Admin</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf" value="<?= esc(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100">Masuk</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
