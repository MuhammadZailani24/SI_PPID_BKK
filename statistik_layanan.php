<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// 1. Ambil Pengaturan Tahun & Teks dari Database (Tabel pengaturan_web)
// =========================================================================
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN ('stat_tahun', 'stat_teks')");
if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Default Values jika belum diatur admin
$tahun_aktif = $settings['stat_tahun'] ?? date('Y');
$def_teks = "Berikut adalah data statistik permohonan informasi publik serta aktivitas unduhan dokumen yang masuk ke PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin sepanjang tahun ini:";
$teks_deskripsi = $settings['stat_teks'] ?? $def_teks;

// =========================================================================
// 2. Ambil Data Statistik Real-Time dari Database (Dikabulkan Sebagian Dihapus)
// =========================================================================
$stat_total = 0; 
$stat_full = 0; 
$stat_reject = 0;
$stat_proses = 0;
$stat_download = 0;
$stat_keberatan = 0;

$cek_tabel = $conn->query("SHOW TABLES LIKE 'permohonan_informasi'");
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    $q_total = $conn->query("SELECT COUNT(*) as jml FROM permohonan_informasi WHERE YEAR(tanggal_masuk) = '$tahun_aktif'");
    if($q_total) $stat_total = $q_total->fetch_assoc()['jml'];

    $q_full = $conn->query("SELECT COUNT(*) as jml FROM permohonan_informasi WHERE status = 'Selesai' AND YEAR(tanggal_masuk) = '$tahun_aktif'");
    if($q_full) $stat_full = $q_full->fetch_assoc()['jml'];

    $q_reject = $conn->query("SELECT COUNT(*) as jml FROM permohonan_informasi WHERE status = 'Ditolak' AND YEAR(tanggal_masuk) = '$tahun_aktif'");
    if($q_reject) $stat_reject = $q_reject->fetch_assoc()['jml'];

    $q_proses = $conn->query("SELECT COUNT(*) as jml FROM permohonan_informasi WHERE status = 'Diproses' AND YEAR(tanggal_masuk) = '$tahun_aktif'");
    if($q_proses) $stat_proses = $q_proses->fetch_assoc()['jml'];
}

// Ambil Real-Time Jumlah File Diunduh
$cek_kolom_dl = $conn->query("SHOW COLUMNS FROM informasi_publik LIKE 'jumlah_download'");
if ($cek_kolom_dl && $cek_kolom_dl->num_rows > 0) {
    $q_dl = $conn->query("SELECT SUM(jumlah_download) as jml FROM informasi_publik");
    if($q_dl && $row_dl = $q_dl->fetch_assoc()) {
        $stat_download = (int)($row_dl['jml'] ?? 0);
    }
} else {
    $cek_log = $conn->query("SHOW TABLES LIKE 'log_download'");
    if ($cek_log && $cek_log->num_rows > 0) {
        $q_log = $conn->query("SELECT COUNT(*) as jml FROM log_download");
        if($q_log) $stat_download = $q_log->fetch_assoc()['jml'];
    } else {
        $stat_download = 0; 
    }
}

// Ambil Real-Time Jumlah Keberatan
$cek_keb_stat = $conn->query("SHOW TABLES LIKE 'keberatan_informasi'");
if ($cek_keb_stat && $cek_keb_stat->num_rows > 0) {
    $q_tot_keb = $conn->query("SELECT COUNT(*) as jml FROM keberatan_informasi WHERE YEAR(tanggal_masuk) = '$tahun_aktif'");
    if($q_tot_keb && $row_keb = $q_tot_keb->fetch_assoc()) {
        $stat_keberatan = (int)($row_keb['jml'] ?? 0);
    }
}

// Untuk perhitungan tinggi grafik bar chart secara proporsional
$max_val = max([$stat_total, $stat_full, $stat_reject, $stat_download, $stat_keberatan, 1]);
$h_total      = ($stat_total / $max_val) * 180;
$h_full       = ($stat_full / $max_val) * 180;
$h_reject     = ($stat_reject / $max_val) * 180;
$h_download   = ($stat_download / $max_val) * 180;
$h_keberatan  = ($stat_keberatan / $max_val) * 180;

// =========================================================================
// 3. Data Real-Time untuk 3 Card Pie Chart
// =========================================================================

