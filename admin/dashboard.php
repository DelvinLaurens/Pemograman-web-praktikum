<?php
require_once("../components/db_conn.php");
require_once("../components/auth.php");
require_once("../components/admin_service.php");

requireAdminLogin('admin/dashboard.php');

$summary = getAdminSummary($conn, currentAdminId());
$nama_pengelola = $_SESSION['nama_penyelenggara'] ?? 'Pengelola';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengelola - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/admin.css?v=1'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include_once("../components/nav.php") ?>

    <main class="admin-page">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span>Dashboard Pengelola</span>
                    <h1>Halo, <?php echo e($nama_pengelola); ?></h1>
                </div>
                <a href="<?php echo url_for('admin/kampanye.php'); ?>" class="admin-primary-link">Kelola Kampanye</a>
            </div>

            <section class="admin-stats">
                <div class="admin-stat-card">
                    <span>Total Kampanye</span>
                    <strong><?php echo (int) $summary['kampanye']; ?></strong>
                </div>
                <div class="admin-stat-card">
                    <span>Donasi Pending</span>
                    <strong><?php echo (int) $summary['pending']; ?></strong>
                </div>
                <div class="admin-stat-card">
                    <span>Dana Verified</span>
                    <strong><?php echo formatRupiah($summary['verified_total']); ?></strong>
                </div>
                <div class="admin-stat-card">
                    <span>Dana Pending</span>
                    <strong><?php echo formatRupiah($summary['pending_total']); ?></strong>
                </div>
            </section>

            <section class="admin-menu-grid">
                <a href="<?php echo url_for('admin/kampanye.php'); ?>" class="admin-menu-card">
                    <span>Kampanye</span>
                    <strong>Tambah, ubah, dan hapus kampanye milik pengelola.</strong>
                </a>
                <a href="<?php echo url_for('admin/donasi.php'); ?>" class="admin-menu-card">
                    <span>Verifikasi Donasi</span>
                    <strong>Lihat bukti transfer dan ubah status donasi.</strong>
                </a>
                <a href="<?php echo url_for('admin/metode-pembayaran.php'); ?>" class="admin-menu-card">
                    <span>Metode Pembayaran</span>
                    <strong>Atur nomor VA, rekening, QRIS, dan instruksi pembayaran.</strong>
                </a>
            </section>
        </div>
    </main>

    <?php include_once("../components/footer.php") ?>
</body>
</html>
