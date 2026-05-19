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

## Development Notes / AI Context

Catatan ini dibuat agar anggota tim atau AI assistant dapat memahami konteks project tanpa membaca seluruh source code terlebih dahulu.

### Fitur Utama Saat Ini

| Fitur | Tujuan | Role | File Terkait | Status |
|---|---|---|---|---|
| Login role donatur/pengelola | Memisahkan akses user biasa dan admin/pengelola | Donatur, Pengelola | `auth/login.php`, `auth/logout.php`, `components/auth.php` | done |
| Donasi campaign | User memilih campaign, nominal, dan metode pembayaran | Donatur | `pages/donasi.php`, `components/donation_service.php`, `components/donation_helper.php` | done |
| Upload bukti pembayaran | Donatur mengirim bukti transfer setelah membuat donasi | Donatur | `pages/verif.php`, `assets/uploads/bukti-transfer/` | done |
| Verifikasi donasi | Admin menerima atau menolak pembayaran donasi | Pengelola | `admin/donasi.php`, `components/admin_service.php`, `css/admin.css` | done |
| Metode pembayaran | Admin mengatur QRIS, bank/VA, dan e-wallet | Pengelola | `admin/metode-pembayaran.php`, `components/admin_service.php`, `database/demi_sesama.sql` | done |
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
| Galang dana masuk database | Form galang dana user tersimpan sebagai pengajuan campaign | Donatur/User, Pengelola | `pages/galang-dana.php`, `admin/kampanye.php`, `database/demi_sesama.sql` | done |
| Approval campaign | Admin menerima/menolak pengajuan campaign sebelum tampil di homepage | Pengelola | `admin/`, `components/admin_service.php`, `database/demi_sesama.sql` | done |
| Alasan penolakan donasi | Admin dapat mencatat alasan ketika menolak bukti pembayaran | Pengelola | `admin/donasi.php`, `components/admin_service.php`, `database/demi_sesama.sql` | optional |
| Preview bukti transfer | Admin melihat bukti transfer langsung tanpa membuka tab baru | Pengelola | `admin/donasi.php`, `css/admin.css`, `assets/uploads/bukti-transfer/` | planned |
| Pagination admin | Tabel donasi/kampanye tetap rapi saat data banyak | Pengelola | `admin/donasi.php`, `admin/kampanye.php`, `components/admin_service.php` | planned |
| Dashboard chart | Dashboard menampilkan grafik ringkas donasi/campaign | Pengelola | `admin/dashboard.php`, `css/admin.css`, `js/script.js` | planned |
| Campaign status system | Campaign memiliki status `pending`, `approved`, `rejected`, dan `completed` | Pengelola | `admin/kampanye.php`, `pages/galang-dana.php`, `database/demi_sesama.sql` | planned |
| Dynamic progress system | Progress campaign dihitung dari target dan dana terkumpul | Donatur, Pengelola | `components/campaign_card.php`, `pages/detail.php`, `components/admin_service.php` | partially done |
| Campaign deadline system | Campaign memiliki batas waktu, sisa hari, dan penutupan otomatis | Donatur, Pengelola | `components/campaign_list.php`, `components/campaign_card.php`, `pages/detail.php` | partially done |
| Campaign update timeline | Pengelola memberi update perkembangan, foto, dan penggunaan dana | Pengelola, Donatur | `admin/`, `pages/detail.php`, `database/demi_sesama.sql` | planned |
| Trending campaign section | Homepage menampilkan Most Funded, Urgent Campaign, dan Latest Campaign | Donatur/User | `index.php`, `components/campaign_list.php`, `css/home.css` | planned |

### Catatan Teknis

- File utama database adalah `database/demi_sesama.sql`; teman satu tim cukup import file ini sekali.
- Helper path ada di `components/path_helper.php`; gunakan `url_for()` untuk link halaman dan `asset_url()` untuk CSS/gambar/upload.
- Halaman public ada di root dan folder `pages/`, halaman admin ada di `admin/`, login/logout ada di `auth/`.
- Jangan pindahkan file tanpa memperbarui link, form action, redirect, dan include.
- Folder `js/` tetap dipakai untuk animasi homepage melalui `js/script.js`.

