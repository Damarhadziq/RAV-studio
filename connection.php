<?php
$localhost  = "localhost";
$hostname   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $hostname, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
