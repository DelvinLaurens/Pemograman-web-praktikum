<?php require_once __DIR__ . "/path_helper.php"; ?>

<footer class="site-footer">
    <div class="container footer-main">
        <div class="footer-brand">
            <a href="<?php echo url_for('index.php'); ?>" class="footer-logo">
                <img src="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>" alt="Logo DemiSesama">
                <span>DemiSesama.</span>
            </a>
            <p>DemiSesama menjadi ruang gotong royong untuk menghubungkan donatur dengan kampanye sosial yang membutuhkan dukungan.</p>
        </div>

        <div class="footer-column">
            <h3>Navigasi</h3>
            <ul>
                <li><a href="<?php echo url_for('index.php'); ?>">Beranda</a></li>
                <li><a href="<?php echo url_for('index.php#kampanye'); ?>">Donasi</a></li>
                <li><a href="<?php echo url_for('pages/galang-dana.php'); ?>">Galang Dana</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Bantuan</h3>
            <ul>
                <li><a href="<?php echo url_for('index.php#kampanye'); ?>">Cari Kampanye</a></li>
                <li><a href="<?php echo url_for('pages/galang-dana.php'); ?>">Ajukan Kampanye</a></li>
                <li><a href="mailto:bantuan@demisesama.test">bantuan@demisesama.test</a></li>
            </ul>
        </div>

        <div class="footer-impact">
            <span>Komitmen Kami</span>
            <p>Setiap donasi diarahkan untuk kampanye yang jelas, mudah dilacak, dan menunggu verifikasi sebelum tercatat sebagai dana terkumpul.</p>
        </div>
    </div>

    <div class="container footer-bottom">
        <p>&copy; 2026 DemiSesama. Semua hak dilindungi.</p>
        <p>Berbagi lebih mudah, dampak lebih nyata.</p>
    </div>
</footer>
