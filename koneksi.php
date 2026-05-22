<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$db = "lab";

$conn = mysqli_connect($host, $user, $pass, $db);
$koneksi = $conn;

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>