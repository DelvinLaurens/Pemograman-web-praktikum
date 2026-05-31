<?php
require_once __DIR__ . "/../components/db_conn.php";
require_once __DIR__ . "/../components/auth.php";
require_once __DIR__ . "/../components/admin_service.php";

requireAdminLogin('admin/detail-kampanye.php');

$admin_id = currentAdminId();
$id_kampanye = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$return_page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$return_page = max(1, $return_page);
$return_query = $return_page > 1 ? '?page=' . $return_page : '';

if (!$id_kampanye) {
    header("Location: " . url_for('admin/kampanye.php' . $return_query));
    exit;
}

$campaign = getManagedCampaignById($conn, $admin_id, $id_kampanye);
$donations = $campaign ? getManagedDonations($conn, $admin_id, '', '', $id_kampanye) : [];
$donation_totals = [
    'PENDING' => ['count' => 0, 'total' => 0],
    'VERIFIED' => ['count' => 0, 'total' => 0],
    'REJECTED' => ['count' => 0, 'total' => 0],
    'EXPIRED' => ['count' => 0, 'total' => 0],
];

foreach ($donations as $donation) {
    $status = (string) ($donation['status'] ?? '');
    if (!isset($donation_totals[$status])) {
        continue;
    }

    $donation_totals[$status]['count']++;
    $donation_totals[$status]['total'] += (float) $donation['nominal_donasi'];
}

$target = $campaign ? (float) $campaign['target_dana'] : 0;
$collected = $campaign ? (float) $campaign['dana_terkumpul'] : 0;
$progress = $target > 0 ? min(100, round(($collected / $target) * 100)) : 0;
$category_label = $campaign ? ucwords(str_replace('_', ' ', (string) $campaign['kategori'])) : '';
$campaign_status = $campaign ? strtolower(trim((string) ($campaign['status'] ?? 'pending'))) : 'pending';
$campaign_status = $campaign_status !== '' ? $campaign_status : 'pending';
$is_campaign_finished = $campaign
    && in_array($campaign_status, ['approved', 'completed'], true)
    && isCampaignClosed($campaign);
$display_status = $is_campaign_finished ? 'completed' : $campaign_status;
$display_status_label = $is_campaign_finished ? 'SELESAI' : strtoupper($campaign_status ?: 'pending');
$deadline_label = '-';
$remaining_label = '-';

if ($campaign && !empty($campaign['batas_waktu'])) {
    $today = new DateTime('today');
    $deadline = new DateTime($campaign['batas_waktu']);
    $deadline_label = $deadline->format('d M Y');

    if ($is_campaign_finished) {
        $remaining_label = 'Selesai';
    } elseif ($deadline < $today) {
        $remaining_label = 'Melewati batas';
    } else {
        $remaining_label = $today->diff($deadline)->days . ' hari lagi';
    }
}

