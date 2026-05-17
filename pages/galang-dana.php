<?php
session_start();
require_once("../components/path_helper.php");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galang Dana - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/form.css?v=3'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <?php include_once("../components/nav.php") ?>

    <main class="halaman-form">
        <div class="container form-container">
            <div class="form-card">
                
                <div class="ringkasan-donasi">
                    <h2>Mulai Galang Dana</h2>
                    <p>Isi formulir di bawah ini untuk mengajukan bantuan atau kampanye sosial Anda.</p>
                </div>
                <form action="<?php echo url_for('pages/galang-dana.php#berhasil'); ?>" class="form-donasi">
                    <div class="form-group">
                        <label for="judul">Judul Kampanye<span class="required">*</span></label>
                        <input type="text" id="judul" name="judul" placeholder="Contoh: Bantu Renovasi Sekolah Dasar" required>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori Kampanye<span class="required">*</span></label>
                        <select name="kategori" id="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="kesehatan">Kesehatan</option>
                            <option value="pendidikan">Pendidikan</option>
                            <option value="bencana">Bencana Alam</option>
                            <option value="sosial">Sosial</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="target">Target Dana (Rp)<span class="required">*</span></label>
                        <input type="number" id="target" name="target" placeholder="Contoh: 500000" required>
                    </div>

                    <div class="form-group">
                        <label for="lokasi">Lokasi Kegiatan<span class="required">*</span></label>
                        <input type="text" id="lokasi" name="lokasi" placeholder="Masukkan nama kota/daerah" required>
                    </div>

                    <div class="form-group">
                        <label for="cerita">Cerita / Alasan Penggalangan Dana<span class="required">*</span></label>
                        <textarea id="cerita" name="cerita" rows="6" placeholder="Ceritakan kondisi dan mengapa Anda menggalang dana..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="foto">Upload Foto Utama Kampanye<span class="required">*</span></label>
                        <input type="file" id="foto" name="foto" accept="image/*" class="input-file" required>
                        <small>Gunakan foto yang jelas untuk menarik donatur.</small>
                    </div>

                    <button type="submit" class="btn-submit-form">Ajukan Kampanye Sekarang</button>
                </form>
                <div id="berhasil" class="pesan-sukses">
                    <h3>Kampanye Berhasil Diajukan!</h3>
                    <p>Kampanye Anda sedang dalam tahap peninjauan oleh admin. Kami akan menghubungi Anda melalui email.</p>
                    <a href="<?php echo url_for('index.php'); ?>" class="btn-kembali-home">Kembali ke Beranda</a>
                </div>

            </div>
        </div>
    </main>

    <?php include_once("../components/footer.php") ?>
</body>
</html>
