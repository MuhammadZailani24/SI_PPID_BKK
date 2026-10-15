<!-- dasar_hukum.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// Ambil data dasar hukum secara dinamis dari tabel database 'dasar_hukum'[cite: 19]
$data_hukum = [];
$query_hukum =$conn->query("SELECT * FROM dasar_hukum ORDER BY id ASC");
if ($query_hukum &&$query_hukum->num_rows > 0) {
    while ($row = $query_hukum->fetch_assoc()) {$data_hukum[] = [
            "judul"    => $row['judul_aturan'],
            "file_url" => $row['file_path']
        ];
    }
}
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN UNTUK DASAR HUKUM
       ========================================================= */
    .dh-wrapper {
        max-width: 1050px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .dh-list-container {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .dh-item {
        display: flex;
        align-items: center;
        background: #ffffff;
        border-radius: 16px;
        padding: 24px 30px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);
        border: 1px solid #eaeaea;
        position: relative;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    /* Aksen garis kiri dinamis */
    .dh-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 6px;
        height: 100%;
        background: linear-gradient(180deg, var(--primary, #00b2a9), #008f88);
        transition: background 0.35s ease;
    }

    /* Efek Hover Kartu Modern */
    .dh-item:hover {
        transform: translateY(-4px) translateX(6px);
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.12);
        border-color: #d1d5db;
    }

    .dh-item:hover::before {
        background: linear-gradient(180deg, var(--hover-color, #ef8a1c), #d97706);
    }

    /* Badge Nomor Urut Modern */
    .dh-number {
        background: #f8fafc;
        color: var(--primary, #00b2a9);
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 800;
        flex-shrink: 0;
        margin-right: 20px;
        border: 1px solid #f1f5f9;
        transition: all 0.35s ease;
    }

    .dh-item:hover .dh-number {
        background: var(--hover-color, #ef8a1c);
        color: white;
        border-color: var(--hover-color, #ef8a1c);
        transform: scale(1.05);
    }

    /* Judul Aturan Hukum */
    .dh-title {
        flex-grow: 1;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.6;
        padding-right: 25px;
    }

    /* Tombol Unduh Modern */
    .dh-btn {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 0.92rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(0, 178, 169, 0.25);
    }

    .dh-btn span {
        font-size: 1.1rem;
    }

    .dh-btn:hover {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        box-shadow: 0 6px 18px rgba(239, 138, 28, 0.35);
        transform: translateY(-2px);
    }

    /* Responsif untuk Layar HP & Tablet */
    @media (max-width: 768px) {
        .dh-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            padding: 20px;
        }
        .dh-number {
            margin-right: 0;
            margin-bottom: 5px;
        }
        .dh-title {
            padding-right: 0;
            font-size: 0.98rem;
        }
        .dh-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Dasar Hukum PPID</h1>
        <p>Landasan hukum pelaksanaan Keterbukaan Informasi Publik</p>
    </section>

    <div class="dh-wrapper">
        <div class="dh-list-container">
            
            <?php 
            $nomor = 1;
            if (!empty($data_hukum)) {
                // Looping data aturan hukum dari database[cite: 19]
                foreach($data_hukum as$hukum) { 
            ?>
                <div class="dh-item">
                    <div class="dh-number"><?php echo sprintf("%02d", $nomor); ?></div>
                    <div class="dh-title">
                        <?php echo htmlspecialchars($hukum['judul']); ?>
                    </div>
                    
                    <!-- Tombol Unduh yang memanggil fungsi JavaScript[cite: 19] -->
                    <button class="dh-btn" onclick="cekFile('<?php echo htmlspecialchars($hukum['file_url']); ?>')">
                        <span>📥</span> Unduh Dokumen
                    </button>
                </div>
            <?php 
                    $nomor++;
                } 
            } else {
                echo '<div style="background: white; padding: 50px; text-align: center; border-radius: 16px; color: #6b7280; border: 1px solid #eaeaea; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">Belum ada dokumen dasar hukum yang diunggah oleh admin.</div>';
            }
            ?>

        </div>
    </div>
</main>

<!-- SCRIPT UNTUK PENGECEKAN FILE[cite: 19] -->
<script>
    function cekFile(urlFile) {
        // Jika urlFile kosong (admin belum upload file)[cite: 19]
        if (urlFile === "" || urlFile === null) {
            // Tampilkan pop-up pemberitahuan[cite: 19]
            alert("Mohon maaf, file dokumen ini belum diunggah oleh Admin. Silakan periksa kembali nanti.");
        } else {
            // Jika file sudah ada, buka/download file tersebut[cite: 19]
            window.open(urlFile, '_blank');
        }
    }
</script>

<?php include 'includes/footer.php'; ?>