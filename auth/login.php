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

$switch_role = $role === 'pengelola' ? 'donatur' : 'pengelola';
$switch_redirect = $redirect;

if ($switch_role === 'donatur' && strpos($switch_redirect, 'admin/') === 0) {
    $switch_redirect = 'index.php';
}

if ($switch_role === 'pengelola' && $switch_redirect === 'index.php') {
    $switch_redirect = 'admin/dashboard.php';
}

$switch_label = $role === 'pengelola' ? 'Masuk sebagai donatur' : 'Masuk sebagai penyelenggara';
$switch_url = 'auth/login.php?role=' . urlencode($switch_role) . '&redirect=' . urlencode($switch_redirect);

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
    <link rel="stylesheet" href="<?php echo asset_url('css/login.css?v=6'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <main class="login-bg">
        <div class="container login-wrapper">
            <section class="login-card" aria-label="Login DemiSesama">
                <div class="login-header">
                    <img src="<?php echo asset_url('assets/images/logo-demisesama.png'); ?>" alt="Logo DemiSesama" class="login-logo">
                    <h1>DemiSesama</h1>
                    <p>Kelola kegiatan sosial dengan lebih mudah dan terstruktur.</p>
                </div>

                <?php if ($error !== ""): ?>
                    <div class="pesan-error login-error">
                        <p><?php echo e($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo url_for('auth/login.php'); ?>" class="login-form">
                    <input type="hidden" name="redirect" value="<?php echo e($redirect); ?>">
                    <input type="hidden" name="role" value="<?php echo e($role); ?>">

                    <div class="input-group">
                        <label for="email">Email<span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo e($email); ?>" autocomplete="email" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password<span class="required">*</span></label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" aria-label="Tampilkan password" aria-pressed="false" title="Tampilkan password">
                                <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 3l18 18"></path>
                                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                    <path d="M9.5 5.3A10.8 10.8 0 0 1 12 5c6 0 9.5 7 9.5 7a15 15 0 0 1-2.2 3.1"></path>
                                    <path d="M6.6 6.7C3.9 8.5 2.5 12 2.5 12s3.5 7 9.5 7a9.7 9.7 0 0 0 4-.8"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-login">Masuk</button>

                    <div class="login-switch">
                        <a href="<?php echo url_for($switch_url); ?>"><?php echo e($switch_label); ?></a>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const passwordInput = document.getElementById("password");
        const passwordToggle = document.querySelector(".password-toggle");

        if (!passwordInput || !passwordToggle) {
            return;
        }

        passwordToggle.addEventListener("click", function() {
            const isPasswordVisible = passwordInput.type === "text";
            passwordInput.type = isPasswordVisible ? "password" : "text";
            passwordToggle.classList.toggle("is-visible", !isPasswordVisible);
            passwordToggle.setAttribute("aria-pressed", String(!isPasswordVisible));
            passwordToggle.setAttribute("aria-label", isPasswordVisible ? "Tampilkan password" : "Sembunyikan password");
            passwordToggle.setAttribute("title", isPasswordVisible ? "Tampilkan password" : "Sembunyikan password");
        });
    });
    </script>

</body>
</html>
