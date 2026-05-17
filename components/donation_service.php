<?php
require_once __DIR__ . "/donation_helper.php";

if (!function_exists('getCampaignForDonation')) {
    function getCampaignForDonation($conn, $id_kampanye) {
        $id_kampanye = (int) $id_kampanye;

        $stmt = mysqli_prepare(
            $conn,
            "SELECT k.*, p.nama_penyelenggara
             FROM kampanye k
             INNER JOIN penyelenggara p ON p.id_penyelenggara = k.id_penyelenggara
             WHERE k.id_kampanye = ?"
        );

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $id_kampanye);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $campaign = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        return $campaign;
    }
}

if (!function_exists('getDonorById')) {
    function getDonorById($conn, $id_donatur) {
        $id_donatur = (int) $id_donatur;
        $stmt = mysqli_prepare($conn, "SELECT id_donatur, nama_lengkap, email FROM donatur WHERE id_donatur = ?");

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $id_donatur);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $donor = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        return $donor;
    }
}

if (!function_exists('validateDonationRequest')) {
    function validateDonationRequest($nominal, $method, $message) {
        $errors = [];
        $nominal = filter_var($nominal, FILTER_VALIDATE_INT);
        $method = normalizePaymentMethod($method);
        $message = trim((string) $message);
        $methods = paymentMethods();

        if (!$nominal || $nominal < 10000) {
            $errors[] = "Nominal donasi minimal Rp10.000.";
        }

        if (!isset($methods[$method])) {
            $errors[] = "Metode pembayaran wajib dipilih.";
        }

        if (strlen($message) > 1000) {
            $errors[] = "Pesan dukungan maksimal 1000 karakter.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => [
                'nominal' => $nominal ? (int) $nominal : 0,
                'method' => $method,
                'message' => $message,
            ],
        ];
    }
}

if (!function_exists('createPendingDonation')) {
    function createPendingDonation($conn, $id_donatur, $id_kampanye, $nominal, $method, $message = "", $expiry_minutes = 30) {
        $validation = validateDonationRequest($nominal, $method, $message);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors'],
                'id_donasi' => null,
            ];
        }

        $data = $validation['data'];
        $id_donatur = (int) $id_donatur;
        $id_kampanye = (int) $id_kampanye;
        $nominal_decimal = (float) $data['nominal'];
        $method = $data['method'];
        $message = $data['message'];
        $expiry_minutes = max(1, (int) $expiry_minutes);

        if (donasiHasColumn($conn, 'waktu_kadaluarsa')) {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO donasi
                    (id_donatur, id_kampanye, nominal_donasi, metode_pembayaran, pesan_dukungan, bukti_transfer, status, waktu_kadaluarsa)
                 VALUES (?, ?, ?, ?, ?, NULL, 'PENDING', DATE_ADD(NOW(), INTERVAL ? MINUTE))"
            );

            if (!$stmt) {
                return ['success' => false, 'errors' => ['Query donasi belum bisa disiapkan.'], 'id_donasi' => null];
            }

            mysqli_stmt_bind_param($stmt, "iidssi", $id_donatur, $id_kampanye, $nominal_decimal, $method, $message, $expiry_minutes);
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO donasi
                    (id_donatur, id_kampanye, nominal_donasi, metode_pembayaran, pesan_dukungan, bukti_transfer, status)
                 VALUES (?, ?, ?, ?, ?, NULL, 'PENDING')"
            );

            if (!$stmt) {
                return ['success' => false, 'errors' => ['Query donasi belum bisa disiapkan.'], 'id_donasi' => null];
            }

            mysqli_stmt_bind_param($stmt, "iidss", $id_donatur, $id_kampanye, $nominal_decimal, $method, $message);
        }

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return [
                'success' => false,
                'errors' => ['Donasi belum bisa disimpan. Coba lagi beberapa saat.'],
                'id_donasi' => null,
            ];
        }

        $id_donasi = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        return [
            'success' => true,
            'errors' => [],
            'id_donasi' => $id_donasi,
        ];
    }
}

