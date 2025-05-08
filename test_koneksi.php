<?php
$conn = mysqli_connect("localhost", "root", "", "rav_studio");

if ($conn) {
    echo "Koneksi Berhasil!";
} else {
    echo "Koneksi Gagal: " . mysqli_connect_error();
}
?>