// Data Card 1: Persentase permohonan selesai (Selesai vs Proses)
$total_selesai_proses = $stat_full + $stat_proses;
$persentase_selesai = ($total_selesai_proses > 0) ? round(($stat_full / $total_selesai_proses) * 100, 1) : 0;

// Data Card 2: Informasi Publik Berdasarkan Kategori
$info_berkala = 0;
$info_sertamerta = 0;
$info_setiapsaat = 0;

$cek_berkala = $conn->query("SHOW TABLES LIKE 'informasi_publik'");
if($cek_berkala && $cek_berkala->num_rows > 0) {
    $q_ib = $conn->query("SELECT COUNT(*) as jml FROM informasi_publik");
    if($q_ib) $info_berkala = $q_ib->fetch_assoc()['jml'];
}

$cek_serta = $conn->query("SHOW TABLES LIKE 'informasi_serta_merta'");
if($cek_serta && $cek_serta->num_rows > 0) {
    $q_ism = $conn->query("SELECT COUNT(*) as jml FROM informasi_serta_merta");
    if($q_ism) $info_sertamerta = $q_ism->fetch_assoc()['jml'];
}

$cek_setiap = $conn->query("SHOW TABLES LIKE 'informasi_setiap_saat'");
if($cek_setiap && $cek_setiap->num_rows > 0) {
    $q_iss = $conn->query("SELECT COUNT(*) as jml FROM informasi_setiap_saat");
    if($q_iss) $info_setiapsaat = $q_iss->fetch_assoc()['jml'];
}

// Data Card 3: Alasan Permohonan Keberatan
$alasan_keberatan = [];
$data_keberatan = [0, 0, 0, 0, 0, 0, 0];

if($cek_keb_stat && $cek_keb_stat->num_rows > 0) {
    $q_keb = $conn->query("SELECT alasan_keberatan, COUNT(*) as jml FROM keberatan_informasi GROUP BY alasan_keberatan");
    if($q_keb) {
        while($r = $q_keb->fetch_assoc()) {
            $alasan = strtolower($r['alasan_keberatan']);
            if(strpos($alasan, 'pengecualian') !== false) $data_keberatan[0] += $r['jml'];
            elseif(strpos($alasan, 'sedia') !== false) $data_keberatan[1] += $r['jml'];
            elseif(strpos($alasan, 'tanggapi sebagaimana') !== false) $data_keberatan[3] += $r['jml'];
            elseif(strpos($alasan, 'tanggapi') !== false) $data_keberatan[2] += $r['jml'];
            elseif(strpos($alasan, 'penuh') !== false) $data_keberatan[4] += $r['jml'];
            elseif(strpos($alasan, 'biaya') !== false) $data_keberatan[5] += $r['jml'];
            elseif(strpos($alasan, 'waktu') !== false) $data_keberatan[6] += $r['jml'];
            else $data_keberatan[1] += $r['jml']; // Default fallback
        }
    }
}

// =========================================================================
// 4. Data Real-Time untuk Tren 7 Bulan Terakhir (Line Chart)
// =========================================================================
$trend_labels = [];
$trend_data = [];
$nama_bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

if ($cek_tabel && $cek_tabel->num_rows > 0) {
    for ($i = 6; $i >= 0; $i--) {
        $waktu = strtotime("-$i months");
        $bulan_angka = date('n', $waktu);
        $tahun_angka = date('Y', $waktu);
        
        $trend_labels[] = $nama_bulan[$bulan_angka] . ' ' . $tahun_angka;
        
        $ym = date('Y-m', $waktu);
        $q_trend = $conn->query("SELECT COUNT(*) as jml FROM permohonan_informasi WHERE status = 'Selesai' AND DATE_FORMAT(tanggal_masuk, '%Y-%m') = '$ym'");
        $jml = 0;
        if ($q_trend && $row = $q_trend->fetch_assoc()) {
            $jml = (int)$row['jml'];
        }
        $trend_data[] = $jml;
    }
}

// =========================================================================
// 5. Data Tabel Penolakan & Keberatan
// =========================================================================
$data_penolakan = [];
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    $q_penolakan = $conn->query("SELECT nama_pemohon, tanggapan, tanggal_masuk FROM permohonan_informasi WHERE status = 'Ditolak' ORDER BY tanggal_masuk DESC");
    if($q_penolakan) {
        while($row = $q_penolakan->fetch_assoc()){
            $data_penolakan[] = $row;
        }
    }
}

