# Penjelasan Kode Proyek "DemiSesama" (Versi Detail & Mendalam)

Dokumen ini berisi analisis teknis dan penjabaran logika kode secara menyeluruh untuk setiap fitur utama pada aplikasi "DemiSesama". Dokumen ini dirancang khusus untuk memenuhi kriteria penilaian akademik dan menjadi panduan komprehensif saat sidang/presentasi di hadapan dosen.

---

## 1. Tampilan Halaman Utama

### Kriteria Penilaian:
- Menampilkan semua data kampanye yang ada di database (1 poin)
- Data yang sudah melewati batas waktu tidak tertampil (1 poin)

### File yang Berkaitan:
1. `index.php` (Antarmuka pengguna untuk halaman beranda)
2. `components/campaign_list.php` (Logika backend pemrosesan data kampanye)

### Analisis Logika Kode secara Mendalam:

1. **Pengambilan Seluruh Data dari Database (Relasional)**
   Pada file `components/campaign_list.php` (sekitar baris 204), sistem mengeksekusi *query* relasional:
   ```php
   $sql = "SELECT k.*, p.nama_penyelenggara 
           FROM kampanye k
           INNER JOIN penyelenggara p ON p.id_penyelenggara = k.id_penyelenggara ...";
   ```
   **Analisis:** 
   Alih-alih menggunakan data statis, sistem membangun data secara dinamis dari tabel `kampanye` (diinisialisasikan sebagai `k`). Penggunaan operasi `INNER JOIN` dengan tabel `penyelenggara` (sebagai `p`) sangat penting. Relasi ini didasarkan pada *Foreign Key* `id_penyelenggara`. Dengan relasi ini, kita tidak hanya menarik deskripsi dan target dana kampanye, tetapi secara bersamaan menarik "Nama Penyelenggara/Artis" untuk dicetak di *card* HTML tanpa harus melakukan dua kali pemanggilan *query*. Hal ini membuktikan efisiensi dalam perancangan *database* relasional.

2. **Filter Waktu & Status (Mencegah Tampilnya Data Kadaluarsa)**
   Untuk memastikan kampanye yang telah ditutup atau melewati batas waktu otomatis menghilang, diterapkan penyaringan level SQL di dalam variabel *array* `$where` (baris 145-149):
   ```php
   $where = [
       "k.status = 'active'",
       "k.batas_waktu >= CURDATE()", // Kunci penyaringan waktu
       "k.dana_terkumpul < k.target_dana",
   ];
   ```
   **Analisis:**
   - Kondisi `k.batas_waktu >= CURDATE()` adalah inti dari persyaratan ini. Fungsi bawaan MySQL `CURDATE()` mengambil tanggal *server* saat ini secara *real-time*. Jika batas waktu kampanye (misalnya 2 hari yang lalu) dievaluasi, nilainya menjadi lebih kecil (`<`) dari `CURDATE()`. Kondisi `>=` menjadi *false*, dan MySQL secara otomatis menolak data tersebut.
   - Tidak hanya batas waktu, sistem memiliki dua validasi tambahan di sisi *database*: kampanye harus berstatus `'active'` (bukan ditutup manual oleh admin), dan `dana_terkumpul` harus `< k.target_dana` (mencegah kampanye yang dananya sudah penuh dari muncul di beranda pencarian donasi baru).

---

## 2. Fitur Pencarian (Search), Pengurutan, dan Pagination

### Kriteria Penilaian:
- Fungsi search bekerja sesuai dengan semua ketentuan (lokasi, nama artis, tanggal) (1 poin)
- Daftar kampanye ditampilkan dari deadline paling dekat serta dana paling kecil (1 poin)
- Terdapat pagination untuk data-data (1 poin)

### File yang Berkaitan:
1. `index.php` (Formular *GET Request* untuk pencarian)
2. `components/campaign_list.php` (Pemrosesan parameter *GET* dan eksekusi SQL)

### Analisis Logika Kode secara Mendalam:

1. **Pemrosesan Pencarian Tiga Parameter Terpadu**
   Sistem menangkap variabel dari *form* dan memasukkannya sebagai kondisi SQL (baris 153-180):
   ```php
   // Pencarian Keyword (Nama Kampanye atau Penyelenggara)
   if ($keyword !== "") {
       $where[] = "(k.judul_kampanye LIKE ? OR p.nama_penyelenggara LIKE ?)"; 
   }
   // Pencarian Lokasi
   if ($lokasi !== "") {
       $where[] = "k.lokasi LIKE ?";
   }
   // Pencarian Tanggal Spesifik
   if ($deadline !== "" && $deadline_is_valid) {
       $where[] = "k.batas_waktu = ?";
   }
   ```
   **Analisis:** 
   Setiap blok `if` bertugas mengecek apakah pengguna mengisi *form* tersebut. Jika diisi, kondisi pencarian (menggunakan operator `LIKE` untuk pencarian parsial/sebagian teks) ditambahkan ke dalam wadah (array) `$where`. Tanda tanya `?` mengindikasikan implementasi **Prepared Statements**. Ini membuktikan bahwa sistem kebal dari ancaman peretasan *SQL Injection* karena apa pun yang diketik *user* akan diubah menjadi string murni, bukan *command* SQL.

2. **Pengurutan Kompleks (Double Sorting)**
   Persyaratan urutan diterjemahkan menjadi satu baris operasi MySQL di baris 208:
   ```php
   ORDER BY k.batas_waktu ASC, k.dana_terkumpul ASC
   ```
   **Analisis:** 
   Penggunaan `ORDER BY` dengan dua kolom sekaligus disebut *multi-level sorting*. `k.batas_waktu ASC` adalah prioritas utama (Ascending: dari hari ini maju ke masa depan). Jika kebetulan ada dua kampanye yang ditutup pada tanggal yang sama persis, prioritas kedua `k.dana_terkumpul ASC` diaktifkan. Kampanye yang mendapatkan sumbangan paling sedikit (angka kecil) akan dinaikkan peringkatnya agar mendapatkan perhatian donatur lebih awal.

3. **Logika Matematika Pagination**
   Pada bagian paling akhir query (baris 209 dan pembuatan navigasi baris 240):
   ```php
   // Query limitasi
   $sql .= " LIMIT ? OFFSET ?";
   
   // Kalkulasi jumlah tombol
   $total_pages = (int) ceil($total_data / $limit);
   ```
   **Analisis:**
   - Pembatasan data dilakukan langsung di mesin *database*, bukan di sisi PHP. `LIMIT` menentukan berapa data per halaman (diatur ke nilai 6), sedangkan `OFFSET` menentukan indeks data mulai (Rumus: `(Halaman_Sekarang - 1) * Limit`). Jika berada di halaman 3, sistem melewati `(3-1)*6 = 12` data pertama.
   - Fungsi `ceil()` (Ceiling) digunakan secara matematis untuk membulatkan ke atas. Jika total data adalah 14, maka `14 / 6 = 2.33`. Fungsi `ceil()` akan memaksa pembulatan menjadi `3` agar tidak ada sisa 2 data yang tertinggal, sehingga antarmuka sukses mencetak tiga buah tombol navigasi halaman (1, 2, 3).

---

## 3. Halaman Detail Kampanye

### Kriteria Penilaian:
- Detail kampanye sesuai dengan yang dipilih pengguna dari halaman awal (1 poin)
- Detail kampanye diambil dari DB (1 poin)

### File yang Berkaitan:
1. `pages/detail.php` (Tampilan dan *Controller* detail spesifik)

### Analisis Logika Kode secara Mendalam:

1. **Pengikatan Parameter URL (Secure Parameter Binding)**
   Pada baris ke-6 `pages/detail.php`, sistem menangkap identitas kampanye:
   ```php
   $id_kampanye = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
   ```
   **Analisis:**
   Parameter `id` dikirimkan melalui URL (metode GET). Untuk mencegah manipulasi URL oleh pengguna usil (misal mengubah URL menjadi `?id=huruf`), sistem tidak menggunakan superglobal mentah `$_GET['id']`. Alih-alih, ia menggunakan fungsi pengaman `filter_input()` dengan mode `FILTER_VALIDATE_INT` yang secara keras memastikan bahwa nilai tersebut 100% adalah *Integer* (bilangan bulat). Jika bukan, proses langsung dihentikan.

