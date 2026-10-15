<!-- struktur_organisasi.php -->
<?php 
require_once 'includes/koneksi.php';

// Ambil data struktur organisasi dari database tabel pengaturan_web
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN (
    'struktur_kepala_upt', 
    'struktur_menteri', 
    'struktur_kepala_bkk', 
    'struktur_kasubag', 
    'struktur_pengelola_sys', 
    'struktur_pengelola_lay', 
    'struktur_sengketa'
)");
if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Nilai cadangan (fallback) jika database belum diisi
$nama_kepala_upt        = $settings['struktur_kepala_upt'] ?? 'Dr. H. Nama Kepala, M.Kes';
$nama_menteri           = $settings['struktur_menteri'] ?? 'Budi Gunadi Sadikin';
$nama_kepala_bkk        = $settings['struktur_kepala_bkk'] ?? 'Dr. H. Nama Kepala, M.Kes';
$nama_kasubag           = $settings['struktur_kasubag'] ?? 'Nama Kasubag, SKM';
$nama_pengelola_sistem  = $settings['struktur_pengelola_sys'] ?? 'Nama Pengelola Sistem, S.Kom';
$nama_pengelola_layanan = $settings['struktur_pengelola_lay'] ?? 'Nama Pengelola Layanan, S.I.Kom';
$nama_sengketa          = $settings['struktur_sengketa'] ?? 'Nama Sengketa, S.H';

include 'includes/header.php'; 
?>

