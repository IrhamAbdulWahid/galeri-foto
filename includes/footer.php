</div>
</main>

<?php if (basename($_SERVER['SCRIPT_NAME']) === 'index.php'): ?>
<footer class="border-top py-4 mt-5 bg-light">
  <div class="container small text-muted text-center">
    <p class="mb-2">
      © <?= date('Y') ?> Galeri Foto & Cerita
    </p>
    <p class="mb-0">
      Galeri Foto & Cerita adalah platform sederhana untuk berbagi momen, foto, dan cerita secara online. 
      Website ini dibuat untuk mendukung kreativitas pengguna dalam mendokumentasikan kegiatan, perjalanan, 
      dan momen berharga. Semua foto yang diunggah akan melalui proses moderasi agar tetap aman dan nyaman 
      untuk semua pengunjung. Tujuan utama website ini adalah memberikan tempat bagi siapa pun untuk menyimpan 
      kenangan digital, berbagi inspirasi, dan menciptakan arsip cerita yang bisa diakses kapan saja.
    </p>
  </div>
</footer>
<?php endif; ?>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>

</body>
</html>
