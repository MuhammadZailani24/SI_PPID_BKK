<!-- tugas_fungsi.php -->
<?php 
require_once 'includes/koneksi.php'; 

// Ambil data Tugas dan Fungsi dari database tabel pengaturan_web
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN ('teks_tugas', 'teks_fungsi')");
if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Data default (fallback) jika database belum diisi
$default_tugas = "1. Melaksanakan pengelolaan informasi publik.";
$default_fungsi = "1. Pelaksanaan pengelolaan informasi publik\n2. Penyusunan dan pemutakhiran daftar informasi publik\n3. Pengujian konsekuensi\n4. Penyelesaian sengketa informasi publik\n5. Pengembangan pengelolaan informasi publik\n6. Penyusunan laporan semester dan tahunan pelaksanaan pengelolaan informasi publik\n7. Koordinasi dengan PPID pelaksana dan PPID pembantu dalam pelaksanaan informasi publik\n8. Pembinaan, pengawasan, dan evaluasi pelaksanaan pengelolaan informasi.";

$teks_tugas  = $settings['teks_tugas'] ?? $default_tugas;
$teks_fungsi = $settings['teks_fungsi'] ?? $default_fungsi;

$pengantar_tugas = "PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin mempunyai tugas";
$pengantar_fungsi = "Dalam melaksanakan tugas tersebut, PPID menyelenggarakan fungsi";

include 'includes/header.php'; 
?>

<style>
    /* =========================================================
       STYLING MODERN & HIDUP UNTUK TUGAS & FUNGSI
       ========================================================= */
    .tf-wrapper {
        max-width: 1200px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .tf-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 35px;
        align-items: stretch;
    }

    .tf-box {
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
    .tf-box::before {
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
    .tf-box:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 178, 169, 0.12);
        border-color: #d1d5db;
    }

    .tf-box:hover::before {
        background: linear-gradient(90deg, var(--hover-color, #ef8a1c), #d97706);
    }

    .tf-header {
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

    .tf-icon {
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
        transition: all 0.3s ease;
    }

    /* Header berubah interaktif saat card di-hover */
    .tf-box:hover .tf-header {
        background: #fffbeb;
        border-color: #fde68a;
        color: #9a3412;
    }

    .tf-box:hover .tf-icon {
        background: var(--hover-color, #ef8a1c);
        transform: rotate(5deg) scale(1.05);
        box-shadow: 0 6px 15px rgba(239, 138, 28, 0.35);
    }

    .tf-intro {
        font-size: 1.02rem;
        color: #4b5563;
        margin-bottom: 20px;
        font-weight: 600;
        line-height: 1.6;
    }

    /* Daftar List Modern dengan Badge Nomor */
    .tf-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 14px;
        flex-grow: 1;
    }

    .tf-list li {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        background: #f9fafb;
        padding: 15px 18px;
        border-radius: 12px;
        border: 1px solid #f3f4f6;
        font-size: 0.98rem;
        color: #374151;
        line-height: 1.6;
        transition: all 0.25s ease;
    }

    .tf-list li:hover {
        background: #ffffff;
        border-color: var(--primary, #00b2a9);
        transform: translateX(6px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    .tf-badge {
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

    .tf-list li:hover .tf-badge {
        background: var(--hover-color, #ef8a1c);
    }

    /* Responsif untuk layar HP & Tablet */
    @media (max-width: 900px) {
        .tf-container {
            grid-template-columns: 1fr;
            gap: 25px;
        }
    }
</style>

<main>
    <!-- Header Banner -->
    <section class="inner-header">
        <h1>Tugas dan Fungsi</h1>
        <p>Tugas dan Fungsi Pejabat Pengelola Informasi dan Dokumentasi (PPID)</p>
    </section>

    <!-- Konten Utama -->
    <div class="tf-wrapper">
        <div class="tf-container">
            
            <!-- KOLOM KIRI: TUGAS -->
            <div class="tf-box">
                <div class="tf-header">
                    <div class="tf-icon">📋</div>
                    <span>Tugas PPID</span>
                </div>
                <p class="tf-intro"><?php echo $pengantar_tugas; ?>:</p>
                <ul class="tf-list">
                    <?php 
                    // Memecah teks tugas secara dinamis berdasarkan baris
                    $tugas_lines = array_filter(array_map('trim', explode("\n", $teks_tugas)));
                    $nomor_tugas = 1;
                    foreach($tugas_lines as $item) {
                        $item_clean = preg_replace('/^\d+[\.\)]\s*/', '', $item);
                        if (!empty($item_clean)) {
                            echo '<li>';
                            echo '<span class="tf-badge">' . $nomor_tugas++ . '</span>';
                            echo '<span>' . htmlspecialchars($item_clean) . '</span>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>

            <!-- KOLOM KANAN: FUNGSI -->
            <div class="tf-box">
                <div class="tf-header">
                    <div class="tf-icon">⚙️</div>
                    <span>Fungsi PPID</span>
                </div>
                <p class="tf-intro"><?php echo $pengantar_fungsi; ?>:</p>
                <ul class="tf-list">
                    <?php 
                    // Memecah teks fungsi secara dinamis berdasarkan baris
                    $fungsi_lines = array_filter(array_map('trim', explode("\n", $teks_fungsi)));
                    $nomor_fungsi = 1;
                    foreach($fungsi_lines as $item) {
                        $item_clean = preg_replace('/^\d+[\.\)]\s*/', '', $item);
                        if (!empty($item_clean)) {
                            echo '<li>';
                            echo '<span class="tf-badge">' . $nomor_fungsi++ . '</span>';
                            echo '<span>' . htmlspecialchars($item_clean) . '</span>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>

        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>