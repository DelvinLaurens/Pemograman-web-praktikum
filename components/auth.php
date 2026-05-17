<?php
require_once __DIR__ . "/donation_helper.php";

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

if (!function_exists('isDonorLoggedIn')) {
    function isDonorLoggedIn() {
        return !empty($_SESSION['id_donatur']) && ($_SESSION['role'] ?? '') === 'donatur';
    }
}

if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn() {
        return !empty($_SESSION['id_penyelenggara']) && ($_SESSION['role'] ?? '') === 'pengelola';
    }
}

if (!function_exists('requireDonorLogin')) {
    function requireDonorLogin($redirect = '') {
        if (isDonorLoggedIn()) {
            return;
        }

        $redirect = $redirect !== '' ? $redirect : basename($_SERVER['REQUEST_URI']);
        if (!redirectUrlIsSafe($redirect)) {
            $redirect = 'index.php';
        }

        header("Location: " . url_for('auth/login.php') . "?redirect=" . urlencode($redirect));
        exit;
    }
}

if (!function_exists('requireAdminLogin')) {
    function requireAdminLogin($redirect = '') {
        if (isAdminLoggedIn()) {
            return;
        }

        $redirect = $redirect !== '' ? $redirect : basename($_SERVER['REQUEST_URI']);
        if (!redirectUrlIsSafe($redirect)) {
            $redirect = 'admin/dashboard.php';
        }

        header("Location: " . url_for('auth/login.php') . "?role=pengelola&redirect=" . urlencode($redirect));
        exit;
    }
}

if (!function_exists('currentAdminId')) {
    function currentAdminId() {
        return (int) ($_SESSION['id_penyelenggara'] ?? 0);
    }
}

if (!function_exists('currentDonorId')) {
    function currentDonorId() {
        return (int) ($_SESSION['id_donatur'] ?? 0);
    }
}
?>