$summary_cards = [
    'VERIFIED' => ['label' => 'Terverifikasi', 'class' => 'status-card-verified'],
    'PENDING' => ['label' => 'Menunggu', 'class' => 'status-card-pending'],
    'REJECTED' => ['label' => 'Ditolak', 'class' => 'status-card-rejected'],
    'EXPIRED' => ['label' => 'Kedaluwarsa', 'class' => 'status-card-expired'],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kampanye - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/form.css?v=4'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/admin.css?v=7'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include_once __DIR__ . "/../components/nav.php" ?>

    <main class="admin-page">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span>Detail Kampanye</span>
                    <h1><?php echo $campaign ? e($campaign['judul_kampanye']) : 'Kampanye Tidak Ditemukan'; ?></h1>
                </div>
                <a href="<?php echo url_for('admin/kampanye.php' . $return_query); ?>" class="admin-secondary-link">Kembali ke Daftar</a>
            </div>

            <?php if (!$campaign): ?>
                <section class="admin-panel">
                    <p class="admin-table-note">Kampanye tidak tersedia atau bukan milik akun ini.</p>
                    <a href="<?php echo url_for('admin/kampanye.php' . $return_query); ?>" class="admin-primary-link">Kembali ke Daftar</a>
                </section>
            <?php else: ?>
                <section class="campaign-detail-grid">
                    <article class="admin-panel campaign-detail-card">
                        <div class="campaign-detail-poster">
                            <img src="<?php echo e(asset_url($campaign['gambar_poster'])); ?>" alt="<?php echo e($campaign['judul_kampanye']); ?>">
                            <span class="status-badge status-<?php echo e($display_status); ?>"><?php echo e($display_status_label); ?></span>
                        </div>
                        <div class="campaign-detail-content">
                            <div class="campaign-detail-meta">
                                <span><?php echo e($category_label); ?></span>
                                <span><?php echo e($campaign['lokasi']); ?></span>
                                <span><?php echo e($remaining_label); ?></span>
                            </div>
                            <h2>Tentang Kampanye</h2>
                            <p class="campaign-detail-description"><?php echo nl2br(e($campaign['deskripsi'])); ?></p>

                            <div class="campaign-info-list">
                                <div>
                                    <span>Kategori</span>
                                    <strong><?php echo e($category_label); ?></strong>
                                </div>
                                <div>
                                    <span>Lokasi</span>
                                    <strong><?php echo e($campaign['lokasi']); ?></strong>
                                </div>
                                <div>
                                    <span>Batas Waktu</span>
                                    <strong><?php echo e($deadline_label); ?></strong>
                                </div>
                            </div>
                        </div>
                    </article>

                    <aside class="admin-panel campaign-detail-side">
                        <div class="admin-panel-head">
                            <div>
                                <h2>Ringkasan Dana</h2>
                                <span>Progress dan tindakan kampanye.</span>
                            </div>
                        </div>

                        <div class="campaign-fund-main">
                            <span>Dana terkumpul</span>
                            <strong><?php echo formatRupiah($collected); ?></strong>
                        </div>

                        <div class="campaign-progress-row">
                            <span>Progress</span>
                            <strong><?php echo (int) $progress; ?>%</strong>
                        </div>
                        <div class="dashboard-progress campaign-detail-progress" aria-label="Progress <?php echo (int) $progress; ?> persen">
                            <span style="width: <?php echo (int) $progress; ?>%;"></span>
                        </div>

                        <div class="campaign-side-facts">
                            <div>
                                <span>Target</span>
                                <strong><?php echo formatRupiah($target); ?></strong>
                            </div>
                            <div>
                                <span>Status waktu</span>
                                <strong><?php echo e($remaining_label); ?></strong>
                            </div>
                        </div>

                        <div class="campaign-detail-actions">
                            <a href="<?php echo url_for('admin/form-kampanye.php?edit=' . (int) $campaign['id_kampanye'] . ($return_page > 1 ? '&page=' . $return_page : '')); ?>" class="admin-primary-link">Edit Kampanye</a>
                            <a href="<?php echo url_for('admin/donasi.php?campaign=' . (int) $campaign['id_kampanye']); ?>" class="admin-secondary-link">Verifikasi Donasi</a>
                        </div>
                    </aside>
                </section>

                <section class="admin-summary-grid campaign-donation-summary">
                    <?php foreach ($summary_cards as $status => $summary_card): ?>
                        <article class="admin-summary-card <?php echo e($summary_card['class']); ?>">
                            <span><?php echo e($summary_card['label']); ?></span>
                            <strong><?php echo formatRupiah($donation_totals[$status]['total']); ?></strong>
                            <p><?php echo (int) $donation_totals[$status]['count']; ?> donasi</p>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="admin-panel">
                    <div class="admin-panel-head">
                        <div>
                            <h2>Donatur Kampanye</h2>
                            <span><?php echo count($donations); ?> data donasi untuk kampanye ini.</span>
                        </div>
                    </div>

                    <div class="verification-table-wrap daftar-donasi-wrap">
                        <table class="verification-table daftar-donasi-table campaign-donor-table">
                            <thead>
                                <tr>
                                    <th>Donatur</th>
                                    <th>Nominal</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Bukti</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($donations)): ?>
                                    <tr class="verification-empty-row"><td colspan="6">Belum ada donatur untuk kampanye ini.</td></tr>
                                <?php endif; ?>

                                <?php foreach ($donations as $donation): ?>
                                    <?php
                                        $status = (string) $donation['status'];
                                        $status_class = preg_replace('/[^a-z0-9_-]/', '', strtolower($status));
                                        $payment = getPaymentMethod($donation['metode_pembayaran']);
                                        $proof_path = trim((string) ($donation['bukti_transfer'] ?? ''));
                                        if ($proof_path !== '' && strpos($proof_path, 'assets/') === false) {
                                            $proof_path = 'assets/uploads/bukti-transfer/' . $proof_path;
                                        }
                                    ?>
                                    <tr class="<?php echo $status === 'PENDING' ? 'verification-row-pending daftar-donasi-row-pending' : 'daftar-donasi-row'; ?>">
                                        <td class="verification-donor">
                                            <strong><?php echo e($donation['nama_lengkap']); ?></strong>
                                            <span class="verification-email"><?php echo e($donation['email']); ?></span>
                                        </td>
                                        <td><strong class="verification-amount"><?php echo formatRupiah($donation['nominal_donasi']); ?></strong></td>
                                        <td><?php echo e($payment['label'] ?? $donation['metode_pembayaran']); ?></td>
                                        <td><span class="verification-status-badge status-<?php echo e($status_class); ?>"><?php echo e(ucfirst(strtolower($status))); ?></span></td>
                                        <td>
                                            <?php if ($proof_path !== ''): ?>
                                                <a class="verification-proof-link" href="<?php echo e(asset_url($proof_path)); ?>" target="_blank" rel="noopener">Lihat Bukti</a>
                                            <?php else: ?>
                                                <span class="verification-muted">Belum upload</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($donation['waktu_donasi']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <?php include_once __DIR__ . "/../components/footer.php" ?>
</body>
</html>
