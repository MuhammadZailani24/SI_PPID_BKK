<?php 
session_start();

// Pengecekan sesi login[cite: 19]
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Panggil file koneksi database[cite: 19]
require_once '../includes/koneksi.php';

$pesan_sukses = false;

// Proses Simpan / Update Pengaturan Form & Narasi Survei[cite: 19]
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_update = [
        'teks_narasi_survei'  => $_POST['teks_narasi_survei'],
        'survei_opsi_layanan' => $_POST['survei_opsi_layanan'],
        'survei_q1'           => $_POST['survei_q1'],
        'survei_q2'           => $_POST['survei_q2'],
        'survei_q3'           => $_POST['survei_q3']
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
    $pesan_sukses = true;
}

// Ambil data pengaturan form survei saat ini[cite: 19]
$settings = [];
$query = $conn->query("SELECT * FROM pengaturan_web");
while($row = $query->fetch_assoc()) {
    $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
}

// Default teks jika di database belum ada[cite: 19]
$default_narasi = "Partisipasi Anda sangat berarti bagi evaluasi pelayanan kami. Jika Anda pernah menggunakan layanan informasi publik PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin, silakan luangkan waktu sejenak untuk mengisi kuesioner pada tautan di bawah ini.";
$default_opsi = "Permohonan Informasi Publik\nPengajuan Keberatan Informasi\nKonsultasi Layanan PPID";
$default_q1 = "Bagaimana pendapat Anda tentang kesesuaian persyaratan pelayanan dengan jenis pelayanannya?";
$default_q2 = "Bagaimana pendapat Anda tentang kecepatan waktu dalam memberikan pelayanan informasi?";
$default_q3 = "Bagaimana pendapat Anda tentang sikap, kesopanan, dan keramahan petugas pelayanan?";

// =========================================================================
// MENGAMBIL DATA STATISTIK IKM REAL-TIME DARI TABEL survei_ikm[cite: 19]
// =========================================================================
$total_responden = 0;
$nilai_ikm = 0.00;
$mutu_pelayanan = "Belum Ada Data";

