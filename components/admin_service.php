<?php
require_once __DIR__ . "/donation_helper.php";

if (!function_exists('fetchAllAssoc')) {
    function fetchAllAssoc($result) {
        $rows = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

if (!function_exists('getAdminSummary')) {
    function getAdminSummary($conn, $id_penyelenggara) {
        $id_penyelenggara = (int) $id_penyelenggara;
        $summary = [
            'kampanye' => 0,
            'pending' => 0,
            'collected_total' => 0,
            'verified_total' => 0,
            'pending_total' => 0,
        ];

        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                COUNT(*) AS total_kampanye,
                COALESCE(SUM(k.dana_terkumpul), 0) AS collected_total,
                (
                    SELECT COUNT(*)
                    FROM donasi d
                    INNER JOIN kampanye kd ON kd.id_kampanye = d.id_kampanye
                    WHERE kd.id_penyelenggara = ? AND d.status = 'PENDING'
                ) AS total_pending,
                (
                    SELECT COALESCE(SUM(d.nominal_donasi), 0)
                    FROM donasi d
                    INNER JOIN kampanye kd ON kd.id_kampanye = d.id_kampanye
                    WHERE kd.id_penyelenggara = ? AND d.status = 'VERIFIED'
                ) AS verified_total,
                (
                    SELECT COALESCE(SUM(d.nominal_donasi), 0)
                    FROM donasi d
                    INNER JOIN kampanye kd ON kd.id_kampanye = d.id_kampanye
                    WHERE kd.id_penyelenggara = ? AND d.status = 'PENDING'
                ) AS pending_total
             FROM kampanye k
             WHERE k.id_penyelenggara = ?"
        );

        if (!$stmt) {
            return $summary;
        }

        mysqli_stmt_bind_param($stmt, "iiii", $id_penyelenggara, $id_penyelenggara, $id_penyelenggara, $id_penyelenggara);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        if ($row) {
            $summary['kampanye'] = (int) $row['total_kampanye'];
            $summary['pending'] = (int) $row['total_pending'];
            $summary['collected_total'] = (float) $row['collected_total'];
            $summary['verified_total'] = (float) $row['verified_total'];
            $summary['pending_total'] = (float) $row['pending_total'];
        }

        return $summary;
    }
}

if (!function_exists('getManagedCampaigns')) {
    function getManagedCampaigns($conn, $id_penyelenggara, $limit = 0, $offset = 0) {
        $id_penyelenggara = (int) $id_penyelenggara;
        $sql = "SELECT
                k.*,
                COALESCE(COUNT(d.id_donasi), 0) AS total_donasi
             FROM kampanye k
             LEFT JOIN donasi d ON d.id_kampanye = k.id_kampanye
             WHERE k.id_penyelenggara = ?
             GROUP BY k.id_kampanye
             ORDER BY k.batas_waktu ASC, k.dana_terkumpul ASC";

        $limit = (int) $limit;
        $offset = max(0, (int) $offset);
        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
        }

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return [];
        }

        if ($limit > 0) {
            mysqli_stmt_bind_param($stmt, "iii", $id_penyelenggara, $limit, $offset);
        } else {
            mysqli_stmt_bind_param($stmt, "i", $id_penyelenggara);
        }
        mysqli_stmt_execute($stmt);
        $rows = fetchAllAssoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $rows;
    }
}

if (!function_exists('countManagedCampaigns')) {
    function countManagedCampaigns($conn, $id_penyelenggara) {
        $id_penyelenggara = (int) $id_penyelenggara;
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM kampanye WHERE id_penyelenggara = ?");

        if (!$stmt) {
            return 0;
        }

        mysqli_stmt_bind_param($stmt, "i", $id_penyelenggara);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        return $row ? (int) $row['total'] : 0;
    }
}

