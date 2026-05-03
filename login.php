<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DemiSesama</title>
    <link rel="icon" type="image/png" href="Asset/tangan2 tnpa bg.png">
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="stylesheet" href="CSS/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <header>
        <div class="container nav-wrapper">
            <div class="logo">
                <a href="index.html" class="logo-link">
                    <img src="Asset/tangan2 tnpa bg.png" alt="logo website" style="height: 40px; margin-right: 10px;">
                    <span>DemiSesama.</span>
                </a>
            </div>
            <nav>
                <ul>
                    <a href="index.html" class="link-kembali-login">← Kembali ke Beranda</a>
                </ul>
            </nav>
        </div>
    </header>

    <main class="login-bg">
        <div class="container login-wrapper">
            <div class="login-card">
                <h2 class="text-center">Selamat Datang</h2>
                <p class="text-center desc-login">Masuk untuk mulai berdonasi atau mengelola kampanye Anda.</p>
                <form action="#" class="login-form">
                    <!-- Input Username / Email -->
                    <div class="input-group">
                        <label for="email">Email / Username<span class="required">*</span></label>
                        <input type="text" id="email" placeholder="Masukkan email atau username" required>
                    </div>

                    <!-- Input Password -->
                    <div class="input-group">
                        <label for="password">Password<span class="required">*</span></label>
                        <input type="password" id="password" placeholder="Masukkan password" required>
                    </div>

                    <button type="submit" class="btn-submit-login">Masuk Sekarang</button>
                    
                    <p class="text-center link-daftar">Belum punya akun? <a href="#">Daftar di sini</a></p>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p>&copy; 2026 DemiSesama</p>
        </div>
    </footer>

</body>
</html>