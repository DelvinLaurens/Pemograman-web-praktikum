<?php
session_start();
require_once("./Component/db_conn.php");
require_once("./Component/donasi_helper.php");

$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';
if (!redirectUrlIsSafe($redirect)) {
    $redirect = 'index.php';
}

if (!empty($_SESSION['id_donatur'])) {
    header("Location: " . $redirect);
    exit;
}

$error = "";
$email = $_POST['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = "Email dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id_donatur, nama_lengkap, email FROM donatur WHERE email = ? AND password = ? LIMIT 1");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $email, $password);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $donatur = $result ? mysqli_fetch_assoc($result) : null;
            mysqli_stmt_close($stmt);

            if ($donatur) {
                $_SESSION['id_donatur'] = $donatur['id_donatur'];
                $_SESSION['nama_lengkap'] = $donatur['nama_lengkap'];
                $_SESSION['email'] = $donatur['email'];
                $_SESSION['role'] = 'donatur';

                header("Location: " . $redirect);
                exit;
            }
        }

        $error = "Email atau password tidak sesuai.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DemiSesama</title>
    <link rel="icon" type="image/png" href="Asset/tangan2 tnpa bg.png">
    <link rel="stylesheet" href="CSS/global.css?v=3">
    <link rel="stylesheet" href="CSS/login.css?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <div class="container nav-wrapper">
            <div class="logo">
                <a href="index.php" class="logo-link">
                    <img src="Asset/tangan2 tnpa bg.png" alt="logo website" class="logo-website">
                    <span>DemiSesama.</span>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="link-kembali-login">Kembali ke Beranda</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="login-bg">
        <div class="container login-wrapper">
            <div class="login-card">
                <h2 class="text-center">Selamat Datang</h2>
                <p class="text-center desc-login">Masuk untuk mulai berdonasi.</p>

                <?php if ($error !== ""): ?>
                    <div class="pesan-error login-error">
                        <p><?php echo e($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="login-form">
                    <input type="hidden" name="redirect" value="<?php echo e($redirect); ?>">

                    <div class="input-group">
                        <label for="email">Email<span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="Masukkan email" value="<?php echo e($email); ?>" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password<span class="required">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    </div>

                    <button type="submit" class="btn-submit-login">Masuk Sekarang</button>

                    <p class="text-center link-daftar">Akun contoh: kevin@gmail.com / kevin123</p>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container text-center">
            <p>&copy; 2026 DemiSesama</p>
        </div>
    </footer>

</body>
</html>
