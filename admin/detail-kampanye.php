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

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'verify' || $action === 'reject') {
        $id_donasi = filter_input(INPUT_POST, 'id_donasi', FILTER_VALIDATE_INT);
        $new_status = $action === 'verify' ? 'VERIFIED' : 'REJECTED';
        $result = updateDonationVerificationStatus($conn, $admin_id, $id_donasi, $new_status);
        if ($result['success']) {
            $success = "Status donasi berhasil diperbarui.";
        } else {
            $errors = $result['errors'];
        }
    } else if ($action === 'close_campaign') {
        $result = updateManagedCampaignStatus($conn, $admin_id, $id_kampanye, 'completed');
        if ($result['success']) {
            $success = "Kampanye berhasil ditutup.";
        } else {
            $errors = $result['errors'];
        }
    } else if ($action === 'open_campaign') {
        $new_date = $_POST['new_batas_waktu'] ?? '';
        if (empty($new_date)) {
            $errors[] = "Tanggal batas waktu baru harus diisi.";
        } else {
            $result = updateManagedCampaignStatus($conn, $admin_id, $id_kampanye, 'active', $new_date);
            if ($result['success']) {
                $success = "Kampanye berhasil dibuka kembali.";
            } else {
                $errors = $result['errors'];
            }
        }
    }
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
$collected = $campaign ? (float) $donation_totals['VERIFIED']['total'] : 0;
$progress = $target > 0 ? min(100, round(($collected / $target) * 100)) : 0;
$category_label = $campaign ? ucwords(str_replace('_', ' ', (string) $campaign['kategori'])) : '';
$campaign_status = $campaign ? strtolower(trim((string) ($campaign['status'] ?? 'active'))) : 'active';
$campaign_status = $campaign_status !== '' ? $campaign_status : 'active';
$is_campaign_finished = $campaign
    && in_array($campaign_status, ['completed'], true)
    && isCampaignClosed($campaign);
