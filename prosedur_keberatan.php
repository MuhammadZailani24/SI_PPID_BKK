<!-- prosedur_keberatan.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// AMBIL DATA DINAMIS DARI DATABASE BERDASARKAN KUNCI DARI PANEL ADMIN
// =========================================================================
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN (
    'desc_keberatan', 
    'keberatan_tahap_1', 
    'keberatan_tahap_2', 
    'keberatan_tahap_3', 
    'keberatan_tahap_4', 
    'keberatan_tahap_6'
)");

if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Data Default (Fallback jika admin belum mengaturnya di database)
$default_pengantar      = "Pemohon informasi dapat mengajukan keberatan apabila <strong>tidak puas</strong> dengan pelayanan informasi yang diberikan. Pengajuan keberatan ditujukan kepada <strong>Atasan PPID</strong> sesuai prosedur yang berlaku.";
$default_l1             = "Pemohon mengisi formulir keberatan melalui website PPID BKK Kelas 1 Banjarmasin secara online.";
$default_l2             = "Petugas Data & Informasi PPID mencatat/meregistrasi dan mengecek kelengkapan berkas keberatan.";
$default_l3             = "Petugas Data & Informasi PPID menyampaikan pengajuan keberatan informasi kepada Atasan PPID.";
$default_l4             = "Atasan PPID menyampaikan tanggapan keberatan kepada Petugas PPID untuk diteruskan kepada Pemohon paling lambat 30 hari kerja sejak diregistrasinya pengajuan keberatan.";
$default_l5_puas        = "Selesai (Pemohon menerima tanggapan keberatan)";
$default_l5_tidak_puas  = "Sengketa Informasi Publik";
$default_l6_sengketa    = "Pemohon dapat mengajukan permohonan sengketa informasi publik ke <strong>Komisi Informasi</strong> dalam waktu 14 hari kerja setelah tanggapan keberatan diterima.";

// Menerapkan data dari database sesuai kunci admin[cite: 21] atau menggunakan default
$teks_pengantar       = $settings['desc_keberatan'] ?? $default_pengantar;
$langkah_1            = $settings['keberatan_tahap_1'] ?? $default_l1;
$langkah_2            = $settings['keberatan_tahap_2'] ?? $default_l2;
$langkah_3            = $settings['keberatan_tahap_3'] ?? $default_l3;
$langkah_4            = $settings['keberatan_tahap_4'] ?? $default_l4;
$langkah_5_puas       = $default_l5_puas;
$langkah_5_tidak_puas = $default_l5_tidak_puas;
$langkah_6_sengketa   = $settings['keberatan_tahap_6'] ?? $default_l6_sengketa;
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN PROSEDUR KEBERATAN
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

    /* KONTENER ALUR VERTIKAL TIMELINE */
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

    /* Kotak Langkah Biasa */
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

    /* Kotak Khusus Sengketa */
    .step-box.box-sengketa {
        background: #ffffff;
        color: #1f2937;
        border: 1px solid #fed7aa;
        border-left: 6px solid var(--hover-color, #ef8a1c);
        box-shadow: 0 6px 20px rgba(239, 138, 28, 0.04);
    }

    .step-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.1);
        border-color: #d1d5db;
    }

    .step-box.box-sengketa:hover {
        border-color: #fdba74;
        box-shadow: 0 15px 35px rgba(239, 138, 28, 0.12);
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

    .step-box.box-sengketa .step-number {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        box-shadow: 0 4px 12px rgba(239, 138, 28, 0.3);
    }

    .teks-alur {
        font-size: 1.02rem;
        line-height: 1.75;
        color: #4b5563;
    }

    /* Khusus Step Keputusan (Puas / Tidak Puas) */
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

    /* RESPONSIVE UNTUK HP & TABLET */
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
        <h1>Prosedur Pengajuan Keberatan</h1>
        <p>Alur tata cara pengajuan keberatan jika permohonan informasi tidak ditanggapi sebagaimana mestinya</p>
    </section>

    <div class="prosedur-wrapper">
        
        <!-- === KOTAK PENGANTAR === -->
        <div class="info-box-light">
            <div class="info-icon-orange">i</div>
            <div class="info-text-dark">
                <?php echo $teks_pengantar; ?>
            </div>
        </div>

        <div class="alur-card">
            <div class="alur-card-header">
                <span>⚖️</span> Alur Mekanisme Pengajuan Keberatan
            </div>
            <div class="alur-card-body">
                
                <div style="text-align: center;">
                    <div class="alur-title-badge">✴ Alur Pengajuan Keberatan</div>
                </div>

                <!-- === KONTENER ALUR VERTIKAL === -->
                <div class="step-timeline">

                    <!-- STEP 1 -->
                    <div class="step-box">
                        <div class="step-number">1</div>
                        <div class="teks-alur">
                            <?php echo $langkah_1; ?>
                        </div>
                    </div>

                    <!-- STEP 2 -->
                    <div class="step-box">
                        <div class="step-number">2</div>
                        <div class="teks-alur">
                            <?php echo $langkah_2; ?>
                        </div>
                    </div>

                    <!-- STEP 3 -->
                    <div class="step-box">
                        <div class="step-number">3</div>
                        <div class="teks-alur">
                            <?php echo $langkah_3; ?>
                        </div>
                    </div>

                    <!-- STEP 4 -->
                    <div class="step-box">
                        <div class="step-number">4</div>
                        <div class="teks-alur">
                            <?php echo $langkah_4; ?>
                        </div>
                    </div>

                    <!-- STEP 5 (Hasil Akhir) -->
                    <div class="step-box" style="background-color: transparent; border: none; box-shadow: none; padding: 0;">
                        <div class="step-number" style="top: 0; z-index: 5;">5</div>
                        
                        <div style="padding-left: 10px;">
                            <!-- Keputusan Puas -->
                            <div class="decision-row" style="margin-top: 0;">
                                <div class="decision-badge">Puas ✔</div>
                                <div class="decision-result">
                                    <span><?php echo htmlspecialchars($langkah_5_puas); ?></span>
                                </div>
                            </div>

                            <!-- Keputusan Tidak Puas -->
                            <div class="decision-row">
                                <div class="decision-badge fail">Tidak Puas ✖</div>
                                <div class="decision-result fail-result">
                                    <span><?php echo htmlspecialchars($langkah_5_tidak_puas); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 6 (Sengketa) -->
                    <div class="step-box box-sengketa">
                        <div class="step-number">6</div>
                        <div class="teks-alur">
                            <span style="color: var(--hover-color, #ef8a1c); font-weight: 700;">Tahap Lanjutan (Jika Tidak Puas):</span><br><br>
                            <?php echo $langkah_6_sengketa; ?>
                        </div>
                    </div>

                </div> <!-- Tutup step-timeline -->

            </div>
        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>