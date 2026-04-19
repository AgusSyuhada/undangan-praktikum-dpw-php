<?php
$host = "localhost";
$user = "root";       // sesuaikan dengan user MySQL Anda
$pass = "";           // sesuaikan dengan password MySQL Anda
$db = "undangan_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>