if (!function_exists('getManagedCampaignById')) {
    function getManagedCampaignById($conn, $id_penyelenggara, $id_kampanye) {
        $id_penyelenggara = (int) $id_penyelenggara;
        $id_kampanye = (int) $id_kampanye;
        $stmt = mysqli_prepare($conn, "SELECT * FROM kampanye WHERE id_kampanye = ? AND id_penyelenggara = ? LIMIT 1");

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "ii", $id_kampanye, $id_penyelenggara);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $campaign = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        return $campaign;
    }
}

if (!function_exists('validateCampaignPayload')) {
    function validateCampaignPayload($data) {
        $errors = [];
        $judul = trim($data['judul_kampanye'] ?? '');
        $kategori = trim($data['kategori'] ?? '');
        $lokasi = trim($data['lokasi'] ?? '');
        $deskripsi = trim($data['deskripsi'] ?? '');
        $target = filter_var($data['target_dana'] ?? null, FILTER_VALIDATE_INT);
        $batas = trim($data['batas_waktu'] ?? '');

        if ($judul === '') {
            $errors[] = "Judul kampanye wajib diisi.";
        }

        if ($kategori === '') {
            $errors[] = "Kategori kampanye wajib dipilih.";
        }

        if ($lokasi === '') {
            $errors[] = "Lokasi kampanye wajib diisi.";
        }

        if ($deskripsi === '') {
            $errors[] = "Deskripsi kampanye wajib diisi.";
        }

        if (!$target || $target < 10000) {
            $errors[] = "Target dana minimal Rp10.000.";
        }

        if ($batas === '' || !DateTime::createFromFormat('Y-m-d', $batas)) {
            $errors[] = "Batas waktu kampanye wajib berupa tanggal valid.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => [
                'judul_kampanye' => $judul,
                'kategori' => $kategori,
                'lokasi' => $lokasi,
                'deskripsi' => $deskripsi,
                'target_dana' => $target ? (int) $target : 0,
                'batas_waktu' => $batas,
            ],
        ];
    }
}

if (!function_exists('uploadCampaignPoster')) {
    function uploadCampaignPoster($file, $required = false) {
        if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required
                ? ['success' => false, 'errors' => ['Poster kampanye wajib diunggah.'], 'path' => null]
                : ['success' => true, 'errors' => [], 'path' => null];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'errors' => ['Upload poster gagal. Coba pilih file lain.'], 'path' => null];
        }

        $max_size = 2 * 1024 * 1024;
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $allowed_mime = ['image/jpeg', 'image/png'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);
        $errors = [];

        if ($file['size'] > $max_size) {
            $errors[] = "Ukuran poster maksimal 2MB.";
        }

        if (!in_array($ext, $allowed_ext, true) || !in_array($mime, $allowed_mime, true)) {
            $errors[] = "Format poster harus JPG atau PNG.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'path' => null];
        }

        $upload_dir = dirname(__DIR__) . "/assets/images/campaigns";
        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true)) {
            return ['success' => false, 'errors' => ['Folder poster belum bisa dibuat.'], 'path' => null];
        }

        $file_name = "kampanye-" . time() . "-" . random_int(1000, 9999) . "." . $ext;
        $target_path = $upload_dir . "/" . $file_name;
        $db_path = "assets/images/campaigns/" . $file_name;

        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            return ['success' => false, 'errors' => ['Poster belum bisa disimpan.'], 'path' => null];
        }

        return ['success' => true, 'errors' => [], 'path' => $db_path];
    }
}

