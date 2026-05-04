<?php
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatRupiah')) {
    function formatRupiah($value) {
        return "Rp " . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('paymentMethods')) {
    function paymentMethods() {
        return [
            'qris' => [
                'label' => 'QRIS',
                'type' => 'qris',
                'image' => 'Asset/qris-demo.svg',
                'instruction' => 'Scan kode QRIS menggunakan aplikasi mobile banking atau e-wallet Anda.',
            ],
            'gopay' => [
                'label' => 'GoPay',
                'type' => 'ewallet',
                'account' => '0812-3456-7890',
                'account_name' => 'Yayasan DemiSesama',
                'instruction' => 'Transfer ke nomor GoPay berikut sesuai nominal donasi.',
            ],
            'dana' => [
                'label' => 'DANA',
                'type' => 'ewallet',
                'account' => '0812-3456-7890',
                'account_name' => 'Yayasan DemiSesama',
                'instruction' => 'Transfer ke nomor DANA berikut sesuai nominal donasi.',
            ],
            'bcava' => [
                'label' => 'BCA Virtual Account',
                'type' => 'bank',
                'account' => '8808 1234 5678',
                'account_name' => 'DemiSesama BCA',
                'instruction' => 'Bayar melalui menu Virtual Account BCA sesuai nominal donasi.',
            ],
            'mandiriva' => [
                'label' => 'Mandiri Virtual Account',
                'type' => 'bank',
                'account' => '70012 1234 5678',
                'account_name' => 'DemiSesama Mandiri',
                'instruction' => 'Bayar melalui menu Virtual Account Mandiri sesuai nominal donasi.',
            ],
        ];
    }
}

if (!function_exists('normalizePaymentMethod')) {
    function normalizePaymentMethod($method) {
        $method = strtolower(trim((string) $method));

        if ($method === 'qris') {
            return 'qris';
        }

        return $method;
    }
}

if (!function_exists('getPaymentMethod')) {
    function getPaymentMethod($method) {
        $methods = paymentMethods();
        $key = normalizePaymentMethod($method);

        return $methods[$key] ?? null;
    }
}

if (!function_exists('donasiHasColumn')) {
    function donasiHasColumn($conn, $column) {
        static $cache = [];
        $column = (string) $column;

        if (array_key_exists($column, $cache)) {
            return $cache[$column];
        }

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'donasi'
               AND COLUMN_NAME = ?"
        );
        if ($stmt === false) {
            $cache[$column] = false;
            return false;
        }

        mysqli_stmt_bind_param($stmt, "s", $column);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        $cache[$column] = $row && (int) $row['total'] > 0;
        mysqli_stmt_close($stmt);

        return $cache[$column];
    }
}

if (!function_exists('donasiSupportsExpired')) {
    function donasiSupportsExpired($conn) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT COLUMN_TYPE
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'donasi'
               AND COLUMN_NAME = 'status'
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $column = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        return isset($column['COLUMN_TYPE']) && stripos($column['COLUMN_TYPE'], 'EXPIRED') !== false;
    }
}

if (!function_exists('getDonationExpiry')) {
    function getDonationExpiry($donasi) {
        if (!empty($donasi['waktu_kadaluarsa'])) {
            return new DateTime($donasi['waktu_kadaluarsa']);
        }

        $expiry = new DateTime($donasi['waktu_donasi']);
        $expiry->modify('+30 minutes');

        return $expiry;
    }
}

if (!function_exists('redirectUrlIsSafe')) {
    function redirectUrlIsSafe($url) {
        $url = trim((string) $url);

        if ($url === '' || preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) {
            return false;
        }

        return strpos($url, '//') !== 0;
    }
}
?>
