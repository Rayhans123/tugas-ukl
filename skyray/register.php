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
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password) || $password !== $confirm_password) {
        $error = "Username harus diisi dan password harus cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Check if username exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_get_result($stmt)->num_rows > 0) {
            $error = "Username sudah terdaftar!";
        } else {
            mysqli_stmt_close($stmt);
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Gagal registrasi: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SkyRay</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const toggle = document.querySelector(`[onclick="togglePassword('${id}')"]`);
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
            if (errorEl) {
                errorEl.classList.add('shake');
                setTimeout(() => errorEl.classList.remove('shake'), 500);
            }
        });
        <?php endif; ?>
    </script>
</head>
    <body style="margin: 0; background: none !important;">
<div class="login-hero" style="padding-top: 180px;">
        <header class="login-header">
            <h1>SkyRay Dessert</h1>
        </header>
        
        <?php if ($success): ?>
            <div class="login-form">
                <div class="form-title" style="color: #2ed573;">
                    <h2 style="color: #059669;">✅ Berhasil!</h2>
                </div>
                <div class="form-footer">
                    <a href="login.php" class="btn btn-login">Login Sekarang</a>
                </div>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-title">
                    <h2>Daftar</h2>
                </div>
                
                <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
                </div>
                
                <div class="input-group">
                    <input type="password" id="reg-password" name="password" placeholder="Password" required autocomplete="new-password" minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePassword('reg-password')">👁 Show</button>
                </div>
                
                <div class="input-group">
                    <input type="password" id="reg-confirm" name="confirm_password" placeholder="Confirm Password" required autocomplete="new-password">
                    <button type="button" class="password-toggle" onclick="togglePassword('reg-confirm')">👁 Show</button>
                </div>
                
                <button type="submit" class="btn btn-login">Daftar Akun</button>
                
                <div class="form-footer">
                    <p>Sudah punya akun? <a href="login.php" class="link-auth">Login sekarang</a></p>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
