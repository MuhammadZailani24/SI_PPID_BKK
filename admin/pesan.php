<?php 
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/koneksi.php';

// Pastikan tabel pesan_masuk ada
$conn->query("CREATE TABLE IF NOT EXISTS pesan_masuk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    judul VARCHAR(200) NOT NULL,
    pesan TEXT NOT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$pesan_sukses = false;

// 1. Proses Simpan / Update Pengaturan Kontak & Google Maps
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['simpan_kontak'])) {$data_update = [
        'kontak_instansi' => $_POST['kontak_instansi'] ?? 'PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin',
        'kontak_alamat'   => $_POST['kontak_alamat'] ?? '',
        'kontak_telepon'  => $_POST['kontak_telepon'] ?? '',
        'kontak_email'    => $_POST['kontak_email'] ?? '',
        'kontak_map_url'  => $_POST['kontak_map_url'] ?? ''
    ];

    foreach ($data_update as$kunci => $nilai) {$nilai_esc = mysqli_real_escape_string($conn,$nilai);
        $cek =$conn->query("SELECT id FROM pengaturan_web WHERE kunci_pengaturan = '$kunci'");
        if ($cek && $cek->num_rows > 0) {$conn->query("UPDATE pengaturan_web SET nilai_pengaturan = '$nilai_esc' WHERE kunci_pengaturan = '$kunci'");
        } else {
            $conn->query("INSERT INTO pengaturan_web (kunci_pengaturan, nilai_pengaturan) VALUES ('$kunci', '$nilai_esc')");
        }
    }
    $pesan_sukses = true;
}

// 2. Proses Hapus Pesan Masuk
$pesan_notif = '';
if (isset($_GET['action']) &&$_GET['action'] == 'hapus_pesan' && isset($_GET['id'])) {$id_hapus = (int)$_GET['id'];$conn->query("DELETE FROM pesan_masuk WHERE id = $id_hapus");
    $pesan_notif = "Pesan berhasil dihapus!";
}

// Ambil data pengaturan kontak dari database
$settings = [];
$query_set =$conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan LIKE 'kontak_%'");
if ($query_set) {
    while ($row = $query_set->fetch_assoc()) {$settings[$row['kunci_pengaturan']] =$row['nilai_pengaturan'];
    }
}