$display_status = $is_campaign_finished ? 'completed' : $campaign_status;
$display_status_label = $is_campaign_finished ? 'SELESAI' : strtoupper($campaign_status ?: 'active');
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
            <?php if (!empty($errors)): ?>
                <div class="pesan-error" style="margin-bottom: 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="pesan-sukses-inline" style="margin-bottom: 1rem;">
                    <p><?php echo e($success); ?></p>
                </div>
            <?php endif; ?>

            <div class="admin-heading">
                <div>
                    <span>Detail Kampanye</span>
                    <h1><?php echo $campaign ? e($campaign['judul_kampanye']) : 'Kampanye Tidak Ditemukan'; ?></h1>
                </div>
                <?php if ($campaign): ?>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <a href="<?php echo url_for('admin/form-kampanye.php?edit=' . (int) $campaign['id_kampanye'] . ($return_page > 1 ? '&page=' . $return_page : '')); ?>" class="admin-primary-link">Edit Kampanye</a>
                        <?php if ($campaign_status === 'active'): ?>
                            <form method="POST" action="" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menutup kampanye ini?');">
                                <input type="hidden" name="action" value="close_campaign">
                                <button type="submit" style="background: #EF4444; color: white; border: none; padding: 0.6rem 1.25rem; border-radius: 6px; cursor: pointer; font-weight: 500; font-family: 'Poppins', sans-serif;">Tutup Kampanye</button>
                            </form>
                        <?php elseif ($campaign_status === 'completed'): ?>
                            <?php
                                $can_reopen = true;
                                if (!empty($campaign['batas_waktu'])) {
                                    $now = new DateTime('today');
                                    $deadline = new DateTime($campaign['batas_waktu']);
                                    if ($deadline < $now && $now->diff($deadline)->days > 3) {
                                        $can_reopen = false;
                                    }
                                }
                            ?>
                            <?php if ($can_reopen): ?>
                                <button type="button" onclick="document.getElementById('reopenModal').style.display='block'" style="background: #F59E0B; color: white; border: none; padding: 0.6rem 1.25rem; border-radius: 6px; cursor: pointer; font-weight: 500; font-family: 'Poppins', sans-serif;">Buka Kampanye</button>
                            <?php else: ?>
                                <span style="background: #e2e8f0; color: #64748b; padding: 0.6rem 1.25rem; border-radius: 6px; font-weight: 500; font-size: 0.875rem;">Mati (> 3 Hari)</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($donations)): ?>
                                    <tr class="verification-empty-row"><td colspan="7">Belum ada donatur untuk kampanye ini.</td></tr>
                                <?php endif; ?>

                                <?php foreach ($donations as $donation): ?>
                                    <?php
                                        $status = (string) $donation['status'];
                                        $is_pending = $status === 'PENDING';
                                        $has_proof = !empty($donation['bukti_transfer']);
                                        $can_accept = $has_proof && $status !== 'VERIFIED';
                                        $can_reject = $status !== 'REJECTED';
                                        $status_class = preg_replace('/[^a-z0-9_-]/', '', strtolower($status));
                                        
                                        $status_label = ucfirst(strtolower($status));
                                        if ($is_pending) {
                                            if ($has_proof) {
                                                $status_label = 'Perlu Verifikasi';
                                                $status_class = 'pending';
                                            } else {
                                                $status_label = 'Belum Bayar';
                                                $status_class = 'expired';
                                            }
                                        }

                                        $row_class = 'daftar-donasi-row';
                                        if ($is_pending && $has_proof) {
                                            $row_class = 'verification-row-pending daftar-donasi-row-pending';
                                        } else if ($is_pending && !$has_proof) {
                                            $row_class = 'verification-row-muted daftar-donasi-row-muted';
                                        }

                                        $payment = getPaymentMethod($donation['metode_pembayaran'], $campaign['id_penyelenggara']);
                                        $proof_path = trim((string) ($donation['bukti_transfer'] ?? ''));
                                        if ($proof_path !== '' && strpos($proof_path, 'assets/') === false) {
                                            $proof_path = 'assets/uploads/bukti-transfer/' . $proof_path;
                                        }
                                    ?>
                                    <tr class="<?php echo $row_class; ?>" <?php echo ($is_pending && !$has_proof) ? 'style="opacity: 0.6;"' : ''; ?>>
                                        <td class="verification-donor">
                                            <strong><?php echo e($donation['nama_lengkap']); ?></strong>
                                            <span class="verification-email"><?php echo e($donation['email']); ?></span>
                                        </td>
                                        <td><strong class="verification-amount"><?php echo formatRupiah($donation['nominal_donasi']); ?></strong></td>
                                        <td><?php echo e($payment['label'] ?? $donation['metode_pembayaran']); ?></td>
                                        <td><span class="verification-status-badge status-<?php echo e($status_class); ?>" <?php echo ($is_pending && !$has_proof) ? 'style="background: #e2e8f0; color: #64748b;"' : ''; ?>><?php echo e($status_label); ?></span></td>
                                        <td>
                                            <?php if ($proof_path !== ''): ?>
                                                <a class="verification-proof-link" href="<?php echo e(asset_url($proof_path)); ?>" target="_blank" rel="noopener">Lihat Bukti</a>
                                            <?php else: ?>
                                                <span class="verification-muted">Belum upload</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($donation['waktu_donasi']); ?></td>
                                        <td>
                                            <form method="POST" action="" class="verification-actions" style="margin: 0; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                <input type="hidden" name="id_donasi" value="<?php echo (int) $donation['id_donasi']; ?>">
                                                <button class="verification-btn verification-btn-accept" type="submit" name="action" value="verify" <?php echo $can_accept ? '' : 'disabled'; ?> style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                                    Terima
                                                </button>
                                                <button class="verification-btn verification-btn-reject" type="submit" name="action" value="reject" <?php echo $can_reject ? '' : 'disabled'; ?> style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
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
            <?php endif; ?>
        </div>
    </main>

    <?php include_once __DIR__ . "/../components/footer.php" ?>

    <div id="reopenModal" style="display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: #fefefe; margin: 15% auto; padding: 2rem; border-radius: 8px; width: 90%; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin-top:0; margin-bottom: 1rem; font-size: 1.25rem;">Buka Kembali Kampanye</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="open_campaign">
                <div style="margin-bottom: 1.5rem;">
                    <label for="new_batas_waktu" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Batas Waktu Baru</label>
                    <input type="date" id="new_batas_waktu" name="new_batas_waktu" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('reopenModal').style.display='none'" style="background: transparent; color: #64748b; border: 1px solid #cbd5e1; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">Batal</button>
                    <button type="submit" style="background: #F59E0B; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