2. **Pengambilan Detail Utuh Secara Real-Time**
   Pada baris 12-18, setelah ID dianggap bersih dan valid, sistem mengeksekusi:
   ```php
   $stmt = mysqli_prepare($conn,
       "SELECT k.*, p.nama_penyelenggara
        FROM kampanye k
        INNER JOIN penyelenggara p ON p.id_penyelenggara = k.id_penyelenggara
        WHERE k.id_kampanye = ?"
   );
   mysqli_stmt_bind_param($stmt, "i", $id_kampanye);
   ```
   **Analisis:**
   *Query* ini adalah jantung dari halaman detail. `WHERE k.id_kampanye = ?` adalah filter paling tajam yang mengekstrak persis satu baris kampanye. Sekali lagi, metode `mysqli_prepare` dan `bind_param("i")` (dimana "i" berarti *integer*) digunakan sebagai pengaman absolut. Data ini kemudian langsung disalurkan ke struktur HTML di bawahnya untuk merender deskripsi, progress bar, dan tenggat waktu secara aktual.

---

## 4. Sistem Otentikasi dan Proses Formulir Donasi

### Kriteria Penilaian:
- Input Nominal, Metode, Pesan, dan Bukti Transfer (0.5 poin)
- Validasi nominal <= 10.000 dan validasi bukti transfer (1 poin)
- Status otomatis PENDING saat tersubmit (1 poin)
- Gambar disimpan di server, letak (path) di DB (0.5 poin)

### File yang Berkaitan:
1. `pages/donasi.php` (Inisiasi nominal dan profil dasar)
2. `pages/verif.php` (Pengunggahan gambar bukti dan penyelesaian transaksi)
3. `components/donation_service.php` (Arsitektur fungsi dan manipulasi file)

### Analisis Logika Kode secara Mendalam:

1. **Pre-populasi Profil (*Readonly* Fields)**
   Di `pages/donasi.php`, *field* email dan nama tidak bisa diketik manual:
   ```php
   <input type="email" id="email" value="<?php echo e($donatur['email']); ?>" readonly>
   ```
   **Analisis:** Sistem memanfaatkan sesi (*session*) pengguna yang sedang aktif (login). Atribut HTML `readonly` diterapkan untuk menjaga integritas data. Data transaksi harus diikat secara pasti kepada *user* yang aktif, sehingga pengguna tidak bisa memasukkan email palsu saat berdonasi.

2. **Validasi Kritis *Backend* (Minimal Rp 10.000)**
   Validasi di sisi *client* (HTML5 `min="10000"`) sangat mudah ditembus peretas dengan teknik *Inspect Element*. Karena itu, sistem menempatkan pertahanan utama di sisi *Backend* (`donation_service.php`):
   ```php
   if (!$nominal || $nominal < 10000) {
       $errors[] = "Nominal donasi minimal Rp10.000.";
   }
   ```
   **Analisis:** Jika PHP mendeteksi intersep *payload* di mana nominal dimanipulasi menjadi di bawah 10.000, transaksi tersebut langsung di- *blacklist* dan array `$errors` akan dipenuhi, mengakibatkan transaksi dibatalkan sebelum menyentuh database.

3. **Status PENDING Otomatis (Default State)**
   Di fungsi `createPendingDonation()` (`donation_service.php`):
   ```php
   $sql = "INSERT INTO donasi (id_donatur, id_kampanye, nominal_donasi, pesan_dukungan, status) 
           VALUES (?, ?, ?, ?, 'PENDING')";
   ```
   **Analisis:** Kolom status pada kueri `INSERT` bersifat statis (di- *hardcode*) menjadi *string* `'PENDING'`. Hal ini berarti tidak ada jalan pintas dari aplikasi agar transaksi baru bisa langsung dianggap sah. Uang donasi belum dijumlahkan ke kampanye sampai ada campur tangan manusia (Penyelenggara) dari sisi panel admin.

