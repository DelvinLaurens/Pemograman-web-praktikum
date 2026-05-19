<?php
require_once("../components/db_conn.php");
require_once("../components/auth.php");
require_once("../components/donation_helper.php");

requireDonorLogin('pages/riwayat-donasi.php');

$id_donatur = currentDonorId();
$summary = [
    'VERIFIED' => ['total' => 0, 'count' => 0],
    'PENDING' => ['total' => 0, 'count' => 0],
    'REJECTED' => ['total' => 0, 'count' => 0],
    'EXPIRED' => ['total' => 0, 'count' => 0],
];

$stmt = mysqli_prepare(
    $conn,
    "SELECT status, COUNT(*) AS total_donasi, COALESCE(SUM(nominal_donasi), 0) AS total_nominal
     FROM donasi
     WHERE id_donatur = ?
     GROUP BY status"
);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id_donatur);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = $result ? mysqli_fetch_assoc($result) : null) {
        if (!$row) {
            break;
        }
        $summary[$row['status']] = [
            'total' => (float) $row['total_nominal'],
            'count' => (int) $row['total_donasi'],
        ];
    }
    mysqli_stmt_close($stmt);
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT d.*, k.judul_kampanye, k.gambar_poster
     FROM donasi d
     INNER JOIN kampanye k ON k.id_kampanye = d.id_kampanye
     WHERE d.id_donatur = ?
     ORDER BY d.waktu_donasi DESC"
);

$donations = [];
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id_donatur);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = $result ? mysqli_fetch_assoc($result) : null) {
        if (!$row) {
            break;
        }
        $donations[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Donasi - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/form.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/admin.css?v=4'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include_once("../components/nav.php") ?>

    <main class="admin-page">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span>Riwayat Donasi</span>
                    <h1>Donasi Saya</h1>
                </div>
                <a href="<?php echo url_for('index.php#kampanye'); ?>" class="admin-primary-link">Donasi Lagi</a>
            </div>

            <section class="admin-stats">
                <?php foreach (['VERIFIED', 'PENDING', 'REJECTED', 'EXPIRED'] as $status): ?>
                    <div class="admin-stat-card">
                        <span><?php echo e($status); ?></span>
                        <strong><?php echo formatRupiah($summary[$status]['total']); ?></strong>
                        <p><?php echo (int) $summary[$status]['count']; ?> donasi</p>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="admin-panel">
                <h2>Daftar Riwayat</h2>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Kampanye</th>
                                <th>Nominal</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($donations)): ?>
                                <tr><td colspan="5">Belum ada riwayat donasi.</td></tr>
                            <?php endif; ?>

                            <?php foreach ($donations as $donasi): ?>
                                <?php $payment = getPaymentMethod($donasi['metode_pembayaran']); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($donasi['judul_kampanye']); ?></strong>
                                        <span><?php echo e($donasi['waktu_donasi']); ?></span>
                                    </td>
                                    <td><?php echo formatRupiah($donasi['nominal_donasi']); ?></td>
                                    <td><?php echo e($payment['label'] ?? $donasi['metode_pembayaran']); ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower(e($donasi['status'])); ?>"><?php echo e($donasi['status']); ?></span></td>
                                    <td>
                                        <?php if (!empty($donasi['bukti_transfer'])): ?>
                                            <?php 
                                                $nama_file = $donasi['bukti_transfer'];
                                                if (strpos($nama_file, 'assets/') === false) {
                                                    $full_path = 'assets/uploads/bukti-transfer/' . $nama_file;
                                                } else {
                                                    $full_path = $nama_file;
                                                }
                                            ?>
                                            <a href="javascript:void(0);" 
                                               data-img-src="<?php echo asset_url($full_path); ?>" 
                                               onclick="openPreviewModal(this)">
                                                Lihat Bukti
                                            </a>
                                        <?php elseif ($donasi['status'] === 'PENDING'): ?>
                                            <a href="<?php echo url_for('pages/verif.php?id=' . (int) $donasi['id_donasi']); ?>">Upload Bukti</a>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <?php include_once("../components/footer.php") ?>

    <div id="transferPreviewModal" class="proof-modal-overlay">
        <span class="proof-modal-close" onclick="closePreviewModal()">&times;</span>
        <div class="proof-modal-content">
            <img id="imgTargetPreview" src="" alt="Struk Bukti Transfer">
        </div>
    </div>

    <script>
    function openPreviewModal(element) {
        var imgSrc = element.getAttribute('data-img-src');
        var modal = document.getElementById('transferPreviewModal');
        var modalImg = document.getElementById('imgTargetPreview');

        modal.style.display = "block";
        modalImg.src = imgSrc;
        document.body.style.overflow = "hidden";
    }

    function closePreviewModal() {
        var modal = document.getElementById('transferPreviewModal');
        modal.style.display = "none";
        document.body.style.overflow = "auto";
    }

    window.onclick = function(event) {
        var modal = document.getElementById('transferPreviewModal');
        if (event.target == modal) {
            closePreviewModal();
        }
    }
    </script>
</body>
</html>