<style>
    /* =========================================================
       STYLING STRUKTUR ORGANISASI (PRESISI & TERSUSUN SEMPURNA)
       ========================================================= */
    .org-main-wrapper {
        max-width: 1100px;
        margin: 40px auto 80px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        display: flex;
        flex-direction: column;
        gap: 40px;
    }

    .org-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.04);
        padding: 45px 30px;
        text-align: center;
        border: 1px solid #eaeaea;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: center; /* Memastikan semua anak elemen berada tepat di tengah secara vertikal */
    }
    
    .org-title {
        font-size: 1.15rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #1f2937;
        margin-bottom: 35px;
        letter-spacing: 0.8px;
        line-height: 1.5;
        position: relative;
        display: inline-block;
        text-align: center;
    }

    .org-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background-color: var(--hover-color, #ef8a1c);
        border-radius: 2px;
    }

    /* Garis Konektor Vertikal Utama */
    .org-line-v {
        width: 3px;
        height: 35px;
        background: linear-gradient(180deg, var(--primary, #00b2a9), #008f88);
        margin: 0 auto;
        border-radius: 2px;
        position: relative;
        z-index: 2;
    }

    /* Kotak Bagan (Modern Card Style) */
    .org-box {
        display: inline-block;
        padding: 18px 30px;
        border: 2px solid #e5e7eb;
        background-color: white;
        min-width: 300px;
        border-radius: 14px;
        position: relative;
        z-index: 3;
        cursor: pointer;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    /* Efek Hover */
    .org-box:hover {
        transform: translateY(-6px) scale(1.02);
        background-color: var(--hover-color, #ef8a1c) !important;
        color: white !important;
        border-color: var(--hover-color, #ef8a1c) !important;
        box-shadow: 0 15px 30px rgba(239, 138, 28, 0.25) !important;
    }

    .org-box:hover .org-name {
        color: white !important;
        opacity: 0.95;
    }

    /* Variasi Warna Kotak */
    .org-box.tosca {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        border: none;
        box-shadow: 0 8px 20px rgba(0, 178, 169, 0.25);
    }
    
    .org-box.orange {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        color: white;
        border: none;
        box-shadow: 0 8px 20px rgba(239, 138, 28, 0.25);
    }

    .org-role {
        font-weight: 800;
        font-size: 1rem;
        display: block;
        letter-spacing: 0.3px;
    }

    .org-name {
        font-size: 0.85rem;
        display: block;
        margin-top: 6px;
        opacity: 0.85;
        font-style: italic;
        color: #4b5563;
        transition: color 0.3s ease;
    }

    .org-box.tosca .org-name, 
    .org-box.orange .org-name {
        color: white;
    }

    /* ==================================================
       KONEKTOR CABANG 3 YANG TERSAMBUNG SEMPURNA
       ================================================== */
    .org-branches-wrapper {
        width: 100%;
        max-width: 950px;
        position: relative;
        margin-top: 0;
    }

    /* Garis Batang Horizontal Penghubung Cabang */
    .branch-horizontal-line {
        position: absolute;
        top: 0;
        left: 16.66%;
        right: 16.66%;
        height: 3px;
        background: var(--primary, #00b2a9);
        border-radius: 2px;
        z-index: 1;
    }

    .branch-items {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        width: 100%;
    }

    .branch-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        padding-top: 30px; /* Ruang untuk garis vertikal turun ke kotak */
    }

    /* Garis Vertikal dari Batang Horizontal ke Masing-Masing Kotak Cabang */
    .branch-vertical-line {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 3px;
        height: 30px;
        background: var(--primary, #00b2a9);
        border-radius: 2px;
        z-index: 1;
    }
    
    .corner-decoration {
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(0,178,169,0.08) 0%, transparent 70%);
        z-index: 1;
        pointer-events: none;
    }

    /* Responsif untuk Layar HP / Tablet */
    @media (max-width: 850px) {
        .branch-items {
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        .branch-horizontal-line, .branch-vertical-line {
            display: none; /* Menonaktifkan garis cabang horizontal di layar kecil agar vertikal murni */
        }
        .branch-item {
            width: 100%;
            padding-top: 0;
        }
        .org-box {
            width: 100%;
            min-width: unset;
        }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Struktur Organisasi</h1>
        <p>Susunan keanggotaan dan hierarki Pejabat Pengelola Informasi dan Dokumentasi (PPID)</p>
    </section>

    <div class="org-main-wrapper">
        
        <!-- BAGAN 1: UNIT PELAKSANA TEKNIS -->
        <div class="org-wrapper">
            <div class="corner-decoration"></div>
            <div class="org-title">
                STRUKTUR ORGANISASI<br>
                UNIT PELAKSANA TEKNIS<br>
                BIDANG KEKARANTINAAN KESEHATAN
            </div>
            
            <div class="org-box tosca">
                <span class="org-role">KEPALA</span>
                <span class="org-name"><?php echo htmlspecialchars($nama_kepala_upt); ?></span>
            </div>
            
            <div class="org-line-v"></div>
            
            <div class="org-box">
                <span class="org-role" style="font-size: 0.95rem;">Jabatan Fungsional dan<br>Jabatan Pelaksana</span>
            </div>
        </div>


        <!-- BAGAN 2: STRUKTUR PPID BKK BANJARMASIN -->
        <div class="org-wrapper">
            <div class="corner-decoration"></div>

            <div class="org-title" style="position: relative; z-index: 2;">
                STRUKTUR ORGANISASI PPID BKK KELAS I BANJARMASIN
            </div>
            
            <!-- Level 1: Menteri Kesehatan -->
            <div class="org-box tosca">
                <span class="org-role">Menteri Kesehatan</span>
                <span class="org-name"><?php echo htmlspecialchars($nama_menteri); ?></span>
            </div>
            
            <div class="org-line-v"></div>
            
            <!-- Level 2: Kepala BKK -->
            <div class="org-box orange">
                <span class="org-role">Kepala BKK Kelas I Banjarmasin</span>
                <span class="org-name"><?php echo htmlspecialchars($nama_kepala_bkk); ?></span>
            </div>
            
            <div class="org-line-v"></div>
            
            <!-- Level 3: Kasubag -->
            <div class="org-box orange">
                <span class="org-role">Kepala Sub Bagian Administrasi Umum</span>
                <span class="org-name"><?php echo htmlspecialchars($nama_kasubag); ?></span>
            </div>
            
            <div class="org-line-v"></div>
            
            <!-- Level 4: 3 Cabang (Pengelola Sistem, Layanan, Sengketa) dengan Konektor Sempurna -->
            <div class="org-branches-wrapper">
                <div class="branch-horizontal-line"></div>
                
                <div class="branch-items">
                    <div class="branch-item">
                        <div class="branch-vertical-line"></div>
                        <div class="org-box orange" style="width: 100%; min-width: unset; padding: 16px 10px;">
                            <span class="org-role" style="font-size: 0.9rem;">Pengelola Sistem Informasi</span>
                            <span class="org-name"><?php echo htmlspecialchars($nama_pengelola_sistem); ?></span>
                        </div>
                    </div>

                    <div class="branch-item">
                        <div class="branch-vertical-line"></div>
                        <div class="org-box orange" style="width: 100%; min-width: unset; padding: 16px 10px;">
                            <span class="org-role" style="font-size: 0.9rem;">Pengelola Layanan Informasi</span>
                            <span class="org-name"><?php echo htmlspecialchars($nama_pengelola_layanan); ?></span>
                        </div>
                    </div>

                    <div class="branch-item">
                        <div class="branch-vertical-line"></div>
                        <div class="org-box orange" style="width: 100%; min-width: unset; padding: 16px 10px;">
                            <span class="org-role" style="font-size: 0.9rem;">Penyelesaian Sengketa Informasi</span>
                            <span class="org-name"><?php echo htmlspecialchars($nama_sengketa); ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>