$cek_tabel = $conn->query("SHOW TABLES LIKE 'survei_ikm'");
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    $q_survei = $conn->query("SELECT COUNT(id) as total, AVG(skor_akhir) as rata_rata FROM survei_ikm");
    if ($q_survei && $row_survei = $q_survei->fetch_assoc()) {
        $total_responden = (int)$row_survei['total'];
        
        if ($total_responden > 0) {
            $nilai_ikm = number_format((float)$row_survei['rata_rata'], 2, '.', '');
            
            if ($nilai_ikm >= 88.31) {
                $mutu_pelayanan = "SANGAT BAIK";
            } elseif ($nilai_ikm >= 76.61) {
                $mutu_pelayanan = "BAIK";
            } elseif ($nilai_ikm >= 65.00) {
                $mutu_pelayanan = "KURANG BAIK";
            } else {
                $mutu_pelayanan = "BURUK";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Survei Layanan - Admin CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        
        /* SIDEBAR MODERN[cite: 19] */
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

        /* KONTEN UTAMA[cite: 19] */
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

        .content-body { padding: 34px 40px 50px; max-width: 1100px; margin: 0 auto; width: 100%; box-sizing: border-box; }
        
        .alert-success { background-color: #dcf7ec; color: #0b8a5a; padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 25px; border: 1px solid #b5ecd4; border-left: 5px solid #10b981; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08); }
        
        /* IKM REALTIME BOX[cite: 19] */
        .ikm-box { 
            position: relative; overflow: hidden;
            background: 
                radial-gradient(500px 260px at 100% 0%, rgba(239, 138, 28, 0.28), transparent 65%),
                radial-gradient(420px 240px at 0% 100%, rgba(255, 255, 255, 0.10), transparent 65%),
                linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 60%, #06706b 100%); 
            color: white; border-radius: var(--radius-lg); padding: 45px 30px; text-align: center; 
            box-shadow: 0 18px 40px rgba(0, 143, 136, 0.28); margin-bottom: 10px; 
        }
        .ikm-box::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--secondary), #f6a94d 50%, rgba(255,255,255,0.6)); }
        .ikm-box h4 { margin: 0 0 15px 0; font-size: 1.05rem; letter-spacing: 1.5px; text-transform: uppercase; color: #c9f3ef; font-weight: 800; }
        .ikm-score { font-size: 5rem; font-weight: 800; margin: 10px 0; color: #ffe2b8; text-shadow: 0 4px 14px rgba(4, 48, 47, 0.25); line-height: 1; letter-spacing: -2px; }
        .ikm-mutu { font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; letter-spacing: 1px; }
        .ikm-stars { color: #ffd089; font-size: 1.8rem; margin-bottom: 20px; letter-spacing: 5px; }
        .ikm-footer { background-color: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.22); display: inline-block; padding: 9px 26px; border-radius: 999px; font-size: 0.92rem; font-weight: 700; -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px); }

        /* CARDS[cite: 19] */
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

        /* Pembungkus transparan untuk kotak IKM: tanpa garis atas & tanpa pemotongan bayangan */
        .card[style*="transparent"] { overflow: visible; }
        .card[style*="transparent"]::before { display: none; }
        
        .card h3 { margin-top: 0; color: var(--primary-dark); font-size: 1.15rem; border-bottom: 1px dashed rgba(0, 143, 136, 0.2); padding-bottom: 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-weight: 800; letter-spacing: -0.2px; }
        .card h3 > span:first-child { 
            display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; 
            border-radius: 12px; background: var(--primary-soft); font-size: 1.05rem; flex-shrink: 0;
        }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; font-size: 0.78rem; margin-bottom: 8px; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.6px; line-height: 1.5; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid #d6e4e3; border-radius: 14px; font-family: inherit; font-size: 0.95rem; box-sizing: border-box; transition: all 0.3s var(--ease); background-color: #f6fbfb; color: var(--text-dark); line-height: 1.5; resize: vertical; }
        .form-control:focus { outline: none; border-color: var(--primary); background-color: white; box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.14); }
        
        .btn-submit { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; padding: 16px 30px; border-radius: 999px; font-weight: 800; font-size: 0.92rem; cursor: pointer; width: 100%; transition: all 0.3s var(--ease); display: flex; align-items: center; justify-content: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.8px; box-shadow: 0 10px 26px rgba(0, 178, 169, 0.3); font-family: inherit; }
        .btn-submit:hover { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); box-shadow: 0 12px 30px rgba(239, 138, 28, 0.4); transform: translateY(-2px); }

        /* MULTIPLE CARDS THEME COLORS[cite: 19] */
        .card-secondary::before { background: linear-gradient(90deg, var(--secondary), #f6a94d 45%, var(--primary)); }
        .card-secondary h3 { color: var(--secondary-dark); }
        .card-secondary h3 > span:first-child { background: var(--secondary-soft); }
        .card-secondary .form-group label { color: var(--secondary-dark); }
        .card-secondary .form-control:focus { border-color: var(--secondary); box-shadow: 0 0 0 4px rgba(239, 138, 28, 0.14); }

        .card-green::before { background: linear-gradient(90deg, var(--success), #5eead4 55%, var(--primary)); }
        .card-green h3 { color: var(--success-dark); }
        .card-green h3 > span:first-child { background: var(--success-soft); }
        .card-green .form-group label { color: var(--success-dark); }
        .card-green .form-control:focus { border-color: var(--success); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.14); }

        /* AKSESIBILITAS */
        .menu-toggle:focus-visible, .btn-logout:focus-visible, .btn-submit:focus-visible,
        .sub-menu li a:focus-visible, .menu-category > a:focus-visible { outline: 3px solid rgba(239, 138, 28, 0.6); outline-offset: 2px; }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }

        /* RESPONSIVE LAYOUT[cite: 19] */
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
            .card h3 { font-size: 1.05rem; margin-bottom: 18px; padding-bottom: 14px; }
            .ikm-score { font-size: 3.8rem; }
            .ikm-box { padding: 35px 20px; border-radius: 20px; }
            .ikm-stars { font-size: 1.5rem; letter-spacing: 3px; }
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

            <!-- MENU LAPORAN (AKTIF & TERBUKA)[cite: 19] -->
            <li class="menu-category open">
                <a onclick="toggleMenu('menu-laporan', this)" class="active-parent">
                    <span class="menu-title"><span>📊</span> Laporan</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-laporan" style="display: block;">
                    <li><a href="laporan_ppid.php">Laporan PPID</a></li>
                    <li><a href="statistik_layanan.php">Statistik Layanan</a></li>
                    <li><a href="hasil_survei.php" class="active">Survei Layanan PPID</a></li>
                </ul>
            </li>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-kontak', this)"><span class="menu-title"><span>📞</span> Kontak</span><span class="arrow">▼</span></a>
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
                <div class="topbar-title">Kelola Formulir & Monitor IKM</div>
            </div>
            <div class="topbar-user">
                <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </header>

        <div class="content-body">
            
            <?php if($pesan_sukses): ?>
                <div class="alert-success">
                    <span>✅</span> Data Narasi dan Pertanyaan Survei berhasil diperbarui!
                </div>
            <?php endif; ?>

            <!-- KARTU 1: MONITOR REAL-TIME IKM[cite: 19] -->
            <div class="card" style="padding: 0; border: none; background: transparent; box-shadow: none; margin-bottom: 25px;">
                <div class="ikm-box">
                    <h4>Indeks Kepuasan Masyarakat Saat Ini</h4>
                    <div class="ikm-score"><?php echo $nilai_ikm; ?></div>
                    <div class="ikm-mutu">MUTU PELAYANAN: <?php echo htmlspecialchars($mutu_pelayanan); ?></div>
                    <div class="ikm-stars">
                        <?php 
                        $stars = 0;
                        if($mutu_pelayanan == "SANGAT BAIK") $stars = 5;
                        elseif($mutu_pelayanan == "BAIK") $stars = 4;
                        elseif($mutu_pelayanan == "KURANG BAIK") $stars = 3;
                        elseif($mutu_pelayanan == "BURUK") $stars = 2;
                        
                        for($i=0; $i<$stars; $i++) echo "★ ";
                        ?>
                    </div>
                    <div class="ikm-footer">Berdasarkan penilaian dari <?php echo $total_responden; ?> responden</div>
                </div>
                <small style="display:block; text-align:center; color:#6b7280; margin-top:15px; font-size: 0.85rem;">*Angka di atas dihitung secara otomatis (real-time) dari data responden yang masuk ke tabel survei_ikm[cite: 19].</small>
            </div>

            <!-- FORM PENGATURAN KONTEN SURVEI[cite: 19] -->
            <form action="" method="POST">
                
                <!-- KARTU 2: TEKS NARASI & OPSI LAYANAN -->
                <div class="card card-secondary">
                    <h3><span>📝</span> Teks Narasi & Opsi Layanan</h3>
                    <div class="form-group">
                        <label>Teks Pengantar / Ajakan (Muncul di atas tombol "Isi Survei")</label>
                        <textarea name="teks_narasi_survei" class="form-control" rows="3" required><?php echo htmlspecialchars($settings['teks_narasi_survei'] ?? $default_narasi); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Daftar Pilihan Dropdown Jenis Layanan (Pisahkan dengan Enter)</label>
                        <textarea name="survei_opsi_layanan" class="form-control" rows="3"><?php echo htmlspecialchars($settings['survei_opsi_layanan'] ?? $default_opsi); ?></textarea>
                    </div>
                </div>

                <!-- KARTU 3: PERTANYAAN SURVEI IKM -->
                <div class="card card-green">
                    <h3><span>⭐</span> Kategori Penilaian (Kuesioner)</h3>
                    <p style="font-size: 0.95rem; color: #6b7280; margin-top: -5px; margin-bottom: 25px; line-height: 1.5;">Anda dapat merombak teks redaksi pertanyaan kuesioner. Pertanyaan ini akan dijawab pengguna menggunakan skala 1 (Sangat Buruk) hingga 4 (Sangat Baik)[cite: 19].</p>
                    
                    <div class="form-group">
                        <label>Pertanyaan Kuesioner 1 (Q1)</label>
                        <input type="text" name="survei_q1" class="form-control" value="<?php echo htmlspecialchars($settings['survei_q1'] ?? $default_q1); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Pertanyaan Kuesioner 2 (Q2)</label>
                        <input type="text" name="survei_q2" class="form-control" value="<?php echo htmlspecialchars($settings['survei_q2'] ?? $default_q2); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Pertanyaan Kuesioner 3 (Q3)</label>
                        <input type="text" name="survei_q3" class="form-control" value="<?php echo htmlspecialchars($settings['survei_q3'] ?? $default_q3); ?>" required>
                    </div>
                </div>

                <div style="margin-bottom: 50px;">
                    <button type="submit" class="btn-submit">💾 Simpan Perubahan Form Survei</button>
                </div>
            </form>

        </div>
    </div>

    <script>
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