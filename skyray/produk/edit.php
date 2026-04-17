<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php?from=admin");
    exit();
}

include '../koneksi.php';

$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT id_produk, nama_produk, harga, stok FROM produk WHERE id_produk = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: index.php");
    exit();
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $harga = filter_var($_POST['harga'] ?? 0, FILTER_VALIDATE_FLOAT);
    $stok = filter_var($_POST['stok'] ?? 0, FILTER_VALIDATE_INT);

    if (empty($nama) || $harga === false || $harga <= 0 || $stok === false || $stok < 0) {
        $error = "Data tidak valid: Nama harus diisi, harga dan stok harus angka positif!";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE produk SET nama_produk = ?, harga = ?, stok = ? WHERE id_produk = ?");
        mysqli_stmt_bind_param($stmt, "sdii", $nama, $harga, $stok, $id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Produk berhasil diupdate!";
        } else {
            $error = "Gagal mengupdate produk: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - SkyRay Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Produk: <?= htmlspecialchars($data['nama_produk']) ?></h1>
            <nav>
                <a href="index.php" class="btn">Kembali ke Daftar</a>
            </nav>
        </header>
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Nama Produk:</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? $data['nama_produk']) ?>" required><br><br>
            <label>Harga (Rp):</label>
            <input type="number" name="harga" step="0.01" min="0" value="<?= htmlspecialchars($_POST['harga'] ?? $data['harga']) ?>" required><br><br>
            <label>Stok:</label>
            <input type="number" name="stok" min="0" value="<?= htmlspecialchars($_POST['stok'] ?? $data['stok']) ?>" required><br><br>
            <button type="submit">Update Produk</button>
        </form>
    </div>
</body>
</html>

