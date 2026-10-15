<!-- prosedur_pelayanan.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// AMBIL DATA DINAMIS DARI DATABASE (Tabel pengaturan_web)
// =========================================================================
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN (
    'prosedur_pengantar', 
    'prosedur_langkah_1_judul', 
    'prosedur_langkah_1_syarat', 
    'prosedur_langkah_3', 
    'prosedur_langkah_4', 
    'prosedur_langkah_5_puas', 
    'prosedur_langkah_5_gagal'
)");

if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Data Default (Fallback jika admin belum mengatur di database)
$default_pengantar = "Prosedur ini mengatur <strong>tata cara permohonan</strong>, verifikasi, dan penyampaian informasi publik kepada masyarakat sesuai dengan <strong>Undang-Undang No. 14 Tahun 2008</strong>.";
$default_l1_judul  = "Pemohon mengisi formulir permohonan informasi dan melampirkan syarat dokumen kelengkapan*, berupa:";
$default_l1_syarat = "<strong>Perorangan:</strong><br>KTP atau Surat Keterangan Kependudukan dari Disdukcapil setempat.\n<strong>Kelompok orang:</strong><br>Surat kuasa, identitas pribadi Pemberi dan Penerima Kuasa.\n<strong>Badan Hukum/Lembaga:</strong><br>KTP pimpinan dan Akta pendirian lembaga.";
$default_l3        = "Jika berkas permohonan informasi lengkap, maka akan diproses jawabannya paling lambat dalam 10 + 7 hari kerja (apabila informasi belum dikuasai/didokumentasikan).";
$default_l4        = "Jika berkas tidak lengkap, Petugas PPID akan mengirimkan surat pemberitahuan untuk melengkapi berkas.";
$default_l5_puas   = "Selesai (Pemohon menerima informasi)";
$default_l5_gagal  = "Mengajukan Keberatan Informasi";

// Menerapkan data dari database atau menggunakan default
$pengantar            = $settings['prosedur_pengantar'] ?? $default_pengantar;
$langkah_1_judul      = $settings['prosedur_langkah_1_judul'] ?? $default_l1_judul;
$langkah_1_syarat_raw = $settings['prosedur_langkah_1_syarat'] ?? $default_l1_syarat;
$langkah_3            = $settings['prosedur_langkah_3'] ?? $default_l3;
$langkah_4            = $settings['prosedur_langkah_4'] ?? $default_l4;
$langkah_5_puas       = $settings['prosedur_langkah_5_puas'] ?? $default_l5_puas;
$langkah_5_tidak_puas = $settings['prosedur_langkah_5_gagal'] ?? $default_l5_gagal;

