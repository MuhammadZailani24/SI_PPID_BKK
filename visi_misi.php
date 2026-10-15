<!-- visi_misi.php -->
<?php 
require_once 'includes/koneksi.php';

// Ambil data Visi dan Misi dari database menggunakan kunci yang sesuai dengan admin ('teks_visi', 'teks_misi')[cite: 19]
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN ('teks_visi', 'teks_misi')");
if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Data default (fallback) jika database belum diisi
$default_visi = "Menjadi penyelenggara layanan informasi publik yang transparan, akuntabel, dan tepercaya dalam mewujudkan masyarakat yang informatif.";
$default_misi = "1. Menyediakan informasi publik yang akurat dan mudah diakses.\n2. Meningkatkan kualitas pelayanan informasi secara berkelanjutan.\n3. Membangun sistem informasi berbasis teknologi yang andal.";

// Menyesuaikan dengan kunci database admin
$visi = $settings['teks_visi'] ?? $default_visi;
$misi = $settings['teks_misi'] ?? $default_misi;

include 'includes/header.php'; 
?>

<style>
    /* =========================================================
       STYLING MODERN & HIDUP UNTUK VISI & MISI
       ========================================================= */
    .vm-wrapper {
        max-width: 1100px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .vm-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 35px;
        align-items: stretch;
    }

    .vm-box {
        background: #ffffff;
        border-radius: 18px;
        padding: 40px;
        border: 1px solid #eaeaea;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: flex;
        flex-direction: column;
    }

    /* Aksen garis atas dinamis */
    .vm-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
        background: linear-gradient(90deg, var(--primary, #00b2a9), #008f88);
        transition: background 0.35s ease;
    }

    /* Efek Hover Kartu Modern */
    .vm-box:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 178, 169, 0.12);
        border-color: #d1d5db;
    }

    .vm-box:hover::before {
        background: linear-gradient(90deg, var(--hover-color, #ef8a1c), #d97706);
    }

    .vm-header {
        display: flex;
        align-items: center;
        gap: 15px;
        background: #f8fafc;
        color: #1f2937;
        font-size: 1.25rem;
        font-weight: 800;
        padding: 18px 22px;
        border-radius: 12px;
        margin-bottom: 25px;
        border: 1px solid #f1f5f9;
        transition: all 0.35s ease;
    }

    .vm-icon {
        width: 40px;
        height: 40px;
        background: var(--primary, #00b2a9);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        box-shadow: 0 4px 10px rgba(0, 178, 169, 0.25);
        transition: all 0.35s ease;
    }

    /* Header berubah interaktif saat card di-hover */
    .vm-box:hover .vm-header {
        background: #fffbeb;
        border-color: #fde68a;
        color: #9a3412;
    }

    .vm-box:hover .vm-icon {
        background: var(--hover-color, #ef8a1c);
        transform: rotate(5deg) scale(1.05);
        box-shadow: 0 6px 15px rgba(239, 138, 28, 0.35);
    }

    .vm-content {
        font-size: 1.05rem;
        line-height: 1.85;
        color: #4b5563;
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .vm-vision-text {
        font-weight: 700;
        text-align: center;
        font-size: 1.25rem;
        color: #1f2937;
        line-height: 1.7;
        font-style: italic;
        padding: 15px 0;
    }

    /* Daftar Misi Modern dengan Badge Nomor */
    .vm-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 15px;
        width: 100%;
    }

    .vm-list li {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        background: #f9fafb;
        padding: 16px 18px;
        border-radius: 12px;
        border: 1px solid #f3f4f6;
        font-size: 1rem;
        color: #374151;
        line-height: 1.6;
        transition: all 0.25s ease;
    }

    .vm-list li:hover {
        background: #ffffff;
        border-color: var(--primary, #00b2a9);
        transform: translateX(6px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    .misi-badge {
        background: var(--primary, #00b2a9);
        color: white;
        min-width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        flex-shrink: 0;
        margin-top: 2px;
        transition: background 0.3s ease;
    }

    .vm-list li:hover .misi-badge {
        background: var(--hover-color, #ef8a1c);
    }

    /* Responsif untuk layar HP & Tablet */
    @media (max-width: 900px) {
        .vm-container {
            grid-template-columns: 1fr;
            gap: 25px;
        }
    }
</style>

<main>
    <!-- Header Banner -->
    <section class="inner-header">
        <h1>Visi dan Misi</h1>
        <p>Penyelenggaraan Informasi Publik</p>
    </section>

    <!-- Konten Utama -->
    <div class="vm-wrapper">
        <div class="vm-container">
            
            <!-- KOTAK VISI -->
            <div class="vm-box">
                <div class="vm-header">
                    <div class="vm-icon">🎯</div>
                    <span>Visi Organisasi</span>
                </div>
                <div class="vm-content">
                    <p class="vm-vision-text">
                        "<?php echo htmlspecialchars($visi); ?>"
                    </p>
                </div>
            </div>

            <!-- KOTAK MISI -->
            <div class="vm-box">
                <div class="vm-header">
                    <div class="vm-icon">🚀</div>
                    <span>Misi Pelayanan</span>
                </div>
                <div class="vm-content" style="width: 100%;">
                    <ul class="vm-list">
                        <?php 
                        // Memecah teks misi berdasarkan baris agar otomatis menjadi list berformat modern
                        $misi_lines = array_filter(array_map('trim', explode("\n", $misi)));
                        $nomor = 1;
                        foreach ($misi_lines as $item) {
                            // Menghapus nomor otomatis bawaan teks jika ada agar tidak berlipat ganda
                            $item_clean = preg_replace('/^\d+[\.\)]\s*/', '', $item);
                            if (!empty($item_clean)) {
                                echo '<li>';
                                echo '<span class="misi-badge">' . $nomor++ . '</span>';
                                echo '<span>' . htmlspecialchars($item_clean) . '</span>';
                                echo '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>