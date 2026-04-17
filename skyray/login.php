<?php
session_start();
include 'koneksi.php';

$error = '';
$success = '';

if (isset($_SESSION['user'])) {
    header("Location: home.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($data && password_verify($password, $data['password'])) {
            $_SESSION['user'] = $data['username'];
            $_SESSION['user_id'] = $data['id'];
            $_SESSION['is_admin'] = true; // Admin flag
            if (isset($_SESSION['checkout_redirect'])) {
                unset($_SESSION['checkout_redirect']);
                header("Location: checkout.php");
            } else {
                header("Location: home.php");
            }
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SkyRay</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        function togglePassword() {
            const input = document.getElementById('login-password');
            const toggle = input.nextElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                toggle.textContent = '🙈 Hide';
            } else {
                input.type = 'password';
                toggle.textContent = '👁 Show';
            }
        }

        // Shake animation on error
        <?php if ($error): ?>
        window.addEventListener('load', () => {
            const errorEl = document.querySelector('.error');
            errorEl.classList.add('shake');
            setTimeout(() => errorEl.classList.remove('shake'), 500);
        });
        <?php endif; ?>
    </script>
</head>
    <body style="margin: 0; background: none !important;">
<div class="login-hero" style="padding-top: 180px;">
        <header class="login-header">
            <h1>SkyRay Dessert</h1>
        </header>
        <!-- Subtitle removed per request -->
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <p style="color: rgba(255,255,255,0.6); text-align: center; margin-bottom: 24px; font-size: 14px;">
        
        </p>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
<div class="form-title">
                <h2>Login</h2>
            </div>
            
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
            </div>
            
            <div class="input-group">
                <input type="password" id="login-password" name="password" placeholder="Password" required autocomplete="current-password">
                <button type="button" class="password-toggle" onclick="togglePassword()">👁 Show</button>
            </div>
            
            <button type="submit" class="btn btn-login">Login</button>
            
            <div class="form-footer">
                <p>Belum punya akun? <a href="register.php" class="link-auth">Daftar sekarang</a></p>
            </div>
        </form>
        
    </div>
</body>
</html>
