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

## Flowchart
### Admin Upload Foto
```mermaid
flowchart LR
A[Start] --> B[Login Admin]
B --> C[Dashboard]
C --> D[Pilih Menu Upload Foto]
D --> E[Isi Data + Pilih Kategori + Upload]
E --> F{Validasi}
F -- OK --> G[Simpan ke DB + Simpan File]
G --> H[Tampil di Website Publik]
H --> I[End]
F -- Gagal --> D
```

### User Akses Galeri
```mermaid
flowchart LR
A[Start] --> B[Buka Website]
B --> C[Pilih Kategori]
C --> D[Foto tampil]
D --> E[Klik Foto]
E --> F[Lihat Detail]
F --> G[End]
```

## ERD (Sederhana)
```mermaid
erDiagram
  users ||--o{ photos : "upload"
  categories ||--o{ photos : "memiliki"
  users {
    int id PK
    varchar username
    varchar password_hash
  }
  categories {
    int id PK
    varchar name
    varchar slug
  }
  photos {
    int id PK
    varchar title
    text description
    int category_id FK
    varchar file_path
    timestamp created_at
  }
```

> Catatan: relasi `users -> photos` tidak dipakai langsung pada skema tabel (kolom `user_id` ditiadakan agar sederhana). Bila ingin menautkan pengupload, tambahkan kolom `user_id` pada tabel `photos` + foreign key.

## Screenshot/Mockup (Deskripsi)
- **Beranda**: Banner sederhana, daftar kategori (button), grid foto terbaru.
- **Galeri**: Grid 4 kolom di desktop, 2 di mobile, pagination.
- **Detail**: Gambar besar di kiri, metadata + deskripsi di kanan.
- **Admin**: Dashboard dua kartu statistik, halaman CRUD tabel + form.

## Catatan Pengembangan
- Untuk hosting shared, set `BASE_URL` di `config.php` misalnya `'/galeri-foto'`.
- Tambah pagination di `index.php` bila koleksi besar.
- Tambah pencarian judul/desk di `galeri.php` (query LIKE).
- Pasang ukuran maksimal upload `php.ini` (`upload_max_filesize`, `post_max_size`).

## Lisensi
Bebas digunakan untuk pembelajaran/Tugas Akhir.
