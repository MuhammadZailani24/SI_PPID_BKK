<?php 
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Panggil file koneksi database
require_once '../includes/koneksi.php';

$pesan_sukses = false;

// =========================================================================
// PROSES SIMPAN / UPDATE DATA KE DATABASE
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_update = [
        // Data Struktur Organisasi
        'struktur_kepala_upt'    => $_POST['kepala_upt'],
        'struktur_menteri'       => $_POST['menteri'],
        'struktur_kepala_bkk'    => $_POST['kepala_bkk'],
        'struktur_kasubag'       => $_POST['kasubag'],
        'struktur_pengelola_sys' => $_POST['pengelola_sys'],
        'struktur_pengelola_lay' => $_POST['pengelola_lay'],
        'struktur_sengketa'      => $_POST['sengketa'],
        // Data Tugas & Fungsi
        'teks_tugas'             => $_POST['tugas'],
        'teks_fungsi'            => $_POST['fungsi']
    ];

    foreach ($data_update as $kunci => $nilai) {
        $nilai_esc = mysqli_real_escape_string($conn, $nilai);
        
        // Menggunakan sumber[cite: 15] untuk struktur tabel pengaturan_web
        $cek = $conn->query("SELECT id FROM pengaturan_web WHERE kunci_pengaturan = '$kunci'");
        if ($cek->num_rows > 0) {
            $conn->query("UPDATE pengaturan_web SET nilai_pengaturan = '$nilai_esc' WHERE kunci_pengaturan = '$kunci'");
        } else {
            $conn->query("INSERT INTO pengaturan_web (kunci_pengaturan, nilai_pengaturan) VALUES ('$kunci', '$nilai_esc')");
        }
    }
    $pesan_sukses = true;
}

// =========================================================================
// MENGAMBIL DATA TERBARU DARI DATABASE
// =========================================================================
$settings = [];
$query_settings = $conn->query("SELECT * FROM pengaturan_web");
while($row = $query_settings->fetch_assoc()) {
    $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
}

// Set variabel dengan data dari DB atau nilai default jika kosong[cite: 15]
$kepala_upt    = $settings['struktur_kepala_upt'] ?? 'Dr. H. Nama Kepala, M.Kes';
$menteri       = $settings['struktur_menteri'] ?? 'Budi Gunadi Sadikin';
$kepala_bkk    = $settings['struktur_kepala_bkk'] ?? 'Dr. H. Nama Kepala, M.Kes';
$kasubag       = $settings['struktur_kasubag'] ?? 'Nama Kasubag, SKM';
$pengelola_sys = $settings['struktur_pengelola_sys'] ?? 'Nama Pengelola Sistem, S.Kom';
$pengelola_lay = $settings['struktur_pengelola_lay'] ?? 'Nama Pengelola Layanan, S.I.Kom';
$sengketa      = $settings['struktur_sengketa'] ?? 'Nama Sengketa, S.H';

