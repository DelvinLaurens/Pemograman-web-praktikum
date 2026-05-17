<?php
require_once("../components/path_helper.php");

$redirect = $_GET['redirect'] ?? 'admin/dashboard.php';
header("Location: " . url_for('auth/login.php') . "?role=pengelola&redirect=" . urlencode($redirect));
exit;
?>
