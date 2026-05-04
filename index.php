<?php
session_start();
include_once("./Component/db_conn.php");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DemiSesama</title>
    <!-- fav icon -->
    <link rel="icon" type="image/png" href="Asset/tangan2 tnpa bg.png">
    <script>document.documentElement.classList.add("animasi-scroll-siap");</script>
    <link rel="stylesheet" href="CSS/global.css?v=3">
    <link rel="stylesheet" href="CSS/home.css?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include_once("./Component/nav_com.php") ?>

    <main>
        <section class="tampilan-utama">
            <div class="hero-overlay"></div>
            <div class="container text-center hero-content">
                <h1>Ayo wujudkan Harapan, Demi sesama.</h1>
                <p class="hero-desc">Demi Sesama hadir sebagai jembatan kebaikan. Di sini, setiap donasi menjadi harapan bagi mereka yang membutuhkan. Mari bersama-sama membantu, berbagi, dan menciptakan dunia yang lebih peduli.</p>

                <div class="search-bar">
                    <form method="GET" action="#kampanye">
                        <i class="fas fa-search"></i>
                        <input type="text" name="keyword" placeholder="Cari judul kampanye..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                        <select name="kategori" id="kategori">
                            <option value="">Semua Kategori</option>
                            <option value="kesehatan" <?php echo ($_GET['kategori'] ?? '') === 'kesehatan' ? 'selected' : ''; ?>>Kesehatan</option>
                            <option value="pendidikan" <?php echo ($_GET['kategori'] ?? '') === 'pendidikan' ? 'selected' : ''; ?>>Pendidikan</option>
                            <option value="bencana_alam" <?php echo ($_GET['kategori'] ?? '') === 'bencana_alam' ? 'selected' : ''; ?>>Bencana Alam</option>
                            <option value="sosial" <?php echo ($_GET['kategori'] ?? '') === 'sosial' ? 'selected' : ''; ?>>Kehidupan Sosial</option>
                            <option value="pembangunan" <?php echo ($_GET['kategori'] ?? '') === 'pembangunan' ? 'selected' : ''; ?>>Pembangunan</option>
                            <option value="lingkungan" <?php echo ($_GET['kategori'] ?? '') === 'lingkungan' ? 'selected' : ''; ?>>Lingkungan</option>
                        </select>

                        <select name="lokasi" id="lokasi">
                            <option value="">Semua Lokasi</option>
                            <option value="sumatera" <?php echo ($_GET['lokasi'] ?? '') === 'sumatera' ? 'selected' : ''; ?>>Sumatera</option>
                            <option value="jawa" <?php echo ($_GET['lokasi'] ?? '') === 'jawa' ? 'selected' : ''; ?>>Jawa</option>
                            <option value="kalimantan" <?php echo ($_GET['lokasi'] ?? '') === 'kalimantan' ? 'selected' : ''; ?>>Kalimantan</option>
                            <option value="sulawesi" <?php echo ($_GET['lokasi'] ?? '') === 'sulawesi' ? 'selected' : ''; ?>>Sulawesi</option>
                            <option value="bali" <?php echo ($_GET['lokasi'] ?? '') === 'bali' ? 'selected' : ''; ?>>Bali</option>
                            <option value="maluku" <?php echo ($_GET['lokasi'] ?? '') === 'maluku' ? 'selected' : ''; ?>>Maluku</option>
                            <option value="papua" <?php echo ($_GET['lokasi'] ?? '') === 'papua' ? 'selected' : ''; ?>>Papua</option>
                            <option value="ntt" <?php echo ($_GET['lokasi'] ?? '') === 'ntt' ? 'selected' : ''; ?>>NTT</option>
                        </select>

                        <select name="range" id="range">
                            <option value="">Semua Target</option>
                            <option value="0-1000000" <?php echo ($_GET['range'] ?? '') === '0-1000000' ? 'selected' : ''; ?>>&lt; 1 Juta</option>
                            <option value="1000000-5000000" <?php echo ($_GET['range'] ?? '') === '1000000-5000000' ? 'selected' : ''; ?>>1 - 5 Juta</option>
                            <option value="5000000-10000000" <?php echo ($_GET['range'] ?? '') === '5000000-10000000' ? 'selected' : ''; ?>>5 - 10 Juta</option>
                            <option value="10000000+" <?php echo ($_GET['range'] ?? '') === '10000000+' ? 'selected' : ''; ?>>&gt; 10 Juta</option>
                        </select>

                        <button type="submit" class="btn-search">Cari</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="kampanye" id="kampanye">
            <div class="container">
                <h2 class="section-title">Kampanye Mendesak</h2>
                <div class="kampanye-grid">
                    <?php include_once("./Component/kampanye_dinamis_com.php"); ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container text-center">
            <p>&copy; 2026 DemiSesama</p>
        </div>
    </footer>

    <script src="JS/script.js?v=3"></script>
</body>
</html>
