<?php
if (!isset($conn)) {
    require_once __DIR__ . "/db_conn.php";
}

require_once __DIR__ . "/path_helper.php";
require_once __DIR__ . "/campaign_card.php";

if (!function_exists('getTrendingCampaignOrderSql')) {
    function getTrendingCampaignOrderSql($type) {
        $type = (string) $type;

        if ($type === 'most_funded') {
            return "k.dana_terkumpul DESC, k.id_kampanye DESC";
        }

        if ($type === 'urgent') {
            return "k.batas_waktu ASC, k.id_kampanye DESC";
        }

        return "k.id_kampanye DESC";
    }
}

if (!function_exists('getTrendingCampaignWhereSql')) {
    function getTrendingCampaignWhereSql() {
        return "k.status = 'approved'
                AND k.batas_waktu >= CURDATE()
                AND k.dana_terkumpul < k.target_dana";
    }
}

if (!function_exists('getTrendingCampaign')) {
    function getTrendingCampaign($conn, $type, $limit = 3) {
        $order_sql = getTrendingCampaignOrderSql($type);
        $where_sql = getTrendingCampaignWhereSql();
        $limit = max(1, (int) $limit);
        $sql = "SELECT
                    k.*,
                    p.nama_penyelenggara
                FROM kampanye k
                INNER JOIN penyelenggara p
                    ON p.id_penyelenggara = k.id_penyelenggara
                WHERE {$where_sql}
                ORDER BY {$order_sql}
                LIMIT ?";

        $stmt = mysqli_prepare(
            $conn,
            $sql
        );

        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $campaigns = [];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $campaigns[] = $row;
            }
        }

        mysqli_stmt_close($stmt);

        return $campaigns;
    }
}

if (!function_exists('getTrendingCampaigns')) {
    function getTrendingCampaigns($conn, $limit = 3) {
        $most_funded_campaigns = getTrendingCampaign(
            $conn,
            'most_funded',
            $limit
        );

        $urgent_campaigns = getTrendingCampaign(
            $conn,
            'urgent',
            $limit
        );

        $latest_campaigns = getTrendingCampaign(
            $conn,
            'latest',
            $limit
        );

        return [
            'most_funded' => $most_funded_campaigns,
            'urgent' => $urgent_campaigns,
            'latest' => $latest_campaigns,
        ];
    }
}

if (!function_exists('getCampaignTrendMeta')) {
    function getCampaignTrendMeta($campaign) {
        $target = (float) ($campaign['target_dana'] ?? 0);
        $collected = (float) ($campaign['dana_terkumpul'] ?? 0);
        $raw_progress = $target > 0
            ? ($collected / $target) * 100
            : 0;
        $progress = min(
            100,
            round($raw_progress)
        );
        $today = new DateTime('today');
        $deadline = new DateTime($campaign['batas_waktu']);
        $days_left = $deadline < $today
            ? 0
            : $today->diff($deadline)->days;

        return [
            'progress' => $progress,
            'days_left' => $days_left,
            'collected' => $collected,
            'target' => $target,
        ];
    }
}

if (!empty($campaign_list_only)) {
    return;
}

$keyword = trim($_GET['keyword'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$lokasi = trim($_GET['lokasi'] ?? '');
$range = trim($_GET['range'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;

$base_sql = "FROM kampanye k
        INNER JOIN penyelenggara p
            ON p.id_penyelenggara = k.id_penyelenggara";

$where = ["(k.status = 'approved' OR k.status = 'completed')"];
$params = [];
$types = "";

if ($keyword !== "") {
    $where[] = "(k.judul_kampanye LIKE ? OR k.kategori LIKE ? OR k.lokasi LIKE ?)";
    $keyword_like = "%{$keyword}%";
    $params[] = $keyword_like;
    $params[] = $keyword_like;
    $params[] = $keyword_like;
    $types .= "sss";
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

$where_sql = " WHERE " . implode(" AND ", $where);
$count_sql = "SELECT COUNT(*) AS total " . $base_sql . $where_sql;
$count_stmt = mysqli_prepare($conn, $count_sql);
$total_data = 0;

if ($count_stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    }

    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $count_row = $count_result ? mysqli_fetch_assoc($count_result) : null;
    $total_data = $count_row ? (int) $count_row['total'] : 0;
    mysqli_stmt_close($count_stmt);
}

$sql = "SELECT
            k.*,
            p.nama_penyelenggara
        " . $base_sql . $where_sql . "
        ORDER BY k.batas_waktu ASC, k.dana_terkumpul ASC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    echo '<p class="deskripsi">Data kampanye belum bisa ditampilkan.</p>';
    return;
}

$query_types = $types . "ii";
$query_params = array_merge($params, [$limit, $offset]);
mysqli_stmt_bind_param($stmt, $query_types, ...$query_params);

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

$total_pages = (int) ceil($total_data / $limit);
if ($total_pages > 1) {
    $query = $_GET;
    echo '<div class="pagination-kampanye">';

    for ($i = 1; $i <= $total_pages; $i++) {
        $query['page'] = $i;
        $url = url_for('index.php?' . http_build_query($query) . '#kampanye');
        $active = $i === $page ? ' active' : '';
        echo '<a class="page-link' . $active . '" href="' . e($url) . '">' . $i . '</a>';
    }

    echo '</div>';
}
?>
