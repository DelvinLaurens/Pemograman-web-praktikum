<?php
session_start();
require_once("../components/db_conn.php");
require_once("../components/donation_helper.php");
require_once("../components/auth.php");

$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';
if (!redirectUrlIsSafe($redirect)) {
    $redirect = 'index.php';
}

$role = $_GET['role'] ?? $_POST['role'] ?? 'donatur';
if (!in_array($role, ['donatur', 'pengelola'], true)) {
    $role = 'donatur';
}

if ($role === 'pengelola' && $redirect === 'index.php') {
    $redirect = 'admin/dashboard.php';
}

if (isDonorLoggedIn() || isAdminLoggedIn()) {
    header("Location: " . url_for($redirect));
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
        if ($role === 'pengelola') {
            $stmt = mysqli_prepare(
                $conn,
                "SELECT id_penyelenggara, nama_penyelenggara, email
                 FROM penyelenggara
                 WHERE email = ? AND pass = ?
                 LIMIT 1"
            );

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $email, $password);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $admin = $result ? mysqli_fetch_assoc($result) : null;
                mysqli_stmt_close($stmt);

                if ($admin) {
                    session_regenerate_id(true);
                    $_SESSION['id_penyelenggara'] = $admin['id_penyelenggara'];
                    $_SESSION['nama_penyelenggara'] = $admin['nama_penyelenggara'];
                    $_SESSION['email_penyelenggara'] = $admin['email'];
                    $_SESSION['role'] = 'pengelola';

                    header("Location: " . url_for($redirect));
                    exit;
                }
            }

            $error = "Email atau password pengelola tidak sesuai.";
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id_donatur, nama_lengkap, email FROM donatur WHERE email = ? AND password = ? LIMIT 1");

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $email, $password);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $donatur = $result ? mysqli_fetch_assoc($result) : null;
                mysqli_stmt_close($stmt);

                if ($donatur) {
                    session_regenerate_id(true);
                    $_SESSION['id_donatur'] = $donatur['id_donatur'];
                    $_SESSION['nama_lengkap'] = $donatur['nama_lengkap'];
                    $_SESSION['email'] = $donatur['email'];
                    $_SESSION['role'] = 'donatur';

                    header("Location: " . url_for($redirect));
                    exit;
                }
            }

            $error = "Email atau password donatur tidak sesuai.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DemiSesama</title>
    <link rel="icon" type="image/png" href="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/global.css?v=3'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/login.css?v=3'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <div class="container nav-wrapper">
            <div class="logo">
                <a href="<?php echo url_for('index.php'); ?>" class="logo-link">
                    <img src="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>" alt="logo website" class="logo-website">
                    <span>DemiSesama.</span>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo url_for('index.php'); ?>" class="link-kembali-login">Kembali ke Beranda</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="login-bg">
        <div class="container login-wrapper">
            <div class="login-card">
                <h2 class="text-center">Selamat Datang</h2>
                <p class="text-center desc-login">Masuk sebagai donatur atau pengelola kampanye.</p>

                <?php if ($error !== ""): ?>
                    <div class="pesan-error login-error">
                        <p><?php echo e($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo url_for('auth/login.php'); ?>" class="login-form">
                    <input type="hidden" name="redirect" value="<?php echo e($redirect); ?>">

                    <div class="login-role-toggle" aria-label="Pilih role login">
                        <label class="<?php echo $role === 'donatur' ? 'active' : ''; ?>">
                            <input type="radio" name="role" value="donatur" <?php echo $role === 'donatur' ? 'checked' : ''; ?>>
                            Donatur
                        </label>
                        <label class="<?php echo $role === 'pengelola' ? 'active' : ''; ?>">
                            <input type="radio" name="role" value="pengelola" <?php echo $role === 'pengelola' ? 'checked' : ''; ?>>
                            Pengelola
                        </label>
                    </div>

                    <div class="input-group">
                        <label for="email">Email<span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="Masukkan email" value="<?php echo e($email); ?>" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password<span class="required">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    </div>

                    <button type="submit" class="btn-submit-login">Masuk Sekarang</button>
                    <p class="text-center link-daftar">Donatur: kevin@gmail.com / kevin123<br>Pengelola: jere@gmail.com / jeremy123</p>
                </form>
            </div>
        </div>
    </main>

    <?php include_once("../components/footer.php") ?>

</body>
</html>
