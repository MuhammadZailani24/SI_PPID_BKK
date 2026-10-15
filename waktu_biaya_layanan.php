<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// AMBIL DATA DINAMIS DARI DATABASE BERDASARKAN KUNCI DARI PANEL ADMIN
// =========================================================================
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN (
    'jadwal_senin_kamis', 'jadwal_jumat', 
    'istirahat_senin_kamis', 'istirahat_jumat', 
    'biaya_poin_1', 'biaya_poin_2', 'biaya_poin_3'
)");

if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Data Default (Fallback jika belum diatur di panel admin)[cite: 21]
$def_waktu_sk     = "Senin - Kamis : 08:00 WITA s/d 16:00 WITA";
$def_waktu_j      = "Jum'at : 08:00 WITA s/d 16:30 WITA";
$def_istirahat_sk = "Senin - Kamis : 12:00 WITA - 13:00 WITA";
$def_istirahat_j  = "Jum'at : 11:30 WITA - 13:00 WITA";

$def_b1 = "Standar biaya perolehan salinan Informasi publik bagi penyediaan dan pemberian pelayanan Informasi Publik kepada Pemohon Informasi Publik tidak dipungut biaya.";
$def_b2 = "Penyediaan dan pemberian pelayanan Informasi Publik kepada Pemohon Informasi Publik diberikan dalam bentuk digital.";
$def_b3 = "Dalam hal Pemohon Informasi Publik meminta Informasi dalam bentuk hardcopy, biaya penggandaan dan pengiriman jasa pos dan jasa kurir terhadap Salinan Informasi Publik yang dimaksud dibebankan kepada Pemohon Informasi Publik.";

// Menerapkan data dari database atau menggunakan default[cite: 21]
$waktu_sk     = $settings['jadwal_senin_kamis'] ?? $def_waktu_sk;
$waktu_j      = $settings['jadwal_jumat'] ?? $def_waktu_j;
$istirahat_sk = $settings['istirahat_senin_kamis'] ?? $def_istirahat_sk;
$istirahat_j  = $settings['istirahat_jumat'] ?? $def_istirahat_j;

$biaya_1 = $settings['biaya_poin_1'] ?? $def_b1;
$biaya_2 = $settings['biaya_poin_2'] ?? $def_b2;
$biaya_3 = $settings['biaya_poin_3'] ?? $def_b3;

// Data Waktu Pelayanan
$data_waktu = [
    [
        "ikon"      => "⏰",
        "label"     => "HARI PELAYANAN",
        "isi_utama" => $waktu_sk,
        "isi_sub"   => $waktu_j
    ],
    [
        "ikon"      => "☕",
        "label"     => "JAM ISTIRAHAT",
        "isi_utama" => $istirahat_sk,
        "isi_sub"   => $istirahat_j
    ]
];

// Data Biaya Pelayanan
$data_biaya = [
    [
        "teks"  => $biaya_1,
        "badge" => "Pasal 63 Ayat 1"
    ],
    [
        "teks"  => $biaya_2,
        "badge" => "Pasal 63 Ayat 2"
    ],
    [
        "teks"  => $biaya_3,
        "badge" => "Pasal 64 Ayat 3"
    ]
];
?>

