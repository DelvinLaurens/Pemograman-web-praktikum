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
|-- CSS/
|-- JS/
|-- assets/
|-- Database/
```

## Cara Menjalankan

1. Letakkan folder project di `xampp\htdocs\`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Import database dari `Database/demi_sesama.sql`.
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

## Development Notes / AI Context

Catatan ini dibuat agar anggota tim atau AI assistant dapat memahami konteks project tanpa membaca seluruh source code terlebih dahulu.

### Fitur Utama Saat Ini

| Fitur | Tujuan | Role | File Terkait | Status |
|---|---|---|---|---|
| Login role donatur/pengelola | Memisahkan akses user biasa dan admin/pengelola | Donatur, Pengelola | `auth/login.php`, `auth/logout.php`, `components/auth.php` | done |
| Donasi campaign | User memilih campaign, nominal, dan metode pembayaran | Donatur | `pages/donasi.php`, `components/donation_service.php`, `components/donation_helper.php` | done |
| Upload bukti pembayaran | Donatur mengirim bukti transfer setelah membuat donasi | Donatur | `pages/verif.php`, `assets/uploads/bukti-transfer/` | done |
| Verifikasi donasi | Admin menerima atau menolak pembayaran donasi | Pengelola | `admin/donasi.php`, `components/admin_service.php`, `CSS/admin.css` | done |
| Metode pembayaran | Admin mengatur QRIS, bank/VA, dan e-wallet | Pengelola | `admin/metode-pembayaran.php`, `components/admin_service.php`, `Database/demi_sesama.sql` | done |
| Kelola kampanye | Admin membuat, mengubah, dan menghapus campaign miliknya | Pengelola | `admin/kampanye.php`, `components/admin_service.php` | done |
| Riwayat donasi | Donatur melihat status donasi yang pernah dibuat | Donatur | `pages/riwayat-donasi.php` | done |

### Flow Singkat Pengguna

1. Donatur membuka `index.php` lalu memilih campaign.
2. Donatur membuka detail campaign di `pages/detail.php`.
3. Donatur klik donasi, login jika belum masuk, lalu mengisi form di `pages/donasi.php`.
4. Sistem membuat donasi berstatus `PENDING`.
5. Donatur upload bukti pembayaran di `pages/verif.php`.
6. Pengelola membuka `admin/donasi.php` untuk melihat bukti dan melakukan verifikasi.
7. Jika diterima, dana campaign bertambah. Jika ditolak, status donasi menjadi `REJECTED`.

### Fitur Yang Sedang / Akan Dikembangkan

| Fitur | Tujuan | Role | File/Folders Terkait | Progress |
|---|---|---|---|---|
| Galang dana masuk database | Form galang dana user tersimpan sebagai pengajuan campaign | Donatur/User, Pengelola | `pages/galang-dana.php`, `admin/kampanye.php`, `Database/demi_sesama.sql` | done |
| Approval campaign | Admin menerima/menolak pengajuan campaign sebelum tampil di homepage | Pengelola | `admin/`, `components/admin_service.php`, `Database/demi_sesama.sql` | done |
| Alasan penolakan donasi | Admin dapat mencatat alasan ketika menolak bukti pembayaran | Pengelola | `admin/donasi.php`, `components/admin_service.php`, `Database/demi_sesama.sql` | optional |
| Preview bukti transfer | Admin melihat bukti transfer langsung tanpa membuka tab baru | Pengelola | `admin/donasi.php`, `CSS/admin.css`, `assets/uploads/bukti-transfer/` | done |
| Pagination admin | Tabel donasi/kampanye tetap rapi saat data banyak | Pengelola | `admin/donasi.php`, `admin/kampanye.php`, `components/admin_service.php` | done |
| Dashboard chart | Dashboard menampilkan grafik ringkas donasi/campaign | Pengelola | `admin/dashboard.php`, `CSS/admin.css`, `JS/script.js` | done |
| Campaign status system | Campaign memiliki status `pending`, `approved`, `rejected`, dan `completed` | Pengelola | `admin/kampanye.php`, `pages/galang-dana.php`, `Database/demi_sesama.sql` | done |
| Dynamic progress system | Progress campaign dihitung dari target dan dana terkumpul | Donatur, Pengelola | `components/campaign_card.php`, `pages/detail.php`, `components/admin_service.php` | partially done |
| Campaign deadline system | Campaign memiliki batas waktu, sisa hari, dan penutupan otomatis | Donatur, Pengelola | `components/campaign_list.php`, `components/campaign_card.php`, `pages/detail.php` | partially done |
| Campaign update timeline | Pengelola memberi update perkembangan, foto, dan penggunaan dana | Pengelola, Donatur | `admin/`, `pages/detail.php`, `Database/demi_sesama.sql` | planned |
| Trending campaign section | Homepage menampilkan Most Funded, Urgent Campaign, dan Latest Campaign | Donatur/User | `index.php`, `components/campaign_list.php`, `CSS/home.css` | done |
| Seed database kampanye | Database contoh cukup untuk demo dan fresh import langsung menampilkan campaign | Developer | `Database/demi_sesama.sql` | done |
| Perbaikan tampilan login | Membuat halaman login lebih rapi dan siap presentasi | Donatur, Pengelola | `auth/login.php`, `CSS/login.css` | planned |

### Catatan Teknis

- File utama database adalah `Database/demi_sesama.sql`; teman satu tim cukup import file ini sekali.
- Helper path ada di `components/path_helper.php`; gunakan `url_for()` untuk link halaman dan `asset_url()` untuk CSS/gambar/upload.
- Halaman public ada di root dan folder `pages/`, halaman admin ada di `admin/`, login/logout ada di `auth/`.
- Jangan pindahkan file tanpa memperbarui link, form action, redirect, dan include.
- Folder `JS/` tetap dipakai untuk animasi homepage melalui `JS/script.js`.

### Catatan Bug / Revisi Berikutnya

- Tombol `Tolak` donasi belum menyimpan alasan penolakan.
- Tolak campaign juga belum menyimpan alasan penolakan untuk dibaca pengaju.
- Progress campaign sudah dinamis dari `dana_terkumpul` dan `target_dana`, tetapi belum realtime tanpa reload.
- Deadline campaign sudah menutup tombol donasi secara tampilan, tetapi belum memiliki auto close formal yang mengubah status database ke `completed`.
- Belum ada timeline update perkembangan campaign.
- `pages/galang-dana.php` sudah menyimpan data, tetapi masih memakai `id_penyelenggara = 1` sebagai default pengelola.
- Tampilan login masih perlu dirapikan untuk presentasi.
- Validasi upload image di form galang dana belum seketat helper upload admin.

### Planned Features & Next Development

1. **Rejected Reason System**

   Menambahkan alasan penolakan campaign/donasi.

   Rencana flow:
   - Admin mengisi alasan reject.
   - Alasan disimpan ke database.
   - User dapat melihat alasan penolakan.

2. **Campaign Completion Sync**

   Menambahkan proses formal untuk menutup campaign.

   Rencana flow:
   - Campaign menjadi `completed` saat target tercapai.
   - Campaign menjadi `completed` saat deadline habis.
   - Status database sinkron dengan tampilan detail dan card campaign.

3. **Campaign Update Timeline**

   Menambahkan fitur update perkembangan campaign.

   Rencana fitur:
   - Upload foto update.
   - Progress kegiatan.
   - Catatan penggunaan dana.
   - Timeline perkembangan campaign di halaman detail.

4. **Galang Dana Ownership**

   Merapikan flow pengajuan campaign dari halaman publik.

   Rencana flow:
   - Pengajuan tidak hardcode ke penyelenggara pertama.
   - Admin/pengelola dapat melihat sumber pengajuan.
   - Validasi upload menggunakan aturan yang konsisten.

5. **Login UI Polish**

   Merapikan tampilan login agar lebih siap demo.

   Rencana fitur:
   - Layout login lebih modern dan responsif.
   - Info akun demo ditampilkan lebih rapi.
   - State error dan role switch lebih jelas.

6. **Future Improvements**

   - Progress realtime tanpa reload jika dibutuhkan.
   - Optimasi query database.
   - Reusable component/function untuk aksi admin.
   - Responsive dashboard UI yang lebih matang.
