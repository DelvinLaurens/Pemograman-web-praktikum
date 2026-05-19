<?php
require_once("../components/db_conn.php");
require_once("../components/auth.php");
require_once("../components/admin_service.php");

requireAdminLogin('admin/kampanye.php');

$admin_id = currentAdminId();
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id_kampanye = filter_input(INPUT_POST, 'id_kampanye', FILTER_VALIDATE_INT) ?: null;
        $result = saveCampaign($conn, $admin_id, $_POST, $_FILES['gambar_poster'] ?? null, $id_kampanye);

        if ($result['success']) {
            header("Location: " . url_for('admin/kampanye.php?saved=1'));
            exit;
        }

        $errors = $result['errors'];
    }

    if ($action === 'delete') {
        $id_kampanye = filter_input(INPUT_POST, 'id_kampanye', FILTER_VALIDATE_INT);
        $result = deleteManagedCampaign($conn, $admin_id, $id_kampanye);

        if ($result['success']) {
            header("Location: " . url_for('admin/kampanye.php?deleted=1'));
            exit;
        }

        $errors = $result['errors'];
    }

    if ($action === 'approve') {
        $id_kampanye = filter_input(INPUT_POST, 'id_kampanye', FILTER_VALIDATE_INT);
        $stmt = $conn->prepare("UPDATE kampanye SET status = 'approved' WHERE id_kampanye = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_kampanye);
            if ($stmt->execute()) {
                header("Location: " . url_for('admin/kampanye.php?saved=1'));
                exit;
            }
        }
    }

    if ($action === 'reject') {
        $id_kampanye = filter_input(INPUT_POST, 'id_kampanye', FILTER_VALIDATE_INT);
        $stmt = $conn->prepare("UPDATE kampanye SET status = 'rejected' WHERE id_kampanye = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_kampanye);
            if ($stmt->execute()) {
                header("Location: " . url_for('admin/kampanye.php?saved=1'));
                exit;
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $success = "Kampanye berhasil disimpan.";
}

if (isset($_GET['deleted'])) {
    $success = "Kampanye berhasil dihapus.";
}

$edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editing = $edit_id ? getManagedCampaignById($conn, $admin_id, $edit_id) : null;
$campaigns = getManagedCampaigns($conn, $admin_id);
$form_source = !empty($errors) ? $_POST : ($editing ?: []);
$kategori_options = [
    'bencana_alam' => 'Bencana Alam',
    'pendidikan' => 'Pendidikan',
    'kesehatan' => 'Kesehatan',
    'lingkungan' => 'Lingkungan',
    'sosial' => 'Sosial',
    'pembangunan' => 'Pembangunan',
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kampanye - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/form.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/admin.css?v=1'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include_once("../components/nav.php") ?>

    <main class="admin-page">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span>Pengelolaan Kampanye</span>
                    <h1><?php echo $editing ? 'Edit Kampanye' : 'Tambah Kampanye'; ?></h1>
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

            <section class="admin-layout">
                <form method="POST" action="<?php echo url_for('admin/kampanye.php'); ?>" enctype="multipart/form-data" class="admin-panel form-donasi">
                    <input type="hidden" name="action" value="save">
                    <?php if ($editing): ?>
                        <input type="hidden" name="id_kampanye" value="<?php echo (int) $editing['id_kampanye']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="judul_kampanye">Judul Kampanye<span class="required">*</span></label>
                        <input type="text" id="judul_kampanye" name="judul_kampanye" value="<?php echo e($form_source['judul_kampanye'] ?? ''); ?>" required>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="kategori">Kategori<span class="required">*</span></label>
                            <select id="kategori" name="kategori" required>
                                <option value="">Pilih kategori</option>
                                <?php foreach ($kategori_options as $value => $label): ?>
                                    <option value="<?php echo e($value); ?>" <?php echo ($form_source['kategori'] ?? '') === $value ? 'selected' : ''; ?>>
                                        <?php echo e($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="lokasi">Lokasi<span class="required">*</span></label>
                            <input type="text" id="lokasi" name="lokasi" value="<?php echo e($form_source['lokasi'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="target_dana">Target Dana<span class="required">*</span></label>
                            <input type="number" id="target_dana" name="target_dana" min="10000" value="<?php echo e($form_source['target_dana'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="batas_waktu">Batas Waktu<span class="required">*</span></label>
                            <input type="date" id="batas_waktu" name="batas_waktu" value="<?php echo e($form_source['batas_waktu'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi<span class="required">*</span></label>
                        <textarea id="deskripsi" name="deskripsi" rows="5" required><?php echo e($form_source['deskripsi'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="gambar_poster">Poster Kampanye<?php echo $editing ? '' : '<span class="required">*</span>'; ?></label>
                        <input type="file" id="gambar_poster" name="gambar_poster" accept=".jpg,.jpeg,.png" class="input-file" <?php echo $editing ? '' : 'required'; ?>>
                        <small>JPG/PNG maksimal 2MB. Saat edit, kosongkan jika tidak ingin mengganti poster.</small>
                    </div>

                    <button type="submit" class="btn-submit-form"><?php echo $editing ? 'Simpan Perubahan' : 'Tambah Kampanye'; ?></button>
                    <?php if ($editing): ?>
                        <a href="<?php echo url_for('admin/kampanye.php'); ?>" class="admin-cancel-link">Batal Edit</a>
                    <?php endif; ?>
                </form>

                <div class="admin-panel">
                    <h2>Daftar Kampanye</h2>
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
                                    <tr><td colspan="4">Belum ada kampanye.</td></tr>
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
                                                <form method="POST" action="<?php echo url_for('admin/kampanye.php'); ?>" style="display:inline;">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="id_kampanye" value="<?php echo (int) $campaign['id_kampanye']; ?>">
                                                    <button type="submit" style="background-color: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;" onclick="return confirm('Setujui kampanye ini untuk dipublikasikan?');">Setuju</button>
                                                </form>

                                                <form method="POST" action="<?php echo url_for('admin/kampanye.php'); ?>" style="display:inline;">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="id_kampanye" value="<?php echo (int) $campaign['id_kampanye']; ?>">
                                                    <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;" onclick="return confirm('Tolak pengajuan kampanye ini?');">Tolak</button>
                                                </form>
                                            <?php endif; ?>

                                            <a href="<?php echo url_for('admin/kampanye.php?edit=' . (int) $campaign['id_kampanye']); ?>">Edit</a>
                                            <form method="POST" action="<?php echo url_for('admin/kampanye.php'); ?>" onsubmit="return confirm('Hapus kampanye ini?');">
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
                </div>
            </section>
        </div>
    </main>

    <?php include_once("../components/footer.php") ?>
</body>
</html>
