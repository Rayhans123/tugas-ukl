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

// Optional: confirm via POST, but for simplicity delete with JS confirm already in list
$stmt = mysqli_prepare($conn, "DELETE FROM produk WHERE id_produk = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
if (mysqli_stmt_execute($stmt)) {
    // Success
} else {
    // Error logged, but redirect anyway
}
mysqli_stmt_close($stmt);

header("Location: index.php");
exit();
?>

