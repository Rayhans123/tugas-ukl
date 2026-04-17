<?php
session_start();
if (!isset($_SESSION['is_admin'])) {
    header("Location: ../login.php?from=admin");
    exit();
}

include '../koneksi.php';

$stmt = mysqli_prepare($conn, "SELECT id_produk, nama_produk, harga, stok FROM produk ORDER BY id_produk DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - SkyRay Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Data Produk Admin</h1>
            <nav>
                <a href="../home.php" class="btn">Home</a>
                <a href="tambah.php" class="btn primary">Tambah Produk</a>
            </nav>
        </header>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>ID</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: #666;">Tidak ada produk. Tambah yang pertama!</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['nama_produk']) ?></strong></td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td><span style="color: <?= $row['stok'] > 0 ? '#4caf50' : '#f44336' ?>"><?= $row['stok'] ?></span></td>
                        <td><code>#<?= $row['id_produk'] ?></code></td>
                        <td>
                            <a href="edit.php?id=<?= $row['id_produk'] ?>" class="btn small primary">Edit</a>
                            <a href="hapus.php?id=<?= $row['id_produk'] ?>" class="btn small danger" onclick="return confirm('Hapus <?= htmlspecialchars($row['nama_produk']) ?>?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

