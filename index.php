<?php
session_start();
include_once("./components/db_conn.php");
require_once("./components/path_helper.php");

$keyword = filter_input(INPUT_GET, 'keyword', FILTER_DEFAULT) ?: '';
$kategori = filter_input(INPUT_GET, 'kategori', FILTER_DEFAULT) ?: '';
$lokasi = filter_input(INPUT_GET, 'lokasi', FILTER_DEFAULT) ?: '';
$range = filter_input(INPUT_GET, 'range', FILTER_DEFAULT) ?: '';

$query = "SELECT * FROM kampanye WHERE (status = 'approved' OR status = 'completed')";

if (!empty($keyword)) {
    $query .= " AND judul_kampanye LIKE '%" . $conn->real_escape_string($keyword) . "%'";
}
if (!empty($kategori)) {
    $query .= " AND kategori = '" . $conn->real_escape_string($kategori) . "'";
}
if (!empty($lokasi)) {
    $query .= " AND lokasi LIKE '%" . $conn->real_escape_string($lokasi) . "%'";
}
if (!empty($range)) {
    if ($range === '0-1000000') $query .= " AND target_dana < 1000000";
    elseif ($range === '1000000-5000000') $query .= " AND target_dana BETWEEN 1000000 AND 5000000";
    elseif ($range === '5000000-10000000') $query .= " AND target_dana BETWEEN 5000000 AND 10000000";
    elseif ($range === '10000000+') $query .= " AND target_dana > 10000000";
}

$query .= " ORDER BY id_kampanye DESC";
$result_campaigns = $conn->query($query);

$campaigns = [];
if ($result_campaigns) {
    while ($row = $result_campaigns->fetch_assoc()) {
        $campaigns[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DemiSesama</title>
    <!-- fav icon -->
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <script>document.documentElement.classList.add("animasi-scroll-siap");</script>
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/home.css?v=3'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include_once("./components/nav.php") ?>

    <main>
        <section class="tampilan-utama">
            <div class="hero-overlay"></div>
            <div class="container text-center hero-content">
                <h1>Ayo wujudkan Harapan, Demi sesama.</h1>
                <p class="hero-desc">Demi Sesama hadir sebagai jembatan kebaikan. Di sini, setiap donasi menjadi harapan bagi mereka yang membutuhkan. Mari bersama-sama membantu, berbagi, dan menciptakan dunia yang lebih peduli.</p>

                <div class="search-bar">
                    <form method="GET" action="<?php echo url_for('index.php#kampanye'); ?>">
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
                    <?php include_once("./components/campaign_list.php"); ?>
                </div>
            </div>
        </section>
    </main>

    <?php include_once("./components/footer.php") ?>

    <script src="<?php echo asset_url('js/script.js?v=3'); ?>"></script>
</body>
</html>
