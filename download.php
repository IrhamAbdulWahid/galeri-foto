<?php 
// Panggil koneksi dan fungsi
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/vendor/autoload.php'; // Autoload Dompdf (jika pakai Composer)

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Ambil ID foto dari URL (contoh: download.php?id=15)
$id = (int)($_GET['id'] ?? 0);

// 2. Query database untuk ambil data foto + kategori
$stmt = db()->prepare("SELECT p.*, c.name AS category_name 
                       FROM photos p 
                       LEFT JOIN categories c ON c.id=p.category_id 
                       WHERE p.id=? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$photo = $stmt->get_result()->fetch_assoc();

// 3. Jika data tidak ada, hentikan proses
if (!$photo) {
    die("Foto tidak ditemukan");
}

// 4. Ambil gambar dan konversi ke Base64
//    Base64 = isi file gambar dikonversi jadi teks panjang
//    Keuntungan: gambar langsung menempel di PDF (pasti tampil)
$imagePath = __DIR__ . '/uploads/' . $photo['file_path']; // path file asli di server
$imageTag = ''; // default kosong

if (file_exists($imagePath)) {
    $imageData = base64_encode(file_get_contents($imagePath)); // isi file → base64
    $mimeType  = mime_content_type($imagePath); // ambil jenis file (jpg/png)
    
    // tag <img> untuk ditaruh di HTML (pakai data URI base64)
    $imageTag  = '<img src="data:' . $mimeType . ';base64,' . $imageData . '" 
                   style="max-width:300px; margin:10px auto; display:block;">';
}

// 5. Susun HTML yang akan dirender ke PDF
$html = '
    <h2 style="text-align:center;">' . htmlspecialchars($photo['title']) . '</h2>
    <p style="text-align:center;"><em>' . htmlspecialchars($photo['category_name']) . '</em></p>
    <div style="text-align:center;">
        ' . $imageTag . '
    </div>
    <p style="text-align:justify; font-size:14px; line-height:1.6;">
        ' . nl2br(htmlspecialchars($photo['description'])) . '
    </p>
';

// 6. Konfigurasi Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true); // izinkan akses resource eksternal jika ada
$dompdf = new Dompdf($options);

// 7. Masukkan HTML ke Dompdf
$dompdf->loadHtml($html);

// 8. Atur ukuran kertas A4 (portrait)
$dompdf->setPaper('A4', 'portrait');

// 9. Render HTML → PDF
$dompdf->render();

// 10. Download PDF (Attachment => true = otomatis download, false = tampil di browser)
$dompdf->stream($photo['title'] . ".pdf", ["Attachment" => true]);
