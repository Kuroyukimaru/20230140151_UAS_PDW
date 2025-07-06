<?php
// Pengaturan koneksi database
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'myklass';

// Membuat koneksi menggunakan mysqli (OOP)
$conn = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>
