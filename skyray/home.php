<?php 
session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyRay Dessert - Kue & Pudding Premium</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="home-page" style="background: white;">
    <header style="background: linear-gradient(135deg, #ff69b4, #ff1493); color: white; padding: 20px; text-align: center;">
        <h1 style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); margin: 0;">SkyRay Dessert</h1>
        <p style="color: white; font-weight: 300; margin: 10px 0;">Kue, Pudding, Donat Premium - Segar Setiap Hari!</p>
        <nav style="margin-top: 20px;">
            <a href="home.php" class="btn">Home</a>
            <?php if (isset($_SESSION['is_admin'])): ?>
                <a href="produk/index.php" class="btn">Kelola Produk</a>
            <?php endif; ?>
            <a href="cart.php" class="btn">Keranjang (<?= count($_SESSION['cart'] ?? []) ?>)</a>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="?logout=1" class="btn danger">Logout (<?= htmlspecialchars($_SESSION['user']) ?>)</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
                <a href="register.php" class="btn">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    
    <div class="container" style="background: white;">
        <section style="text-align: center; margin: 40px 0;">
            <h2 style="color: #333;">Produk Terfavorit</h2>
            <?php 
            include 'koneksi.php';
            $stmt = mysqli_prepare($conn, "SELECT id_produk, nama_produk, harga, stok, gambar FROM produk ORDER BY id_produk DESC LIMIT 6");
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; margin-top: 30px;">
                <?php while ($product = mysqli_fetch_assoc($result)): ?>
                <div style="background: white; padding: 25px; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); transition: transform 0.3s;">
                    <?php if (!empty($product['gambar']) && file_exists("uploads/" . $product['gambar'])): ?>
                        <img src="uploads/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>" style="width: 100%; height: 250px; object-fit: cover; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <?php else: ?>
                        <div class="no-image">No Image</div>
                    <?php endif; ?>
                    <div style="margin-top: 20px;">
                        <h3 style="margin: 0 0 10px 0; color: #333; font-weight: bold;"><?= htmlspecialchars($product['nama_produk']) ?></h3>
                        <p style="font-size: 1.3em; font-weight: bold; color: #e91e63; margin: 10px 0;">Rp <?= number_format($product['harga'], 0, ',', '.') ?></p>
                        <p style="color: #666; margin: 5px 0;">Stok: <span style="color: <?= $product['stok'] > 0 ? '#4caf50' : '#f44336' ?>; font-weight: bold;"><?= $product['stok'] ?></span></p>
                        <form method="POST" action="cart.php" style="display: flex; gap: 10px; margin-top: 20px; align-items: center;">
                            <input type="hidden" name="id" value="<?= $product['id_produk'] ?>">
                            <input type="number" name="qty" value="1" min="1" max="<?= $product['stok'] ?>" style="width: 70px; padding: 10px; border: 2px solid #ff69b4; border-radius: 8px; font-weight: bold;">
                            <button type="submit" name="add" class="btn" style="flex: 1; background: linear-gradient(135deg, #ff69b4, #ff1493); border: none; padding: 12px 20px; font-weight: bold; border-radius: 25px; color: white;">Add to Cart</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; mysqli_stmt_close($stmt); ?>
            </div>
        </section>
        
        <section style="background: linear-gradient(135deg, #fff0f5, #ffe4e1); padding: 50px 20px; border-radius: 20px; text-align: center; margin: 60px 0;">
            <h2 style="color: #333;">Kenapa SkyRay?</h2>
            <p style="font-size: 1.1em; color: #666; max-width: 600px; margin: 0 auto 30px;">Bahan premium, handmade fresh daily.</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; max-width: 800px; margin: 0 auto;">
                <div style="font-size: 1.1em; color: #333;"><strong>100% Natural</strong></div>
                <div style="font-size: 1.1em; color: #333;"><strong>Rating 4.9/5</strong></div>
            </div>
        </section>
    </div>
    
    <style>
    body {
        margin: 0;
        background: white;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    .no-image {
        width: 100%;
        height: 250px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 2em;
        color: #adb5bd;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    div[style*="box-shadow"] {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    div[style*="box-shadow"]:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 32px rgba(0,0,0,0.15);
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    </style>
    
    <?php include 'footer.php'; ?>
    
</body>
</html>