if (!function_exists('saveCampaign')) {
    function saveCampaign($conn, $id_penyelenggara, $payload, $file, $id_kampanye = null) {
        $validation = validateCampaignPayload($payload);
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        $id_penyelenggara = (int) $id_penyelenggara;
        $id_kampanye = $id_kampanye ? (int) $id_kampanye : null;
        $existing = $id_kampanye ? getManagedCampaignById($conn, $id_penyelenggara, $id_kampanye) : null;

        if ($id_kampanye && !$existing) {
            return ['success' => false, 'errors' => ['Kampanye tidak ditemukan atau bukan milik akun ini.']];
        }

        $upload = uploadCampaignPoster($file, !$id_kampanye);
        if (!$upload['success']) {
            return ['success' => false, 'errors' => $upload['errors']];
        }

        $data = $validation['data'];
        $gambar = $upload['path'] ?: ($existing['gambar_poster'] ?? '');

        if ($id_kampanye) {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE kampanye
                 SET judul_kampanye = ?, kategori = ?, lokasi = ?, deskripsi = ?, target_dana = ?, batas_waktu = ?, gambar_poster = ?
                 WHERE id_kampanye = ? AND id_penyelenggara = ?"
            );
            if (!$stmt) {
                return ['success' => false, 'errors' => ['Query update kampanye belum bisa disiapkan.']];
            }
            mysqli_stmt_bind_param(
                $stmt,
                "ssssissii",
                $data['judul_kampanye'],
                $data['kategori'],
                $data['lokasi'],
                $data['deskripsi'],
                $data['target_dana'],
                $data['batas_waktu'],
                $gambar,
                $id_kampanye,
                $id_penyelenggara
            );
        } else {
            $dana_terkumpul = 0;
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO kampanye
                    (id_penyelenggara, judul_kampanye, kategori, lokasi, deskripsi, target_dana, dana_terkumpul, batas_waktu, gambar_poster)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            if (!$stmt) {
                return ['success' => false, 'errors' => ['Query tambah kampanye belum bisa disiapkan.']];
            }
            mysqli_stmt_bind_param(
                $stmt,
                "issssiiss",
                $id_penyelenggara,
                $data['judul_kampanye'],
                $data['kategori'],
                $data['lokasi'],
                $data['deskripsi'],
                $data['target_dana'],
                $dana_terkumpul,
                $data['batas_waktu'],
                $gambar
            );
        }

        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok
            ? ['success' => true, 'errors' => []]
            : ['success' => false, 'errors' => ['Kampanye belum bisa disimpan.']];
    }
}