$tugas  = $settings['teks_tugas'] ?? "1. Melaksanakan pengelolaan informasi publik.";
$fungsi = $settings['teks_fungsi'] ?? "1. Pelaksanaan pengelolaan informasi publik\n2. Penyusunan dan pemutakhiran daftar informasi publik";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Struktur & Tugas - Admin CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* =====================================================================
           TEMA: Balai Kekarantinaan Kesehatan Kelas I Banjarmasin
           Palet: Teal Kemenkes (#00b2a9) + Oranye (#ef8a1c) + Deep Teal gelap
           (disamakan dengan dashboard.php dan halaman admin lainnya)
           ===================================================================== */
        :root { 
            --primary: #00b2a9; 
            --primary-dark: #008f88;
            --primary-deep: #04302f;
            --primary-soft: #e6f7f6;
            --secondary: #ef8a1c; 
            --secondary-dark: #d4730a;
            --secondary-soft: #fff4e5;
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

        /* KONTEN BODY */
        .content-body { padding: 36px 40px 50px; max-width: 1200px; margin: 0 auto; width: 100%; }
        
        .alert-success { background-color: #dcf7ec; color: #0b8a5a; padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 25px; border: 1px solid #b5ecd4; border-left: 5px solid #10b981; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08); }
        
        /* CARD & FORM */
        .card { 
            position: relative; overflow: hidden; background: #ffffff; border-radius: var(--radius-lg); padding: 34px; 
            box-shadow: var(--card-shadow); margin-bottom: 30px; border: 1px solid var(--border-soft); 
            transition: box-shadow 0.35s var(--ease); 
        }
        /* Garis atas lama (inline) diganti dengan garis gradasi */
        .card[style*="border-top"] { border-top: 1px solid var(--border-soft) !important; }
        .card::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; 
            background: linear-gradient(90deg, var(--primary), #4fd8d0 55%, var(--secondary));
        }
        .card[style*="--secondary"]::before { background: linear-gradient(90deg, var(--secondary), #f6a94d 45%, var(--primary)); }
        .card:hover { box-shadow: var(--card-hover-shadow); }
        .card-header { border-bottom: 1px dashed rgba(0, 143, 136, 0.2); padding-bottom: 18px; margin-bottom: 24px; }
        .card-header h2 { margin: 0; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; font-weight: 800; letter-spacing: -0.2px; }
        .card-header h2 > span:first-child { 
            display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; 
            border-radius: 12px; background: var(--primary-soft); font-size: 1.05rem;
        }
        .card[style*="--secondary"] .card-header h2 > span:first-child { background: var(--secondary-soft); }
        .card-header p { margin: 10px 0 0 0; color: var(--text-muted); font-size: 0.9rem; line-height: 1.55; }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; font-size: 0.78rem; margin-bottom: 8px; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.6px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid #d6e4e3; border-radius: 14px; font-family: inherit; font-size: 0.95rem; line-height: 1.5; box-sizing: border-box; transition: all 0.3s var(--ease); background-color: #f6fbfb; color: var(--text-dark); resize: vertical; }
        .form-control:focus { outline: none; border-color: var(--primary); background-color: white; box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.14); }
        
        .box-section { background: linear-gradient(135deg, #f4fbfa, #eef8f7); padding: 25px; border-radius: 20px; border: 1px solid var(--border-soft); }
        .box-title { font-weight: 800; color: var(--primary-dark); margin-bottom: 20px; text-transform: uppercase; font-size: 0.78rem; letter-spacing: 1px; border-bottom: 1px dashed rgba(0, 143, 136, 0.25); padding-bottom: 12px; }

        .btn-submit { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; padding: 17px 30px; border-radius: 999px; font-weight: 800; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s var(--ease); width: 100%; text-transform: uppercase; letter-spacing: 0.8px; box-shadow: 0 10px 26px rgba(0, 178, 169, 0.3); font-family: inherit; }
        .btn-submit:hover { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); transform: translateY(-2px); box-shadow: 0 12px 30px rgba(239, 138, 28, 0.4); }

        /* AKSESIBILITAS */
        .menu-toggle:focus-visible, .btn-logout:focus-visible, .btn-submit:focus-visible,
        .sub-menu li a:focus-visible, .menu-category > a:focus-visible { outline: 3px solid rgba(239, 138, 28, 0.6); outline-offset: 2px; }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }
        
        /* RESPONSIVE DESIGN UNTUK TABLET & HP */
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
            .card-header { margin-bottom: 20px; padding-bottom: 14px; }
            .card-header h2 { font-size: 1.1rem; }
            
            .form-row { grid-template-columns: 1fr; gap: 15px; } 
            .box-section { padding: 16px; border-radius: 16px; }
            .form-control { font-size: 0.9rem; padding: 12px 14px; }
            .btn-submit { font-size: 0.88rem; padding: 15px 20px; }
        }
    </style>
</head>
<body>

    <!-- Overlay untuk versi mobile[cite: 15] -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR LENGKAP[cite: 15] -->
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
                    <li><a href="berita.php">Push Berita Informasi & Foto</a></li>
                </ul>
            </li>

            <div class="menu-label">Kelola Konten Website</div>

            <!-- PROFIL (AKTIF DI HALAMAN INI)[cite: 15] -->
            <li class="menu-category open">
                <a onclick="toggleMenu('menu-profil', this)" class="active-parent">
                    <span class="menu-title"><span>🏢</span> Profil</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-profil" style="display: block;">
                    <li><a href="profil_ppid.php">Profil PPID</a></li>
                    <li><a href="visi_misi.php">Visi dan Misi</a></li>
                    <li><a href="struktur_tugas.php" class="active">Struktur & Tugas Fungsi</a></li>
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
                <a onclick="toggleMenu('menu-standar', this)"><span class="menu-title"><span>⚖️</span> Standar Layanan</span><span class="arrow">▼</span></a>
                <ul class="sub-menu" id="menu-standar">
                    <li><a href="prosuder_layanan.php">Prosedur & Kanal Layanan</a></li>
                    <li><a href="waktu_biaya.php">Waktu dan Biaya Layanan</a></li>
                    <li><a href="maklumat_layanan.php">Maklumat Layanan</a></li>
                </ul>
            </li>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-laporan', this)"><span class="menu-title"><span>📊</span> Laporan</span><span class="arrow">▼</span></a>
                <ul class="sub-menu" id="menu-laporan">
                    <li><a href="laporan_ppid.php">Laporan PPID</a></li>
                    <li><a href="statistik_layanan.php">Statistik Layanan</a></li>
                    <li><a href="hasil_survei.php">Survei Layanan PPID</a></li>
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

    <!-- KONTEN KANAN[cite: 15] -->
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">Struktur & Tugas Fungsi</div>
            </div>
            <div class="topbar-user">
                <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </header>

        <div class="content-body">
            
            <?php if($pesan_sukses): ?>
                <div class="alert-success">
                    <span>✅</span> Data Struktur, Tugas, dan Fungsi berhasil diperbarui di database!
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                
                <!-- BAGIAN 1: STRUKTUR ORGANISASI[cite: 15] -->
                <div class="card" style="border-top: 5px solid var(--primary);">
                    <div class="card-header">
                        <h2 style="color: var(--primary);"><span>🏛️</span> Struktur Organisasi (Nama Pejabat)</h2>
                        <p>Atur nama-nama pejabat yang akan tampil di dalam bagan hierarki halaman depan.</p>
                    </div>
                    
                    <div class="form-row">
                        <!-- Bagan 1[cite: 15] -->
                        <div class="box-section">
                            <div class="box-title">Bagan Unit Pelaksana Teknis</div>
                            <div class="form-group">
                                <label>Kepala UPT BKK</label>
                                <input type="text" name="kepala_upt" class="form-control" value="<?php echo htmlspecialchars($kepala_upt); ?>" required>
                            </div>
                        </div>
                        
                        <!-- Bagan 2[cite: 15] -->
                        <div class="box-section">
                            <div class="box-title">Bagan Hierarki PPID</div>
                            <div class="form-group">
                                <label>Menteri Kesehatan</label>
                                <input type="text" name="menteri" class="form-control" value="<?php echo htmlspecialchars($menteri); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Kepala BKK Kelas I Banjarmasin</label>
                                <input type="text" name="kepala_bkk" class="form-control" value="<?php echo htmlspecialchars($kepala_bkk); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Kepala Sub Bagian Administrasi Umum</label>
                                <input type="text" name="kasubag" class="form-control" value="<?php echo htmlspecialchars($kasubag); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Pengelola Sistem Informasi</label>
                                <input type="text" name="pengelola_sys" class="form-control" value="<?php echo htmlspecialchars($pengelola_sys); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Pengelola Layanan Informasi</label>
                                <input type="text" name="pengelola_lay" class="form-control" value="<?php echo htmlspecialchars($pengelola_lay); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Penyelesaian Sengketa Informasi</label>
                                <input type="text" name="sengketa" class="form-control" value="<?php echo htmlspecialchars($sengketa); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: TUGAS DAN FUNGSI[cite: 15] -->
                <div class="card" style="border-top: 5px solid var(--secondary);">
                    <div class="card-header">
                        <h2 style="color: var(--secondary);"><span>📋</span> Tugas dan Fungsi PPID</h2>
                        <p>Kelola rincian tugas dan poin-poin fungsi Pejabat Pengelola Informasi dan Dokumentasi.</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label style="color: var(--primary);">Rincian Tugas</label>
                            <textarea name="tugas" class="form-control" rows="10" required><?php echo htmlspecialchars($tugas); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label style="color: var(--secondary);">Rincian Fungsi</label>
                            <textarea name="fungsi" class="form-control" rows="10" required><?php echo htmlspecialchars($fungsi); ?></textarea>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 40px;">
                    <button type="submit" class="btn-submit">💾 Simpan Perubahan Struktur & Tugas</button>
                </div>

            </form>

        </div>
    </div>

    <script>
        // Interaksi Sidebar Accordion[cite: 15]
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

        // Toggle mobile sidebar[cite: 15]
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }
    </script>
</body>
</html>