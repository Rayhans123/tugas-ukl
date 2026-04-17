<?php
session_start();
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['checkout_redirect'] = true;
    header("Location: login.php");
    exit();
}
include 'koneksi.php';

$total = 0;
foreach ($_SESSION['cart'] as $id => $qty) {
    $stmt = mysqli_prepare($conn, "SELECT harga FROM produk WHERE id_produk = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $harga = mysqli_fetch_assoc($result)['harga'] ?? 0;
    $total += $harga * $qty;
    mysqli_stmt_close($stmt);
}

// Disable FK check for demo
mysqli_query($conn, 'SET foreign_key_checks = 0');

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, total) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "id", $_SESSION['user_id'], $total);
    if (mysqli_stmt_execute($stmt)) {
        $order_id = mysqli_insert_id($conn);
        foreach ($_SESSION['cart'] as $id => $qty) {
            $stmt2 = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt2, "iiid", $order_id, $id, $qty, $harga);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
            
            // Update stock
            $stmt3 = mysqli_prepare($conn, "UPDATE produk SET stok = stok - ? WHERE id_produk = ?");
            mysqli_stmt_bind_param($stmt3, "ii", $qty, $id);
            mysqli_stmt_execute($stmt3);
            mysqli_stmt_close($stmt3);
        }
        unset($_SESSION['cart']);
        mysqli_query($conn, 'SET foreign_key_checks = 1');
        $success = "Pesanan berhasil! ID Order: #$order_id. Total: Rp " . number_format($total, 0, ',', '.');
    } else {
        mysqli_query($conn, 'SET foreign_key_checks = 1');
        $error = "Gagal membuat pesanan: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    mysqli_query($conn, 'SET foreign_key_checks = 1');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - SkyRay Dessert</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Checkout</h1>
            <nav>
                <a href="cart.php" class="btn">← Kembali Keranjang</a>
            </nav>
        </header>
        <?php if (isset($success)): ?>
            <div class="success" style="text-align: center; padding: 40px; font-size: 1.2em; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 12px; margin: 20px 0;">
                <?= $success ?>
                <br><br>
                <a href="login.php" class="btn">Kembali Login</a>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="error"><?= $error ?></div>
            <a href="cart.php" class="btn">Coba Lagi</a>
        <?php else: ?>
            <div style="background: white; padding: 32px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: 20px 0;">
                <h2>Total Belanja</h2>
                <div style="font-size: 2em; font-weight: bold; color: #ff6b9d; text-align: right; margin: 20px 0;">
                    Rp <?= number_format($total, 0, ',', '.') ?>
                </div>
                <form method="POST" style="text-align: center;">
                    <button type="submit" class="btn" style="font-size: 1.2em; padding: 16px 40px; background: linear-gradient(135deg, #ff6b9d, #ff4773);">✅ Konfirmasi Pesanan</button>
                </form>
                <p style="text-align: center; color: #666; margin-top: 20px;">Demo checkout - pesanan tersimpan di database</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
