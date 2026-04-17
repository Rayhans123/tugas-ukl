<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php?from=admin");
    exit();
}

include '../koneksi.php';

$success = $error = '';
$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $harga = filter_var($_POST['harga'] ?? 0, FILTER_VALIDATE_FLOAT);
    $stok = filter_var($_POST['stok'] ?? 0, FILTER_VALIDATE_INT);
    $image_name = '';

    // Handle image upload (optional)
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_extension, $allowed) && $_FILES['image']['size'] < 2000000) {
            $image_name = uniqid() . '.' . $file_extension;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                // Upload success
            } else {
                $error = "Gagal menyimpan gambar ke server!";
                $image_name = '';
            }
        } else {
            $error = "Gambar tidak valid (JPG/PNG/WEBP <2MB)!";
        }
    }

    if (empty($nama) || $harga === false || $harga <= 0 || $stok === false || $stok < 0) {
        $error = "Data tidak valid!";
    } else {
        // Use produk table (no image column needed - files only)
        if (!empty($image_name)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO produk (nama_produk, harga, stok, gambar) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sdis", $nama, $harga, $stok, $image_name);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO produk (nama_produk, harga, stok) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sdi", $nama, $harga, $stok);
        }
        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($conn);
            $success = "Produk '$nama' berhasil ditambahkan! ID: $new_id";
            if (!empty($image_name)) {
                $success .= ". Gambar '$image_name' diupload ke /uploads/ (manual link DB)";
            }
            $_POST = []; // Clear form
        } else {
            $error = "Gagal simpan database: " . mysqli_error($conn);
            if ($image_name) unlink($upload_dir . $image_name);
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
    <title>Tambah Produk - SkyRay Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Tambah Produk + Upload Gambar (Opsional)</h1>
            <nav>
                <a href="index.php" class="btn">Daftar Produk</a>
                <a href="../home.php" class="btn">Preview Home</a>
            </nav>
        </header>
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <label>Nama Produk:</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required><br><br>
            <label>Harga (Rp):</label>
            <input type="number" name="harga" step="0.01" min="1" value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>" required><br><br>
            <label>Stok:</label>
            <input type="number" name="stok" min="0" value="<?= htmlspecialchars($_POST['stok'] ?? '') ?>" required><br><br>
            <label>Gambar (JPG/PNG/WEBP <2MB, optional):</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"><br><br>
            <button type="submit">Simpan Produk</button>
        </form>
        <p style="color: #666; font-size: 0.9em; margin-top: 20px;">
           
        </p>
    </div>
</body>
</html>

