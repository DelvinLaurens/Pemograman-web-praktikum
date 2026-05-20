<?php
require_once("../components/db_conn.php");
require_once("../components/auth.php");
require_once("../components/admin_service.php");

requireAdminLogin('admin/dashboard.php');

$admin_id = currentAdminId();
$summary = getAdminSummary($conn, $admin_id);
$monthly_donations = getDashboardMonthlyDonations($conn, $admin_id, 6);
$donation_status_totals = getDashboardDonationStatusTotals($conn, $admin_id);
$top_campaigns = getDashboardTopCampaigns($conn, $admin_id, 5);
$chart_data = [
    'monthlyLabels' => $monthly_donations['labels'],
    'monthlyTotals' => $monthly_donations['totals'],
    'statusLabels' => array_keys($donation_status_totals),
    'statusTotals' => array_values($donation_status_totals),
];
$nama_pengelola = $_SESSION['nama_penyelenggara'] ?? 'Pengelola';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengelola - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/admin.css?v=5'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include_once("../components/nav.php") ?>

    <main class="admin-page">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span>Dashboard Pengelola</span>
                    <h1>Halo, <?php echo e($nama_pengelola); ?></h1>
                </div>
                <a href="<?php echo url_for('admin/kampanye.php'); ?>" class="admin-primary-link">Kelola Kampanye</a>
            </div>

            <section class="admin-stats">
                <div class="admin-stat-card">
                    <span>Total Kampanye</span>
                    <strong><?php echo (int) $summary['kampanye']; ?></strong>
                </div>
                <div class="admin-stat-card">
                    <span>Donasi Pending</span>
                    <strong><?php echo (int) $summary['pending']; ?></strong>
                </div>
                <div class="admin-stat-card">
                    <span>Dana Verified</span>
                    <strong><?php echo formatRupiah($summary['verified_total']); ?></strong>
                </div>
                <div class="admin-stat-card">
                    <span>Dana Pending</span>
                    <strong><?php echo formatRupiah($summary['pending_total']); ?></strong>
                </div>
            </section>

            <section class="dashboard-chart-grid">
                <div class="admin-panel dashboard-chart-panel">
                    <div class="admin-panel-head">
                        <div>
                            <h2>Grafik Donasi</h2>
                            <span>Donasi verified dalam 6 bulan terakhir.</span>
                        </div>
                    </div>
                    <div class="dashboard-chart-box">
                        <canvas id="monthlyDonationChart"></canvas>
                    </div>
                </div>

                <div class="admin-panel dashboard-chart-panel">
                    <div class="admin-panel-head">
                        <div>
                            <h2>Status Donasi</h2>
                            <span>Ringkasan status semua donasi.</span>
                        </div>
                    </div>
                    <div class="dashboard-chart-box dashboard-chart-box-small">
                        <canvas id="donationStatusChart"></canvas>
                    </div>
                </div>
            </section>

            <section class="admin-panel dashboard-top-panel">
                <div class="admin-panel-head">
                    <div>
                        <h2>Top Campaign</h2>
                        <span>Kampanye dengan dana terkumpul terbesar.</span>
                    </div>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table dashboard-top-table">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Dana Terkumpul</th>
                                <th>Target</th>
                                <th>Progress</th>
                                <th>Donasi Verified</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_campaigns)): ?>
                                <tr><td colspan="5">Belum ada kampanye.</td></tr>
                            <?php endif; ?>

                            <?php foreach ($top_campaigns as $campaign): ?>
                                <?php
                                    $target = (float) $campaign['target_dana'];
                                    $collected = (float) $campaign['dana_terkumpul'];
                                    $progress = $target > 0 ? min(100, ($collected / $target) * 100) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($campaign['judul_kampanye']); ?></strong>
                                    </td>
                                    <td><?php echo formatRupiah($collected); ?></td>
                                    <td><?php echo formatRupiah($target); ?></td>
                                    <td>
                                        <div class="dashboard-progress" aria-label="Progress <?php echo (int) round($progress); ?> persen">
                                            <span style="width: <?php echo e((string) $progress); ?>%;"></span>
                                        </div>
                                        <small><?php echo (int) round($progress); ?>%</small>
                                    </td>
                                    <td><?php echo (int) $campaign['total_donasi']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-menu-grid">
                <a href="<?php echo url_for('admin/kampanye.php'); ?>" class="admin-menu-card">
                    <span>Kampanye</span>
                    <strong>Tambah, ubah, dan hapus kampanye milik pengelola.</strong>
                </a>
                <a href="<?php echo url_for('admin/donasi.php'); ?>" class="admin-menu-card">
                    <span>Verifikasi Donasi</span>
                    <strong>Lihat bukti transfer dan ubah status donasi.</strong>
                </a>
                <a href="<?php echo url_for('admin/metode-pembayaran.php'); ?>" class="admin-menu-card">
                    <span>Metode Pembayaran</span>
                    <strong>Atur nomor VA, rekening, QRIS, dan instruksi pembayaran.</strong>
                </a>
            </section>
        </div>
    </main>

    <?php include_once("../components/footer.php") ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var dashboardChartData = <?php echo json_encode($chart_data, JSON_NUMERIC_CHECK); ?>;

        function formatDashboardRupiah(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(value || 0);
        }

        var monthlyCanvas = document.getElementById('monthlyDonationChart');
        if (monthlyCanvas && window.Chart) {
            new Chart(monthlyCanvas, {
                type: 'bar',
                data: {
                    labels: dashboardChartData.monthlyLabels,
                    datasets: [{
                        label: 'Dana Verified',
                        data: dashboardChartData.monthlyTotals,
                        backgroundColor: '#2563EB',
                        borderRadius: 8,
                        maxBarThickness: 42
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return formatDashboardRupiah(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatDashboardRupiah(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        var statusCanvas = document.getElementById('donationStatusChart');
        if (statusCanvas && window.Chart) {
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: dashboardChartData.statusLabels,
                    datasets: [{
                        data: dashboardChartData.statusTotals,
                        backgroundColor: ['#F59E0B', '#10B981', '#EF4444', '#64748B'],
                        borderColor: '#FFFFFF',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '62%'
                }
            });
        }
    </script>
</body>
</html>
