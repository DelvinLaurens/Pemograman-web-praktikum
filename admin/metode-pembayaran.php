<?php
require_once("../components/db_conn.php");
require_once("../components/auth.php");
require_once("../components/admin_service.php");

requireAdminLogin('admin/metode-pembayaran.php');

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_metode = filter_input(INPUT_POST, 'id_metode', FILTER_VALIDATE_INT) ?: null;
    $result = savePaymentMethodRow($conn, $_POST, $_FILES['gambar_file'] ?? null, $id_metode);

    if ($result['success']) {
        header("Location: " . url_for('admin/metode-pembayaran.php?saved=1'));
        exit;
    }

    $errors = $result['errors'];
}

if (isset($_GET['saved'])) {
    $success = "Metode pembayaran berhasil disimpan.";
}

$edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editing = $edit_id ? getPaymentMethodRowById($conn, $edit_id) : null;
$rows = getPaymentMethodRows($conn);
$form_source = !empty($errors) ? $_POST : ($editing ?: ['aktif' => 1]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metode Pembayaran - DemiSesama</title>
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
                    <span>Pengaturan Pembayaran</span>
                    <h1><?php echo $editing ? 'Edit Metode' : 'Tambah Metode'; ?></h1>
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
                <form method="POST" action="<?php echo url_for('admin/metode-pembayaran.php'); ?>" enctype="multipart/form-data" class="admin-panel form-donasi">
                    <?php if ($editing): ?>
                        <input type="hidden" name="id_metode" value="<?php echo (int) $editing['id_metode']; ?>">
                    <?php endif; ?>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="kode">Kode<span class="required">*</span></label>
                            <input type="text" id="kode" name="kode" value="<?php echo e($form_source['kode'] ?? ''); ?>" placeholder="contoh: bcava" required>
                        </div>
                        <div class="form-group">
                            <label for="label">Nama Metode<span class="required">*</span></label>
                            <input type="text" id="label" name="label" value="<?php echo e($form_source['label'] ?? ''); ?>" placeholder="BCA Virtual Account" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="tipe">Tipe<span class="required">*</span></label>
                            <select id="tipe" name="tipe" required>
                                <?php foreach (['bank' => 'Bank/VA/Rekening', 'ewallet' => 'E-Wallet', 'qris' => 'QRIS'] as $value => $label): ?>
                                    <option value="<?php echo e($value); ?>" <?php echo ($form_source['tipe'] ?? 'bank') === $value ? 'selected' : ''; ?>>
                                        <?php echo e($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gambar">Path Gambar QRIS</label>
                            <input type="text" id="gambar" name="gambar" value="<?php echo e($form_source['gambar'] ?? ''); ?>" placeholder="assets/images/payments/qris-demo.svg">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="gambar_file">Upload Gambar QRIS</label>
                        <input type="file" id="gambar_file" name="gambar_file" accept=".jpg,.jpeg,.png,.svg" class="input-file">
                        <small>Opsional. Jika diisi, path gambar akan tersimpan otomatis.</small>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="nomor_tujuan">Nomor Tujuan</label>
                            <input type="text" id="nomor_tujuan" name="nomor_tujuan" value="<?php echo e($form_source['nomor_tujuan'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="nama_pemilik">Atas Nama</label>
                            <input type="text" id="nama_pemilik" name="nama_pemilik" value="<?php echo e($form_source['nama_pemilik'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="instruksi">Instruksi<span class="required">*</span></label>
                        <textarea id="instruksi" name="instruksi" rows="4" required><?php echo e($form_source['instruksi'] ?? ''); ?></textarea>
                    </div>

                    <label class="admin-check">
                        <input type="checkbox" name="aktif" value="1" <?php echo !empty($form_source['aktif']) ? 'checked' : ''; ?>>
                        Aktif dan tampil di form donasi
                    </label>

                    <button type="submit" class="btn-submit-form">Simpan Metode</button>
                    <?php if ($editing): ?>
                        <a href="<?php echo url_for('admin/metode-pembayaran.php'); ?>" class="admin-cancel-link">Batal Edit</a>
                    <?php endif; ?>
                </form>

                <div class="admin-panel">
                    <h2>Daftar Metode</h2>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Metode</th>
                                    <th>Tipe</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rows)): ?>
                                    <tr><td colspan="4">Belum ada data metode. Jalankan migration jika tabel belum tersedia.</td></tr>
                                <?php endif; ?>

                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($row['label']); ?></strong>
                                            <span><?php echo e($row['kode']); ?></span>
                                        </td>
                                        <td><?php echo e($row['tipe']); ?></td>
                                        <td><?php echo (int) $row['aktif'] === 1 ? 'Aktif' : 'Nonaktif'; ?></td>
                                        <td class="admin-actions">
                                            <a href="<?php echo url_for('admin/metode-pembayaran.php?edit=' . (int) $row['id_metode']); ?>">Edit</a>
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