$data_tabel_keberatan = [];
if ($cek_keb_stat && $cek_keb_stat->num_rows > 0) {
    $q_tabel_keb = $conn->query("SELECT nama_pemohon, alasan_keberatan, tanggal_masuk FROM keberatan_informasi ORDER BY tanggal_masuk DESC");
    if($q_tabel_keb) {
        while($row = $q_tabel_keb->fetch_assoc()){
            $data_tabel_keberatan[] = $row;
        }
    }
}
?>

<!-- Library Chart.js, Datalabels, jQuery & DataTables -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN STATISTIK LAYANAN
       ========================================================= */
    .statistik-wrapper {
        max-width: 1200px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        display: flex;
        flex-direction: column;
        gap: 40px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .statistik-header-box { text-align: center; }
    .statistik-title { font-size: 2rem; font-weight: 800; color: #1f2937; margin-bottom: 12px; letter-spacing: 0.3px; }
    .statistik-desc { font-size: 1.02rem; color: #4b5563; max-width: 750px; margin: 0 auto; line-height: 1.75; }

    /* Grid Kartu Ringkasan (5 Kolom Modern) */
    .stat-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 20px;
    }

    .stat-user-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 28px 20px;
        text-align: center;
        border: 1px solid #eaeaea;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .stat-user-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(0, 178, 169, 0.12); border-color: #d1d5db; }
    .stat-user-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px; }

    .card-total::before { background: linear-gradient(90deg, var(--primary, #00b2a9), #008f88); }
    .card-full::before { background: linear-gradient(90deg, #10b981, #059669); }
    .card-reject::before { background: linear-gradient(90deg, #ef4444, #dc2626); }
    .card-download::before { background: linear-gradient(90deg, #6366f1, #4f46e5); }
    .card-keberatan::before { background: linear-gradient(90deg, #ec4899, #be185d); }

    .stat-user-card label { font-size: 0.72rem; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 12px; min-height: 28px; }
    .stat-user-num { font-size: 2.3rem; font-weight: 800; line-height: 1; }

    .card-total .stat-user-num { color: var(--primary, #00b2a9); }
    .card-full .stat-user-num { color: #10b981; }
    .card-reject .stat-user-num { color: #ef4444; }
    .card-download .stat-user-num { color: #6366f1; }
    .card-keberatan .stat-user-num { color: #ec4899; }

    /* Kotak Grafik Visualisasi */
    .chart-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #eaeaea;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.04);
        padding: 45px;
        position: relative;
    }
    .chart-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: linear-gradient(90deg, var(--primary, #00b2a9), var(--secondary, #ef8a1c)); }
    .chart-card h3 { text-align: center; font-size: 1.35rem; font-weight: 800; color: #1f2937; margin-bottom: 40px; letter-spacing: 0.3px; }

    /* Container Batang Grafik */
    .bar-chart-container { display: flex; justify-content: space-around; align-items: flex-end; height: 250px; border-bottom: 2px solid #e5e7eb; padding-bottom: 0; position: relative; gap: 15px; }
    .bar-group { display: flex; flex-direction: column; align-items: center; flex: 1; max-width: 90px; height: 100%; justify-content: flex-end; }
    .bar-value { font-size: 0.85rem; font-weight: 800; color: #4b5563; margin-bottom: 10px; }
    .bar { width: 100%; border-radius: 10px 10px 0 0; transition: height 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275); }

    .bar-total { background: linear-gradient(180deg, var(--primary, #00b2a9), #008f88); }
    .bar-full { background: linear-gradient(180deg, #10b981, #059669); }
    .bar-reject { background: linear-gradient(180deg, #ef4444, #dc2626); }
    .bar-download { background: linear-gradient(180deg, #6366f1, #4f46e5); }
    .bar-keberatan { background: linear-gradient(180deg, #ec4899, #be185d); }
    .bar-label { font-size: 0.72rem; font-weight: 700; color: #6b7280; text-align: center; margin-top: 15px; line-height: 1.3; }

    /* Grid Pie Chart */
    .pie-cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-top: 10px; }
    .pie-card { background: #ffffff; border-radius: 20px; border: 1px solid #eaeaea; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03); padding: 30px 25px; display: flex; flex-direction: column; }
    .pie-card h4 { font-size: 0.95rem; font-weight: 800; color: #1f2937; margin-bottom: 25px; padding-bottom: 12px; border-bottom: 2px solid var(--secondary, #ef8a1c); }
    .pie-chart-wrapper { position: relative; width: 100%; height: 240px; display: flex; justify-content: center; align-items: center; margin-bottom: 25px; }
    .doughnut-center-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2rem; font-weight: 900; color: #1f2937; pointer-events: none; }
    
    .custom-legend { list-style: none; padding: 0; margin: 0; border-top: 1px solid #f3f4f6; padding-top: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: flex-end; }
    .custom-legend li { display: flex; align-items: center; font-size: 0.85rem; color: #4b5563; margin-bottom: 10px; font-weight: 500; }
    .custom-legend li span.box { width: 14px; height: 14px; border-radius: 4px; margin-right: 12px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }

    /* Trend Line Chart Card */
    .trend-card { background: #ffffff; border-radius: 20px; border: 1px solid #eaeaea; box-shadow: 0 10px 35px rgba(0, 0, 0, 0.04); padding: 40px; margin-top: 10px; }
    .trend-header { margin-bottom: 30px; }
    .trend-header h3 { font-size: 1.25rem; font-weight: 800; color: #1f2937; margin-bottom: 8px; }
    .trend-header p { font-size: 0.9rem; color: #6b7280; margin: 0; }
    .trend-chart-wrapper { position: relative; height: 350px; width: 100%; }

    /* =================================================
       STYLING TABEL DATA ALASAN PENOLAKAN & KEBERATAN
       ================================================= */
    .table-section {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #eaeaea;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.04);
        padding: 35px;
        margin-top: 20px;
    }
    
    .table-section-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
    }

    .table-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.2rem;
        color: white;
    }
    .icon-orange { background: var(--secondary, #ef8a1c); box-shadow: 0 4px 10px rgba(239, 138, 28, 0.3); }
    .icon-slate { background: #1f2937; box-shadow: 0 4px 10px rgba(31, 41, 55, 0.3); }

    .table-section-header div h3 { margin: 0; font-size: 1.15rem; font-weight: 800; color: #1f2937; }
    .table-section-header div p { margin: 4px 0 0 0; font-size: 0.85rem; color: #6b7280; }

    /* Kustomisasi Tampilan DataTables */
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 15px;
        margin-left: 10px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary, #00b2a9);
        box-shadow: 0 0 0 3px rgba(0, 178, 169, 0.15);
    }
    .dataTable-custom {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        margin-top: 15px !important;
        margin-bottom: 15px !important;
    }
    .dataTable-custom thead th {
        background-color: #00b2a9; /* Dark Slate Blue matching App Sidebar */
        color: white;
        text-transform: uppercase;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 16px;
        border-bottom: none !important;
        text-align: left;
    }
    .dataTable-custom tbody td {
        padding: 18px 16px;
        font-size: 0.88rem;
        color: #4b5563;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: top;
        line-height: 1.6;
    }
    .dataTable-custom tbody tr:hover { background-color: #f8fafc; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--sidebar-bg, #111827) !important;
        color: white !important;
        border: none !important;
        border-radius: 6px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6 !important;
        color: #1f2937 !important;
        border: none !important;
    }
    .dataTables_info, .dataTables_paginate {
        margin-top: 15px;
        font-size: 0.85rem;
        color: #6b7280 !important;
    }

    @media (max-width: 992px) {
        .pie-cards-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .chart-card, .trend-card, .table-section { padding: 30px 15px; }
        .bar-label { font-size: 0.65rem; }
        .stat-cards-grid { grid-template-columns: repeat(2, 1fr); }
        .pie-cards-grid { grid-template-columns: 1fr; }
        .trend-chart-wrapper { height: 250px; }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Statistik Layanan</h1>
        <p>Data rekapitulasi pelayanan informasi publik dan unduhan dokumen PPID</p>
    </section>

    <div class="statistik-wrapper">
        
        <!-- Header & Deskripsi Dinamis -->
        <div class="statistik-header-box">
            <h2 class="statistik-title">Ringkasan Statistik Tahun <?php echo htmlspecialchars($tahun_aktif); ?></h2>
            <p class="statistik-desc">
                <?php echo htmlspecialchars($teks_deskripsi); ?>
            </p>
        </div>

        <!-- 5 Kartu Angka Statistik -->
        <div class="stat-cards-grid">
            <div class="stat-user-card card-total">
                <label>Jumlah Permohonan</label>
                <div class="stat-user-num"><?php echo $stat_total; ?></div>
            </div>
            <div class="stat-user-card card-full">
                <label>Permohonan Selesai</label>
                <div class="stat-user-num"><?php echo $stat_full; ?></div>
            </div>
            <div class="stat-user-card card-reject">
                <label>Permohonan Di Tolak</label>
                <div class="stat-user-num"><?php echo $stat_reject; ?></div>
            </div>
            <div class="stat-user-card card-download">
                <label>Jumlah Unduh</label>
                <div class="stat-user-num"><?php echo $stat_download; ?></div>
            </div>
            <div class="stat-user-card card-keberatan">
                <label>Jumlah Keberatan</label>
                <div class="stat-user-num"><?php echo $stat_keberatan; ?></div>
            </div>
        </div>

        <!-- Grafik Visualisasi Batang -->
        <div class="chart-card">
            <h3>Grafik Visualisasi Pelayanan Informasi & Unduhan Dokumen</h3>
            
            <div class="bar-chart-container">
                <div class="bar-group">
                    <div class="bar-value"><?php echo $stat_total; ?></div>
                    <div class="bar bar-total" style="height: <?php echo max($h_total, 8); ?>px;"></div>
                    <div class="bar-label">Jumlah Permohonan</div>
                </div>
                <div class="bar-group">
                    <div class="bar-value"><?php echo $stat_full; ?></div>
                    <div class="bar bar-full" style="height: <?php echo max($h_full, 8); ?>px;"></div>
                    <div class="bar-label">Permohonan Selesai</div>
                </div>
                <div class="bar-group">
                    <div class="bar-value"><?php echo $stat_reject; ?></div>
                    <div class="bar bar-reject" style="height: <?php echo max($h_reject, 8); ?>px;"></div>
                    <div class="bar-label">Permohonan Ditolak</div>
                </div>
                <div class="bar-group">
                    <div class="bar-value"><?php echo $stat_download; ?></div>
                    <div class="bar bar-download" style="height: <?php echo max($h_download, 8); ?>px;"></div>
                    <div class="bar-label">Jumlah Unduh</div>
                </div>
                <div class="bar-group">
                    <div class="bar-value"><?php echo $stat_keberatan; ?></div>
                    <div class="bar bar-keberatan" style="height: <?php echo max($h_keberatan, 8); ?>px;"></div>
                    <div class="bar-label">Jumlah Keberatan</div>
                </div>
            </div>
        </div>

        <!-- 3 KARTU DIAGRAM PIE / DOUGHNUT BAWAH -->
        <div class="pie-cards-grid">
            
            <!-- Card 1: Persentase Selesai -->
            <div class="pie-card">
                <h4>Persentase permohonan selesai</h4>
                <div class="pie-chart-wrapper">
                    <canvas id="chartSelesai"></canvas>
                    <div class="doughnut-center-text"><?php echo $persentase_selesai; ?>%</div>
                </div>
                <ul class="custom-legend">
                    <li><span class="box" style="background:#00b2a9;"></span> Selesai (<?php echo $stat_full; ?>)</li>
                    <li><span class="box" style="background:#d1d5db;"></span> Dalam proses (<?php echo $stat_proses; ?>)</li>
                </ul>
            </div>

            <!-- Card 2: Informasi Kategori -->
            <div class="pie-card">
                <h4>Informasi publik berdasarkan kategori</h4>
                <div class="pie-chart-wrapper">
                    <canvas id="chartKategori"></canvas>
                </div>
                <ul class="custom-legend">
                    <li><span class="box" style="background:#008f88;"></span> Tersedia setiap saat (<?php echo $info_setiapsaat; ?>)</li>
                    <li><span class="box" style="background:#00b2a9;"></span> Berkala (<?php echo $info_berkala; ?>)</li>
                    <li><span class="box" style="background:#ef8a1c;"></span> Serta merta (<?php echo $info_sertamerta; ?>)</li>
                    <li><span class="box" style="background:#dc2626;"></span> Dikecualikan (0)</li>
                </ul>
            </div>

            <!-- Card 3: Alasan Keberatan -->
            <div class="pie-card">
                <h4>Alasan permohonan keberatan</h4>
                <div class="pie-chart-wrapper">
                    <canvas id="chartKeberatan"></canvas>
                </div>
                <ul class="custom-legend">
                    <li><span class="box" style="background:#008f88;"></span> Pengecualian (<?php echo $data_keberatan[0]; ?>)</li>
                    <li><span class="box" style="background:#00b2a9;"></span> Tidak disediakan informasi berkala (<?php echo $data_keberatan[1]; ?>)</li>
                    <li><span class="box" style="background:#5eead4;"></span> Tidak ditanggapi (<?php echo $data_keberatan[2]; ?>)</li>
                    <li><span class="box" style="background:#ef8a1c;"></span> Tidak ditanggapi sebagaimana diminta (<?php echo $data_keberatan[3]; ?>)</li>
                    <li><span class="box" style="background:#fcd34d;"></span> Tidak dipenuhi (<?php echo $data_keberatan[4]; ?>)</li>
                    <li><span class="box" style="background:#d1d5db;"></span> Pengenaan biaya tidak wajar (<?php echo $data_keberatan[5]; ?>)</li>
                    <li><span class="box" style="background:#9ca3af;"></span> Melebihi jangka waktu (<?php echo $data_keberatan[6]; ?>)</li>
                </ul>
            </div>

        </div>

        <!-- TREND LINE CHART CARD -->
        <div class="trend-card">
            <div class="trend-header">
                <h3>Permohonan selesai — tren 7 bulan terakhir</h3>
                <p>Grafik garis menunjukkan volume permohonan yang telah diselesaikan per bulan untuk memantau dinamika layanan.</p>
            </div>
            <div class="trend-chart-wrapper">
                <canvas id="chartTrend"></canvas>
            </div>
        </div>

        <!-- TABEL 1: ALASAN PENOLAKAN PERMOHONAN -->
        <div class="table-section">
            <div class="table-section-header">
                <div class="table-icon icon-orange">
                    🛡️
                </div>
                <div>
                    <h3>Alasan Penolakan Permohonan Informasi Publik</h3>
                    <p>Rincian permohonan yang ditolak beserta alasan dan tanggal penolakannya.</p>
                </div>
            </div>
            
            <table id="tabelPenolakan" class="dataTable-custom">
                <thead>
                    <tr>
                        <th style="width: 5%;">NO</th>
                        <th style="width: 25%;">NAMA PEMOHON</th>
                        <th style="width: 50%;">ALASAN PENOLAKAN</th>
                        <th style="width: 20%;">TANGGAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach($data_penolakan as $row): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['tanggapan'] ?: 'Ditolak/Dikecualikan sebagaimana diatur dalam UU.')); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($row['tanggal_masuk'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- TABEL 2: ALASAN KEBERATAN -->
        <div class="table-section">
            <div class="table-section-header">
                <div class="table-icon icon-slate">
                    ⚖️
                </div>
                <div>
                    <h3>Alasan Keberatan Informasi Publik</h3>
                    <p>Daftar keberatan yang diajukan beserta alasannya.</p>
                </div>
            </div>
            
            <table id="tabelKeberatan" class="dataTable-custom">
                <thead>
                    <tr>
                        <th style="width: 5%;">NO</th>
                        <th style="width: 25%;">NAMA PEMOHON</th>
                        <th style="width: 50%;">ALASAN KEBERATAN</th>
                        <th style="width: 20%;">TANGGAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no_keb = 1;
                    foreach($data_tabel_keberatan as $row): 
                    ?>
                    <tr>
                        <td><?php echo $no_keb++; ?></td>
                        <td style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['alasan_keberatan'])); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($row['tanggal_masuk'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<script>
    // Inisialisasi DataTables untuk Pencarian dan Pagination yang elegan
    $(document).ready(function() {
        $('#tabelPenolakan').DataTable({
            "language": {
                "search": "Cari :",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "previous": "Previous",
                    "next": "Next"
                }
            },
            "pageLength": 5, // Sesuai dengan screenshot (menampilkan 5 per halaman)
            "lengthChange": false
        });

        $('#tabelKeberatan').DataTable({
            "language": {
                "search": "Cari :",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "previous": "Previous",
                    "next": "Next"
                }
            },
            "pageLength": 5, 
            "lengthChange": false
        });
    });

    // Registrasi Plugin Datalabels agar persentase tampil di dalam diagram
    Chart.register(ChartDataLabels);

    // Konfigurasi umum Chart.js
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = "#6b7280";

    // Fungsi Format Data Labels (Hitung & Tampilkan Persentase)
    const datalabelsConfig = {
        color: '#ffffff',
        font: { weight: 'bold', size: 13 },
        formatter: (value, ctx) => {
            if (value === 0) return ''; 
            let dataArr = ctx.chart.data.datasets[0].data;
            let sum = dataArr.reduce((a, b) => a + b, 0);
            if (sum === 0) return '';
            return (value * 100 / sum).toFixed(1) + "%";
        },
        anchor: 'center',
        align: 'center'
    };

    // 1. Chart Persentase Selesai (Doughnut)
    const ctxSelesai = document.getElementById('chartSelesai').getContext('2d');
    new Chart(ctxSelesai, {
        type: 'doughnut',
        data: {
            labels: ['Selesai', 'Dalam Proses'],
            datasets: [{
                data: [<?php echo $stat_full; ?>, <?php echo $stat_proses; ?>],
                backgroundColor: ['#00b2a9', '#d1d5db'],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                datalabels: { display: false },
                tooltip: {
                    callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.raw; } }
                }
            }
        }
    });

    // 2. Chart Informasi Kategori (Pie)
    const ctxKategori = document.getElementById('chartKategori').getContext('2d');
    new Chart(ctxKategori, {
        type: 'pie',
        data: {
            labels: ['Tersedia setiap saat', 'Berkala', 'Serta merta', 'Dikecualikan'],
            datasets: [{
                data: [<?php echo $info_setiapsaat; ?>, <?php echo $info_berkala; ?>, <?php echo $info_sertamerta; ?>, 0],
                backgroundColor: ['#008f88', '#00b2a9', '#ef8a1c', '#dc2626'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: datalabelsConfig,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let val = context.raw;
                            let percentage = total > 0 ? ((val / total) * 100).toFixed(1) + '%' : '0%';
                            return ' ' + context.label + ': ' + val + ' (' + percentage + ')';
                        }
                    }
                }
            }
        }
    });

    // 3. Chart Alasan Keberatan (Pie)
    const ctxKeberatan = document.getElementById('chartKeberatan').getContext('2d');
    new Chart(ctxKeberatan, {
        type: 'pie',
        data: {
            labels: ['Pengecualian', 'Tidak disediakan berkala', 'Tidak ditanggapi', 'Tidak ditanggapi sesuai diminta', 'Tidak dipenuhi', 'Biaya tak wajar', 'Melebihi waktu'],
            datasets: [{
                data: [
                    <?php echo $data_keberatan[0]; ?>, 
                    <?php echo $data_keberatan[1]; ?>, 
                    <?php echo $data_keberatan[2]; ?>, 
                    <?php echo $data_keberatan[3]; ?>, 
                    <?php echo $data_keberatan[4]; ?>, 
                    <?php echo $data_keberatan[5]; ?>, 
                    <?php echo $data_keberatan[6]; ?>
                ],
                backgroundColor: ['#008f88', '#00b2a9', '#5eead4', '#ef8a1c', '#fcd34d', '#d1d5db', '#9ca3af'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: datalabelsConfig,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let val = context.raw;
                            let percentage = total > 0 ? ((val / total) * 100).toFixed(1) + '%' : '0%';
                            return ' ' + context.label + ': ' + val + ' (' + percentage + ')';
                        }
                    }
                }
            }
        }
    });

    // 4. Chart Trend 7 Bulan Terakhir (Line Area Chart)
    const trendLabels = <?php echo json_encode($trend_labels); ?>;
    const trendData = <?php echo json_encode($trend_data); ?>;
    
    const ctxTrend = document.getElementById('chartTrend').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Jumlah Permohonan Selesai',
                data: trendData,
                backgroundColor: 'rgba(0, 178, 169, 0.12)', 
                borderColor: '#00b2a9', 
                borderWidth: 3,
                pointBackgroundColor: '#ffffff', 
                pointBorderColor: '#ef8a1c', 
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true, 
                tension: 0.4 
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        font: { size: 12, weight: '500' }
                    }
                },
                datalabels: {
                    display: false 
                },
                tooltip: {
                    backgroundColor: 'rgba(31, 41, 55, 0.9)',
                    titleFont: { size: 13 },
                    bodyFont: { size: 14, weight: 'bold' },
                    padding: 10,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: { font: { size: 11 } }
                },
                y: {
                    grid: { color: '#f3f4f6', drawBorder: false },
                    beginAtZero: true,
                    ticks: { font: { size: 11 }, precision: 0 }
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>