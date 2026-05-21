<?php
session_start();
require_once("../components/db_conn.php");
require_once("../components/donation_helper.php");

$id_kampanye = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_kampanye) {
    $id_kampanye = 1;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT k.*, p.nama_penyelenggara
     FROM kampanye k
     INNER JOIN penyelenggara p ON p.id_penyelenggara = k.id_penyelenggara
     WHERE k.id_kampanye = ?"
);

$kampanye = null;
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id_kampanye);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $kampanye = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
}

if ($kampanye) {
    $target = (float) $kampanye['target_dana'];
    $terkumpul = (float) $kampanye['dana_terkumpul'];
    $persentase = $target > 0 ? min(round(($terkumpul / $target) * 100), 100) : 0;

    $hari_ini = new DateTime();
    $batas_waktu = new DateTime($kampanye['batas_waktu']);
    $sisa_hari = $batas_waktu < $hari_ini ? 0 : $hari_ini->diff($batas_waktu)->days;
    $campaign_closed = isCampaignClosed($kampanye);
    $is_admin_view = !empty($_SESSION['id_penyelenggara']) && ($_SESSION['role'] ?? '') === 'pengelola';
    $kategori = ucwords(str_replace('_', ' ', $kampanye['kategori']));
    $metode_kampanye = campaignPaymentMethodLabels($kampanye);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kampanye - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/detail.css?v=4'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include_once("../components/nav.php") ?>

    <main class="halaman-detail">
        <div class="container">
            <?php if (!$kampanye): ?>
                <div class="detail-kampanye">
                    <div class="konten-utama">
                        <h2 class="judul-detail">Kampanye tidak ditemukan</h2>
                        <p class="deskripsi">Data kampanye yang Anda buka belum tersedia.</p>
                        <a href="<?php echo url_for('index.php#kampanye'); ?>" class="btn-donasi-sekarang">Kembali ke Daftar Kampanye</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="detail-kampanye">
                    <div class="konten-utama">
                        <img src="<?php echo e(asset_url($kampanye['gambar_poster'])); ?>" alt="<?php echo e($kampanye['judul_kampanye']); ?>" class="detail-image">

                        <div class="detail-kategori-lokasi">
                            <span class="badge-kategori"><?php echo e($kategori); ?></span>
                            <span class="badge-lokasi">Lokasi: <?php echo e($kampanye['lokasi']); ?></span>
                        </div>
                        <h2 class="judul-detail"><?php echo e($kampanye['judul_kampanye']); ?></h2>
                        <p class="penyelenggara-detail">Diselenggarakan oleh: <strong><?php echo e($kampanye['nama_penyelenggara']); ?></strong></p>

                        <div class="deskripsi-kampanye">
                            <h3>Cerita Kampanye</h3>
                            <p><?php echo nl2br(e($kampanye['deskripsi'])); ?></p>
                        </div>
                    </div>
                    <div class="konten-samping">
                        <div class="box-donasi">
                            <p class="teks-target">Target Dana: <span><?php echo formatRupiah($kampanye['target_dana']); ?></span></p>
                            <p class="teks-terkumpul">Terkumpul: <strong><?php echo formatRupiah($kampanye['dana_terkumpul']); ?></strong></p>
                            <div class="progress-bar-detail">
                                <div class="progress-detail" style="width: <?php echo $persentase; ?>%;"></div>
                            </div>
                            <p class="teks-persen">Terkumpul <?php echo $persentase; ?>% dari target</p>
                            <p class="teks-waktu">Waktu tersisa: <strong><?php echo $campaign_closed ? 'Penggalangan Ditutup' : $sisa_hari . ' Hari Lagi'; ?></strong></p>
                            <?php if ($is_admin_view): ?>
                                <p class="detail-admin-note">Anda sedang login sebagai pengelola.</p>
                            <?php elseif ($campaign_closed): ?>
                                <span class="btn-donasi-sekarang btn-donasi-disabled">Donasi Ditutup</span>
                            <?php else: ?>
                                <a href="<?php echo url_for('pages/donasi.php?id=' . (int) $kampanye['id_kampanye']); ?>" class="btn-donasi-sekarang">Donasi Sekarang</a>
                            <?php endif; ?>
                            <div class="info-rekening">
                                <h4>Metode Pembayaran:</h4>
                                <ul>
                                    <?php foreach ($metode_kampanye as $metode): ?>
                                        <li><?php echo e($metode); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include_once("../components/footer.php") ?>
</body>
</html>
