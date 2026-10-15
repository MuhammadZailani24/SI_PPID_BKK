<!-- fasilitas_layanan.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// AMBIL DATA DARI DATABASE (Tabel pengaturan_web)
// =========================================================================
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN ('kanal_offline', 'kanal_website', 'kanal_email', 'kanal_wa')");
if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Data Default (Fallback jika belum diatur di admin)
$default_offline = "Meja Layanan Informasi (Desk PPID) di Kantor BKK Kelas 1 Banjarmasin.";
$default_website = "Pengisian formulir elektronik melalui portal resmi ppid.bkkbanjarmasin.go.id.";
$default_email   = "Mengirimkan permohonan ke alamat email resmi: ppid@bkkbanjarmasin.go.id.";
$default_wa      = "Layanan konsultasi cepat melalui nomor admin: 0812-XXXX-XXXX.";

$teks_pengantar = "Guna memudahkan masyarakat dalam mengakses layanan, PPID menyediakan berbagai sarana dan prasarana layanan informasi publik, antara lain:";

// Array fasilitas dengan data yang ditarik langsung dari database
$data_fasilitas = [
    [
        "ikon"      => "🏢", 
        "judul"     => "Datang Langsung (Offline)", 
        "deskripsi" => $settings['kanal_offline'] ?? $default_offline
    ],
    [
        "ikon"      => "💻", 
        "judul"     => "Website (Online)", 
        "deskripsi" => $settings['kanal_website'] ?? $default_website
    ],
    [
        "ikon"      => "✉️", 
        "judul"     => "Surat Elektronik (Email)", 
        "deskripsi" => $settings['kanal_email'] ?? $default_email
    ],
    [
        "ikon"      => "📱", 
        "judul"     => "WhatsApp (Chat Only)", 
        "deskripsi" => $settings['kanal_wa'] ?? $default_wa
    ]
];
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN FASILITAS LAYANAN
       ========================================================= */
    .fasilitas-wrapper {
        max-width: 950px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .fasilitas-header-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 15px;
        position: relative;
        padding-bottom: 15px;
        letter-spacing: 0.3px;
    }

    .fasilitas-header-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background-color: var(--hover-color, #ef8a1c);
        border-radius: 2px;
    }

    .fasilitas-desc {
        font-size: 1.02rem;
        line-height: 1.75;
        color: #4b5563;
        margin-bottom: 35px;
    }

    /* LIST VERTIKAL KARTU MODERN */
    .fasilitas-list-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .fasilitas-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 28px 32px;
        border: 1px solid #eaeaea;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .fasilitas-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 6px;
        height: 100%;
        background: linear-gradient(180deg, var(--primary, #00b2a9), #008f88);
        transition: background 0.35s ease;
    }

    .fasilitas-card:hover {
        transform: translateY(-4px) translateX(6px);
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.12);
        border-color: #d1d5db;
    }

    .fasilitas-card:hover::before {
        background: linear-gradient(180deg, var(--hover-color, #ef8a1c), #d97706);
    }

    .fasilitas-icon-box {
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        color: var(--primary, #00b2a9);
        width: 65px;
        height: 65px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        flex-shrink: 0;
        border: 1px solid #99f6e4;
        transition: all 0.35s ease;
    }

    .fasilitas-card:hover .fasilitas-icon-box {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        color: white;
        border-color: transparent;
        transform: scale(1.05) rotate(3deg);
        box-shadow: 0 6px 18px rgba(239, 138, 28, 0.3);
    }

    .fasilitas-content {
        flex-grow: 1;
    }

    .fasilitas-card-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 8px;
        transition: color 0.3s ease;
    }

    .fasilitas-card:hover .fasilitas-card-title {
        color: var(--hover-color, #ef8a1c);
    }

    .fasilitas-card-text {
        font-size: 0.98rem;
        color: #4b5563;
        line-height: 1.7;
        margin: 0;
    }

    @media (max-width: 768px) {
        .fasilitas-card {
            flex-direction: column;
            align-items: flex-start;
            padding: 22px;
            gap: 18px;
        }
        .fasilitas-card:hover {
            transform: translateY(-4px);
        }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Kanal Layanan Informasi</h1>
        <p>Sarana dan fasilitas untuk mengakses layanan PPID secara offline maupun online</p>
    </section>

    <div class="fasilitas-wrapper">
        
        <h2 class="fasilitas-header-title">Fasilitas Layanan Utama</h2>
        <p class="fasilitas-desc">
            <?php echo $teks_pengantar; ?>
        </p>

        <!-- KONTENER LIST VERTIKAL -->
        <div class="fasilitas-list-container">
            
            <?php 
            foreach($data_fasilitas as $fasilitas) {
            ?>
                <div class="fasilitas-card">
                    <div class="fasilitas-icon-box">
                        <?php echo $fasilitas['ikon']; ?>
                    </div>
                    <div class="fasilitas-content">
                        <div class="fasilitas-card-title">
                            <?php echo htmlspecialchars($fasilitas['judul']); ?>
                        </div>
                        <p class="fasilitas-card-text">
                            <?php echo htmlspecialchars($fasilitas['deskripsi']); ?>
                        </p>
                    </div>
                </div>
            <?php } ?>

        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>