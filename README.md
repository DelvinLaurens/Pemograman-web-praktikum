# DemiSesama

DemiSesama adalah website crowdfunding sosial yang menjadi jembatan kebaikan antara donatur dan penggalang dana. Platform ini membantu masyarakat menemukan dan mendukung kampanye sosial seperti bantuan bencana alam, pendidikan, kesehatan, dan lingkungan.

## Deskripsi Project

Project ini dibuat untuk Mini Project Praktikum Pemrograman Web. Versi Mini Project 2 sudah memakai PHP, MySQL, session login, komponen reusable, donasi, upload bukti transfer, dashboard pengelola, verifikasi donasi, dan pengaturan metode pembayaran.

## Struktur File

```text
demisesama/
|-- index.php
|-- admin/
|   |-- dashboard.php
|   |-- kampanye.php
|   |-- donasi.php
|   |-- metode-pembayaran.php
|   |-- login.php
|-- auth/
|   |-- login.php
|   |-- logout.php
|-- pages/
|   |-- detail.php
|   |-- donasi.php
|   |-- verif.php
|   |-- galang-dana.php
|   |-- riwayat-donasi.php
|-- components/
|-- css/
|-- js/
|-- assets/
|-- database/
```

## Cara Menjalankan

1. Letakkan folder project di `xampp\htdocs\`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Import database dari `database/demi_sesama.sql`.
4. Buka `http://localhost/demisesama/index.php`.

## Akun Contoh

- Donatur: `kevin@gmail.com` / `kevin123`
- Pengelola: `jere@gmail.com` / `jeremy123`

## Halaman Mini Project 2

- `auth/login.php`: login donatur dan pengelola.
- `admin/dashboard.php`: dashboard pengelola.
- `admin/kampanye.php`: CRUD kampanye milik pengelola.
- `admin/donasi.php`: verifikasi atau tolak donasi.
- `admin/metode-pembayaran.php`: pengaturan metode pembayaran.
- `pages/riwayat-donasi.php`: riwayat donasi donatur yang login.
