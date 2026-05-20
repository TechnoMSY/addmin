<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "lab";

$conn = mysqli_connect($host, $user, $pass, $db);
$koneksi = $conn; // Tambahkan ini agar aman untuk kodingan lama yang menggunakan $koneksi

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>