<?php
session_start();
require_once("../components/path_helper.php");

session_unset();
session_destroy();

header("Location: " . url_for('index.php'));
exit;
?>