if (!function_exists('deleteManagedCampaign')) {
    function deleteManagedCampaign($conn, $id_penyelenggara, $id_kampanye) {
        $campaign = getManagedCampaignById($conn, $id_penyelenggara, $id_kampanye);
        if (!$campaign) {
            return ['success' => false, 'errors' => ['Kampanye tidak ditemukan.']];
        }

        if ((float) $campaign['dana_terkumpul'] >= 10000) {
            return ['success' => false, 'errors' => ['Kampanye yang sudah memiliki dana terkumpul tidak dapat dihapus.']];
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM kampanye WHERE id_kampanye = ? AND id_penyelenggara = ?");
        if (!$stmt) {
            return ['success' => false, 'errors' => ['Query hapus kampanye belum bisa disiapkan.']];
        }

        $id_penyelenggara = (int) $id_penyelenggara;
        $id_kampanye = (int) $id_kampanye;
        mysqli_stmt_bind_param($stmt, "ii", $id_kampanye, $id_penyelenggara);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok
            ? ['success' => true, 'errors' => []]
            : ['success' => false, 'errors' => ['Kampanye belum bisa dihapus.']];
    }
}

if (!function_exists('getManagedDonations')) {
    function getManagedDonations($conn, $id_penyelenggara, $status = '', $search = '', $campaign_id = 0, $limit = 0, $offset = 0) {
        $id_penyelenggara = (int) $id_penyelenggara;
        $status = trim($status);
        $search = trim($search);
        $campaign_id = (int) $campaign_id;
        $sql = "SELECT
                    d.*,
                    dn.nama_lengkap,
                    dn.email,
                    k.id_kampanye AS campaign_id,
                    k.judul_kampanye,
                    k.dana_terkumpul
                FROM donasi d
                INNER JOIN kampanye k ON k.id_kampanye = d.id_kampanye
                INNER JOIN donatur dn ON dn.id_donatur = d.id_donatur
                WHERE k.id_penyelenggara = ?";
        $types = "i";
        $params = [$id_penyelenggara];

        if ($status !== '') {
            $sql .= " AND d.status = ?";
            $types .= "s";
            $params[] = $status;
        }

        if ($campaign_id > 0) {
            $sql .= " AND k.id_kampanye = ?";
            $types .= "i";
            $params[] = $campaign_id;
        }

        if ($search !== '') {
            $sql .= " AND (dn.nama_lengkap LIKE ? OR dn.email LIKE ?)";
            $types .= "ss";
            $search_term = "%" . $search . "%";
            $params[] = $search_term;
            $params[] = $search_term;
        }

        $sql .= " ORDER BY
                    CASE WHEN d.status = 'PENDING' THEN 0 ELSE 1 END,
                    d.waktu_donasi DESC";
        $limit = (int) $limit;
        $offset = max(0, (int) $offset);
        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $types .= "ii";
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $rows = fetchAllAssoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $rows;
    }
}

if (!function_exists('countManagedDonations')) {
    function countManagedDonations($conn, $id_penyelenggara, $status = '', $search = '', $campaign_id = 0) {
        $id_penyelenggara = (int) $id_penyelenggara;
        $status = trim($status);
        $search = trim($search);
        $campaign_id = (int) $campaign_id;
        $sql = "SELECT COUNT(*) AS total
                FROM donasi d
                INNER JOIN kampanye k ON k.id_kampanye = d.id_kampanye
                INNER JOIN donatur dn ON dn.id_donatur = d.id_donatur
                WHERE k.id_penyelenggara = ?";
        $types = "i";
        $params = [$id_penyelenggara];

        if ($status !== '') {
            $sql .= " AND d.status = ?";
            $types .= "s";
            $params[] = $status;
        }

        if ($campaign_id > 0) {
            $sql .= " AND k.id_kampanye = ?";
            $types .= "i";
            $params[] = $campaign_id;
        }

        if ($search !== '') {
            $sql .= " AND (dn.nama_lengkap LIKE ? OR dn.email LIKE ?)";
            $types .= "ss";
            $search_term = "%" . $search . "%";
            $params[] = $search_term;
            $params[] = $search_term;
        }

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return 0;
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        return $row ? (int) $row['total'] : 0;
    }
}

if (!function_exists('getCampaignDonationSummary')) {
    function getCampaignDonationSummary($conn, $id_penyelenggara) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                k.id_kampanye,
                k.judul_kampanye,
                k.dana_terkumpul,
                COALESCE(SUM(CASE WHEN d.status = 'PENDING' THEN d.nominal_donasi ELSE 0 END), 0) AS dana_pending,
                COALESCE(SUM(CASE WHEN d.status = 'VERIFIED' THEN d.nominal_donasi ELSE 0 END), 0) AS dana_verified
             FROM kampanye k
             LEFT JOIN donasi d ON d.id_kampanye = k.id_kampanye
             WHERE k.id_penyelenggara = ?
             GROUP BY k.id_kampanye
             ORDER BY k.batas_waktu ASC"
        );

        if (!$stmt) {
            return [];
        }

        $id_penyelenggara = (int) $id_penyelenggara;
        mysqli_stmt_bind_param($stmt, "i", $id_penyelenggara);
        mysqli_stmt_execute($stmt);
        $rows = fetchAllAssoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $rows;
    }
}