<style>
    /* Wrapper Utama */
    .wb-wrapper {
        max-width: 900px;
        margin: 40px auto 80px auto;
        padding: 0 20px;
        display: flex;
        flex-direction: column;
        gap: 40px;
    }

    /* Styling Kartu (Card) */
    .wb-card {
        background-color: white;
        border-radius: 16px;
        border: 1px solid #eaeaea;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .wb-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.12);
        border-color: rgba(0, 178, 169, 0.3);
    }

    .wb-card-header {
        padding: 30px;
        display: flex;
        align-items: center;
        gap: 20px;
        border-bottom: 1px solid #f0f0f0;
        background-color: #fafbfc;
    }

    .wb-header-icon {
        background-color: var(--primary, #00b2a9);
        color: white;
        width: 55px;
        height: 55px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        box-shadow: 0 5px 15px rgba(0, 178, 169, 0.3);
        transition: all 0.3s ease;
    }

    .wb-card:hover .wb-header-icon {
        background-color: var(--hover-color, #ef8a1c);
        box-shadow: 0 5px 15px rgba(239, 138, 28, 0.4);
        transform: scale(1.05) rotate(-5deg);
    }

    .wb-header-text h2 {
        font-size: 1.35rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 5px 0;
    }

    .wb-header-text p {
        font-size: 0.95rem;
        color: #777;
        margin: 0;
    }

    .wb-card-body {
        padding: 30px;
    }

    /* WAKTU PELAYANAN */
    .waktu-item {
        display: flex;
        align-items: flex-start;
        gap: 20px;
        padding: 20px 0;
        border-bottom: 1px dashed #eee;
        transition: all 0.3s ease;
    }

    .waktu-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .waktu-item:first-child {
        padding-top: 0;
    }

    .waktu-item:hover {
        transform: translateX(10px);
    }

    .waktu-icon-small {
        background-color: rgba(0, 178, 169, 0.1);
        color: var(--primary, #00b2a9);
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .waktu-item:hover .waktu-icon-small {
        background-color: var(--hover-color, #ef8a1c);
        color: white;
    }

    .waktu-text-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #999;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .waktu-text-main {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin: 0 0 5px 0;
    }

    .waktu-text-sub {
        font-size: 0.95rem;
        color: #666;
        margin: 0;
    }

    /* BIAYA PELAYANAN */
    .biaya-intro {
        font-size: 1rem;
        line-height: 1.6;
        color: #555;
        margin-bottom: 30px;
    }

    .biaya-item {
        display: flex;
        align-items: flex-start;
        gap: 20px;
        padding: 20px 0;
        border-bottom: 1px dashed #eee;
        transition: all 0.3s ease;
    }

    .biaya-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .biaya-item:hover {
        transform: translateX(10px);
    }

    .biaya-number {
        background-color: var(--primary, #00b2a9);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        font-weight: bold;
        flex-shrink: 0;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(0, 178, 169, 0.2);
    }

    .biaya-item:hover .biaya-number {
        background-color: var(--hover-color, #ef8a1c);
        box-shadow: 0 3px 10px rgba(239, 138, 28, 0.3);
        transform: scale(1.1);
    }

    .biaya-text-wrapper {
        flex-grow: 1;
    }

    .biaya-text {
        font-size: 1rem;
        line-height: 1.6;
        color: #444;
        display: inline;
    }

    .pasal-badge {
        display: inline-block;
        background-color: rgba(239, 138, 28, 0.12);
        color: var(--hover-color, #ef8a1c);
        font-size: 0.8rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        margin-left: 8px;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    .biaya-item:hover .pasal-badge {
        background-color: var(--hover-color, #ef8a1c);
        color: white;
    }

    @media (max-width: 600px) {
        .wb-card-header { padding: 20px; }
        .wb-card-body { padding: 20px; }
        .pasal-badge { margin-left: 0; margin-top: 10px; display: table; }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Waktu dan Biaya Layanan</h1>
        <p>Informasi jadwal dan ketentuan tarif pelayanan PPID</p>
    </section>

    <div class="wb-wrapper">
        
        <!-- KARTU 1: WAKTU PELAYANAN -->
        <div class="wb-card">
            
            <div class="wb-card-header">
                <div class="wb-header-icon">🕒</div>
                <div class="wb-header-text">
                    <h2>Waktu Pelayanan</h2>
                    <p>Jadwal layanan informasi publik</p>
                </div>
            </div>

            <div class="wb-card-body">
                <?php foreach($data_waktu as $waktu) { ?>
                    <div class="waktu-item">
                        <div class="waktu-icon-small"><?php echo $waktu['ikon']; ?></div>
                        <div>
                            <div class="waktu-text-label"><?php echo htmlspecialchars($waktu['label']); ?></div>
                            <p class="waktu-text-main"><?php echo htmlspecialchars($waktu['isi_utama']); ?></p>
                            <?php if(!empty($waktu['isi_sub'])) { ?>
                                <p class="waktu-text-sub"><?php echo htmlspecialchars($waktu['isi_sub']); ?></p>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>

        </div>

        <!-- KARTU 2: BIAYA PELAYANAN -->
        <div class="wb-card">
            
            <div class="wb-card-header">
                <div class="wb-header-icon">💰</div>
                <div class="wb-header-text">
                    <h2>Biaya Pelayanan</h2>
                    <p>Informasi biaya perolehan salinan informasi publik</p>
                </div>
            </div>

            <div class="wb-card-body">
                <p class="biaya-intro">
                    Sesuai dengan Peraturan Menteri Kesehatan Republik Indonesia Nomor 37 Tahun 2018 tentang Pedoman Pengelolaan Informasi Publik di Lingkungan Kementerian Kesehatan:
                </p>

                <?php 
                $nomor = 1;
                foreach($data_biaya as $biaya) { 
                ?>
                    <div class="biaya-item">
                        <div class="biaya-number"><?php echo $nomor; ?></div>
                        <div class="biaya-text-wrapper">
                            <span class="biaya-text"><?php echo htmlspecialchars($biaya['teks']); ?></span>
                            <span class="pasal-badge"><?php echo htmlspecialchars($biaya['badge']); ?></span>
                        </div>
                    </div>
                <?php 
                    $nomor++;
                } 
                ?>
            </div>

        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>