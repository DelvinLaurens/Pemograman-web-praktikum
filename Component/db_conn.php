<?php
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "demi_sesama";

    $conn = mysqli_connect($host, $username, $password, $database);

    if (!$conn) {
        die ("Koneksi ke database gagal: ". mysqli_connect_errno());
    }
?>