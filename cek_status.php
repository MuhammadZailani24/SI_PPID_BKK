<!-- cek_status.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================================
// AUTO-PATCH DATABASE: Otomatis memastikan kolom yang dibutuhkan ada
// =========================================================================================
try {
    $cek_tabel_p = $conn->query("SHOW TABLES LIKE 'permohonan_informasi'");
    if ($cek_tabel_p && $cek_tabel_p->num_rows > 0) {
        $conn->query("ALTER TABLE permohonan_informasi ADD COLUMN IF NOT EXISTS nomor_registrasi VARCHAR(50)");
        $conn->query("ALTER TABLE permohonan_informasi ADD COLUMN IF NOT EXISTS file_jawaban_pdf VARCHAR(255)");
        $conn->query("ALTER TABLE permohonan_informasi ADD COLUMN IF NOT EXISTS tanggapan TEXT");
        $conn->query("ALTER TABLE permohonan_informasi ADD COLUMN IF NOT EXISTS alasan_penolakan TEXT");
    }

    $cek_tabel_k = $conn->query("SHOW TABLES LIKE 'keberatan_informasi'");
    if ($cek_tabel_k && $cek_tabel_k->num_rows > 0) {
        $conn->query("ALTER TABLE keberatan_informasi ADD COLUMN IF NOT EXISTS nomor_registrasi VARCHAR(50)");
        $conn->query("ALTER TABLE keberatan_informasi ADD COLUMN IF NOT EXISTS file_jawaban_pdf VARCHAR(255)");
        $conn->query("ALTER TABLE keberatan_informasi ADD COLUMN IF NOT EXISTS tanggapan TEXT");
        $conn->query("ALTER TABLE keberatan_informasi ADD COLUMN IF NOT EXISTS alasan_penolakan TEXT");
    }
} catch (Exception $e) {
    // Abaikan error auto-patch
}

// Ambil parameter jenis layanan dari URL (GET) atau Form (REQUEST)
$jenis_cari = isset($_REQUEST['jenis_layanan']) ? $_REQUEST['jenis_layanan'] : 'permohonan';
$kode_cari = isset($_REQUEST['kode']) ? mysqli_real_escape_string($conn, trim($_REQUEST['kode'])) : '';

$data_ditemukan = false;
$hasil = null;
$error_msg = "";

if (!empty($kode_cari)) {
    try {
        if ($jenis_cari == 'permohonan') {
            $cek_tabel = $conn->query("SHOW TABLES LIKE 'permohonan_informasi'");
            if($cek_tabel && $cek_tabel->num_rows > 0) {
                $q = $conn->query("SELECT * FROM permohonan_informasi WHERE nomor_registrasi = '$kode_cari'");
                if ($q && $q->num_rows > 0) {
                    $hasil = $q->fetch_assoc();
                    $data_ditemukan = true;
                } else {
                    $error_msg = "Nomor Registrasi Permohonan '$kode_cari' tidak ditemukan. Pastikan kode yang Anda masukkan sudah benar.";
                }
            } else {
                $error_msg = "Sistem permohonan informasi belum tersedia.";
            }
        } 
        else if ($jenis_cari == 'keberatan') {
            $cek_tabel = $conn->query("SHOW TABLES LIKE 'keberatan_informasi'");
            if($cek_tabel && $cek_tabel->num_rows > 0) {
                $q = $conn->query("SELECT * FROM keberatan_informasi WHERE nomor_registrasi = '$kode_cari'");
                if ($q && $q->num_rows > 0) {
                    $hasil = $q->fetch_assoc();
                    $data_ditemukan = true;
                } else {
                    $error_msg = "Nomor Registrasi Keberatan '$kode_cari' tidak ditemukan. Pastikan kode yang Anda masukkan sudah benar.";
                }
            } else {
                $error_msg = "Sistem pengajuan keberatan belum tersedia.";
            }
        }
    } catch (Exception $e) {
        $error_msg = "Terjadi kesalahan pada sistem pencarian data. Mohon coba beberapa saat lagi.";
    }
}
?>

