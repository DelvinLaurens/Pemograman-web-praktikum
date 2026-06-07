# DemiSesama

DemiSesama adalah website crowdfunding sosial yang membantu donatur menemukan dan mendukung kampanye bantuan. Website ini dapat digunakan untuk kampanye kesehatan, pendidikan, bencana alam, lingkungan, pembangunan, dan kebutuhan sosial lainnya.

Website ini dibangun menggunakan PHP dan MySQL. Sistem sudah mendukung login berdasarkan role, pengelolaan kampanye, proses donasi, upload bukti pembayaran, verifikasi donasi, serta dashboard untuk pengelola.

## Fitur Utama

- Menampilkan daftar kampanye aktif di halaman utama.
- Mencari kampanye berdasarkan kata kunci, kategori, lokasi, dan tanggal batas waktu.
- Menampilkan detail kampanye, target dana, dana terkumpul, lokasi, kategori, dan batas waktu.
- Donatur dapat melakukan donasi setelah login.
- Donatur dapat mengunggah bukti pembayaran.
- Donatur dapat melihat riwayat donasi beserta statusnya.
- Pengguna dapat mengajukan kampanye melalui halaman galang dana.
- Pengelola dapat mengelola data kampanye.
- Pengelola dapat memverifikasi atau menolak donasi.
- Pengelola dapat mengatur metode pembayaran.
- Dashboard pengelola menampilkan ringkasan kampanye dan donasi.

## Role Pengguna

### Pengunjung

Pengunjung dapat melihat halaman utama, mencari kampanye, membuka detail kampanye, dan melihat informasi umum website.

### Donatur

Donatur dapat login, melakukan donasi, mengunggah bukti pembayaran, serta melihat riwayat donasi yang pernah dibuat.

### Pengelola

Pengelola dapat login ke halaman admin untuk mengelola kampanye, melihat donasi masuk, melakukan verifikasi pembayaran, dan mengatur metode pembayaran.

## Alur Donasi

1. Donatur membuka halaman utama.
2. Donatur memilih salah satu kampanye.
3. Donatur membuka halaman detail kampanye.
4. Donatur menekan tombol donasi.
5. Jika belum login, donatur diarahkan ke halaman login.
6. Donatur mengisi nominal, metode pembayaran, dan pesan dukungan.
7. Sistem membuat data donasi dengan status `PENDING`.
8. Donatur mengunggah bukti pembayaran.
9. Pengelola memeriksa bukti pembayaran dari halaman admin.
10. Jika diterima, status donasi berubah menjadi `VERIFIED` dan dana kampanye bertambah.
11. Jika ditolak, status donasi berubah menjadi `REJECTED`.

## Struktur Folder

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

## Halaman Penting

- `index.php`: halaman utama website.
- `pages/detail.php`: detail kampanye.
- `pages/donasi.php`: form donasi.
- `pages/verif.php`: upload bukti pembayaran.
- `pages/galang-dana.php`: pengajuan kampanye baru.
- `pages/riwayat-donasi.php`: riwayat donasi donatur.
- `auth/login.php`: login donatur.
- `admin/login.php`: login pengelola.
- `admin/dashboard.php`: ringkasan data pengelola.
- `admin/kampanye.php`: pengelolaan kampanye.
- `admin/donasi.php`: verifikasi donasi.
- `admin/metode-pembayaran.php`: pengaturan metode pembayaran.

## Cara Menjalankan

1. Letakkan folder project di dalam `xampp\htdocs\`.
2. Jalankan Apache dan MySQL melalui XAMPP.
3. Import file database dari `database/demi_sesama.sql`.
4. Buka website melalui browser:

```text
http://localhost/demisesama/index.php
```

## Akun Contoh

### Donatur

```text
Email: kevin@gmail.com
Password: kevin123
```

### Pengelola

```text
Email: jere@gmail.com
Password: jeremy123
```

## Database

Database utama berada di folder `database/` dengan nama file `demi_sesama.sql`. File ini berisi struktur tabel dan data awal yang diperlukan agar website dapat langsung dijalankan setelah proses import.

## Teknologi

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP
