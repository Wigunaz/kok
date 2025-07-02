<?php


$host = "localhost"; // Ganti dengan host server MySQL jika perlu
$username = "root";  // Ganti dengan username MySQL Anda
$password = "";      // Ganti dengan password MySQL Anda
$dbname = "db_2401010444"; // Ganti dengan nama database yang ingin Anda hubungkan

// Membuat koneksi ke database MySQL
$conn = mysqli_connect($host, $username, $password, $dbname);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
} else {
    echo "";
}
?>