4. **Arsitektur Penyimpanan File (Non-BLOB)**
   Penyelesaian unggahan terjadi di `pages/verif.php` dan `uploadDonationProof()`:
   ```php
   $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
   $target_path = "assets/uploads/bukti-transfer/donasi-" . $id_donasi . "-" . time() . ".jpg";
   
   if (move_uploaded_file($file['tmp_name'], $target_path)) {
       // Update Database dengan Path String
   }
   ```
   **Analisis:** 
   Terdapat tiga mekanisme pintar di sini:
   - **Keamanan Ekstensi:** Sistem mengecek ekstensi asli. File berekstensi berbahaya `.php` atau `.exe` akan ditolak mentah-mentah.
   - **Pemindahan Fisik (`move_uploaded_file`):** *File* biner yang dikirim pengguna dari *browser* disimpan secara sementara di sistem operasi lokal (*tmp_name*), lalu dipindahkan ke dalam direktori khusus (`assets/uploads/bukti-transfer/`).
   - **Pencegahan BLOB (Binary Large Object):** Praktik buruk menyimpan data gambar biner ke dalam *Database* dapat menyebabkan kinerja memburuk dan basis data membengkak. Sistem ini menggunakan kolom bertipe `VARCHAR` untuk sekadar menyimpan untaian teks alamat *file* (`$target_path`). Pemanggilan di HTML sangat ringan karena menggunakan tag standar `<img src="alamat_teks" />`.

---

## 5. Sistem Otentikasi (Login & Logout)

### Kriteria Penilaian:
- Tampilan halaman utama memiliki button untuk login (0.5 poin)
- Form login dengan username/password (0.5 poin)
- Terdapat validasi login kosong/salah (0.5 poin)
- Berhasil login dari DB (0.5 poin)
- Simpan ke session dan cetak nama user (1 poin)
- Tombol login berubah jadi logout (0.5 poin)
- Redirect jika diakses tanpa login (1.5 poin)

### File yang Berkaitan:
1. `components/nav.php` (Pengubah wujud antarmuka navigasi)
2. `auth/login.php` (Sistem pencocokan kredensial)
3. `components/auth.php` (Lapisan keamanan pengecek sesi)

### Analisis Logika Kode secara Mendalam:

1. **Navigasi Dasar & Validasi Input Kosong**
   Di `auth/login.php`, sebelum menembak ke *database*, PHP bertindak sebagai gerbang:
   ```php
   $username = trim($_POST['username'] ?? '');
   if ($username === '' || $password === '') {
       $error = "Username dan password wajib diisi.";
   }
   ```
   **Analisis:** Penggunaan `trim()` sangat krusial. Terkadang pengguna tanpa sengaja memasukkan spasi ekstra (*whitespace*) di belakang teks. `trim` menghapus spasi siluman itu sehingga data bersih. Jika variabel kosong, PHP akan memblokir dan menghemat kinerja *database* dari pencarian sia-sia.

2. **Otentikasi Kredensial & Pembuatan Sesi (Session)**
   Di `auth/login.php` baris 80:
   ```php
   $stmt = mysqli_prepare($conn, "SELECT id_donatur, nama_lengkap FROM donatur WHERE username = ? AND password = ? LIMIT 1");
   // ... jika username & password MATCH di DB:
   $_SESSION['id_donatur'] = $donatur['id_donatur'];
   $_SESSION['nama_lengkap'] = $donatur['nama_lengkap'];
   ```
   **Analisis:**
   Kueri ini mencari baris data persis yang cocok dengan kedua kolom (`username AND password`). Penggunaan klausa `LIMIT 1` membuat SQL langsung menghentikan pencarian begitu satu *user* ditemukan (optimasi kecepatan). Jika ada, data paling krusial disuntikkan ke dalam `$_SESSION`, yaitu mekanisme memori penyimpanan sementara (RAM) di *server PHP* yang mengikuti ke mana pun pengguna menavigasikan halamannya.

3. **Perubahan State Tombol (Login menjadi Logout) Secara Dinamis**
   Di `components/nav.php`:
   ```php
   <?php if (!empty($_SESSION['id_donatur'])): ?>
       <li><span class="nav-user">Halo, <?php echo htmlspecialchars($nama_pertama_nav); ?></span></li>
       <li><a href="...logout.php" class="btn-logout">Logout</a></li>
   <?php else: ?>
       <li><a href="...login.php">Login</a></li>
   <?php endif; ?>
   ```
   **Analisis:** Skrip antarmuka ini mengendus keberadaan nilai `id_donatur` di memori *Session*. Apabila kondisinya terbukti (pengguna sudah *login*), ia mengeksekusi blok kode atas yang mencetak nama pemanggil dan memunculkan tombol *Logout*. Fungsi `htmlspecialchars` melindungi nama dari ancaman peretasan *XSS* jika di namanya terdapat karakter HTML aneh. Jika sesi kosong, blok bawah tereksekusi yang merender tombol *Login* standar.

