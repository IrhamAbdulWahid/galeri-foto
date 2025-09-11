<?php require_once __DIR__ . '/../functions.php'; ?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Galeri Foto'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
  </head>
  <body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>/index.php">Galeri Foto & Cerita</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Beranda</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/galeri.php">Galeri Publik</a></li>
          
          <?php if (!is_logged_in()): ?>
          <!-- Hanya tampil kalau belum login (publik) -->
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/request.php">Request Foto</a></li>
          <?php endif; ?>
        </ul>
        <div class="d-flex gap-2">
          <?php if (is_logged_in()): ?>
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-outline-primary btn-sm">Dashboard</a>
            <a href="<?= BASE_URL ?>/admin/logout.php" class="btn btn-danger btn-sm">Logout</a>
          <?php else: ?>
            <a href="<?= BASE_URL ?>/admin/login.php" class="btn btn-primary btn-sm">Login Admin</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
  <main class="py-4">
    <div class="container">
