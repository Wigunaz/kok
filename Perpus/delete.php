<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $id = mysqli_real_escape_string($conn, $id);
    $sql = "DELETE FROM buku_2401010444 WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: admin.php");
        exit();
    } else {
        echo "Gagal menghapus data: " . mysqli_error($conn);
    }
} else {
    echo "Judul tidak ditemukan di URL.";
}
?>