4. **Pertahanan Penyelundupan (Middleware Redirect)**
   Pada file tertutup seperti `pages/donasi.php` baris 17:
   ```php
   requireDonorLogin($current_url);
   ```
   **Analisis:** Berada di file inti `auth.php`, fungsi ini mengecek jika sesi benar-benar tidak aktif. Jika ya, ia mengaktifkan perintah `header("Location: login.php")`. Secara teknis, ini adalah injeksi *Header HTTP 302 Redirect* langsung ke *browser*, memaksa *browser* seketika banting setir kembali ke halaman gerbang tanpa memproses sebaris kode pun di bawahnya.

---

## 6. Pengelolaan Data Kampanye (Admin)

### Kriteria Penilaian:
- Pengelola dapat melihat daftar donatur untuk kampanye yang dikelolanya (0.5 poin)
- Pengguna dapat mengubah data kampanye yang telah dibuat (1 poin)
- Pengguna dapat melakukan penghapusan data kampanye (1 poin)
- Data kampanye yang sudah memiliki dana terkumpul (>=10.000) tidak dapat dihapus (1 poin)
- Data gambar disimpan di server, bukan di DB dalam bentuk blob (0.5 poin)

### File yang Berkaitan:
1. `admin/detail-kampanye.php` (Tampilan terpusat data kampanye untuk Penyelenggara)
2. `admin/form-kampanye.php` (Antarmuka pembaruan/edit entitas)
3. `components/admin_service.php` (Pusat logika bisnis pengelolaan (*CRUD*))

### Analisis Logika Kode secara Mendalam:

1. **Pemanggilan Riwayat Donatur Terisolasi**
   Di `admin/detail-kampanye.php` (baris 262-271):
   ```php
   $stmt = mysqli_prepare($conn, 
      "SELECT d.*, dt.nama_lengkap 
       FROM donasi d 
       INNER JOIN donatur dt ON dt.id_donatur = d.id_donatur 
       WHERE d.id_kampanye = ?"
   );
   ```
   **Analisis:**
   Penyelenggara A tidak boleh melihat riwayat donatur Penyelenggara B. Kondisi `WHERE d.id_kampanye = ?` memastikan sistem mengisolasi data dan hanya menyedot rekam jejak untuk objek kampanye miliknya saja. Pemanggilan `INNER JOIN` ke `donatur` dibutuhkan agar Penyelenggara tidak hanya melihat "ID: 4", melainkan tulisan ramah nama asli donatur (misalnya "Budi Santoso").

2. **Eksekusi Penimpaan Data (Update Kampanye)**
   Saat Penyelenggara mengisi ulang formulir di `form-kampanye.php`, fungsi di `admin_service.php` akan menjalankan:
   ```php
   "UPDATE kampanye SET judul_kampanye = ?, target_dana = ? ... WHERE id_kampanye = ?"
   ```
   **Analisis:** 
   *Query UPDATE* ini adalah inti dari operasi Edit. Sistem tidak membuang kampanye lama, ia menimpa (*overwrite*) kolom spesifik yang disematkan dengan variabel terbaru yang didapat dari form `$_POST`.

3. **Logika Bisnis: Blokade Penghapusan Dana Terkumpul (>= Rp 10.000)**
   Sebelum mengeksekusi penghapusan di `admin_service.php`, sistem melakukan interogasi status kampanye:
   ```php
   if ((float) $campaign['dana_terkumpul'] >= 10000) {
       return ['success' => false, 'errors' => ['Kampanye yang sudah memiliki dana terkumpul tidak dapat dihapus.']];
   }
   // Jika lulus pengecekan di atas, maka sistem boleh menjalankan:
   // "DELETE FROM kampanye WHERE id_kampanye = ?"
   ```
   **Analisis:**
   Fungsi ini menjadi satpam dari *business rules*. Sistem menarik saldo `dana_terkumpul` dan secara paksa mengubah tipe datanya (`type casting`) menjadi numerik desimal `(float)` untuk menjamin presisi matematis. Jika variabel ini bernilai `10000` atau lebih, algoritma mengeksekusi sintaks `return`. `return` ini tidak sekadar memberikan pesan *error*, tetapi secara harafiah **membunuh fungsi tersebut** saat itu juga. Karena fungsinya mati prematur, baris perintah berdarah dingin `DELETE FROM` di bawahnya dipastikan tidak akan pernah tereksekusi. Inilah alasan mengapa kampanye berdana terkunci dengan aman.