if (!function_exists('updateDonationVerificationStatus')) {
    function updateDonationVerificationStatus($conn, $id_penyelenggara, $id_donasi, $new_status) {
        $allowed = ['VERIFIED', 'REJECTED'];
        if (!in_array($new_status, $allowed, true)) {
            return ['success' => false, 'errors' => ['Status verifikasi tidak valid.']];
        }

        $id_penyelenggara = (int) $id_penyelenggara;
        $id_donasi = (int) $id_donasi;
        mysqli_begin_transaction($conn);

        $stmt = mysqli_prepare(
            $conn,
            "SELECT d.id_donasi, d.id_kampanye, d.nominal_donasi, d.status, d.bukti_transfer
             FROM donasi d
             INNER JOIN kampanye k ON k.id_kampanye = d.id_kampanye
             WHERE d.id_donasi = ? AND k.id_penyelenggara = ?
             FOR UPDATE"
        );

        if (!$stmt) {
            mysqli_rollback($conn);
            return ['success' => false, 'errors' => ['Query donasi belum bisa disiapkan.']];
        }

        mysqli_stmt_bind_param($stmt, "ii", $id_donasi, $id_penyelenggara);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $donasi = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        if (!$donasi) {
            mysqli_rollback($conn);
            return ['success' => false, 'errors' => ['Donasi tidak ditemukan atau bukan dari kampanye Anda.']];
        }

        if (empty($donasi['bukti_transfer']) && $new_status === 'VERIFIED') {
            mysqli_rollback($conn);
            return ['success' => false, 'errors' => ['Donasi belum memiliki bukti transfer.']];
        }

        $old_status = $donasi['status'];
        if ($old_status === $new_status) {
            mysqli_commit($conn);
            return ['success' => true, 'errors' => []];
        }

        $stmt = mysqli_prepare($conn, "UPDATE donasi SET status = ? WHERE id_donasi = ?");
        if (!$stmt) {
            mysqli_rollback($conn);
            return ['success' => false, 'errors' => ['Status donasi belum bisa diperbarui.']];
        }
        mysqli_stmt_bind_param($stmt, "si", $new_status, $id_donasi);
        $updated = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$updated) {
            mysqli_rollback($conn);
            return ['success' => false, 'errors' => ['Status donasi belum bisa disimpan.']];
        }

        $delta = 0;
        if ($old_status !== 'VERIFIED' && $new_status === 'VERIFIED') {
            $delta = (float) $donasi['nominal_donasi'];
        } elseif ($old_status === 'VERIFIED' && $new_status !== 'VERIFIED') {
            $delta = -1 * (float) $donasi['nominal_donasi'];
        }

        if ($delta !== 0.0) {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE kampanye
                 SET dana_terkumpul = GREATEST(0, dana_terkumpul + ?)
                 WHERE id_kampanye = ?"
            );
            if (!$stmt) {
                mysqli_rollback($conn);
                return ['success' => false, 'errors' => ['Dana terkumpul belum bisa diperbarui.']];
            }
            $id_kampanye = (int) $donasi['id_kampanye'];
            mysqli_stmt_bind_param($stmt, "di", $delta, $id_kampanye);
            $fund_updated = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if (!$fund_updated) {
                mysqli_rollback($conn);
                return ['success' => false, 'errors' => ['Dana terkumpul belum bisa disimpan.']];
            }
        }

        mysqli_commit($conn);
        return ['success' => true, 'errors' => []];
    }
}

if (!function_exists('paymentMethodTableExists')) {
    function paymentMethodTableExists($conn) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE 'metode_pembayaran'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('getPaymentMethodRows')) {
    function getPaymentMethodRows($conn) {
        if (!paymentMethodTableExists($conn)) {
            return [];
        }

        $result = mysqli_query($conn, "SELECT * FROM metode_pembayaran ORDER BY aktif DESC, label ASC");
        return fetchAllAssoc($result);
    }
}

