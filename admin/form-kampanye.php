<?php
require_once __DIR__ . "/../components/db_conn.php";
require_once __DIR__ . "/../components/auth.php";
require_once __DIR__ . "/../components/admin_service.php";

$conn = $GLOBALS['conn'] ?? null;

/** @var mysqli|null $conn */
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Koneksi database belum tersedia.");
}

requireAdminLogin('admin/form-kampanye.php');

$admin_id = currentAdminId();
$errors = [];
$return_page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$return_page = max(1, $return_page);
$edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $return_page = filter_input(INPUT_POST, 'return_page', FILTER_VALIDATE_INT) ?: $return_page;
    $return_page = max(1, $return_page);
    $edit_id = filter_input(INPUT_POST, 'id_kampanye', FILTER_VALIDATE_INT) ?: null;

    $result = saveCampaign($conn, $admin_id, $_POST, $_FILES['gambar_poster'] ?? null, $edit_id);

    if ($result['success']) {
        $page_query = $return_page > 1 ? '&page=' . $return_page : '';
        header("Location: " . url_for('admin/kampanye.php?saved=1' . $page_query));
        exit;
    }

    $errors = $result['errors'];
}

$editing = $edit_id ? getManagedCampaignById($conn, $admin_id, $edit_id) : null;
if ($edit_id && !$editing && empty($errors)) {
    $errors[] = "Kampanye tidak ditemukan atau bukan milik akun ini.";
}

$form_source = !empty($errors) ? $_POST : ($editing ?: []);
$kategori_options = [
    'bencana_alam' => 'Bencana Alam',
    'pendidikan' => 'Pendidikan',
    'kesehatan' => 'Kesehatan',
    'lingkungan' => 'Lingkungan',
    'sosial' => 'Sosial',
    'pembangunan' => 'Pembangunan',
];
$return_query = $return_page > 1 ? '?page=' . $return_page : '';
$form_query = [];
if ($editing) {
    $form_query['edit'] = (int) $editing['id_kampanye'];
}
if ($return_page > 1) {
    $form_query['page'] = $return_page;
}
$form_action = 'admin/form-kampanye.php' . (!empty($form_query) ? '?' . http_build_query($form_query) : '');
$is_editing = !empty($editing);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_editing ? 'Edit Kampanye' : 'Tambah Kampanye'; ?> - DemiSesama</title>
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
                    <h1><?php echo $is_editing ? 'Edit Kampanye' : 'Tambah Kampanye'; ?></h1>
                </div>
                <a href="<?php echo url_for('admin/kampanye.php' . $return_query); ?>" class="admin-secondary-link">Kembali ke Daftar</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="pesan-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($edit_id && !$editing): ?>
                <section class="admin-panel">
                    <p class="admin-table-note">Silakan kembali ke daftar kampanye dan pilih data yang tersedia.</p>
                    <a href="<?php echo url_for('admin/kampanye.php' . $return_query); ?>" class="admin-primary-link">Kembali ke Daftar</a>
                </section>
            <?php else: ?>
                <form method="POST" action="<?php echo url_for($form_action); ?>" enctype="multipart/form-data" class="admin-panel form-donasi">
                    <input type="hidden" name="return_page" value="<?php echo (int) $return_page; ?>">
                    <?php if ($is_editing): ?>
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
                        <label for="gambar_poster">Poster Kampanye<?php echo $is_editing ? '' : '<span class="required">*</span>'; ?></label>
                        <input type="file" id="gambar_poster" name="gambar_poster" accept=".jpg,.jpeg,.png" class="input-file" <?php echo $is_editing ? '' : 'required'; ?>>
                        <small>JPG/PNG maksimal 2MB. Saat edit, kosongkan jika tidak ingin mengganti poster.</small>
                    </div>

                    <button type="submit" class="btn-submit-form"><?php echo $is_editing ? 'Simpan Perubahan' : 'Tambah Kampanye'; ?></button>
                    <a href="<?php echo url_for('admin/kampanye.php' . $return_query); ?>" class="admin-cancel-link">Batal</a>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <?php include_once __DIR__ . "/../components/footer.php" ?>
</body>
</html>