---

## 7. Verifikasi Data Donasi

### Kriteria Penilaian:
- Fitur verifikasi hanya bisa diakses oleh penyelenggara (0.5 poin)
- Penyelenggara dapat melihat semua donasi (nama dan nominal) untuk kampanye miliknya, baik diterima, tolak, atau pending (1 poin)
- Penyelenggara dapat memverifikasi bukti transfer donasi: (1.5 poin)
  - a. Jika diterima/diverifikasi maka dana terkumpul bertambah.
  - b. Jika ditolak, dana tidak masuk.
- Tertampil jumlah dana yang sudah terkumpul per kampanye (0.5 poin)
- Tertampil jumlah dana yang masih pending per kampanye (0.5 poin)

### File yang Berkaitan:
1. `admin/detail-kampanye.php` (Papan kendali (*Dashboard*) verifikasi dan cincin ringkasan)
2. `components/admin_service.php` (Mesin kalkulasi saldo)

### Analisis Logika Kode secara Mendalam:

1. **Pemaksaan Hierarki Hak Akses**
   ```php
   // Baris 1 di detail-kampanye.php
   requireAdminLogin();
   ```
   **Analisis:** Jika sesi `$_SESSION['id_penyelenggara']` tidak terdeteksi, maka pengguna dilempar (*bounced*) secara paksa dari direktori `admin/`. Level otorisasi di sini lebih ketat daripada sekadar `requireDonorLogin`, menciptakan hierarki pengguna yang solid.

2. **Algoritma Pemindahan Kekayaan (Verifikasi Diterima vs Ditolak)**
   Fungsi `updateDonationVerificationStatus()` di `admin_service.php` menampung kejeniusan matematis berikut:
   ```php
   $delta = 0;
   
   // Skenario A: Jika Tombol TERIMA yang ditekan
   if ($old_status !== 'VERIFIED' && $new_status === 'VERIFIED') {
       $delta = (float) $donasi['nominal_donasi'];
   } 
   // Skenario B: Jika Tombol TOLAK (REJECTED) yang ditekan, kondisi IF gagal, $delta tetap 0.

   if ($delta !== 0.0) {
       $stmt = mysqli_prepare($conn, 
           "UPDATE kampanye SET dana_terkumpul = dana_terkumpul + ? WHERE id_kampanye = ?"
       );
       mysqli_stmt_bind_param($stmt, "di", $delta, $id_kampanye);
       mysqli_stmt_execute($stmt);
   }
   ```
   **Analisis Super Rinci:**
   - Variabel `$delta` (perubahan/selisih) diatur nilai dasarnya menjadi `0`.
   - **(a) Jika Diverifikasi:** Jika Penyelenggara memutuskan menerima donasi, maka status donasi berubah dari `PENDING` menuju `VERIFIED`. Karena ini, *statement IF* menjadi *True*. Nilai `$delta` dinaikkan menjadi setara dengan nilai transfer donatur (misalnya 50.000). Kondisi kedua `if ($delta !== 0.0)` ikut menjadi *True*. Perintah `UPDATE` dijalankan! MySQL disuruh melakukan operasi matematika aritmatika di dalam sistemnya (`dana_terkumpul + 50000`). Saldo kampanye meledak membesar, target pun terpenuhi.
   - **(b) Jika Ditolak:** Jika Penyelenggara menolak, maka `$new_status` adalah `REJECTED`. *Statement IF* pertama gagal, maka `$delta` tidak berubah alias tetap angka `0`. Kondisi kedua `if ($delta !== 0.0)` gagal. Karena gagal, fungsi pembaruan `UPDATE` ter- *skip* dan tidak tereksekusi sama sekali. Uang kampanye selamat tanpa penambahan ilegal!

