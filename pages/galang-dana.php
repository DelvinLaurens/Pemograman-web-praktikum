<?php
session_start();
require_once("../components/auth.php");

if (isAdminLoggedIn()) {
    header("Location: " . url_for('admin/kampanye.php'));
    exit;
}

header("Location: " . url_for('index.php'));
exit;
?>