$kontak_instansi =$settings['kontak_instansi'] ?? "PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin";
$kontak_alamat   =$settings['kontak_alamat'] ?? "Jl. P.M. Noor No.1, Pelambuan, Kec. Banjarmasin Barat, Kota Banjarmasin, Kalimantan Selatan 70119";
$kontak_telepon  =$settings['kontak_telepon'] ?? "(0511) 1234567";
$kontak_email    =$settings['kontak_email'] ?? "ppid@bkkbanjarmasin.go.id";
$kontak_map_url  =$settings['kontak_map_url'] ?? "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.136894056262!2d114.57502431475713!3d-3.316829997585094!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de423d2427f7db5%3A0x8035fb14197c36a4!2sKantor%20Kesehatan%20Pelabuhan%20(KKP)%20Kelas%20II%20Banjarmasin!5e0!3m2!1sid!2sid!4v1675200000000!5m2!1sid!2sid";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Informasi Kontak & Pesan - Admin CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* =====================================================================
           TEMA: Balai Kekarantinaan Kesehatan Kelas I Banjarmasin
           Palet: Teal Kemenkes (#00b2a9) + Oranye (#ef8a1c) + Deep Teal gelap
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

        .content-body { padding: 34px 40px 50px; max-width: 1400px; margin: 0 auto; width: 100%; box-sizing: border-box; }
        
        /* ALERTS */
        .alert-success { background-color: #dcf7ec; color: #0b8a5a; padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 25px; border: 1px solid #b5ecd4; border-left: 5px solid #10b981; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08); }

        /* LAYOUT 2 KOLOM ADMIN */
        .admin-kontak-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }
        .admin-kontak-grid > * { min-width: 0; }

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

        .card[style*="border-top-color"] { border-top-color: var(--border-soft) !important; }
        .card[style*="border-top-color: var(--secondary)"]::before { background: linear-gradient(90deg, var(--secondary), #f6a94d 45%, var(--primary)); }
        .card[style*="border-top-color: var(--secondary)"] h3 { color: var(--secondary-dark) !important; }
        .card[style*="border-top-color: var(--secondary)"] h3 > span:first-child { background: var(--secondary-soft); }
        
        .card h3 { margin-top: 0; color: var(--primary-dark); font-size: 1.15rem; border-bottom: 1px dashed rgba(0, 143, 136, 0.2); padding-bottom: 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-weight: 800; letter-spacing: -0.2px; }
        .card h3 > span:first-child { 
            display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; 
            border-radius: 12px; background: var(--primary-soft); font-size: 1.05rem; flex-shrink: 0;
        }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; font-size: 0.78rem; margin-bottom: 8px; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.6px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid #d6e4e3; border-radius: 14px; font-family: inherit; font-size: 0.95rem; box-sizing: border-box; transition: all 0.3s var(--ease); background-color: #f6fbfb; color: var(--text-dark); line-height: 1.5; resize: vertical; }
        .form-control:focus { outline: none; border-color: var(--primary); background-color: white; box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.14); }
        
        .btn-submit { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; padding: 15px 28px; border-radius: 999px; font-weight: 800; font-size: 0.9rem; cursor: pointer; transition: all 0.3s var(--ease); display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; text-transform: uppercase; letter-spacing: 0.8px; box-shadow: 0 10px 26px rgba(0, 178, 169, 0.3); font-family: inherit; }
        .btn-submit:hover { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); box-shadow: 0 12px 30px rgba(239, 138, 28, 0.4); transform: translateY(-2px); }

        /* TABEL PESAN */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 12px; }
        table { width: 100%; min-width: 500px; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
        table th, table td { padding: 16px 15px; border-bottom: 1px solid #edf3f3; text-align: left; vertical-align: top; }
        table th { 
            background: var(--primary-soft); color: var(--primary-dark); font-weight: 700; 
            text-transform: uppercase; font-size: 0.74rem; letter-spacing: 0.7px; border-bottom: none; 
        }
        table th:first-child { border-radius: 12px 0 0 12px; }
        table th:last-child { border-radius: 0 12px 12px 0; }
        table td { color: #2c4a49; }
        table tbody tr:last-child td { border-bottom: none; }
        table tbody tr { transition: background-color 0.2s ease; }
        table tbody tr:hover td { background-color: #f4fbfa; }
        table tbody tr:hover td:first-child { box-shadow: inset 3px 0 0 var(--primary); }
        
        .btn-action { padding: 8px 16px; border-radius: 999px; font-size: 0.82rem; font-weight: 700; cursor: pointer; border: 1px solid #ffd0d0; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s var(--ease); }
        .btn-del { background-color: #ffe9e9; color: #d12c2c; }
        .btn-del:hover { background-color: #d12c2c; color: white; border-color: #d12c2c; transform: translateY(-1px); box-shadow: 0 8px 18px rgba(209, 44, 44, 0.28); }

        /* AKSESIBILITAS */
        .menu-toggle:focus-visible, .btn-logout:focus-visible, .btn-submit:focus-visible, .btn-action:focus-visible,
        .sub-menu li a:focus-visible, .menu-category > a:focus-visible { outline: 3px solid rgba(239, 138, 28, 0.6); outline-offset: 2px; }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }

        @media (max-width: 1024px) {
            .admin-kontak-grid { grid-template-columns: 1fr; }
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
            .card h3 { font-size: 1.05rem; margin-bottom: 18px; padding-bottom: 14px; }
        }
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
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

            <!-- MENU KONTAK (AKTIF) -->
            <li class="menu-category open">
                <a onclick="toggleMenu('menu-kontak', this)" class="active-parent">
                    <span class="menu-title"><span>📞</span> Kontak</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-kontak" style="display: block;">
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="pesan.php" class="active">Kelola Kontak & Pesan</a></li>
                </ul>
            </li>
        </ul>
    </aside>

    <!-- KONTEN KANAN -->
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">Kelola Informasi Kontak & Pesan Masuk</div>
            </div>
            <div class="topbar-user">
                <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </header>

        <div class="content-body">
            
            <?php if($pesan_sukses): ?>
                <div class="alert-success"><span>✅</span> Pengaturan Informasi Kontak dan Google Maps berhasil diperbarui!</div>
            <?php endif; ?>

            <?php if(!empty($pesan_notif)): ?>
                <div class="alert-success"><span>✅</span> <?php echo htmlspecialchars($pesan_notif); ?></div>
            <?php endif; ?>

            <!-- LAYOUT 2 KOLOM: KIRI (FORM KONTAK & MAPS), KANAN (KOTAK MASUK PESAN) -->
            <div class="admin-kontak-grid">
                
                <!-- KOLOM KIRI: EDIT KONTAK & MAPS -->
                <div class="card" style="border-top-color: var(--primary);">
                    <h3><span>📍</span> Edit Informasi Kontak & Maps</h3>
                    
                    <form action="" method="POST">
                        <input type="hidden" name="simpan_kontak" value="1">

                        <div class="form-group">
                            <label>Nama Instansi</label>
                            <input type="text" name="kontak_instansi" class="form-control" value="<?php echo htmlspecialchars($kontak_instansi); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Alamat Lengkap</label>
                            <textarea name="kontak_alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($kontak_alamat); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" name="kontak_telepon" class="form-control" value="<?php echo htmlspecialchars($kontak_telepon); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Alamat Email</label>
                            <input type="email" name="kontak_email" class="form-control" value="<?php echo htmlspecialchars($kontak_email); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Link / URL Embed Google Maps (`src="..."`)</label>
                            <textarea name="kontak_map_url" class="form-control" rows="4" required><?php echo htmlspecialchars($kontak_map_url); ?></textarea>
                            <small style="color: #6b7280; font-size: 0.78rem; margin-top: 6px; display: block;">*Hanya copy bagian link URL-nya saja dari dalam tag iframe src="..."</small>
                        </div>

                        <button type="submit" class="btn-submit">💾 Simpan Perubahan Kontak</button>
                    </form>
                </div>

                <!-- KOLOM KANAN: KOTAK MASUK PESAN PENGGUNA -->
                <div class="card" style="border-top-color: var(--secondary);">
                    <h3 style="color: var(--secondary);"><span>✉️</span> Kotak Masuk Pesan Pengguna</h3>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th width="35%">Pengirim</th>
                                    <th width="50%">Pesan</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $q_pesan =$conn->query("SELECT * FROM pesan_masuk ORDER BY id DESC");
                                if($q_pesan &&$q_pesan->num_rows > 0):
                                    while($row =$q_pesan->fetch_assoc()):
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-dark);"><?php echo htmlspecialchars($row['nama']); ?></div>
                                        <div style="font-size: 0.78rem; color: #6b7280;"><?php echo htmlspecialchars($row['email']); ?></div>
                                        <div style="font-size: 0.72rem; color: #9ca3af; margin-top: 2px;">🕒 <?php echo htmlspecialchars($row['tanggal']); ?></div>
                                        <div style="font-weight: 700; color: var(--primary); font-size: 0.8rem; margin-top: 4px;"><?php echo htmlspecialchars($row['judul']); ?></div>
                                    </td>
                                    <td>
                                        <span style="color: #4b5563; font-size: 0.9rem; line-height: 1.5; display: block;"><?php echo nl2br(htmlspecialchars($row['pesan'])); ?></span>
                                    </td>
                                    <td>
                                        <a href="pesan.php?action=hapus_pesan&id=<?php echo $row['id']; ?>" class="btn-action btn-del" onclick="return confirm('Yakin ingin menghapus pesan ini?')">🗑️ Hapus</a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding: 40px 20px; font-style: italic;">Belum ada pesan masuk dari pengunjung.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
    
    <script>
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

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }
    </script>
</body>
</html>