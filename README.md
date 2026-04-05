# DemiSesama 
DemiSesama adalah website crowdfunding sosial yang menjadi jembatan kebaikan antara donatur dan penggalang dana. Platform ini membantu masyarakat menemukan dan mendukung kampanye sosial seperti bantuan bencana alam, pendidikan, kesehatan, dan pembangunan fasilitas umum.

---

## Deskripsi Project
Project ini dibuat sebagai **Mini Project #1** pada mata kuliah Praktikum Pemrograman Web 2025/2026 Genap. Website bersifat **statis** dan dibangun menggunakan HTML dan CSS murni tanpa framework apapun.

---
##  Struktur File
```
DemiSesama/
│
├── index.html          # Halaman Utama (daftar kampanye & pencarian)
├── detail.html         # Halaman Detail Kampanye
├── donasi.html         # Halaman Formulir Donasi
├── galang-dana.html    # Halaman Pengajuan Galang Dana
├── login.html          # Halaman Login
│
├── CSS/
│   ├── global.css      # Styling global (header, footer, variabel warna)
│   ├── home.css        # Styling halaman utama
│   ├── styledetail.css # Styling halaman detail kampanye
│   ├── form.css        # Styling formulir donasi & galang dana
│   └── login.css       # Styling halaman login
│
└── Asset/
    ├── tangan2 tnpa bg.png     # Logo website
    ├── banjir melawi.jpg       # Foto kampanye banjir Melawi
    ├── gempa manado.jpg        # Foto kampanye gempa Manado
    ├── puting beliung kupang.jpeg  # Foto kampanye puting beliung Kupang
    ├── banjir bali.jpg         # Foto kampanye banjir Bali
    ├── bantuan penddikan desa.jpg  # Foto kampanye pendidikan desa
    └── reboisasi hutan.jpg     # Foto kampanye reboisasi hutan
```

---

##  Halaman Website

### 1. Halaman Utama (`index.html`)
- Menampilkan daftar 6 kampanye donasi yang sedang berjalan
- Setiap kampanye menampilkan: poster, judul, penyelenggara, target dana, dana terkumpul, progress bar, dan sisa waktu
- Terdapat search bar dengan fitur filter (kategori, lokasi, rentang target dana)
- Setiap kampanye memiliki tautan menuju halaman detail

### 2. Halaman Detail (`detail.html`)
- Menampilkan informasi lengkap kampanye dalam layout 2 kolom
- Kolom kiri: gambar, kategori, lokasi, judul, penyelenggara, dan deskripsi kampanye
- Kolom kanan: progress bar, dana terkumpul, sisa waktu, tombol donasi, dan metode pembayaran

### 3. Halaman Donasi (`donasi.html`)
- Form donasi berisi: nama lengkap, email, nominal donasi, metode pembayaran, pesan dukungan, dan upload bukti transfer
- Menampilkan pesan sukses setelah form disubmit

### 4. Halaman Galang Dana (`galang-dana.html`)
- Form pengajuan kampanye baru berisi: judul, kategori, target dana, lokasi, cerita/alasan, dan foto kampanye
- Menampilkan pesan konfirmasi setelah pengajuan berhasil

### 5. Halaman Login (`login.html`)
- Form login dengan field email/username dan password
- Mendukung dua jenis pengguna: Donatur dan Pengelola Kampanye

---

### Palet Warna

```css
--primary      : #1E3A8A  /* Biru Navy — warna utama */
--primary-hover: #3B82F6  /* Biru terang — hover */
--button       : #F59E0B  /* Oranye — tombol & aksen */
--button-hover : #D97706  /* Oranye gelap — hover tombol */
--bg-body      : #F3F4F6  /* Abu-abu terang — background */
--main-text    : #1F2937  /* Abu-abu gelap — teks utama */
--text-desc    : #6B7280  /* Abu-abu pudar — deskripsi */
--danger       : #DC2626  /* Merah — sisa waktu */
```

---
## 📌 Catatan

- Website bersifat statis — pencarian dan filter belum berfungsi secara dinamis
- Form donasi dan login belum terhubung ke backend
- Seluruh data yang ditampilkan merupakan data dummy
---
## !Disclaimer
- di file ini ada folder js dan php itu digunakan untuk belajar jadi belum ada push nya hehe. folder itu digunakan untuk ngulik ngulik aja