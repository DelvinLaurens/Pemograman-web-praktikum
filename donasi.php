<?php
session_start();
require_once("./Component/db_conn.php");
require_once("./Component/donation_service.php");

$id_kampanye = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_kampanye) {
    $id_kampanye = filter_input(INPUT_POST, 'id_kampanye', FILTER_VALIDATE_INT);
}

if (!$id_kampanye) {
    $id_kampanye = 1;
}

$current_url = "donasi.php?id=" . urlencode((string) $id_kampanye);
if (empty($_SESSION['id_donatur'])) {
    header("Location: login.php?redirect=" . urlencode($current_url));
    exit;
}

$kampanye = getCampaignForDonation($conn, $id_kampanye);
$donatur = getDonorById($conn, $_SESSION['id_donatur']);

if (!$donatur) {
    session_unset();
    session_destroy();
    header("Location: login.php?redirect=" . urlencode($current_url));
    exit;
}

$methods = paymentMethods();
$errors = [];
$selected_method = $_POST['metode'] ?? '';
$nominal = $_POST['nominal'] ?? '';
$pesan = $_POST['pesan'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $kampanye) {
    $result = createPendingDonation(
        $conn,
        $donatur['id_donatur'],
        $kampanye['id_kampanye'],
        $_POST['nominal'] ?? null,
        $_POST['metode'] ?? '',
        $_POST['pesan'] ?? ''
    );

    if ($result['success']) {
        header("Location: verif.php?id=" . urlencode((string) $result['id_donasi']));
        exit;
    }

    $errors = $result['errors'];
}

$target = $kampanye ? (float) $kampanye['target_dana'] : 0;
$terkumpul = $kampanye ? (float) $kampanye['dana_terkumpul'] : 0;
$persentase = $target > 0 ? min(round(($terkumpul / $target) * 100), 100) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Donasi - DemiSesama</title>
    <link rel="icon" type="image/png" href="Asset/tangan2 tnpa bg.png">
    <link rel="stylesheet" href="CSS/global.css?v=3">
    <link rel="stylesheet" href="CSS/form.css?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include_once("./Component/nav_com.php") ?>

    <main class="halaman-form">
        <div class="container form-container">
            <div class="form-card">
                <?php if (!$kampanye): ?>
                    <div class="ringkasan-donasi">
                        <h2>Kampanye tidak ditemukan</h2>
                        <p>Silakan kembali ke daftar kampanye dan pilih donasi yang tersedia.</p>
                    </div>
                    <a href="index.php#kampanye" class="btn-kembali-home">Kembali ke Kampanye</a>
                <?php else: ?>
                    <div class="ringkasan-donasi">
                        <h2>Formulir Donasi</h2>
                        <p>Anda akan berdonasi untuk kampanye:<br><strong><?php echo e($kampanye['judul_kampanye']); ?></strong></p>
                    </div>

                    <div class="summary-grid">
                        <div>
                            <span>Target Dana</span>
                            <strong><?php echo formatRupiah($kampanye['target_dana']); ?></strong>
                        </div>
                        <div>
                            <span>Dana Terkumpul</span>
                            <strong><?php echo formatRupiah($kampanye['dana_terkumpul']); ?></strong>
                        </div>
                        <div>
                            <span>Progress</span>
                            <strong><?php echo $persentase; ?>%</strong>
                        </div>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="pesan-error">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo e($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="donasi.php?id=<?php echo (int) $kampanye['id_kampanye']; ?>" class="form-donasi">
                        <input type="hidden" name="id_kampanye" value="<?php echo (int) $kampanye['id_kampanye']; ?>">

                        <div class="form-group">
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" id="nama" value="<?php echo e($donatur['nama_lengkap']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?php echo e($donatur['email']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="nominal">Nominal Donasi (Rp)<span class="required">*</span></label>
                            <input type="number" id="nominal" name="nominal" placeholder="Contoh: 50000" min="10000" value="<?php echo e($nominal); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="metode">Metode Pembayaran<span class="required">*</span></label>
                            <select name="metode" id="metode" required>
                                <option value="">-- Pilih Metode --</option>
                                <?php foreach ($methods as $key => $method): ?>
                                    <option value="<?php echo e($key); ?>" <?php echo normalizePaymentMethod($selected_method) === $key ? 'selected' : ''; ?>>
                                        <?php echo e($method['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="pesan">Pesan Dukungan (Opsional)</label>
                            <textarea id="pesan" name="pesan" rows="4" placeholder="Tulis doa untuk kampanye ini..."><?php echo e($pesan); ?></textarea>
                        </div>

                        <button type="submit" class="btn-submit-form">Kirim Donasi Sekarang</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container text-center">
            <p>&copy; 2026 DemiSesama</p>
        </div>
    </footer>
</body>
</html>
