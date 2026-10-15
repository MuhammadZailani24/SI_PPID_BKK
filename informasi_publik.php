<!-- informasi_berkala.php -->
<?php 
require_once 'includes/koneksi.php'; // Hubungkan ke database[cite: 19]
include 'includes/header.php'; //[cite: 19]

// =========================================================================
// 1. Kerangka Dasar (Skeleton) Data Informasi Publik[cite: 19]
// Menjamin kategori dan sub-kategori tetap tampil meski belum ada file dari admin[cite: 19]
// =========================================================================
$data_informasi = [
    "Profil" => [
        "Tugas dan Fungsi" => [],
        "Struktur Organisasi" => [],
        "Profil Pimpinan" => [],
        "Wilayah Kerja" => [],
        "Kontak" => []
    ],
    "Dokumen Keuangan" => [
        "Rencana Kerja Anggaran" => [],
        "Neraca" => [],
        "Laporan Realisasi Anggaran" => [],
        "Laporan Keuangan" => [],
        "Laporan Barang Milik Negara" => []
    ],
    "Dokumen Perencanaan dan Laporan Kinerja" => [
        "Rencana Aksi Kegiatan" => [],
        "Rencana Kinerja Tahunan" => [],
        "Perjanjian Kinerja" => [],
        "Laporan Kinerja" => [],
        "Laporan Profil" => []
    ]
];