if (!function_exists('getDonationVerificationData')) {
    function getDonationVerificationData($conn, $id_donasi, $id_donatur) {
        $id_donasi = (int) $id_donasi;
        $id_donatur = (int) $id_donatur;

        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                d.*,
                k.judul_kampanye,
                k.target_dana,
                k.dana_terkumpul,
                p.nama_penyelenggara,
                dn.nama_lengkap,
                dn.email
             FROM donasi d
             INNER JOIN kampanye k ON k.id_kampanye = d.id_kampanye
             INNER JOIN penyelenggara p ON p.id_penyelenggara = k.id_penyelenggara
             INNER JOIN donatur dn ON dn.id_donatur = d.id_donatur
             WHERE d.id_donasi = ? AND d.id_donatur = ?"
        );

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "ii", $id_donasi, $id_donatur);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $donation = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        return $donation;
    }
}

if (!function_exists('isDonationPaymentExpired')) {
    function isDonationPaymentExpired($donation) {
        if (!$donation || $donation['status'] !== 'PENDING' || !empty($donation['bukti_transfer'])) {
            return false;
        }

        $expiry = getDonationExpiry($donation);
        $now = new DateTime();

        return $now > $expiry;
    }
}

if (!function_exists('syncDonationExpiry')) {
    function syncDonationExpiry($conn, &$donation) {
        if (!$donation) {
            return false;
        }

        $expired = isDonationPaymentExpired($donation);

        if ($expired && donasiSupportsExpired($conn)) {
            $id_donasi = (int) $donation['id_donasi'];
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE donasi
                 SET status = 'EXPIRED'
                 WHERE id_donasi = ?
                   AND status = 'PENDING'
                   AND (bukti_transfer IS NULL OR bukti_transfer = '')"
            );

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id_donasi);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $donation['status'] = 'EXPIRED';
            }
        }

        return $expired;
    }
}

if (!function_exists('validateDonationProofFile')) {
    function validateDonationProofFile($file) {
        $errors = [];

        if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ["Bukti transfer wajib diunggah."];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ["Upload bukti transfer gagal. Coba pilih file lain."];
        }

        $max_size = 2 * 1024 * 1024;
        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
        $allowed_mime = ['image/jpeg', 'image/png', 'application/pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);

        if ($file['size'] > $max_size) {
            $errors[] = "Ukuran bukti transfer maksimal 2MB.";
        }

        if (!in_array($ext, $allowed_ext, true) || !in_array($mime, $allowed_mime, true)) {
            $errors[] = "Format bukti transfer harus JPG, PNG, atau PDF.";
        }

        return $errors;
    }
}

if (!function_exists('uploadDonationProof')) {
    function uploadDonationProof($conn, $donation, $file) {
        if (!$donation) {
            return ['success' => false, 'errors' => ['Data donasi tidak ditemukan.'], 'path' => null];
        }

        if ($donation['status'] === 'EXPIRED' || isDonationPaymentExpired($donation)) {
            return ['success' => false, 'errors' => ['Waktu pembayaran sudah habis. Silakan buat donasi baru.'], 'path' => null];
        }

        if (!empty($donation['bukti_transfer'])) {
            return ['success' => false, 'errors' => ['Bukti transfer sudah diunggah dan sedang menunggu verifikasi.'], 'path' => null];
        }

        $errors = validateDonationProofFile($file);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'path' => null];
        }

        $id_donasi = (int) $donation['id_donasi'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $upload_dir = dirname(__DIR__) . "/assets/uploads/bukti-transfer";

        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true)) {
            return ['success' => false, 'errors' => ['Folder upload bukti transfer belum bisa dibuat.'], 'path' => null];
        }

        $file_name = "donasi-" . $id_donasi . "-" . time() . "." . $ext;
        $target_path = $upload_dir . "/" . $file_name;
        $db_path = "assets/uploads/bukti-transfer/" . $file_name;

        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            return ['success' => false, 'errors' => ['File bukti transfer belum bisa disimpan.'], 'path' => null];
        }

        $stmt = mysqli_prepare($conn, "UPDATE donasi SET bukti_transfer = ?, status = 'PENDING' WHERE id_donasi = ?");
        if (!$stmt) {
            return ['success' => false, 'errors' => ['Sistem belum bisa memperbarui data donasi.'], 'path' => $db_path];
        }

        mysqli_stmt_bind_param($stmt, "si", $db_path, $id_donasi);
        $updated = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$updated) {
            return ['success' => false, 'errors' => ['Bukti tersimpan, tetapi data donasi belum bisa diperbarui.'], 'path' => $db_path];
        }

        return ['success' => true, 'errors' => [], 'path' => $db_path];
    }
}
?>
