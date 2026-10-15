<!-- detail_informasi.php -->
<?php include 'includes/header.php'; 
// Menangkap parameter kategori dari URL agar tampil dinamis
$kategori = isset($_GET['kategori']) ? str_replace('_', ' ', $_GET['kategori']) : 'Informasi Publik';
?>

<main>
    <section class="inner-header">
        <h1>Detail Informasi Publik</h1>
        <p>Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin</p>
    </section>

    <section class="inner-content">
        <a href="informasi_publik.php" class="btn-back">&laquo; Kembali ke Daftar Informasi</a>
        
        <h2>Kategori: <?php echo htmlspecialchars($kategori); ?></h2>
        <p>Berikut adalah rincian informasi lengkap serta kumpulan dokumen resmi yang berada di bawah penguasaan Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin untuk kategori ini.</p>
        
        <table class="detail-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Dokumen / Informasi Publik</th>
                    <th>Tahun Terbit</th>
                    <th>Aksi Unduh</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Dokumen Resmi Transparansi dan Akuntabilitas Layanan <?php echo htmlspecialchars($kategori); ?></td>
                    <td>2026</td>
                    <td><a href="#" class="btn-download">Unduh PDF</a></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Laporan Kinerja dan Rekapitulasi Pendukung Kategori <?php echo htmlspecialchars($kategori); ?></td>
                    <td>2026</td>
                    <td><a href="#" class="btn-download">Unduh PDF</a></td>
                </tr>
            </tbody>
        </table>
    </section>
</main>

<?php include 'includes/footer.php'; ?>