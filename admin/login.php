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
        sleep(1);
    }
}

$title = 'Login Admin';
include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-12 col-md-5 col-lg-4">
    <div class="card shadow-sm border-0 rounded-3">
      <div class="card-body p-4">
        <h1 class="h5 fw-bold text-center mb-3 text-primary">Login Admin</h1>
        <p class="text-muted small text-center mb-4">Silakan masuk untuk mengelola galeri</p>

        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="post" novalidate autocomplete="off">
          <input type="hidden" name="csrf" value="<?= esc(csrf_token()) ?>">

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input type="password" name="password" id="password" class="form-control" required>
              <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <button class="btn btn-primary w-100 fw-semibold rounded-3">Masuk</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
  const pwd = document.getElementById('password');
  const icon = this.querySelector('i');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    icon.classList.remove('bi-eye');
    icon.classList.add('bi-eye-slash');
  } else {
    pwd.type = 'password';
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
