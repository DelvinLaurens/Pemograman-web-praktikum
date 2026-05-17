<?php
require_once __DIR__ . "/path_helper.php";

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$halaman_aktif = current_page_path();
$nama_lengkap_nav = trim($_SESSION['nama_lengkap'] ?? 'Donatur');
$nama_pertama_nav = preg_split('/\s+/', $nama_lengkap_nav)[0] ?? 'Donatur';
$nama_pengelola_nav = trim($_SESSION['nama_penyelenggara'] ?? 'Pengelola');
$nama_pertama_pengelola_nav = preg_split('/\s+/', $nama_pengelola_nav)[0] ?? 'Pengelola';
?>

<header>
    <div class="container nav-wrapper">
        <a href="<?php echo url_for('index.php'); ?>" class="logo logo-link">
            <img src="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>" alt="logo website" class="logo-website">
            <span >DemiSesama.</span>
        </a>
        <nav>
            <ul>
                <?php if (!empty($_SESSION['id_penyelenggara']) && ($_SESSION['role'] ?? '') === 'pengelola'): ?>
                    <li><a href="<?php echo url_for('admin/dashboard.php'); ?>" class="<?php echo $halaman_aktif === 'admin/dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?php echo url_for('admin/kampanye.php'); ?>" class="<?php echo $halaman_aktif === 'admin/kampanye.php' ? 'active' : ''; ?>">Kampanye</a></li>
                    <li><a href="<?php echo url_for('admin/donasi.php'); ?>" class="<?php echo $halaman_aktif === 'admin/donasi.php' ? 'active' : ''; ?>">Donasi</a></li>
                    <li><span class="nav-user">Halo, <?php echo htmlspecialchars($nama_pertama_pengelola_nav, ENT_QUOTES, 'UTF-8'); ?></span></li>
                    <li><a href="<?php echo url_for('auth/logout.php'); ?>" class="btn-login btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo url_for('index.php'); ?>" class="<?php echo $halaman_aktif === 'index.php' ? 'active' : ''; ?>">Beranda</a></li>
                    <li><a href="<?php echo url_for('index.php#kampanye'); ?>">Donasi</a></li>
                    <li><a href="<?php echo url_for('pages/galang-dana.php'); ?>" class="<?php echo $halaman_aktif === 'pages/galang-dana.php' ? 'active' : ''; ?>">Galang Dana</a></li>
                    <?php if (!empty($_SESSION['id_donatur'])): ?>
                    <li><a href="<?php echo url_for('pages/riwayat-donasi.php'); ?>" class="<?php echo $halaman_aktif === 'pages/riwayat-donasi.php' ? 'active' : ''; ?>">Riwayat</a></li>
                    <li><span class="nav-user">Halo, <?php echo htmlspecialchars($nama_pertama_nav, ENT_QUOTES, 'UTF-8'); ?></span></li>
                    <li><a href="<?php echo url_for('auth/logout.php'); ?>" class="btn-login btn-logout">Logout</a></li>
                    <?php else: ?>
                    <li><a href="<?php echo url_for('auth/login.php'); ?>" class="btn-login <?php echo $halaman_aktif === 'auth/login.php' ? 'active' : ''; ?>">Login</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
