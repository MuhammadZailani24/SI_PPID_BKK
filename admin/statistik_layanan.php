<?php 
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Panggil file koneksi database
require_once '../includes/koneksi.php';

$pesan_sukses = false;

// Tahun sistem (dipakai saat menyimpan pengaturan)
$tahun_sistem = date('Y');

// Proses Simpan / Update Pengaturan Teks
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_update = [
        'stat_tahun'   => $tahun_sistem,
        'stat_teks'    => $_POST['stat_teks']
    ];

    foreach ($data_update as $kunci => $nilai) {
        $nilai_esc = mysqli_real_escape_string($conn, $nilai);
        $cek = $conn->query("SELECT id FROM pengaturan_web WHERE kunci_pengaturan = '$kunci'");
        if ($cek->num_rows > 0) {
            $conn->query("UPDATE pengaturan_web SET nilai_pengaturan = '$nilai_esc' WHERE kunci_pengaturan = '$kunci'");
        } else {
            $conn->query("INSERT INTO pengaturan_web (kunci_pengaturan, nilai_pengaturan) VALUES ('$kunci', '$nilai_esc')");
        }
    }
    
    $conn->query("UPDATE pengaturan_web SET nilai_pengaturan = '$tahun_sistem' WHERE kunci_pengaturan = 'stat_tahun'");
    $pesan_sukses = true;
}

// =========================================================================
// 1. Ambil Pengaturan Tahun & Teks dari Database (SAMA DENGAN SISI USER)
// =========================================================================
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN ('stat_tahun', 'stat_teks')");
if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Default Values jika belum diatur admin (sama dengan sisi user)
$tahun_aktif = $settings['stat_tahun'] ?? date('Y');
$def_teks = "Berikut adalah data statistik permohonan informasi publik serta aktivitas unduhan dokumen yang masuk ke PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin sepanjang tahun ini:";
$teks_deskripsi = $settings['stat_teks'] ?? $def_teks;

