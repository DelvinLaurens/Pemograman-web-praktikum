<?php
$halaman_aktif = basename($_SERVER['PHP_SELF']);
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
                <li><a href="login.php" class="btn-login <?php echo $halaman_aktif === 'login.php' ? 'active' : ''; ?>">Login</a></li>
            </ul>
        </nav>
    </div>
</header>