if (!function_exists('getPaymentMethodRowById')) {
    function getPaymentMethodRowById($conn, $id_metode) {
        if (!paymentMethodTableExists($conn)) {
            return null;
        }

        $id_metode = (int) $id_metode;
        $stmt = mysqli_prepare($conn, "SELECT * FROM metode_pembayaran WHERE id_metode = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $id_metode);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        return $row;
    }
}

if (!function_exists('uploadPaymentMethodImage')) {
    function uploadPaymentMethodImage($file) {
        if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'errors' => [], 'path' => null];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'errors' => ['Upload gambar pembayaran gagal.'], 'path' => null];
        }

        $max_size = 2 * 1024 * 1024;
        $allowed_ext = ['jpg', 'jpeg', 'png', 'svg'];
        $allowed_mime = ['image/jpeg', 'image/png', 'image/svg+xml'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);
        $errors = [];

        if ($file['size'] > $max_size) {
            $errors[] = "Ukuran gambar pembayaran maksimal 2MB.";
        }

        if (!in_array($ext, $allowed_ext, true) || !in_array($mime, $allowed_mime, true)) {
            $errors[] = "Format gambar pembayaran harus JPG, PNG, atau SVG.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'path' => null];
        }

        $upload_dir = dirname(__DIR__) . "/assets/images/payments";
        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true)) {
            return ['success' => false, 'errors' => ['Folder gambar pembayaran belum bisa dibuat.'], 'path' => null];
        }

        $file_name = "payment-" . time() . "-" . random_int(1000, 9999) . "." . $ext;
        $target_path = $upload_dir . "/" . $file_name;
        $db_path = "assets/images/payments/" . $file_name;

        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            return ['success' => false, 'errors' => ['Gambar pembayaran belum bisa disimpan.'], 'path' => null];
        }

        return ['success' => true, 'errors' => [], 'path' => $db_path];
    }
}

if (!function_exists('savePaymentMethodRow')) {
    function savePaymentMethodRow($conn, $payload, $file = null, $id_metode = null) {
        if (!paymentMethodTableExists($conn)) {
            return ['success' => false, 'errors' => ['Tabel metode_pembayaran belum ada. Jalankan migration Mini Project 2.']];
        }

        $kode = strtolower(trim($payload['kode'] ?? ''));
        $label = trim($payload['label'] ?? '');
        $tipe = trim($payload['tipe'] ?? 'bank');
        $nomor = trim($payload['nomor_tujuan'] ?? '');
        $nama = trim($payload['nama_pemilik'] ?? '');
        $instruksi = trim($payload['instruksi'] ?? '');
        $gambar = trim($payload['gambar'] ?? '');
        $aktif = !empty($payload['aktif']) ? 1 : 0;
        $errors = [];

        if (!preg_match('/^[a-z0-9_-]+$/', $kode)) {
            $errors[] = "Kode metode hanya boleh huruf kecil, angka, underscore, atau strip.";
        }

        if ($label === '') {
            $errors[] = "Nama metode wajib diisi.";
        }

        if (!in_array($tipe, ['qris', 'ewallet', 'bank'], true)) {
            $errors[] = "Tipe metode tidak valid.";
        }

        if ($instruksi === '') {
            $errors[] = "Instruksi pembayaran wajib diisi.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $upload = uploadPaymentMethodImage($file);
        if (!$upload['success']) {
            return ['success' => false, 'errors' => $upload['errors']];
        }

        if (!empty($upload['path'])) {
            $gambar = $upload['path'];
        }

        $id_metode = $id_metode ? (int) $id_metode : null;
        if ($id_metode) {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE metode_pembayaran
                 SET kode = ?, label = ?, tipe = ?, nomor_tujuan = ?, nama_pemilik = ?, instruksi = ?, gambar = ?, aktif = ?
                 WHERE id_metode = ?"
            );
            if (!$stmt) {
                return ['success' => false, 'errors' => ['Query update metode belum bisa disiapkan.']];
            }
            mysqli_stmt_bind_param($stmt, "sssssssii", $kode, $label, $tipe, $nomor, $nama, $instruksi, $gambar, $aktif, $id_metode);
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO metode_pembayaran
                    (kode, label, tipe, nomor_tujuan, nama_pemilik, instruksi, gambar, aktif)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            if (!$stmt) {
                return ['success' => false, 'errors' => ['Query tambah metode belum bisa disiapkan.']];
            }
            mysqli_stmt_bind_param($stmt, "sssssssi", $kode, $label, $tipe, $nomor, $nama, $instruksi, $gambar, $aktif);
        }

        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok
            ? ['success' => true, 'errors' => []]
            : ['success' => false, 'errors' => ['Metode pembayaran belum bisa disimpan. Pastikan kode tidak duplikat.']];
    }
}
?>
