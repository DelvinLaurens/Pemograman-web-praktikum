<?php
session_start();
include_once("./components/db_conn.php");
require_once("./components/path_helper.php");

$keyword = filter_input(INPUT_GET, 'keyword', FILTER_DEFAULT) ?: '';
$kategori = filter_input(INPUT_GET, 'kategori', FILTER_DEFAULT) ?: '';
$lokasi = filter_input(INPUT_GET, 'lokasi', FILTER_DEFAULT) ?: '';
$deadline = filter_input(INPUT_GET, 'deadline', FILTER_DEFAULT) ?: '';
$deadline_is_valid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline);

$query = "SELECT k.*
        FROM kampanye k
        INNER JOIN penyelenggara p ON p.id_penyelenggara = k.id_penyelenggara
        WHERE k.status = 'approved'
        AND k.batas_waktu >= CURDATE()
        AND k.dana_terkumpul < k.target_dana";

if (!empty($keyword)) {
    $keyword_safe = $conn->real_escape_string($keyword);
    $query .= " AND (
        k.judul_kampanye LIKE '%{$keyword_safe}%'
        OR k.kategori LIKE '%{$keyword_safe}%'
        OR k.lokasi LIKE '%{$keyword_safe}%'
        OR p.nama_penyelenggara LIKE '%{$keyword_safe}%'
    )";
}
if (!empty($kategori)) {
    $query .= " AND k.kategori = '" . $conn->real_escape_string($kategori) . "'";
}
if (!empty($lokasi)) {
    $query .= " AND k.lokasi LIKE '%" . $conn->real_escape_string($lokasi) . "%'";
}
if (!empty($deadline) && $deadline_is_valid) {
    $query .= " AND k.batas_waktu = '" . $conn->real_escape_string($deadline) . "'";
}

$query .= " ORDER BY k.id_kampanye DESC";
$result_campaigns = $conn->query($query);

$campaigns = [];
if ($result_campaigns) {
    while ($row = $result_campaigns->fetch_assoc()) {
        $campaigns[] = $row;
    }
}

$campaign_list_only = true;
require_once("./components/campaign_list.php");
unset($campaign_list_only);
$latest_campaigns = getTrendingCampaign($conn, 'latest', 3);
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
    <link rel="stylesheet" href="<?php echo asset_url('css/home.css?v=14'); ?>">
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
                        <input type="text" name="keyword" placeholder="Cari judul atau penyelenggara..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

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

                        <input type="date" name="deadline" class="search-date" aria-label="Tanggal deadline kampanye" title="Tanggal deadline kampanye" value="<?php echo htmlspecialchars($_GET['deadline'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                        <button type="submit" class="btn-search">Cari</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="kampanye kampanye-terbaru" id="kampanye-terbaru">
            <div class="container">
                <h2 class="section-title">Kampanye Terbaru</h2>
                <div class="kampanye-grid">
                    <?php if (!empty($latest_campaigns)): ?>
                        <?php foreach ($latest_campaigns as $kampanye_terbaru): ?>
                            <?php cardKampanye($kampanye_terbaru); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="deskripsi">Belum ada kampanye terbaru yang tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="kampanye" id="kampanye">
            <div class="container">
                <h2 class="section-title">Kampanye Mendesak</h2>
                <div class="kampanye-grid">
                    <?php include("./components/campaign_list.php"); ?>
                </div>
            </div>
        </section>
    </main>

    <?php include_once("./components/footer.php") ?>

    <script src="<?php echo asset_url('js/script.js?v=3'); ?>"></script>
</body>
</html>
