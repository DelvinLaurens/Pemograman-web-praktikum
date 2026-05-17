<?php
session_start();
require_once("../components/db_conn.php");
require_once("../components/donation_service.php");

$id_donasi = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_donasi) {
    $id_donasi = filter_input(INPUT_POST, 'id_donasi', FILTER_VALIDATE_INT);
}

if (!$id_donasi) {
    header("Location: " . url_for('index.php#kampanye'));
    exit;
}

$current_url = "pages/verif.php?id=" . urlencode((string) $id_donasi);
if (empty($_SESSION['id_donatur'])) {
    header("Location: " . url_for('auth/login.php') . "?redirect=" . urlencode($current_url));
    exit;
}

$donasi = getDonationVerificationData($conn, $id_donasi, $_SESSION['id_donatur']);

if (!$donasi) {
    http_response_code(404);
}

$payment = $donasi ? getPaymentMethod($donasi['metode_pembayaran']) : null;
$errors = [];
$success = "";
$expired = false;
$expiry_timestamp = 0;

if ($donasi) {
    $expiry = getDonationExpiry($donasi);
    $expired = syncDonationExpiry($conn, $donasi);
    $expiry_timestamp = $expiry->getTimestamp() * 1000;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $donasi) {
    $upload = uploadDonationProof($conn, $donasi, $_FILES['bukti'] ?? null);

    if ($upload['success']) {
        $success = "Bukti transfer berhasil dikirim. Donasi Anda menunggu verifikasi penyelenggara.";
        $donasi['bukti_transfer'] = $upload['path'];
        $donasi['status'] = 'PENDING';
        $expired = false;
    } else {
        $errors = $upload['errors'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/form.css?v=3'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include_once("../components/nav.php") ?>

    <main class="halaman-form">
        <div class="container form-container">
            <div class="form-card form-card-wide">
                <?php if (!$donasi): ?>
                    <div class="ringkasan-donasi">
                        <h2>Donasi tidak ditemukan</h2>
                        <p>Data donasi tidak tersedia atau bukan milik akun Anda.</p>
                    </div>
                    <a href="<?php echo url_for('index.php#kampanye'); ?>" class="btn-kembali-home">Kembali ke Kampanye</a>
                <?php else: ?>
                    <div class="ringkasan-donasi">
                        <h2>Verifikasi Pembayaran</h2>
                        <p>Lengkapi pembayaran untuk kampanye:<br><strong><?php echo e($donasi['judul_kampanye']); ?></strong></p>
                    </div>

                    <div class="status-row">
                        <span class="status-badge status-<?php echo strtolower(e($donasi['status'])); ?>"><?php echo e($donasi['status']); ?></span>
                        <?php if (empty($donasi['bukti_transfer']) && $donasi['status'] === 'PENDING'): ?>
                            <div class="countdown-box">
                                <span>Sisa waktu pembayaran</span>
                                <strong id="countdown" data-expiry="<?php echo (int) $expiry_timestamp; ?>">--:--</strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="summary-grid">
                        <div>
                            <span>Donatur</span>
                            <strong><?php echo e($donasi['nama_lengkap']); ?></strong>
                        </div>
                        <div>
                            <span>Nominal</span>
                            <strong><?php echo formatRupiah($donasi['nominal_donasi']); ?></strong>
                        </div>
                        <div>
                            <span>Metode</span>
                            <strong><?php echo e($payment['label'] ?? $donasi['metode_pembayaran']); ?></strong>
                        </div>
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

                    <?php if ($payment): ?>
                        <div class="metode-box">
                            <div>
                                <h3><?php echo e($payment['label']); ?></h3>
                                <p><?php echo e($payment['instruction']); ?></p>

                                <?php if ($payment['type'] === 'qris'): ?>
                                    <div class="qris-frame">
                                        <img src="<?php echo e(asset_url($payment['image'])); ?>" alt="Kode QRIS DemiSesama">
                                    </div>
                                <?php else: ?>
                                    <div class="rekening-card">
                                        <span>Nomor Tujuan</span>
                                        <strong><?php echo e($payment['account']); ?></strong>
                                        <span>Atas Nama</span>
                                        <strong><?php echo e($payment['account_name']); ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="instruksi-bayar">
                                <h3>Instruksi</h3>
                                <ol>
                                    <li>Bayar sesuai nominal donasi.</li>
                                    <li>Simpan bukti pembayaran.</li>
                                    <li>Upload bukti pada form di bawah sebelum waktu habis.</li>
                                </ol>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($donasi['status'] === 'EXPIRED' || $expired): ?>
                        <div class="pesan-error">
                            <p>Waktu pembayaran sudah habis. Donasi ini ditandai kadaluarsa dan tidak akan memenuhi database aktif.</p>
                        </div>
                        <a href="<?php echo url_for('pages/donasi.php?id=' . (int) $donasi['id_kampanye']); ?>" class="btn-submit-form btn-link-center">Buat Donasi Baru</a>
                    <?php elseif (!empty($donasi['bukti_transfer'])): ?>
                        <div class="pesan-info">
                            <p>Bukti transfer sudah diterima. Status tetap <strong>PENDING</strong> sampai penyelenggara melakukan verifikasi.</p>
                            <a href="<?php echo e(asset_url($donasi['bukti_transfer'])); ?>" target="_blank" rel="noopener">Lihat bukti transfer</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="<?php echo url_for('pages/verif.php?id=' . (int) $donasi['id_donasi']); ?>" enctype="multipart/form-data" class="form-donasi" id="form-upload-bukti">
                            <input type="hidden" name="id_donasi" value="<?php echo (int) $donasi['id_donasi']; ?>">

                            <div class="form-group">
                                <label for="bukti">Upload Bukti Transfer (JPG/PNG/PDF)<span class="required">*</span></label>
                                <input type="file" id="bukti" name="bukti" accept=".jpg, .jpeg, .png, .pdf" required class="input-file">
                                <small>Maksimal ukuran file 2MB.</small>
                            </div>

                            <button type="submit" class="btn-submit-form">Konfirmasi Pembayaran</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include_once("../components/footer.php") ?>

    <script>
        const countdown = document.getElementById('countdown');
        const uploadForm = document.getElementById('form-upload-bukti');

        if (countdown) {
            const expiry = Number(countdown.dataset.expiry);

            const updateCountdown = () => {
                const remaining = expiry - Date.now();

                if (remaining <= 0) {
                    countdown.textContent = '00:00';
                    if (uploadForm) {
                        uploadForm.querySelectorAll('input, button').forEach((element) => {
                            element.disabled = true;
                        });
                    }
                    return;
                }

                const totalSeconds = Math.floor(remaining / 1000);
                const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
                const seconds = String(totalSeconds % 60).padStart(2, '0');
                countdown.textContent = `${minutes}:${seconds}`;
            };

            updateCountdown();
            setInterval(updateCountdown, 1000);
        }
    </script>
</body>
</html>
