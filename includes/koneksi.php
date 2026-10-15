<?php
// includes/koneksi.php

$host     = "localhost";
$username = "root";
$password = "";
$database = "ppid_bkk_banjarmasin"; // Sesuaikan dengan nama database Anda jika berbeda

// Inisialisasi variabel koneksi untuk mencegah warning linter (Intelephense)
/** @var mysqli|null $conn */
$conn = null;

// Membuat koneksi menggunakan MySQLi
$conn = new mysqli($host, $username, $password, $database);

// Periksa apakah koneksi berhasil atau gagal
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset UTF-8 untuk menghindari masalah karakter khusus
$conn->set_charset("utf8mb4");
?>