3. **Agregasi Ringkasan Finansial Dinamis (Terkumpul & Pending)**
   Bagaimana sistem mengetahui total Rp dan total transaksi secara serentak? (`detail-kampanye.php` baris 252-259)
   ```php
   "SELECT status, COUNT(*) AS total_donasi, SUM(nominal_donasi) AS total_nominal 
    FROM donasi WHERE id_kampanye = ? GROUP BY status"
   ```
   **Analisis:**
   Fungsi ini adalah sihir analitik SQL.
   - Fungsi Agregat `SUM()` bertugas menjumlahkan semua baris Rupiah di dalam tabel layaknya aplikasi *Excel*.
   - Operasi penutup `GROUP BY status` memerintahkan mesin MySQL memisah dan mengotak-ngotakkan hasilnya berdasarkan status persisnya. Alhasil, kita mendapatkan nilai matang terpisah yang rapi: Kelompok `VERIFIED` untuk dicetak di papan *"Terverifikasi"*, dan kelompok `PENDING` untuk mengisi papan kuning *"Menunggu"*. Sangat bersih dan anti lelet (*anti-bottleneck*).

---

## 8. Bonus: Riwayat Donatur

### Kriteria Penilaian:
- Terdapat ringkasan donasi yang pernah dilakukan (Contoh: Verified Rp750.000 (3 donasi), Pending Rp200.000 (2 donasi), dst)
- Menampilkan riwayat donasi donatur yang login (diterima, ditolak, pending) (1 poin)
- Terdapat indikator visual (warna hijau untuk verified, kuning pending, merah ditolak) (1 poin)

### File yang Berkaitan:
1. `pages/riwayat-donasi.php` (Tampilan rekap portofolio personal Donatur)
2. `css/global.css` (Mesin penyedia *Badge* warna reaktif)

### Analisis Logika Kode secara Mendalam:

1. **Agregasi Ringkasan Personal Donatur (COUNT & SUM Terkait)**
   Sama seperti analitik panel Admin, riwayat ini dipanggil menggunakan (baris 18):
   ```php
   $stmt = mysqli_prepare($conn, 
      "SELECT status, COUNT(*) AS total_donasi, COALESCE(SUM(nominal_donasi), 0) AS total_nominal 
       FROM donasi WHERE id_donatur = ? GROUP BY status"
   );
   ```
   **Analisis:** Hal pembeda yang radikal adalah klausa filternya: `WHERE id_donatur = ?`. Sistem mencari sumbangan atas nama profil yang aktif, terlepas dari berapapun kampanye artis yang telah disumbang. Fungsi cerdas `COALESCE(..., 0)` diletakkan untuk mencegah ancaman hasil berwujud `NULL` jika donatur belum pernah menyumbang di satu kategori tertentu, memastikan hasil komputasi HTML mencetak angka bulat `0` dan aplikasi tidak mengalami *crash*.

2. **Daftar Jejak Donasi Non-Diskriminatif**
   Riwayat lengkap dicetak dari *Query*: `SELECT d.* ... WHERE d.id_donatur = ? ORDER BY d.waktu_donasi DESC`.
   **Analisis:** Tanpa klausa pembatas status (misal `AND status = 'VERIFIED'`), secara *default* operasi ini menggali dan mencetak *seluruh* *array* rekam jejak secara kronologis terbalik (`DESC`) dari donasi hari ini, mundur hingga donasi paling purba. Memberikan transparansi mutlak (kriteria 1 poin).

3. **Cetak Biru Logika Indikator Visual (Status Badge System)**
   Di antarmuka tabel baris 126, sistem mencetak atribut class secara manipulatif:
   ```php
   <span class="status-badge status-<?php echo strtolower(e($donasi['status'])); ?>">
       <?php echo e($donasi['status']); ?>
   </span>
   ```
   **Analisis:**
   Inilah pusat rekayasa UI. Database menyimpan *string* beringas seperti `'VERIFIED'` atau `'REJECTED'` (huruf kapital).
   Fungsi dasar PHP `strtolower()` akan melucuti huruf kapital itu, mengubahnya menjadi versi jinak berhuruf kecil `'verified'`. Ia disatukan dengan kata kunci `status-`, melahirkan susunan *Class HTML* baru secara taktis: `status-verified`.
   Di dalam ekosistem `css/global.css` aplikasi Anda:
   - Kelas `.status-verified` telah diinstruksikan membawa warna latar belakang Hijau Zamrud.
   - Kelas `.status-pending` melukiskan Kuning Matahari.
   - Kelas `.status-rejected` menghasilkan Merah Darah.
   Hasil akhirnya, indikator warna berubah-ubah seirama dengan nilai dari pangkalan data (*database*), memenuhi kriteria poin visual dari dosen Anda secara mutlak.
