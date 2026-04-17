<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $id = (int)$_POST['id'];
        $qty = (int)$_POST['qty'];
        $_SESSION['cart'][$id] = $qty;
    } elseif (isset($_POST['remove'])) {
        $id = (int)$_POST['id'];
        unset($_SESSION['cart'][$id]);
    }
}

$total = 0;
$items = [];
if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $stmt = mysqli_prepare($conn, "SELECT id_produk AS id, nama_produk, harga FROM produk WHERE id_produk IN ($placeholders)");
    $types = str_repeat('i', count($_SESSION['cart']));
    $ids = array_keys($_SESSION['cart']);
    mysqli_stmt_bind_param($stmt, $types, ...$ids);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $qty = $_SESSION['cart'][$row['id']];
        $subtotal = $row['harga'] * $qty;
        $total += $subtotal;
        $items[] = $row + ['qty' => $qty, 'subtotal' => $subtotal];
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - SkyRay Dessert</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Keranjang Belanja (<?= count($items) ?>)</h1>
            <nav>
                <a href="home.php" class="btn">Home</a>
            </nav>
            <?php if (!isset($_SESSION['user'])): ?>
                <div style="text-align: center; color: #ff4757; padding: 20px; background: #ffe6e6; border-radius: 10px; margin: 20px 0;">
                    Login dulu untuk checkout!
                </div>
                <div style="text-align: center;">
                    <a href="login.php" class="btn">Login</a>
                    <a href="register.php" class="btn">Register</a>
                </div>
            <?php endif; ?>
        </header>
        <?php if (empty($items)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>Keranjang kosong. <a href="home.php">Mulai belanja</a></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                        <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="remove" value="1">
                                <button type="submit" class="btn small danger" onclick="return confirm('Hapus item ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="text-align: right; font-size: 1.3em; margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                <strong>Total: Rp <?= number_format($total, 0, ',', '.') ?></strong>
            </div>
            <a href="checkout.php" class="btn primary" style="font-size: 1.1em; padding: 15px 30px;">Checkout & Bayar</a>
        <?php endif; ?>
    </div>
</body>
</html>