<style>
    .tracking-wrapper { max-width: 800px; margin: 40px auto 80px auto; padding: 0 20px; font-family: 'Inter', sans-serif; }
    
    /* Kartu Pencarian yang Dipercantik */
    .search-box { 
        background: #ffffff; 
        padding: 40px; 
        border-radius: 20px; 
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06); 
        border: 1px solid #e5e7eb; 
        border-top: 6px solid var(--primary, #00b2a9);
        margin-bottom: 40px; 
        position: relative;
    }

    .search-box-icon {
        width: 60px;
        height: 60px;
        background-color: #f0fdfa;
        color: var(--primary, #00b2a9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        margin: 0 auto 15px auto;
        box-shadow: 0 4px 10px rgba(0, 178, 169, 0.1);
    }

    .search-box h2 { 
        margin-top: 0; 
        color: #1f2937; 
        font-size: 1.4rem; 
        font-weight: 800;
        margin-bottom: 8px; 
        text-align: center; 
    }

    .search-box p.search-subtitle {
        text-align: center;
        color: #6b7280;
        font-size: 0.92rem;
        margin-bottom: 30px;
    }
    
    .form-group { margin-bottom: 20px; }
    
    .form-control { 
        width: 100%; 
        padding: 15px 18px; 
        border: 2px solid #e5e7eb; 
        border-radius: 12px; 
        font-size: 1rem; 
        box-sizing: border-box; 
        font-family: inherit; 
        background-color: #f9fafb;
        transition: all 0.3s ease;
    }

    .form-control:focus { 
        outline: none; 
        border-color: var(--primary, #00b2a9); 
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.12); 
    }
    
    .btn-cari { 
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%); 
        color: white; 
        border: none; 
        padding: 15px 25px; 
        border-radius: 12px; 
        font-size: 1.02rem; 
        font-weight: 700; 
        cursor: pointer; 
        width: 100%; 
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 178, 169, 0.25);
    }

    .btn-cari:hover { 
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%); 
        box-shadow: 0 6px 20px rgba(239, 138, 28, 0.35); 
        transform: translateY(-2px); 
    }

    .alert-error { background-color: #fee2e2; color: #b91c1c; padding: 15px 20px; border-radius: 12px; border: 1px solid #fecaca; text-align: center; margin-bottom: 25px; font-weight: 600; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.05); }

    /* Hasil Card */
    .result-card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; border-top: 6px solid var(--primary, #00b2a9); }
    .result-header { border-bottom: 1px dashed #e5e7eb; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .result-header h3 { margin: 0; color: #1f2937; font-size: 1.3rem; font-weight: 800; }
    .badge-status { padding: 8px 18px; border-radius: 20px; font-weight: bold; font-size: 0.9rem; color: white; background: #6b7280; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    /* Warna Status */
    .status-pending { background: #f59e0b; }
    .status-proses { background: #3b82f6; }
    .status-selesai { background: #10b981; }
    .status-ditolak { background: #ef4444; }

    /* Layout Data Rapi */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
    .info-item { background: #f9fafb; padding: 16px 20px; border-radius: 12px; border: 1px solid #f3f4f6; }
    .info-item label { display: block; font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 700; margin-bottom: 8px; letter-spacing: 0.5px; }
    .info-item div { font-size: 1rem; color: #1f2937; font-weight: 700; word-break: break-all; line-height: 1.4; }

    /* Timeline Tracker Horizontal */
    .tracker { display: flex; justify-content: space-between; position: relative; margin: 40px 0 10px 0; }
    .tracker::before { content: ''; position: absolute; top: 22px; left: 10%; right: 10%; height: 4px; background: #e5e7eb; z-index: 1; }
    
    .step { text-align: center; position: relative; z-index: 2; width: 33.33%; }
    .step-icon { width: 50px; height: 50px; background: #e5e7eb; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 12px auto; font-size: 1.3rem; color: white; font-weight: bold; transition: 0.3s; border: 5px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .step-label { font-size: 0.9rem; font-weight: bold; color: #9ca3af; }

    .step.active .step-icon { background: var(--primary, #00b2a9); }
    .step.active .step-label { color: var(--primary, #00b2a9); }
    
    /* Box Tanggapan */
    .tanggapan-box { background: #f0fdfa; border: 1px solid #ccfbf1; padding: 20px 25px; border-radius: 12px; margin-top: 40px; border-left: 5px solid var(--primary, #00b2a9); }
    .tanggapan-box h4 { margin: 0 0 10px 0; font-size: 1rem; color: #115e59; display: flex; align-items: center; gap: 8px; }
    .tanggapan-box p { margin: 0; font-size: 0.95rem; color: #333; line-height: 1.6; }
    
    /* Box Penolakan */
    .tanggapan-box.box-ditolak { background: #fef2f2; border-color: #fecaca; border-left-color: #ef4444; }
    .tanggapan-box.box-ditolak h4 { color: #991b1b; }

    /* Note Box Merah (Peringatan & WA) */
    .note-box { background: #fff5f5; border: 1px solid #fed7d7; border-left: 5px solid #ef4444; padding: 25px; border-radius: 12px; margin-top: 40px; color: #991b1b; font-size: 0.95rem; line-height: 1.6; text-align: center; }
    .note-box strong { color: #b91c1c; font-size: 1.05rem; display: block; margin-bottom: 8px; }
    
    .btn-wa { display: inline-flex; align-items: center; gap: 8px; background: #25D366; color: white; padding: 12px 25px; border-radius: 30px; font-weight: 700; text-decoration: none; font-size: 0.95rem; transition: 0.3s; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3); margin-top: 15px; }
    .btn-wa:hover { background: #1ebc59; transform: translateY(-3px); box-shadow: 0 6px 15px rgba(37, 211, 102, 0.4); }

    .btn-file-action { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-weight: bold; font-size: 0.85rem; text-decoration: none; transition: 0.2s; }
    .btn-view { background: #0f766e; color: white; }
    .btn-view:hover { background: #115e59; }
    .btn-download { background: #ef8a1c; color: white; }
    .btn-download:hover { background: #d97706; }

    @media (max-width: 600px) {
        .info-grid { grid-template-columns: 1fr; gap: 15px; }
        .info-item div { word-break: break-word; }
        .step-label { font-size: 0.75rem; }
        .step-icon { width: 40px; height: 40px; font-size: 1rem; border-width: 3px; }
        .tracker::before { top: 18px; }
        .search-box, .result-card { padding: 25px 20px; }
    }
</style>

<main>
    <section class="inner-header" style="text-align: center; padding: 45px 20px; background: var(--primary, #00b2a9); color: white;">
        <h1 style="margin: 0 0 10px 0; font-size: 2.2rem; font-weight: 800;">Cek Status Layanan</h1>
        <p style="margin: 0; font-size: 1rem; opacity: 0.95;">
            <?php echo ($jenis_cari == 'keberatan') ? 'Lacak kemajuan pengajuan keberatan informasi publik Anda' : 'Lacak kemajuan permohonan informasi publik Anda'; ?>
        </p>
    </section>

    <div class="tracking-wrapper">
        
        <!-- FORM PENCARIAN -->
        <div class="search-box">
            <div class="search-box-icon">
                <?php echo ($jenis_cari == 'keberatan') ? '⚖️' : '🔍'; ?>
            </div>
            <h2>
                <?php echo ($jenis_cari == 'keberatan') ? 'Lacak Status Pengajuan Keberatan' : 'Lacak Status Permohonan Informasi'; ?>
            </h2>
            <p class="search-subtitle">
                <?php echo ($jenis_cari == 'keberatan') ? 'Masukkan nomor registrasi pengajuan keberatan untuk melihat status terbaru.' : 'Masukkan nomor registrasi permohonan informasi untuk melihat status terbaru.'; ?>
            </p>

            <form action="" method="GET">
                <input type="hidden" name="jenis_layanan" value="<?php echo htmlspecialchars($jenis_cari); ?>">

                <div class="form-group">
                    <input type="text" name="kode" class="form-control" placeholder="<?php echo ($jenis_cari == 'keberatan') ? 'Contoh: KEB-2026-XXXX' : 'Contoh: REQ-2026-XXXX'; ?>" value="<?php echo htmlspecialchars($kode_cari); ?>" required autocomplete="off">
                </div>
                <button type="submit" class="btn-cari">Lacak Status Sekarang 🚀</button>
            </form>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- HASIL TRACKING DATA -->
        <?php if ($data_ditemukan && $hasil): ?>
            
            <?php 
                $status_db = strtolower($hasil['status'] ?? '');
                $step = 1; 
                $badge_class = "status-pending";
                $is_rejected = false;

                if (in_array($status_db, ['diproses', 'proses'])) {
                    $step = 2;
                    $badge_class = "status-proses";
                } elseif (in_array($status_db, ['selesai', 'dikabulkan', 'dikabulkan sepenuhnya'])) {
                    $step = 3;
                    $badge_class = "status-selesai";
                } elseif (in_array($status_db, ['ditolak', 'dikecualikan'])) {
                    $step = 3;
                    $badge_class = "status-ditolak";
                    $is_rejected = true;
                }
            ?>

            <div class="result-card">
                <div class="result-header">
                    <h3>Detail Tracking Data</h3>
                    <span class="badge-status <?php echo $badge_class; ?>">
                        <?php echo strtoupper($hasil['status'] ?? 'MENUNGGU'); ?>
                    </span>
                </div>

                <!-- DATA DENGAN LAYOUT RAPI (BOX) -->
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nomor Registrasi / Kode</label>
                        <div><?php echo htmlspecialchars($hasil['nomor_registrasi'] ?? $kode_cari); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Nama Pemohon</label>
                        <div style="word-break: break-word;"><?php echo htmlspecialchars($hasil['nama_pemohon'] ?? '-'); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Tanggal Masuk</label>
                        <div>
                            <?php 
                            $tgl = $hasil['tanggal_masuk'] ?? '';
                            echo !empty($tgl) ? date('d F Y, H:i', strtotime($tgl)) : '-';
                            ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Jenis Layanan</label>
                        <div style="word-break: break-word;"><?php echo $jenis_cari == 'permohonan' ? 'Permohonan Informasi' : 'Pengajuan Keberatan'; ?></div>
                    </div>
                </div>

                <!-- PROGRESS BAR / TIMELINE -->
                <div class="tracker">
                    <div class="step <?php echo ($step >= 1) ? 'active' : ''; ?>">
                        <div class="step-icon">1</div>
                        <div class="step-label">Berkas Masuk</div>
                    </div>
                    <div class="step <?php echo ($step >= 2) ? 'active' : ''; ?>">
                        <div class="step-icon">2</div>
                        <div class="step-label">Sedang Diproses</div>
                    </div>
                    <div class="step <?php echo ($step >= 3) ? 'active' : ''; ?>">
                        <div class="step-icon">3</div>
                        <div class="step-label">Selesai / Ditanggapi</div>
                    </div>
                </div>

                <!-- TANGGAPAN / ALASAN PENOLAKAN -->
                <?php 
                $tanggapan_admin = $hasil['tanggapan'] ?? '';
                $alasan_tolak = $hasil['alasan_penolakan'] ?? '';
                $file_pdf = $hasil['file_jawaban_pdf'] ?? '';
                
                // Cek mana yang ada isinya, dahulukan alasan_penolakan jika ditolak
                $pesan_admin = '';
                if (!empty($alasan_tolak)) {
                    $pesan_admin = $alasan_tolak;
                } elseif (!empty($tanggapan_admin)) {
                    $pesan_admin = $tanggapan_admin;
                }

                if(!empty($pesan_admin) || !empty($file_pdf)): 
                ?>
                    <div class="tanggapan-box <?php echo $is_rejected ? 'box-ditolak' : ''; ?>">
                        <h4>
                            <?php echo $is_rejected ? '❌ Alasan Penolakan Layanan:' : '💬 Tanggapan / Catatan Petugas PPID:'; ?>
                        </h4>
                        
                        <?php if(!empty($pesan_admin)): ?>
                            <p><?php echo nl2br(htmlspecialchars($pesan_admin)); ?></p>
                        <?php endif; ?>

                        <?php if(!empty($file_pdf)): ?>
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #99f6e4; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                                <span style="font-size: 0.9rem; font-weight: bold; color: #0f766e;">📄 Dokumen Jawaban Resmi:</span>
                                <div style="display: flex; gap: 8px;">
                                    <a href="<?php echo htmlspecialchars($file_pdf); ?>" target="_blank" class="btn-file-action btn-view">👁️ Lihat PDF</a>
                                    <a href="<?php echo htmlspecialchars($file_pdf); ?>" download class="btn-file-action btn-download">📥 Unduh PDF</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- CATATAN WA (NOTE BOX MERAH) -->
                <?php if (!$is_rejected && $step < 3): // Hanya tampil jika belum selesai dan belum ditolak ?>
                <div class="note-box">
                    <strong>⚠️ Perhatian</strong>
                    Proses layanan informasi standar membutuhkan estimasi waktu maksimal <b>3 Hari Kerja</b> setelah berkas dinyatakan lengkap.<br> Jika Anda membutuhkan pelayanan lebih cepat atau memiliki pertanyaan, silakan hubungi Admin kami.
                    <br>
                    <a href="https://wa.me/628516662390" target="_blank" class="btn-wa">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                        </svg> 
                        Hubungi WhatsApp Admin
                    </a>
                </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

    </div>
</main>

<?php include 'includes/footer.php'; ?>