// =========================================================================
// 2. Ambil Data dari Database dan Masukkan ke dalam Kerangka[cite: 19]
// =========================================================================
$query_dokumen = $conn->query("SELECT * FROM informasi_publik ORDER BY id ASC");
if ($query_dokumen && $query_dokumen->num_rows > 0) {
    while ($row = $query_dokumen->fetch_assoc()) {
        $kategori_utama = $row['kategori_utama'];
        $sub_kategori   = $row['sub_kategori'];
        
        // Memastikan kategori dari DB cocok dengan kerangka yang kita buat[cite: 19]
        if (isset($data_informasi[$kategori_utama][$sub_kategori])) {
            $data_informasi[$kategori_utama][$sub_kategori][] = [
                "judul" => $row['judul_dokumen'],
                "link"  => !empty($row['file_path']) ? $row['file_path'] : '#'
            ];
        }
    }
}
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN INFORMASI PUBLIK BERKALA
       ========================================================= */
    .berkala-wrapper {
        max-width: 1250px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .berkala-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        align-items: start;
    }

    .kategori-col {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Kotak Header Kategori Modern */
    .kategori-header-box {
        background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        border: 1px solid #eaeaea;
        border-radius: 16px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        height: 160px; 
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        cursor: default;
    }

    .kategori-header-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--primary, #00b2a9), #008f88);
        transition: background 0.35s ease;
    }

    /* Efek Hover Header Kategori */
    .kategori-header-box:hover {
        transform: translateY(-6px);
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        border-color: transparent;
        box-shadow: 0 15px 35px rgba(239, 138, 28, 0.25);
    }

    .kategori-header-box:hover::before {
        background: rgba(255, 255, 255, 0.4);
    }

    .kategori-icon {
        font-size: 2.3rem;
        margin-bottom: 10px;
        transition: transform 0.3s ease;
        line-height: 1;
    }

    .kategori-title {
        color: #1f2937;
        font-weight: 800;
        font-size: 1.05rem;
        line-height: 1.4;
        transition: color 0.3s ease;
    }

    /* Warna Berubah Menjadi Putih saat Header di-hover */
    .kategori-header-box:hover .kategori-title,
    .kategori-header-box:hover .kategori-icon {
        color: white;
        transform: scale(1.05);
    }

    /* Kontainer List Accordion Modern */
    .accordion-container {
        background: #ffffff;
        border: 1px solid #eaeaea;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
    }

    .accordion-item {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.3s ease;
    }
    
    .accordion-item:last-child {
        border-bottom: none;
    }

    .accordion-header {
        padding: 16px 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        background-color: #ffffff;
        transition: all 0.25s ease;
    }

    .accordion-header:hover {
        background-color: #f8fafc;
    }

    .accordion-title {
        font-size: 0.95rem;
        color: #374151;
        font-weight: 700;
        transition: color 0.25s ease;
    }

    .accordion-icon {
        width: 28px;
        height: 28px;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        color: #4b5563;
        transition: all 0.3s ease;
    }

    /* Keadaan Aktif (Saat Accordion Terbuka) */
    .accordion-item.active .accordion-header {
        background-color: #fffbeb;
    }
    
    .accordion-item.active .accordion-title {
        color: var(--hover-color, #ef8a1c);
    }

    .accordion-item.active .accordion-icon {
        background: var(--hover-color, #ef8a1c);
        color: white;
        transform: rotate(180deg);
    }

    .accordion-item.active .accordion-icon::before {
        content: "−"; 
    }
    
    .accordion-item .accordion-icon::before {
        content: "+";
    }

    /* Konten File PDF di dalam Accordion */
    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-out, padding 0.4s ease;
        background-color: #ffffff;
    }

    .accordion-item.active .accordion-content {
        max-height: 800px; 
        padding: 12px 22px 22px 22px;
    }

    .file-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .file-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        font-size: 0.9rem;
        color: #4b5563;
        background: #f9fafb;
        border-radius: 10px;
        text-decoration: none;
        border: 1px solid #f3f4f6;
        transition: all 0.25s ease;
    }

    .file-link:hover {
        background: #ffffff;
        color: var(--primary, #00b2a9); 
        border-color: var(--primary, #00b2a9);
        transform: translateX(6px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .empty-state {
        font-size: 0.85rem;
        color: #9ca3af;
        font-style: italic;
        padding: 5px 10px;
    }

    /* Responsif untuk Tablet & HP */
    @media (max-width: 992px) {
        .berkala-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Informasi Publik Berkala</h1>
        <p>Kumpulan informasi yang wajib disediakan dan diumumkan secara berkala</p>
    </section>

    <div class="berkala-wrapper">
        <div class="berkala-grid">
            
            <?php 
            // Array Ikon Khusus untuk tiap Kolom Utama[cite: 19]
            $icons = [
                "Profil" => "👥", 
                "Dokumen Keuangan" => "💰", 
                "Dokumen Perencanaan dan Laporan Kinerja" => "📑"
            ];

            // LOOPING 1: Mengambil 3 Kolom Utama[cite: 19]
            foreach($data_informasi as $kategori_utama => $sub_kategori) { 
            ?>
                
                <div class="kategori-col">
                    
                    <!-- Kotak Header (Ukurannya kini seragam dan bisa melayang/hover)[cite: 19] -->
                    <div class="kategori-header-box">
                        <div class="kategori-icon"><?php echo $icons[$kategori_utama]; ?></div>
                        <div class="kategori-title"><?php echo htmlspecialchars($kategori_utama); ?></div>
                    </div>

                    <!-- Kotak Daftar Accordion[cite: 19] -->
                    <div class="accordion-container">
                        
                        <?php 
                        // LOOPING 2: Mengambil Sub Kategori (Tugas & Fungsi, Neraca, dll)[cite: 19]
                        foreach($sub_kategori as $judul_sub => $daftar_file) { 
                        ?>
                            <div class="accordion-item">
                                <!-- Header Accordion[cite: 19] -->
                                <div class="accordion-header" onclick="toggleAccordion(this)">
                                    <span class="accordion-title"><?php echo htmlspecialchars($judul_sub); ?></span>
                                    <span class="accordion-icon"></span>
                                </div>
                                
                                <!-- Isi / Konten Dropdown File PDF[cite: 19] -->
                                <div class="accordion-content">
                                    <?php if(count($daftar_file) > 0) { ?>
                                        <ul class="file-list">
                                            <?php 
                                            // LOOPING 3: Mengambil List File PDF dari database[cite: 19]
                                            foreach($daftar_file as $file) { ?>
                                                <li>
                                                    <a href="<?php echo htmlspecialchars($file['link']); ?>" target="_blank" class="file-link">
                                                        <span>📄</span>
                                                        <span><?php echo htmlspecialchars($file['judul']); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    <?php } else { ?>
                                        <div class="empty-state">Belum ada dokumen tersedia.</div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                    </div>

                </div> <!-- Tutup Kolom -->

            <?php } ?>

        </div>
    </div>
</main>

<!-- SCRIPT LOGIKA ACCORDION (+ / -)[cite: 19] -->
<script>
function toggleAccordion(element) {
    // Cari elemen induk (.accordion-item)[cite: 19]
    const item = element.parentElement;
    
    // Apakah elemen yang di-klik sudah terbuka?[cite: 19]
    const isActive = item.classList.contains('active');
    
    // (Opsional) Tutup semua accordion lain di dalam satu kolom yang sama agar rapi[cite: 19]
    const container = item.parentElement;
    const allItems = container.querySelectorAll('.accordion-item');
    allItems.forEach(function(el) {
        el.classList.remove('active');
    });

    // Jika tadinya tertutup, buka. (Toggling)[cite: 19]
    if (!isActive) {
        item.classList.add('active');
    }
}
</script>

<?php include 'includes/footer.php'; ?>[cite: 19]