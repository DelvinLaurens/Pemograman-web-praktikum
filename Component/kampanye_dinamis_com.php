<?php
if (!isset($conn)) {
    require_once __DIR__ . "/db_conn.php";
}

require_once __DIR__ . "/card_com.php";

$keyword = trim($_GET['keyword'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$lokasi = trim($_GET['lokasi'] ?? '');
$range = trim($_GET['range'] ?? '');

$sql = "SELECT
            k.*,
            p.nama_penyelenggara
        FROM kampanye k
        INNER JOIN penyelenggara p
            ON p.id_penyelenggara = k.id_penyelenggara";

$where = [];
$params = [];
$types = "";

if ($keyword !== "") {
    $where[] = "k.judul_kampanye LIKE ?";
    $params[] = "%{$keyword}%";
    $types .= "s";
}

if ($kategori !== "") {
    $where[] = "k.kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

if ($lokasi !== "") {
    $lokasi_keyword = $lokasi === "ntt" ? "NTT" : $lokasi;
    $where[] = "k.lokasi LIKE ?";
    $params[] = "%{$lokasi_keyword}%";
    $types .= "s";
}

if ($range !== "") {
    if ($range === "10000000+") {
        $where[] = "k.target_dana >= ?";
        $params[] = 10000000;
        $types .= "i";
    } else {
        $range_parts = explode("-", $range);

        if (count($range_parts) === 2) {
            $where[] = "k.target_dana BETWEEN ? AND ?";
            $params[] = (int) $range_parts[0];
            $params[] = (int) $range_parts[1];
            $types .= "ii";
        }
    }
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY k.batas_waktu ASC LIMIT 6";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    echo '<p class="deskripsi">Data kampanye belum bisa ditampilkan.</p>';
    return;
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    while ($kampanye = mysqli_fetch_assoc($result)) {
        cardKampanye($kampanye);
    }
} else {
    echo '<p class="deskripsi">Belum ada kampanye yang sesuai.</p>';
}

mysqli_stmt_close($stmt);
?>
