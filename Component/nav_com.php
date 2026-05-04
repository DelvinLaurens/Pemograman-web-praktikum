<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$halaman_aktif = basename($_SERVER['PHP_SELF']);
$nama_lengkap_nav = trim($_SESSION['nama_lengkap'] ?? 'Donatur');
$nama_pertama_nav = preg_split('/\s+/', $nama_lengkap_nav)[0] ?? 'Donatur';
?>

<header>
    <div class="container nav-wrapper">
        <a href="index.php" class="logo logo-link">
            <img src="Asset/tangan2 tnpa bg.png" alt="logo website" class="logo-website">
            <span >DemiSesama.</span>
        </a>
        <nav>
            <ul>
                <li><a href="index.php" class="<?php echo $halaman_aktif === 'index.php' ? 'active' : ''; ?>">Beranda</a></li>
                <li><a href="index.php#kampanye">Donasi</a></li>
                <li><a href="galang-dana.php" class="<?php echo $halaman_aktif === 'galang-dana.php' ? 'active' : ''; ?>">Galang Dana</a></li>
                <?php if (!empty($_SESSION['id_donatur'])): ?>
                    <li><span class="nav-user">Halo, <?php echo htmlspecialchars($nama_pertama_nav, ENT_QUOTES, 'UTF-8'); ?></span></li>
                    <li><a href="logout.php" class="btn-login btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login <?php echo $halaman_aktif === 'login.php' ? 'active' : ''; ?>">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