// Memecah teks syarat menjadi array berdasarkan baris baru
$syarat_array = array_filter(array_map('trim', explode("\n", $langkah_1_syarat_raw)));
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN PROSEDUR PELAYANAN
       ========================================================= */
    .prosedur-wrapper { 
        max-width: 950px; 
        margin: 50px auto 90px auto; 
        padding: 0 20px; 
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .info-box-light {
        background: #ffffff; 
        color: #1f2937;
        border-radius: 18px;
        padding: 30px 35px;
        display: flex;
        align-items: center;
        gap: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        border: 1px solid #eaeaea;
        margin-bottom: 35px;
        border-left: 6px solid var(--hover-color, #ef8a1c);
        transition: all 0.35s ease;
    }

    .info-box-light:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(239, 138, 28, 0.12);
    }

    .info-icon-orange {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: 0 6px 18px rgba(239, 138, 28, 0.35);
    }

    .info-text-dark { 
        font-size: 1.02rem; 
        line-height: 1.75; 
        color: #4b5563; 
    }
    
    .info-text-dark strong { 
        color: var(--primary, #00b2a9); 
        font-weight: 700;
    }

    /* Kotak Utama Alur Prosedur */
    .alur-card {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 35px rgba(0,0,0,0.04);
        border: 1px solid #eaeaea;
    }

    .alur-card-header {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        padding: 20px 30px;
        font-weight: 700;
        font-size: 1.15rem;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: 0.3px;
    }

    .alur-card-body {
        padding: 50px 40px;
        background-color: #fafafa; 
    }

    .alur-title-badge {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        color: white;
        padding: 10px 30px;
        border-radius: 30px;
        font-weight: 800;
        display: inline-block;
        margin-bottom: 40px;
        font-size: 1.1rem;
        box-shadow: 0 6px 20px rgba(239, 138, 28, 0.3);
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    /* KOTAK VERTIKAL TIMELINE */
    .step-timeline {
        display: flex;
        flex-direction: column;
        gap: 35px;
        margin-left: 25px;
        position: relative;
    }

    /* Garis vertikal penghubung timeline */
    .step-timeline::before {
        content: '';
        position: absolute;
        top: 20px;
        bottom: 20px;
        left: -25px;
        width: 3px;
        background: #e5e7eb;
        z-index: 1;
    }

    .step-box {
        background: #ffffff;
        color: #1f2937;
        border-radius: 16px;
        padding: 30px;
        position: relative;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid #eaeaea;
        border-left: 6px solid var(--primary, #00b2a9);
        z-index: 2;
    }

    .step-box.box-fail {
        background: #ffffff;
        color: #1f2937;
        border: 1px solid #fee2e2;
        border-left: 6px solid #ef4444;
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.03);
    }

    .step-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.1);
        border-color: #d1d5db;
    }

    .step-box.box-fail:hover {
        border-color: #fca5a5;
        box-shadow: 0 15px 35px rgba(239, 68, 68, 0.1);
    }

    .step-number {
        position: absolute;
        top: 25px;
        left: -48px;
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        font-weight: 800;
        border: 4px solid #fafafa;
        box-shadow: 0 4px 12px rgba(0, 178, 169, 0.3);
        z-index: 3;
    }
    
    .step-box.box-fail .step-number {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .syarat-item {
        margin-top: 12px;
        font-size: 0.95rem;
        line-height: 1.7;
        background: #f9fafb; 
        padding: 14px 18px;
        border-radius: 12px;
        border: 1px solid #f3f4f6;
        color: #4b5563;
    }

    .teks-alur {
        font-size: 1.02rem;
        line-height: 1.75;
        color: #4b5563;
    }

    .decision-row {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 15px;
    }

    .decision-badge {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        font-weight: 700;
        width: 130px;
        text-align: center;
        font-size: 0.92rem;
        box-shadow: 0 4px 12px rgba(0, 178, 169, 0.2);
    }

    .decision-badge.fail { 
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }

    .decision-result {
        background: #ffffff;
        border: 1px solid #eaeaea;
        color: #1f2937;
        padding: 14px 22px;
        border-radius: 12px;
        font-weight: 700;
        flex-grow: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .decision-result.fail-result {
        border-color: #fee2e2; 
        color: #ef4444;
        background: #fff5f5;
    }

    @media (max-width: 768px) {
        .info-box-light { flex-direction: column; text-align: center; padding: 25px; }
        .alur-card-body { padding: 30px 20px; }
        .step-timeline { margin-left: 15px; }
        .step-timeline::before { left: -15px; }
        .step-number { left: -37px; width: 38px; height: 38px; font-size: 0.85rem; top: 22px; }
        .decision-row { flex-direction: column; align-items: stretch; }
        .decision-badge { width: 100%; }
        .decision-result { width: 100%; box-sizing: border-box; flex-direction: column; align-items: flex-start; gap: 10px; }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Prosedur Pelayanan Informasi</h1>
        <p>Alur tata cara pengajuan permohonan informasi publik secara resmi</p>
    </section>

    <div class="prosedur-wrapper">
        
        <!-- KOTAK PENGANTAR -->
        <div class="info-box-light">
            <div class="info-icon-orange">i</div>
            <div class="info-text-dark">
                <?php echo $pengantar; ?>
            </div>
        </div>

        <div class="alur-card">
            <div class="alur-card-header">
                <span>🗂️</span> Alur Mekanisme Pelayanan Informasi Publik
            </div>
            <div class="alur-card-body">
                
                <div style="text-align: center;">
                    <div class="alur-title-badge">✴ Alur Pengajuan Permohonan</div>
                </div>

                <div class="step-timeline">

                    <!-- STEP 1 & 2 -->
                    <div class="step-box">
                        <div class="step-number">1-2</div>
                        <p style="font-weight: 700; font-size: 1.05rem; margin-top: 0; margin-bottom: 15px; color: #1f2937;">
                            <?php echo htmlspecialchars($langkah_1_judul); ?>
                        </p>
                        <div>
                            <?php foreach($syarat_array as $syarat) { ?>
                                <div class="syarat-item"><?php echo $syarat; ?></div>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- STEP 3 -->
                    <div class="step-box">
                        <div class="step-number">3</div>
                        <div class="teks-alur">
                            <strong style="color: var(--primary, #00b2a9);">(Berkas Lengkap)</strong><br><br>
                            <?php echo htmlspecialchars($langkah_3); ?>
                        </div>
                    </div>

                    <!-- STEP 4 -->
                    <div class="step-box box-fail">
                        <div class="step-number">4</div>
                        <div class="teks-alur">
                            <strong style="color: #ef4444;">(Berkas Ditolak / Tidak Lengkap)</strong><br><br>
                            <?php echo htmlspecialchars($langkah_4); ?>
                        </div>
                    </div>

                    <!-- STEP 5 -->
                    <div class="step-box" style="background-color: transparent; border: none; box-shadow: none; padding: 0;">
                        <div class="step-number" style="top: 0; z-index: 5;">5</div>
                        
                        <div style="padding-left: 10px;">
                            <div class="decision-row" style="margin-top: 0;">
                                <div class="decision-badge">Puas ✔</div>
                                <div class="decision-result">
                                    <span><?php echo htmlspecialchars($langkah_5_puas); ?></span>
                                </div>
                            </div>

                            <div class="decision-row">
                                <div class="decision-badge fail">Tidak Puas ✖</div>
                                <div class="decision-result fail-result">
                                    <span><?php echo htmlspecialchars($langkah_5_tidak_puas); ?></span>
                                    <a href="user/keberatan/form.php" style="color: #ef4444; text-decoration: underline; font-weight: 700;">Ajukan Sekarang &raquo;</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>