### Catatan Bug / Revisi Berikutnya

- `pages/galang-dana.php` masih berupa form tampilan dan belum menyimpan data ke database. (done)
- Belum ada approval campaign untuk pengajuan dari user. (done)
- Tombol `Tolak` donasi belum menyimpan alasan penolakan.
- Bukti transfer admin masih dibuka lewat link, belum preview modal.
- Tabel admin belum memakai pagination.
- Tabel `kampanye` belum memiliki kolom status campaign seperti `pending`, `approved`, `rejected`, dan `completed`. (done)
- Progress campaign sudah dinamis dari `dana_terkumpul` dan `target_dana`, tetapi belum realtime tanpa reload.
- Deadline campaign sudah memakai `batas_waktu` dan sisa hari, tetapi belum memiliki auto close formal ke status `completed`.
- Dashboard admin belum memiliki grafik Chart.js.
- Homepage belum memiliki section trending campaign.
- Belum ada timeline update perkembangan campaign.

### Planned Features & Next Development

1. **Campaign Status System**

   Menambahkan sistem status campaign agar alur approval lebih jelas.

   Status yang direncanakan:
   - `pending`
   - `approved`
   - `rejected`
   - `completed`

   Catatan:
   - Campaign baru otomatis berstatus `pending`.
   - Admin dapat approve/reject campaign.
   - Campaign `rejected` tidak tampil di homepage.
   - Campaign `completed` saat target tercapai atau deadline habis.

2. **Dynamic Progress System**

   Progress campaign sudah berjalan sebagian menggunakan `dana_terkumpul` dan `target_dana`.

   Pengembangan berikutnya:
   - Progress bar otomatis lebih konsisten di semua halaman.
   - Persentase progress otomatis.
   - Total donasi auto update setelah donasi verified.
   - Jika dibutuhkan, field dapat dirapikan menjadi konsep `current_amount` dan `target_amount`.

3. **Campaign Deadline System**

   Deadline sudah berjalan sebagian melalui `batas_waktu` dan sisa hari.

   Pengembangan berikutnya:
   - Countdown timer campaign.
   - Auto close campaign ketika deadline selesai.
   - Status berubah ke `completed` jika campaign selesai.

4. **Dashboard Analytics**

   Menambahkan analytics pada admin dashboard menggunakan Chart.js.

   Rencana isi dashboard:
   - Statistik donasi bulanan.
   - Total dana terkumpul.
   - Total campaign aktif.
   - Top campaign.
   - Grafik donasi.

5. **Rejected Reason System**

   Menambahkan alasan penolakan campaign/donasi.

   Rencana flow:
   - Admin mengisi alasan reject.
   - Alasan disimpan ke database.
   - User dapat melihat alasan penolakan.

6. **Campaign Update Timeline**

   Menambahkan fitur update perkembangan campaign.

   Rencana fitur:
   - Upload foto update.
   - Progress kegiatan.
   - Catatan penggunaan dana.
   - Timeline perkembangan campaign.

7. **Trending Campaign Section**

   Menambahkan section trending campaign pada homepage.

   Kategori:
   - Most Funded.
   - Urgent Campaign.
   - Latest Campaign.

8. **Future Improvements**

   - Pagination pada seluruh tabel admin.
   - Modal image preview untuk bukti transfer.
   - Reusable component/function.
   - Validasi upload image yang lebih ketat.
   - Optimasi query database.
   - Responsive dashboard UI.

8. **Tambahkan Konten di Database**

   - butuh lebih banyak konten di database.
   - terlalu sedikit acara penggalangan dananya
   - tambahkan minimal 25

9. **tampilan login harus di perbaiki sih**
