# Website Galeri Foto — PHP Native + MySQL

## Deskripsi
Aplikasi galeri foto & Cerita:
- **Admin**: login, kelola kategori, kelola foto (upload/edit/hapus), Menunggu Persetujuan.
- **User (Publik)**: lihat beranda, galeri (filter kategori), detail foto, download foto, request foto (dengan menunggu persetujuan dari admin).

Stack: PHP Native, MySQL, Bootstrap 5, js, css.

## Struktur Folder
```
/galeri-foto
├── index.php
├── galeri.php
├── detail.php
├── config.php
├── functions.php
├── request.php
├── download.php
├── composer.json
├── composer.lock
├── /includes
│   ├── header.php
│   └── footer.php
├── /admin
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── kategori.php
│   ├── foto.php
│   ├── pending_items.php
│   └── seed_admin.php
├── /uploads
└── /assets
    ├── /css/style.css
    └── /js/app.js
├── /vendor     #fitur untuk mendowload berupa pdf
```
## Keamanan Singkat
- Password memakai `password_hash()` + `password_verify()` (bcrypt) md5.
- Query menggunakan prepared statements (menghindari SQL Injection).
- Form dilengkapi **CSRF token** dasar.
- Validasi MIME dan ekstensi saat upload gambar (jpg/png/webp).
- MIME type adalah tipe asli file yang dibaca dari header file, bukan cuma dari namanya.
- MIME itu singkatan dari: Multipurpose Internet Mail Extensions

## Flowchart berada di flow web galeri.jpg

## Lisensi
Bebas digunakan untuk pembelajaran/Tugas Akhir.
