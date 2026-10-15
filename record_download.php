<?php
require_once 'includes/koneksi.php';

// Buat tabel log_download secara otomatis jika belum ada
$conn->query("CREATE TABLE IF NOT EXISTS log_download (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul_file VARCHAR(255),
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Tangkap data judul file yang diklik user
if (isset($_GET['judul'])) {
    $judul = $conn->real_escape_string($_GET['judul']);
    $conn->query("INSERT INTO log_download (judul_file) VALUES ('$judul')");
}
?>