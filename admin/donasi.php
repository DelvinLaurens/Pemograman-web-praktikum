<?php
require_once("../components/db_conn.php");
require_once("../components/auth.php");
require_once("../components/admin_service.php");

requireAdminLogin('admin/donasi.php');

$admin_id = currentAdminId();
$errors = [];
$success = "";

$status_filter = trim($_GET['status'] ?? '');
$allowed_status = ['', 'PENDING', 'VERIFIED', 'REJECTED'];
if (!in_array($status_filter, $allowed_status, true)) {
    $status_filter = '';
}

$search_query = trim($_GET['q'] ?? '');
$campaign_filter = filter_input(INPUT_GET, 'campaign', FILTER_VALIDATE_INT);
$campaign_filter = $campaign_filter ? (int) $campaign_filter : 0;
if ($campaign_filter < 0) {
    $campaign_filter = 0;
}

$filter_params = [];
if ($search_query !== '') {
    $filter_params['q'] = $search_query;
}
if ($status_filter !== '') {
    $filter_params['status'] = $status_filter;
}
if ($campaign_filter > 0) {
    $filter_params['campaign'] = $campaign_filter;
}

$filter_query = http_build_query($filter_params);
$form_action = url_for('admin/donasi.php' . ($filter_query !== '' ? '?' . $filter_query : ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_donasi = filter_input(INPUT_POST, 'id_donasi', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    $new_status = $action === 'verify' ? 'VERIFIED' : ($action === 'reject' ? 'REJECTED' : '');
    $result = updateDonationVerificationStatus($conn, $admin_id, $id_donasi, $new_status);

    if ($result['success']) {
        $redirect_params = $filter_params;
        $redirect_params['updated'] = '1';
        header("Location: " . url_for('admin/donasi.php?' . http_build_query($redirect_params)));
        exit;
    }

    $errors = $result['errors'];
}

if (isset($_GET['updated'])) {
    $success = "Status donasi berhasil diperbarui.";
}

$summary = getAdminSummary($conn, $admin_id);
$campaign_options = getManagedCampaigns($conn, $admin_id);
$donations = getManagedDonations($conn, $admin_id, $status_filter, $search_query, $campaign_filter);
$active_filter_count = ($search_query !== '' ? 1 : 0) + ($status_filter !== '' ? 1 : 0) + ($campaign_filter > 0 ? 1 : 0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Donasi - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/form.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/admin.css?v=4'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include_once("../components/nav.php") ?>

    <main class="admin-page verification-page">
        <div class="container">
            <div class="admin-heading verification-heading">
                <div>
                    <span>Admin Crowdfunding</span>
                    <h1>Verifikasi Donasi</h1>
                    <p>Antrian pembayaran dan bukti transfer donasi campaign.</p>
                </div>
                <a href="<?php echo url_for('admin/dashboard.php'); ?>" class="admin-secondary-link">Dashboard</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="pesan-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="pesan-sukses-inline">
                    <p><?php echo e($success); ?></p>
                </div>
            <?php endif; ?>

            <section class="verification-stats" aria-label="Ringkasan donasi">
                <article class="verification-stat-card stat-total">
                    <span class="verification-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 7.5C4 5.6 7.6 4 12 4s8 1.6 8 3.5S16.4 11 12 11 4 9.4 4 7.5Z"></path>
                            <path d="M4 7.5v4c0 1.9 3.6 3.5 8 3.5s8-1.6 8-3.5v-4"></path>
                            <path d="M4 11.5v4C4 17.4 7.6 19 12 19s8-1.6 8-3.5v-4"></path>
                        </svg>
                    </span>
                    <div>
                        <span>Total Dana Terkumpul</span>
                        <strong><?php echo formatRupiah($summary['collected_total']); ?></strong>
                    </div>
                </article>

                <article class="verification-stat-card stat-pending">
                    <span class="verification-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 6v6l4 2"></path>
                            <path d="M21 12a9 9 0 1 1-3.1-6.8"></path>
                            <path d="M21 4v5h-5"></path>
                        </svg>
                    </span>
                    <div>
                        <span>Total Dana Pending</span>
                        <strong><?php echo formatRupiah($summary['pending_total']); ?></strong>
                    </div>
                </article>

                <article class="verification-stat-card stat-verified">
                    <span class="verification-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 6 9 17l-5-5"></path>
                        </svg>
                    </span>
                    <div>
                        <span>Total Dana Verified</span>
                        <strong><?php echo formatRupiah($summary['verified_total']); ?></strong>
                    </div>
                </article>

                <article class="verification-stat-card stat-campaign">
                    <span class="verification-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 5h14v5H5z"></path>
                            <path d="M5 14h6v5H5z"></path>
                            <path d="M15 14h4v5h-4z"></path>
                        </svg>
                    </span>
                    <div>
                        <span>Total Campaign</span>
                        <strong><?php echo (int) $summary['kampanye']; ?></strong>
                    </div>
                </article>
            </section>

            <section class="verification-filter-panel">
                <div class="verification-filter-head">
                    <div>
                        <h2>Filter Donasi</h2>
                        <span><?php echo count($donations); ?> data ditemukan<?php echo $active_filter_count > 0 ? " dari " . (int) $active_filter_count . " filter aktif" : ""; ?></span>
                    </div>
                    <?php if ($summary['pending'] > 0): ?>
                        <strong><?php echo (int) $summary['pending']; ?> pending</strong>
                    <?php endif; ?>
                </div>

                <form method="GET" action="<?php echo url_for('admin/donasi.php'); ?>" class="verification-filter">
                    <label class="verification-field verification-search-field" for="donation-search">
                        <span>Search Donatur</span>
                        <input
                            type="search"
                            name="q"
                            id="donation-search"
                            value="<?php echo e($search_query); ?>"
                            placeholder="Cari nama atau email"
                        >
                    </label>

                    <label class="verification-field" for="status-filter">
                        <span>Status</span>
                        <select name="status" id="status-filter">
                            <option value="">Semua</option>
                            <?php foreach (['PENDING', 'VERIFIED', 'REJECTED'] as $status): ?>
                                <option value="<?php echo e($status); ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                                    <?php echo e(ucfirst(strtolower($status))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="verification-field" for="campaign-filter">
                        <span>Campaign</span>
                        <select name="campaign" id="campaign-filter">
                            <option value="">Semua Campaign</option>
                            <?php foreach ($campaign_options as $campaign): ?>
                                <option value="<?php echo (int) $campaign['id_kampanye']; ?>" <?php echo $campaign_filter === (int) $campaign['id_kampanye'] ? 'selected' : ''; ?>>
                                    <?php echo e($campaign['judul_kampanye']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <div class="verification-filter-actions">
                        <button type="submit">Find</button>
                        <a href="<?php echo url_for('admin/donasi.php'); ?>">Reset</a>
                    </div>
                </form>
            </section>

            <section class="verification-table-panel daftar-donasi-panel">
                <div class="verification-table-head daftar-donasi-head">
                    <div>
                        <h2>Daftar Donasi</h2>
                        <span>Antrian verifikasi pembayaran masuk.</span>
                    </div>
                </div>

                <div class="verification-table-wrap daftar-donasi-wrap">
                    <table class="verification-table daftar-donasi-table">
                        <thead>
                            <tr>
                                <th>Donatur</th>
                                <th>Email</th>
                                <th>Campaign</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                <th>Bukti Transfer</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($donations)): ?>
                                <tr class="verification-empty-row daftar-donasi-empty-row">
                                    <td colspan="7">Belum ada donasi sesuai filter.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($donations as $donasi): ?>
                                <?php
                                    $status = (string) $donasi['status'];
                                    $status_class = preg_replace('/[^a-z0-9_-]/', '', strtolower($status));
                                    $is_pending = $status === 'PENDING';
                                    $has_proof = !empty($donasi['bukti_transfer']);
                                    $can_accept = $has_proof && $status !== 'VERIFIED';
                                    $can_reject = $status !== 'REJECTED';
                                ?>
                                <tr class="<?php echo $is_pending ? 'verification-row-pending daftar-donasi-row-pending' : 'daftar-donasi-row'; ?>">
                                    <td class="verification-donor">
                                        <strong><?php echo e($donasi['nama_lengkap']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="verification-email"><?php echo e($donasi['email']); ?></span>
                                    </td>
                                    <td>
                                        <strong class="verification-campaign"><?php echo e($donasi['judul_kampanye']); ?></strong>
                                    </td>
                                    <td>
                                        <strong class="verification-amount"><?php echo formatRupiah($donasi['nominal_donasi']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="verification-status-badge status-<?php echo e($status_class); ?>">
                                            <?php echo e(ucfirst(strtolower($status))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($has_proof): ?>
                                            <a class="verification-proof-link" href="<?php echo e(asset_url($donasi['bukti_transfer'])); ?>" target="_blank" rel="noopener">
                                                Lihat Bukti
                                            </a>
                                        <?php else: ?>
                                            <span class="verification-muted">Belum upload</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="<?php echo e($form_action); ?>" class="verification-actions">
                                            <input type="hidden" name="id_donasi" value="<?php echo (int) $donasi['id_donasi']; ?>">
                                            <button class="verification-btn verification-btn-accept" type="submit" name="action" value="verify" <?php echo $can_accept ? '' : 'disabled'; ?>>
                                                Terima
                                            </button>
                                            <button class="verification-btn verification-btn-reject" type="submit" name="action" value="reject" <?php echo $can_reject ? '' : 'disabled'; ?>>
                                                Tolak
                                            </button>
                                        </form>
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
</body>
</html>
