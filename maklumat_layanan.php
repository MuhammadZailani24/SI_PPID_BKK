<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// AMBIL DATA FILE MAKLUMAT DARI DATABASE (Tabel pengaturan_web)
// =========================================================================
$query = $conn->query("SELECT nilai_pengaturan FROM pengaturan_web WHERE kunci_pengaturan = 'file_maklumat'");
$row = $query ? $query->fetch_assoc() : null;
$file_maklumat = $row['nilai_pengaturan'] ?? '';
?>

<style>
    /* Wrapper Utama */
    .maklumat-wrapper {
        max-width: 900px;
        margin: 40px auto 80px auto;
        padding: 0 20px;
    }

    /* Styling Kartu Maklumat */
    .maklumat-card {
        background-color: white;
        border-radius: 16px;
        border: 1px solid #eaeaea;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        padding: 40px;
        text-align: center;
    }

    .maklumat-header-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary, #00b2a9);
        margin-bottom: 15px;
    }

    .maklumat-desc {
        font-size: 1.05rem;
        line-height: 1.7;
        color: #444;
        margin-bottom: 30px;
    }

    .pdf-container {
        width: 100%;
        height: 600px;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-top: 20px;
    }

    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: var(--primary, #00b2a9);
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        font-size: 1rem;
        margin-top: 20px;
        transition: 0.3s ease;
    }

    .btn-download:hover {
        background-color: var(--primary-dark, #008f88);
        box-shadow: 0 5px 15px rgba(0, 178, 169, 0.3);
    }

    .alert-warning-custom {
        background-color: #fef9c3;
        color: #854d0e;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #fef08a;
        font-weight: 600;
        text-align: left;
    }

    @media (max-width: 768px) {
        .maklumat-card { padding: 20px; }
        .pdf-container { height: 400px; }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Maklumat Pelayanan</h1>
        <p>Pernyataan kesanggupan dan kewajiban penyelenggara dalam menyelenggarakan pelayanan publik</p>
    </section>

    <div class="maklumat-wrapper">
        <div class="maklumat-card">
            <h2 class="maklumat-header-title">Maklumat Pelayanan PPID</h2>
            <p class="maklumat-desc">
                Berikut adalah dokumen resmi Maklumat Pelayanan Publik Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin yang berlaku.
            </p>

            <?php if (!empty($file_maklumat) && file_exists($file_maklumat)): ?>
                <?php 
                    $file_ext = strtolower(pathinfo($file_maklumat, PATHINFO_EXTENSION));
                ?>
                
                <?php if ($file_ext == 'pdf'): ?>
                    <iframe src="<?php echo htmlspecialchars($file_maklumat); ?>" class="pdf-container"></iframe>
                    <div style="margin-top: 25px;">
                        <a href="<?php echo htmlspecialchars($file_maklumat); ?>" target="_blank" class="btn-download">
                            📥 Unduh / Buka Dokumen Maklumat (PDF)
                        </a>
                    </div>
                <?php elseif (in_array($file_ext, ['jpg', 'jpeg', 'png'])): ?>
                    <div style="margin-top: 20px;">
                        <img src="<?php echo htmlspecialchars($file_maklumat); ?>" alt="Maklumat Pelayanan" style="max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <div style="margin-top: 25px;">
                        <a href="<?php echo htmlspecialchars($file_maklumat); ?>" target="_blank" class="btn-download">
                            🔍 Lihat Gambar Ukuran Penuh
                        </a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert-warning-custom">
                    ⚠️ Dokumen maklumat pelayanan belum diunggah oleh admin melalui panel pengelola. Silakan unggah dokumen melalui halaman admin.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>