// =========================================================================
// 2. Ambil Data Statistik Real-Time dari Database (SAMA DENGAN SISI USER)
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
// 5. Data Tabel Penolakan & Keberatan (SAMA DENGAN SISI USER)
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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Statistik Layanan - Admin CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Library Chart.js, DataLabels, jQuery & DataTables (sama dengan sisi user) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <style>
        /* =====================================================================
           TEMA: Balai Kekarantinaan Kesehatan Kelas I Banjarmasin
           Palet: Teal Kemenkes (#00b2a9) + Oranye (#ef8a1c) + Deep Teal gelap
           (disamakan dengan laporan_ppid.php dan halaman admin lainnya)
           ===================================================================== */
        :root { 
            --primary: #00b2a9; 
            --primary-dark: #008f88;
            --primary-deep: #04302f;
            --primary-soft: #e6f7f6;
            --secondary: #ef8a1c; 
            --secondary-dark: #d4730a;
            --secondary-soft: #fff4e5;
            --success: #10b981;
            --success-dark: #0b8a5a;
            --success-soft: #dcf7ec;
            --sidebar-bg: linear-gradient(180deg, #05383a 0%, #04302f 55%, #031f20 100%); 
            --sidebar-text: #9fc4c2;
            --sidebar-hover: rgba(255, 255, 255, 0.07);
            --bg-light: #eef5f5; 
            --text-dark: #12302f;
            --text-muted: #5f7a79;
            --border-soft: rgba(0, 143, 136, 0.12);
            --card-shadow: 0 1px 2px rgba(4, 48, 47, 0.04), 0 8px 24px rgba(4, 48, 47, 0.06);
            --card-hover-shadow: 0 2px 4px rgba(4, 48, 47, 0.05), 0 18px 40px rgba(0, 143, 136, 0.16);
            --radius-lg: 24px;
            --radius-md: 16px;
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
        }
        
        * { box-sizing: border-box; }
        body { 
            margin: 0; padding: 0; 
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif; 
            background-color: var(--bg-light);
            background-image:
                radial-gradient(900px 500px at 105% -10%, rgba(0, 178, 169, 0.14), transparent 60%),
                radial-gradient(700px 450px at -5% 110%, rgba(239, 138, 28, 0.10), transparent 60%);
            background-attachment: fixed;
            color: var(--text-dark); 
            display: flex; min-height: 100vh; overflow-x: hidden; 
            -webkit-font-smoothing: antialiased;
        }
        
        /* SIDEBAR MODERN */
        .sidebar { 
            width: 280px; background: var(--sidebar-bg); color: white; 
            display: flex; flex-direction: column; position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; 
            transition: transform 0.35s var(--ease); 
            box-shadow: 8px 0 30px rgba(3, 31, 32, 0.25); 
            border-right: 1px solid rgba(255,255,255,0.04);
        }
        .sidebar::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; z-index: 2;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(0, 178, 169, 0.35); border-radius: 5px; }
        
        .sidebar-header { 
            padding: 34px 22px 26px; text-align: center; 
            border-bottom: 1px solid rgba(255,255,255,0.07); 
            background: radial-gradient(circle at 50% 0%, rgba(0, 178, 169, 0.22) 0%, transparent 70%); 
        }
        .sidebar-header h3 { margin: 0; font-size: 1.35rem; color: #ffffff; font-weight: 800; letter-spacing: 2px; }
        .sidebar-header p { 
            display: inline-block; margin: 10px 0 0 0; padding: 5px 14px; font-size: 0.7rem; 
            color: #7fe3dc; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; 
            background: rgba(0, 178, 169, 0.14); border: 1px solid rgba(0, 178, 169, 0.3); border-radius: 20px; 
        }
        
        .sidebar-menu { list-style: none; padding: 16px 12px 24px; margin: 0; }
        .menu-category { margin-bottom: 4px; }
        .menu-category > a { 
            display: flex; justify-content: space-between; align-items: center; 
            color: var(--sidebar-text); text-decoration: none; padding: 12px 14px; 
            font-size: 0.9rem; font-weight: 500; border-radius: 12px; 
            transition: all 0.25s var(--ease); cursor: pointer; position: relative;
        }
        .menu-category > a:hover { background-color: var(--sidebar-hover); color: white; transform: translateX(4px); }
        .menu-category > a.active-parent { 
            background: linear-gradient(90deg, rgba(0, 178, 169, 0.28) 0%, rgba(0, 178, 169, 0.06) 100%); 
            color: #ffffff; font-weight: 700; 
            box-shadow: inset 3px 0 0 var(--secondary);
        }
        
        .menu-title { display: flex; align-items: center; gap: 12px; }
        .menu-title > span:first-child { 
            display: inline-flex; align-items: center; justify-content: center; 
            width: 32px; height: 32px; border-radius: 10px; 
            background: rgba(255,255,255,0.06); font-size: 0.95rem; 
        }
        .menu-category > a.active-parent .menu-title > span:first-child { background: rgba(0, 178, 169, 0.35); }
        .arrow { font-size: 0.65rem; transition: transform 0.3s ease; opacity: 0.7; }
        .menu-category.open .arrow { transform: rotate(180deg); color: var(--secondary); opacity: 1; }
        
        .sub-menu { list-style: none; padding: 6px 0 6px 14px; margin: 0; display: none; overflow: hidden; }
        .sub-menu li a { 
            display: flex; align-items: center; gap: 10px; color: #7fa3a1; text-decoration: none; 
            padding: 10px 15px 10px 38px; font-size: 0.84rem; border-radius: 10px; 
            transition: all 0.2s ease; position: relative; 
        }
        .sub-menu li a:hover, .sub-menu li a.active { color: white; background-color: var(--sidebar-hover); }
        .sub-menu li a.active { font-weight: 700; }
        .sub-menu li a::before { 
            content: ""; position: absolute; left: 17px; top: 50%; transform: translateY(-50%); 
            width: 6px; height: 6px; background-color: #3c6563; border-radius: 50%; transition: 0.3s; 
        }
        .sub-menu li a:hover::before, .sub-menu li a.active::before { background-color: var(--secondary); box-shadow: 0 0 10px var(--secondary); }

        .menu-label { 
            font-size: 0.68rem; color: #5f8a88; text-transform: uppercase; letter-spacing: 1.6px; 
            padding: 20px 16px 8px; font-weight: 700; display: flex; align-items: center; gap: 10px; 
        }
        .menu-label::after { content: ""; flex: 1; height: 1px; background: rgba(255,255,255,0.08); }

        /* KONTEN UTAMA */
        .main-content { flex: 1; margin-left: 280px; display: flex; flex-direction: column; min-width: 0; transition: margin-left 0.35s var(--ease); }
        
        .topbar { 
            background-color: rgba(255, 255, 255, 0.78); 
            -webkit-backdrop-filter: saturate(180%) blur(14px); backdrop-filter: saturate(180%) blur(14px);
            height: 72px; padding: 0 36px; display: flex; align-items: center; justify-content: space-between; 
            border-bottom: 1px solid var(--border-soft);
            box-shadow: 0 4px 20px rgba(4, 48, 47, 0.04); position: sticky; top: 0; z-index: 990; 
        }
        .topbar-left { display: flex; align-items: center; gap: 15px; }
        .menu-toggle { 
            display: none; background: var(--primary-soft); border: none; font-size: 1.3rem; color: var(--primary-dark); 
            cursor: pointer; padding: 6px 12px; border-radius: 10px; 
        }
        .topbar-title { font-size: 1.2rem; font-weight: 800; color: var(--text-dark); letter-spacing: -0.2px; }
        
        .topbar-user { display: flex; align-items: center; gap: 14px; font-size: 0.9rem; font-weight: 600; color: var(--text-muted); }
        .topbar-user span { 
            padding: 8px 16px; background: var(--primary-soft); color: var(--primary-dark); 
            border-radius: 999px; font-weight: 700; 
        }
        .btn-logout { 
            background: linear-gradient(135deg, #fff1f1, #ffe0e0); color: #d12c2c; padding: 9px 20px; 
            border-radius: 999px; text-decoration: none; font-size: 0.83rem; font-weight: 700; 
            border: 1px solid #ffd0d0; transition: all 0.3s var(--ease); 
        }
        .btn-logout:hover { background: #d12c2c; color: white; border-color: #d12c2c; box-shadow: 0 8px 18px rgba(209, 44, 44, 0.25); transform: translateY(-1px); }

        .content-body { padding: 34px 40px 50px; max-width: 1200px; margin: 0 auto; width: 100%; box-sizing: border-box; }
        
        /* ALERTS & INFO BOX */
        .alert-success { background-color: #dcf7ec; color: #0b8a5a; padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 25px; border: 1px solid #b5ecd4; border-left: 5px solid #10b981; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08); }
        .info-box { 
            background-color: var(--primary-soft); padding: 20px 24px; border-radius: var(--radius-md); 
            font-size: 0.93rem; color: var(--primary-dark); margin-bottom: 30px; 
            border: 1px solid rgba(0, 178, 169, 0.22); border-left: 5px solid var(--primary); 
            display: flex; gap: 15px; align-items: flex-start; box-shadow: var(--card-shadow); line-height: 1.6; 
        }
        .info-box strong { color: var(--primary-deep); }
        
        /* CARDS */
        .card { 
            position: relative; overflow: hidden; background: #ffffff; border-radius: var(--radius-lg); padding: 34px; 
            box-shadow: var(--card-shadow); border: 1px solid var(--border-soft); margin-bottom: 30px; 
            transition: box-shadow 0.35s var(--ease); 
        }
        .card::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; 
            background: linear-gradient(90deg, var(--primary), #4fd8d0 55%, var(--secondary));
        }
        .card:hover { box-shadow: var(--card-hover-shadow); }
        
        .card h3 { margin-top: 0; color: var(--primary-dark); font-size: 1.15rem; border-bottom: 1px dashed rgba(0, 143, 136, 0.2); padding-bottom: 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-weight: 800; letter-spacing: -0.2px; }
        .card h3 > span:first-child { 
            display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; 
            border-radius: 12px; background: var(--primary-soft); font-size: 1.05rem; flex-shrink: 0;
        }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; font-size: 0.78rem; margin-bottom: 8px; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.6px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid #d6e4e3; border-radius: 14px; font-family: inherit; font-size: 0.95rem; box-sizing: border-box; transition: all 0.3s var(--ease); background-color: #f6fbfb; color: var(--text-dark); line-height: 1.5; }
        .form-control:focus { outline: none; border-color: var(--primary); background-color: white; box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.14); }
        .form-control-readonly { background-color: #eaf2f2; cursor: not-allowed; color: var(--text-muted); font-weight: 700; }
        .form-hint { color: var(--text-muted); font-size: 0.75rem; margin-top: 6px; display: block; }

        .btn-submit { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; padding: 15px 28px; border-radius: 999px; font-weight: 800; font-size: 0.9rem; cursor: pointer; transition: all 0.3s var(--ease); display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.8px; box-shadow: 0 10px 26px rgba(0, 178, 169, 0.3); font-family: inherit; }
        .btn-submit:hover { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); box-shadow: 0 12px 30px rgba(239, 138, 28, 0.4); transform: translateY(-2px); }

        /* TEMA WARNA KARTU */
        .card-secondary::before { background: linear-gradient(90deg, var(--secondary), #f6a94d 45%, var(--primary)); }
        .card-secondary h3 { color: var(--secondary-dark); }
        .card-secondary h3 > span:first-child { background: var(--secondary-soft); }

        .card-success::before { background: linear-gradient(90deg, var(--success), #5eead4 55%, var(--primary)); }
        .card-success h3 { color: var(--success-dark); }
        .card-success h3 > span:first-child { background: var(--success-soft); }

        .card-has-desc h3 { margin-bottom: 8px; }
        .card-desc { color: var(--text-muted); font-size: 0.9rem; margin: 0 0 25px 0; line-height: 1.6; }
        
        /* GRID MONITOR KOTAK ANGKA (5 KARTU, SAMA DENGAN SISI USER) */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 18px; margin-bottom: 25px; }
        .stat-box { 
            padding: 26px 20px 22px; border-radius: var(--radius-md); border: 1px solid var(--border-soft); 
            text-align: center; background: #ffffff; box-shadow: var(--card-shadow); 
            transition: transform 0.3s var(--ease), box-shadow 0.3s var(--ease); position: relative; overflow: hidden; 
        }
        .stat-box:hover { transform: translateY(-4px); box-shadow: var(--card-hover-shadow); }
        .stat-box::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 4px; }
        
        .stat-box label { font-size: 0.72rem; color: var(--text-muted); margin-bottom: 12px; display: block; font-weight: 700; text-transform: uppercase; letter-spacing: 0.9px; }
        .stat-number { font-size: 2.5rem; font-weight: 800; line-height: 1; letter-spacing: -1px; }
        
        .box-total::before { background-color: var(--primary); }
        .box-total .stat-number { color: var(--primary); }
        .box-full::before { background-color: #10b981; }
        .box-full .stat-number { color: #10b981; }
        .box-reject::before { background-color: #ef4444; }
        .box-reject .stat-number { color: #ef4444; }
        .box-download::before { background-color: #6366f1; }
        .box-download .stat-number { color: #6366f1; }
        .box-keberatan::before { background-color: #ec4899; }
        .box-keberatan .stat-number { color: #ec4899; }

        .sub-section { margin-top: 35px; border-top: 1px dashed rgba(0, 143, 136, 0.2); padding-top: 25px; }
        .sub-section h4 { margin: 0 0 15px 0; font-size: 1rem; color: var(--primary-dark); font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .sub-section h4 > span:first-child { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 10px; background: var(--primary-soft); font-size: 0.9rem; }

        /* GRID PIE CHARTS (3 KOLOM) */
        .pie-cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 30px; }
        .pie-card { 
            position: relative; overflow: hidden; background: #ffffff; border-radius: var(--radius-lg); 
            border: 1px solid var(--border-soft); box-shadow: var(--card-shadow); padding: 30px 25px 26px; 
            display: flex; flex-direction: column; transition: box-shadow 0.35s var(--ease); 
        }
        .pie-card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--primary), #4fd8d0 55%, var(--secondary)); }
        .pie-card:hover { box-shadow: var(--card-hover-shadow); }
        .pie-card h4 { font-size: 0.95rem; font-weight: 800; color: var(--primary-dark); margin: 0 0 25px 0; padding-bottom: 14px; border-bottom: 1px dashed rgba(0, 143, 136, 0.2); text-align: center; letter-spacing: -0.1px; }
        
        .pie-chart-wrapper { position: relative; width: 100%; height: 240px; display: flex; justify-content: center; align-items: center; margin-bottom: 25px; }
        
        .doughnut-center-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2rem; font-weight: 800; color: var(--primary-deep); pointer-events: none; }
        
        .custom-legend { list-style: none; padding: 0; margin: 0; border-top: 1px solid #edf3f3; padding-top: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: flex-end; }
        .custom-legend li { display: flex; align-items: center; font-size: 0.84rem; color: #2c4a49; margin-bottom: 10px; font-weight: 500; }
        .custom-legend li:last-child { margin-bottom: 0; }
        .custom-legend li span.box { width: 14px; height: 14px; border-radius: 4px; margin-right: 12px; display: inline-block; flex-shrink: 0; box-shadow: 0 2px 4px rgba(4, 48, 47, 0.12); }

        /* CHART CONTAINER STYLING (BAR & LINE) */
        .chart-container { position: relative; width: 100%; height: 340px; margin-top: 15px; display: flex; justify-content: center; }

        /* TABEL DATA PENOLAKAN & KEBERATAN (DataTables bertema admin) */
        .card .dataTables_wrapper { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 4px; }
        .card .dataTables_wrapper .dataTables_filter { margin-bottom: 6px; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; }
        .card .dataTables_wrapper .dataTables_filter input { border: 1.5px solid #d6e4e3; border-radius: 999px; padding: 9px 16px; margin-left: 10px; outline: none; background-color: #f6fbfb; font-family: inherit; color: var(--text-dark); transition: all 0.3s var(--ease); }
        .card .dataTables_wrapper .dataTables_filter input:focus { border-color: var(--primary); background-color: #ffffff; box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.14); }

        .card table.dataTable-custom { width: 100% !important; min-width: 620px; border-collapse: separate; border-spacing: 0; margin: 12px 0 !important; font-size: 0.9rem; }
        .card table.dataTable.no-footer { border-bottom: none; }
        .card table.dataTable-custom thead th { 
            background-color: var(--primary-soft); color: var(--primary-dark); font-weight: 700; 
            text-transform: uppercase; font-size: 0.74rem; letter-spacing: 0.7px; 
            padding: 16px 28px 16px 15px; border-bottom: none; text-align: left; 
        }
        .card table.dataTable-custom thead th:first-child { border-radius: 12px 0 0 12px; }
        .card table.dataTable-custom thead th:last-child { border-radius: 0 12px 12px 0; }
        .card table.dataTable-custom tbody td { padding: 16px 15px; color: #2c4a49; border-bottom: 1px solid #edf3f3; vertical-align: top; line-height: 1.6; }
        .card table.dataTable-custom tbody tr:last-child td { border-bottom: none; }
        .card table.dataTable-custom tbody tr { background-color: transparent; transition: background-color 0.2s ease; }
        .card table.dataTable-custom tbody tr:hover td { background-color: #f4fbfa; }
        .card table.dataTable-custom tbody tr:hover td:first-child { box-shadow: inset 3px 0 0 var(--primary); }
        .card table.dataTable-custom tbody td.dataTables_empty { text-align: center; padding: 40px 20px; color: #9ca3af; font-style: italic; }

        .card .dataTables_wrapper .dataTables_info { margin-top: 12px; font-size: 0.85rem; color: var(--text-muted) !important; }
        .card .dataTables_wrapper .dataTables_paginate { margin-top: 10px; font-size: 0.85rem; }
        .card .dataTables_wrapper .dataTables_paginate .paginate_button { border-radius: 999px !important; border: none !important; font-weight: 700; color: var(--primary-dark) !important; padding: 6px 14px; }
        .card .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: var(--primary-soft) !important; color: var(--primary-dark) !important; border: none !important; }
        .card .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .card .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%) !important; color: #ffffff !important; border: none !important; }
        .card .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .card .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover { color: #9db3b2 !important; background: transparent !important; }

        /* AKSESIBILITAS */
        .menu-toggle:focus-visible, .btn-logout:focus-visible, .btn-submit:focus-visible,
        .sub-menu li a:focus-visible, .menu-category > a:focus-visible { outline: 3px solid rgba(239, 138, 28, 0.6); outline-offset: 2px; }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }

        /* RESPONSIVE LAYOUT */
        @media (max-width: 1024px) {
            .pie-cards-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            .menu-toggle { display: block; }
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(3, 31, 32, 0.55); z-index: 999; backdrop-filter: blur(3px); }
            .sidebar-overlay.active { display: block; }
        }
        @media (max-width: 768px) {
            .topbar { padding: 0 15px; height: 66px; }
            .topbar-title { font-size: 1.05rem; }
            .topbar-user span { display: none; }
            .content-body { padding: 20px 15px 40px; }
            .card { padding: 24px 18px; border-radius: 20px; margin-bottom: 20px; }
            .pie-card { padding: 26px 18px 22px; border-radius: 20px; }
            .card h3 { font-size: 1.05rem; margin-bottom: 18px; padding-bottom: 14px; }
            .card-has-desc h3 { margin-bottom: 8px; }
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .pie-cards-grid { grid-template-columns: 1fr; }
            .chart-container { height: 260px; }
            .stat-number { font-size: 2.1rem; }
            .card .dataTables_wrapper .dataTables_filter { text-align: left; }
            .card .dataTables_wrapper .dataTables_filter input { margin-left: 0; width: 100%; margin-top: 6px; }
        }
    </style>
</head>
<body>

    <!-- Overlay untuk versi mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR LENGKAP -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>PANEL ADMIN</h3>
            <p>PPID BKK Banjarmasin</p>
        </div>
        
        <ul class="sidebar-menu">
            <li class="menu-category"><a href="dashboard.php"><span class="menu-title"><span>🏠</span> Dashboard Admin</span></a></li>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-operasional', this)">
                    <span class="menu-title"><span>⚡</span> Atur Beranda & Layanan</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-operasional">
                    <li><a href="permohonan.php">Data Permohonan Publik</a></li>
                    <li><a href="keberatan.php">Data Keberatan Publik</a></li>
                    <li><a href="berita.php">Push Berita & Informasi</a></li>
                </ul>
            </li>

            <div class="menu-label">Kelola Konten Website</div>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-profil', this)"><span class="menu-title"><span>🏢</span> Profil</span><span class="arrow">▼</span></a>
                <ul class="sub-menu" id="menu-profil">
                    <li><a href="profil_ppid.php">Profil PPID</a></li>
                    <li><a href="visi_misi.php">Visi dan Misi</a></li>
                    <li><a href="struktur_tugas.php">Struktur & Tugas Fungsi</a></li>
                    <li><a href="dasar_hukum.php">Dasar Hukum PPID</a></li>
                </ul>
            </li>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-info', this)"><span class="menu-title"><span>📢</span> Informasi Publik</span><span class="arrow">▼</span></a>
                <ul class="sub-menu" id="menu-info">
                    <li><a href="informasi_publik.php">Informasi Publik</a></li>
                    <li><a href="informasi_serta_merta.php">Informasi Serta Merta</a></li>
                    <li><a href="informasi_setiap_saat.php">Informasi Setiap Saat</a></li>
                </ul>
            </li>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-standar', this)">
                    <span class="menu-title"><span>⚖️</span> Standar Layanan</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-standar">
                    <li><a href="prosuder_layanan.php">Prosedur & Kanal Layanan</a></li>
                    <li><a href="waktu_biaya.php">Waktu dan Biaya Layanan</a></li>
                    <li><a href="maklumat_layanan.php">Maklumat Layanan</a></li>
                </ul>
            </li>

            <!-- MENU LAPORAN (AKTIF & TERBUKA) -->
            <li class="menu-category open">
                <a onclick="toggleMenu('menu-laporan', this)" class="active-parent">
                    <span class="menu-title"><span>📊</span> Laporan</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-laporan" style="display: block;">
                    <li><a href="laporan_ppid.php">Laporan PPID</a></li>
                    <li><a href="statistik_layanan.php" class="active">Statistik Layanan</a></li>
                    <li><a href="hasil_survei.php">Survei Layanan PPID</a></li>
                </ul>
            </li>

            <!-- MENU KONTAK -->
            <li class="menu-category">
                <a onclick="toggleMenu('menu-kontak', this)">
                    <span class="menu-title"><span>📞</span> Kontak</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-kontak">
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="pesan.php">Kontak / Pesan Masuk</a></li>
                </ul>
            </li>
        </ul>
    </aside>

    <!-- KONTEN KANAN -->
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">Kelola & Monitor Statistik Layanan</div>
            </div>
            <div class="topbar-user">
                <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </header>

        <div class="content-body">
            
            <?php if($pesan_sukses): ?>
                <div class="alert-success">
                    <span>✅</span> Pengaturan tahun dan teks berhasil diperbarui!
                </div>
            <?php endif; ?>

            <div class="info-box">
                <span style="font-size: 1.5rem; line-height: 1;">💡</span>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">Sinkronisasi Real-Time</strong> 
                    Data statistik, diagram, tren permohonan, dan tabel pada halaman ini diambil dengan query yang sama dan <strong>100% sinkron secara real-time</strong> dengan tampilan pada situs publik (<em>user</em>).
                </div>
            </div>

            <!-- KARTU 1: RINGKASAN STATISTIK (5 KARTU ANGKA & BAR CHART) -->
            <div class="card card-secondary card-has-desc">
                <h3><span>📊</span> Ringkasan Statistik Tahun <?php echo htmlspecialchars($tahun_aktif); ?></h3>
                <p class="card-desc"><?php echo htmlspecialchars($teks_deskripsi); ?></p>
                
                <div class="stat-grid">
                    <div class="stat-box box-total">
                        <label>Jumlah Permohonan</label>
                        <div class="stat-number"><?php echo $stat_total; ?></div>
                    </div>
                    <div class="stat-box box-full">
                        <label>Permohonan Selesai</label>
                        <div class="stat-number"><?php echo $stat_full; ?></div>
                    </div>
                    <div class="stat-box box-reject">
                        <label>Permohonan Di Tolak</label>
                        <div class="stat-number"><?php echo $stat_reject; ?></div>
                    </div>
                    <div class="stat-box box-download">
                        <label>Jumlah Unduh</label>
                        <div class="stat-number"><?php echo $stat_download; ?></div>
                    </div>
                    <div class="stat-box box-keberatan">
                        <label>Jumlah Keberatan</label>
                        <div class="stat-number"><?php echo $stat_keberatan; ?></div>
                    </div>
                </div>

                <div class="sub-section">
                    <h4><span>📈</span> Grafik Visualisasi Pelayanan Informasi & Unduhan Dokumen</h4>
                    <div class="chart-container">
                        <canvas id="statistikChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- KARTU 2: 3 DIAGRAM PIE / DOUGHNUT (SINKRON DENGAN USER) -->
            <div class="pie-cards-grid">
                
                <div class="pie-card">
                    <h4>Persentase permohonan selesai</h4>
                    <div class="pie-chart-wrapper">
                        <canvas id="chartSelesai"></canvas>
                        <div class="doughnut-center-text"><?php echo $persentase_selesai; ?>%</div>
                    </div>
                    <ul class="custom-legend">
                        <li><span class="box" style="background:#00b2a9;"></span> Selesai (<?php echo $stat_full; ?>)</li>
                        <li><span class="box" style="background:#cfdcdb;"></span> Dalam proses (<?php echo $stat_proses; ?>)</li>
                    </ul>
                </div>

                <div class="pie-card">
                    <h4>Informasi publik berdasarkan kategori</h4>
                    <div class="pie-chart-wrapper">
                        <canvas id="chartKategori"></canvas>
                    </div>
                    <ul class="custom-legend">
                        <li><span class="box" style="background:#008f88;"></span> Tersedia setiap saat (<?php echo $info_setiapsaat; ?>)</li>
                        <li><span class="box" style="background:#00b2a9;"></span> Berkala (<?php echo $info_berkala; ?>)</li>
                        <li><span class="box" style="background:#ef8a1c;"></span> Serta merta (<?php echo $info_sertamerta; ?>)</li>
                        <li><span class="box" style="background:#d12c2c;"></span> Dikecualikan (0)</li>
                    </ul>
                </div>

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
                        <li><span class="box" style="background:#cfdcdb;"></span> Pengenaan biaya tidak wajar (<?php echo $data_keberatan[5]; ?>)</li>
                        <li><span class="box" style="background:#9db3b2;"></span> Melebihi jangka waktu (<?php echo $data_keberatan[6]; ?>)</li>
                    </ul>
                </div>

            </div>

            <!-- KARTU 3: GRAFIK TREN 7 BULAN TERAKHIR -->
            <div class="card card-success card-has-desc">
                <h3><span>📈</span> Permohonan selesai — tren 7 bulan terakhir</h3>
                <p class="card-desc">Grafik garis menunjukkan volume permohonan yang telah diselesaikan per bulan untuk memantau dinamika layanan.</p>
                
                <div class="chart-container">
                    <canvas id="chartTrend"></canvas>
                </div>
            </div>

            <!-- KARTU 4: TABEL ALASAN PENOLAKAN PERMOHONAN (SAMA DENGAN USER) -->
            <div class="card card-secondary card-has-desc">
                <h3><span>🛡️</span> Alasan Penolakan Permohonan Informasi Publik</h3>
                <p class="card-desc">Rincian permohonan yang ditolak beserta alasan dan tanggal penolakannya.</p>
                
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
                            <td style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['tanggapan'] ?: 'Ditolak/Dikecualikan sebagaimana diatur dalam UU.')); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($row['tanggal_masuk'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- KARTU 5: TABEL ALASAN KEBERATAN (SAMA DENGAN USER) -->
            <div class="card card-has-desc">
                <h3><span>⚖️</span> Alasan Keberatan Informasi Publik</h3>
                <p class="card-desc">Daftar keberatan yang diajukan beserta alasannya.</p>
                
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
                            <td style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['alasan_keberatan'])); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($row['tanggal_masuk'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- KARTU 6: PENGATURAN TEKS (FUNGSI TETAP ADA) -->
            <form action="" method="POST">
                <div class="card">
                    <h3><span>📝</span> Edit Deskripsi Pengantar Halaman User</h3>
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div class="form-group" style="width: 220px; flex-shrink: 0;">
                            <label>Tahun Rekapitulasi</label>
                            <input type="text" class="form-control form-control-readonly" value="<?php echo htmlspecialchars($tahun_aktif); ?>" readonly title="Tahun otomatis diatur oleh sistem">
                            <small class="form-hint">*Otomatis mengikuti tahun sistem saat pengaturan disimpan.</small>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 250px;">
                            <label>Teks Deskripsi Statistik</label>
                            <textarea name="stat_teks" class="form-control" rows="3" required><?php echo htmlspecialchars($teks_deskripsi); ?></textarea>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <button type="submit" class="btn-submit" style="width: auto;">💾 Simpan Pengaturan Teks</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <script>
        // Inisialisasi DataTables (konfigurasi sama dengan sisi user)
        $(document).ready(function() {
            const dtConfig = {
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
            };
            $('#tabelPenolakan').DataTable(dtConfig);
            $('#tabelKeberatan').DataTable(dtConfig);
        });

        // Registrasi Plugin ChartDataLabels
        Chart.register(ChartDataLabels);
        Chart.defaults.font.family = "'Plus Jakarta Sans', 'Segoe UI', sans-serif";
        Chart.defaults.color = "#5f7a79";

        // Tooltip seragam bertema deep teal
        const tooltipTema = {
            backgroundColor: 'rgba(4, 48, 47, 0.92)',
            titleFont: { size: 13, weight: '700' },
            bodyFont: { size: 13, weight: '600' },
            padding: 12,
            cornerRadius: 10
        };

        // Konfigurasi Format Angka Persentase di dalam Pie
        const datalabelsPie = {
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

        // Tooltip pie dengan persentase
        const tooltipPie = Object.assign({}, tooltipTema, {
            callbacks: {
                label: function(context) {
                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                    let percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) + '%' : '0%';
                    return ' ' + context.label + ': ' + context.raw + ' (' + percentage + ')';
                }
            }
        });

        // 1. Chart Bar Utama (label & data sama dengan sisi user)
        new Chart(document.getElementById('statistikChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Jumlah Permohonan', 'Permohonan Selesai', 'Permohonan Ditolak', 'Jumlah Unduh', 'Jumlah Keberatan'],
                datasets: [{
                    label: 'Jumlah Data',
                    data: [
                        <?php echo $stat_total; ?>, 
                        <?php echo $stat_full; ?>, 
                        <?php echo $stat_reject; ?>,
                        <?php echo $stat_download; ?>,
                        <?php echo $stat_keberatan; ?>
                    ],
                    backgroundColor: ['rgba(0, 178, 169, 0.2)', 'rgba(16, 185, 129, 0.2)', 'rgba(239, 68, 68, 0.2)', 'rgba(99, 102, 241, 0.2)', 'rgba(236, 72, 153, 0.2)'],
                    borderColor: ['#00b2a9', '#10b981', '#ef4444', '#6366f1', '#ec4899'],
                    borderWidth: 2,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 26 } },
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        display: true,
                        anchor: 'end',
                        align: 'top',
                        color: '#2c4a49',
                        font: { weight: '800', size: 13 },
                        formatter: (value) => value
                    },
                    tooltip: Object.assign({}, tooltipTema, { displayColors: false })
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: '#edf3f3' }, ticks: { precision: 0 } }
                }
            }
        });

        // 2. Chart Pie Selesai
        new Chart(document.getElementById('chartSelesai').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Dalam Proses'],
                datasets: [{
                    data: [<?php echo $stat_full; ?>, <?php echo $stat_proses; ?>],
                    backgroundColor: ['#00b2a9', '#cfdcdb'],
                    borderWidth: 3, borderColor: '#ffffff', hoverOffset: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: {
                    legend: { display: false },
                    datalabels: { display: false },
                    tooltip: Object.assign({}, tooltipTema, { callbacks: { label: function(context) { return ' ' + context.label + ': ' + context.raw; } } })
                }
            }
        });

        // 3. Chart Pie Kategori
        new Chart(document.getElementById('chartKategori').getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['Tersedia setiap saat', 'Berkala', 'Serta merta', 'Dikecualikan'],
                datasets: [{
                    data: [<?php echo $info_setiapsaat; ?>, <?php echo $info_berkala; ?>, <?php echo $info_sertamerta; ?>, 0],
                    backgroundColor: ['#008f88', '#00b2a9', '#ef8a1c', '#d12c2c'],
                    borderWidth: 2, borderColor: '#ffffff', hoverOffset: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: datalabelsPie,
                    tooltip: tooltipPie
                }
            }
        });

        // 4. Chart Pie Keberatan
        new Chart(document.getElementById('chartKeberatan').getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['Pengecualian', 'Tidak disediakan berkala', 'Tidak ditanggapi', 'Tidak ditanggapi sesuai diminta', 'Tidak dipenuhi', 'Biaya tak wajar', 'Melebihi waktu'],
                datasets: [{
                    data: [
                        <?php echo $data_keberatan[0]; ?>, <?php echo $data_keberatan[1]; ?>, <?php echo $data_keberatan[2]; ?>, 
                        <?php echo $data_keberatan[3]; ?>, <?php echo $data_keberatan[4]; ?>, <?php echo $data_keberatan[5]; ?>, <?php echo $data_keberatan[6]; ?>
                    ],
                    backgroundColor: ['#008f88', '#00b2a9', '#5eead4', '#ef8a1c', '#fcd34d', '#cfdcdb', '#9db3b2'],
                    borderWidth: 2, borderColor: '#ffffff', hoverOffset: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: datalabelsPie,
                    tooltip: tooltipPie
                }
            }
        });

        // 5. Chart Trend 7 Bulan (Line)
        new Chart(document.getElementById('chartTrend').getContext('2d'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_labels); ?>,
                datasets: [{
                    label: 'Jumlah Permohonan Selesai',
                    data: <?php echo json_encode($trend_data); ?>,
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
                    legend: { display: true, position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { size: 12 } } },
                    datalabels: { display: false },
                    tooltip: Object.assign({}, tooltipTema, { displayColors: false })
                },
                scales: {
                    x: { grid: { color: '#edf3f3', drawBorder: false }, ticks: { font: { size: 11 } } },
                    y: { grid: { color: '#edf3f3', drawBorder: false }, beginAtZero: true, ticks: { font: { size: 11 }, precision: 0 } }
                }
            }
        });

        // Interaksi Sidebar Accordion
        function toggleMenu(menuId, element) {
            const submenu = document.getElementById(menuId);
            const parentLi = element.parentElement;
            
            if (submenu.style.display === "block") {
                submenu.style.display = "none";
                parentLi.classList.remove('open');
                element.classList.remove('active-parent');
            } else {
                document.querySelectorAll('.sub-menu').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.menu-category').forEach(el => el.classList.remove('open'));
                document.querySelectorAll('.menu-category > a').forEach(el => el.classList.remove('active-parent'));
                
                submenu.style.display = "block";
                parentLi.classList.add('open');
                element.classList.add('active-parent');
            }
        }

        // Toggle mobile sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }
    </script>

</body>
</html>