<?php
require_once __DIR__ . "/../components/db_conn.php";
require_once __DIR__ . "/../components/auth.php";
require_once __DIR__ . "/../components/admin_service.php";

$conn = $GLOBALS['conn'] ?? null;

/** @var mysqli|null $conn */
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Koneksi database belum tersedia.");
}

requireAdminLogin('admin/kampanye.php');

$admin_id = currentAdminId();
$errors = [];
$success = "";
$items_per_page = 10;
$current_page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$current_page = max(1, $current_page);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $redirect_page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: $current_page;
    $redirect_page = max(1, $redirect_page);
    $page_query = $redirect_page > 1 ? '&page=' . $redirect_page : '';

    if ($action === 'delete') {
        $id_kampanye = filter_input(INPUT_POST, 'id_kampanye', FILTER_VALIDATE_INT);
        $result = deleteManagedCampaign($conn, $admin_id, $id_kampanye);

        if ($result['success']) {
            header("Location: " . url_for('admin/kampanye.php?deleted=1' . $page_query));
            exit;
        }

        $errors = $result['errors'];
    }

    if ($action === 'approve') {
        $id_kampanye = filter_input(INPUT_POST, 'id_kampanye', FILTER_VALIDATE_INT);
        $result = updateManagedCampaignStatus($conn, $admin_id, $id_kampanye, 'approved');

        if ($result['success']) {
            header("Location: " . url_for('admin/kampanye.php?status=1' . $page_query));
            exit;
        }

        $errors = $result['errors'];
    }

    if ($action === 'reject') {
        $id_kampanye = filter_input(INPUT_POST, 'id_kampanye', FILTER_VALIDATE_INT);
        $result = updateManagedCampaignStatus($conn, $admin_id, $id_kampanye, 'rejected');

        if ($result['success']) {
            header("Location: " . url_for('admin/kampanye.php?status=1' . $page_query));
            exit;
        }

        $errors = $result['errors'];
    }
}

if (isset($_GET['saved'])) {
    $success = "Kampanye berhasil disimpan.";
}

if (isset($_GET['deleted'])) {
    $success = "Kampanye berhasil dihapus.";
}

if (isset($_GET['status'])) {
    $success = "Status kampanye berhasil diperbarui.";
}

$total_campaigns = countManagedCampaigns($conn, $admin_id);
$total_pages = max(1, (int) ceil($total_campaigns / $items_per_page));
if ($current_page > $total_pages) {
    $current_page = $total_pages;
}
$offset = ($current_page - 1) * $items_per_page;
$campaigns = getManagedCampaigns($conn, $admin_id, $items_per_page, $offset);
$current_page_query = $current_page > 1 ? '?page=' . $current_page : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kampanye - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/form.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/admin.css?v=1'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include_once __DIR__ . "/../components/nav.php" ?>

    <main class="admin-page">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span>Pengelolaan Kampanye</span>
                    <h1>Daftar Kampanye</h1>
                </div>
                <a href="<?php echo url_for('admin/form-kampanye.php'); ?>" class="admin-primary-link">Tambah Kampanye</a>
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

            <section class="admin-panel">
                <div class="admin-panel-head">
                    <div>
                        <h2>Kampanye Saya</h2>
                        <span>Menampilkan halaman <?php echo (int) $current_page; ?> dari <?php echo (int) $total_pages; ?>, total <?php echo (int) $total_campaigns; ?> kampanye.</span>
                    </div>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Dana</th>
                                <th>Batas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($campaigns)): ?>
                                <tr><td colspan="5">Belum ada kampanye.</td></tr>
                            <?php endif; ?>

                            <?php foreach ($campaigns as $campaign): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($campaign['judul_kampanye']); ?></strong>
                                        <span><?php echo e($campaign['kategori']); ?></span>
                                    </td>
                                    <td><?php echo formatRupiah($campaign['dana_terkumpul']); ?></td>
                                    <td><?php echo e($campaign['batas_waktu']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($campaign['status'] ?? 'pending'); ?>">
                                            <?php echo strtoupper($campaign['status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                    <td class="admin-actions">
                                        <?php if (($campaign['status'] ?? 'pending') === 'pending'): ?>
                                            <form method="POST" action="<?php echo url_for('admin/kampanye.php' . $current_page_query); ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="id_kampanye" value="<?php echo (int) $campaign['id_kampanye']; ?>">
                                                <button type="submit" value="approve" onclick="return confirm('Setujui kampanye ini untuk dipublikasikan?');">Setuju</button>
                                            </form>

                                            <form method="POST" action="<?php echo url_for('admin/kampanye.php' . $current_page_query); ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="id_kampanye" value="<?php echo (int) $campaign['id_kampanye']; ?>">
                                                <button type="submit" value="reject" onclick="return confirm('Tolak pengajuan kampanye ini?');">Tolak</button>
                                            </form>
                                        <?php endif; ?>

                                        <a href="<?php echo url_for('admin/form-kampanye.php?edit=' . (int) $campaign['id_kampanye'] . ($current_page > 1 ? '&page=' . $current_page : '')); ?>">Edit</a>
                                        <form method="POST" action="<?php echo url_for('admin/kampanye.php' . $current_page_query); ?>" onsubmit="return confirm('Hapus kampanye ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_kampanye" value="<?php echo (int) $campaign['id_kampanye']; ?>">
                                            <button type="submit" <?php echo (float) $campaign['dana_terkumpul'] >= 10000 ? 'disabled' : ''; ?>>Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav class="admin-pagination" aria-label="Pagination kampanye">
                        <a class="<?php echo $current_page <= 1 ? 'is-disabled' : ''; ?>" href="<?php echo url_for('admin/kampanye.php' . ($current_page > 2 ? '?page=' . ($current_page - 1) : '')); ?>">Sebelumnya</a>

                        <?php for ($page_number = 1; $page_number <= $total_pages; $page_number++): ?>
                            <a class="<?php echo $page_number === $current_page ? 'is-active' : ''; ?>" href="<?php echo url_for('admin/kampanye.php' . ($page_number > 1 ? '?page=' . $page_number : '')); ?>">
                                <?php echo (int) $page_number; ?>
                            </a>
                        <?php endfor; ?>

                        <a class="<?php echo $current_page >= $total_pages ? 'is-disabled' : ''; ?>" href="<?php echo url_for('admin/kampanye.php?page=' . min($total_pages, $current_page + 1)); ?>">Selanjutnya</a>
                    </nav>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <?php include_once __DIR__ . "/../components/footer.php" ?>
</body>
</html>
