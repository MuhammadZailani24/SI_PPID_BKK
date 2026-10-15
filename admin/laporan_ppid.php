<?php 
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Panggil file koneksi database[cite: 19]
require_once '../includes/koneksi.php';

$pesan = '';
$tipe_pesan = '';

// ==========================================================
// 1. PROSES HAPUS LAPORAN
// ==========================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = (int)$_GET['id'];
    $cek_file = $conn->query("SELECT file_path FROM laporan_ppid WHERE id = $id_hapus")->fetch_assoc();
    
    if ($cek_file) {
        if (!empty($cek_file['file_path']) && file_exists("../" . $cek_file['file_path'])) {
            unlink("../" . $cek_file['file_path']); // Hapus file dari server
        }
        $conn->query("DELETE FROM laporan_ppid WHERE id = $id_hapus");
        $pesan = "Dokumen Laporan PPID berhasil dihapus!";
        $tipe_pesan = "success";
    }
}

// ==========================================================
// 2. PROSES UPDATE TEKS PENGANTAR
// ==========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_teks'])) {
    $teks_laporan = mysqli_real_escape_string($conn, $_POST['teks_pengantar_laporan']);
    
    $cek = $conn->query("SELECT id FROM pengaturan_web WHERE kunci_pengaturan = 'teks_pengantar_laporan'");
    if ($cek->num_rows > 0) {
        $conn->query("UPDATE pengaturan_web SET nilai_pengaturan = '$teks_laporan' WHERE kunci_pengaturan = 'teks_pengantar_laporan'");
    } else {
        $conn->query("INSERT INTO pengaturan_web (kunci_pengaturan, nilai_pengaturan) VALUES ('teks_pengantar_laporan', '$teks_laporan')");
    }
    $pesan = "Teks pengantar Laporan PPID berhasil diperbarui!";
    $tipe_pesan = "success";
}

// ==========================================================
// 3. PROSES UPLOAD LAPORAN BARU
// ==========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_laporan'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $tahun = (int)$_POST['tahun'];
    
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == 0) {
        $target_dir = "../uploads/laporan/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['file_laporan']['name']);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        if ($file_type == 'pdf') {
            if (move_uploaded_file($_FILES['file_laporan']['tmp_name'], $target_file)) {
                $file_path = "uploads/laporan/" . $file_name;
                $conn->query("INSERT INTO laporan_ppid (tahun, judul, file_path) VALUES ('$tahun', '$judul', '$file_path')");
                
                $pesan = "Dokumen Laporan Tahun $tahun berhasil diunggah!";
                $tipe_pesan = "success";
            } else {
                $pesan = "Gagal mengunggah file ke server.";
                $tipe_pesan = "error";
            }
        } else {
            $pesan = "Format tidak didukung! Laporan harus berformat PDF.";
            $tipe_pesan = "error";
        }
    }
}

// Ambil Teks Pengantar dari Database
$teks_default = "Sebagai bentuk pertanggungjawaban dan transparansi, Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin secara rutin menerbitkan Laporan Layanan Informasi Publik setiap tahunnya.";
$query_teks = $conn->query("SELECT nilai_pengaturan FROM pengaturan_web WHERE kunci_pengaturan = 'teks_pengantar_laporan'");
$teks_pengantar = ($query_teks->num_rows > 0) ? $query_teks->fetch_assoc()['nilai_pengaturan'] : $teks_default;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Laporan PPID - Admin CMS</title>
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

        .content-body { padding: 34px 40px 50px; max-width: 1200px; margin: 0 auto; width: 100%; box-sizing: border-box; }
        
        /* ALERTS */
        .alert-success { background-color: #dcf7ec; color: #0b8a5a; padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 25px; border: 1px solid #b5ecd4; border-left: 5px solid #10b981; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08); }
        .alert-error { background-color: #ffe5e5; color: #b91c1c; padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 25px; border: 1px solid #ffc9c9; border-left: 5px solid #ef4444; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(239, 68, 68, 0.08); }
        
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
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid #d6e4e3; border-radius: 14px; font-family: inherit; font-size: 0.95rem; box-sizing: border-box; transition: all 0.3s var(--ease); background-color: #f6fbfb; color: var(--text-dark); }
        .form-control:focus { outline: none; border-color: var(--primary); background-color: white; box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.14); }
        
        .btn-submit { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; padding: 15px 28px; border-radius: 999px; font-weight: 800; font-size: 0.9rem; cursor: pointer; transition: all 0.3s var(--ease); display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.8px; box-shadow: 0 10px 26px rgba(0, 178, 169, 0.3); font-family: inherit; }
        .btn-submit:hover { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); box-shadow: 0 12px 30px rgba(239, 138, 28, 0.4); transform: translateY(-2px); }

        /* TABLES */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 12px; margin-top: 15px; }
        .data-table { width: 100%; min-width: 650px; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
        .data-table th, .data-table td { padding: 16px 15px; border-bottom: 1px solid #edf3f3; text-align: left; vertical-align: middle; }
        .data-table th { 
            background: var(--primary-soft); color: var(--primary-dark); font-weight: 700; 
            text-transform: uppercase; font-size: 0.74rem; letter-spacing: 0.7px; border-bottom: none; 
        }
        .data-table th:first-child { border-radius: 12px 0 0 12px; }
        .data-table th:last-child { border-radius: 0 12px 12px 0; }
        .data-table td { color: #2c4a49; }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr { transition: background-color 0.2s ease; }
        .data-table tbody tr:hover td { background-color: #f4fbfa; }
        .data-table tbody tr:hover td:first-child { box-shadow: inset 3px 0 0 var(--primary); }

        .btn-sm-view { background-color: #e4f1ff; color: #1b64c8; border: 1px solid #c4dffc; padding: 8px 16px; border-radius: 999px; text-decoration: none; font-size: 0.82rem; font-weight: 700; transition: all 0.3s var(--ease); display: inline-flex; align-items: center; gap: 6px; margin-right: 5px; }
        .btn-sm-view:hover { background-color: #2f80ed; color: white; border-color: #2f80ed; transform: translateY(-1px); box-shadow: 0 8px 18px rgba(47, 128, 237, 0.3); }
        
        .btn-sm-del { background-color: #ffe9e9; color: #d12c2c; border: 1px solid #ffd0d0; padding: 8px 16px; border-radius: 999px; text-decoration: none; font-size: 0.82rem; font-weight: 700; transition: all 0.3s var(--ease); display: inline-flex; align-items: center; gap: 6px; }
        .btn-sm-del:hover { background-color: #d12c2c; color: white; border-color: #d12c2c; transform: translateY(-1px); box-shadow: 0 8px 18px rgba(209, 44, 44, 0.28); }

        /* TEMA WARNA KARTU */
        .card-secondary::before { background: linear-gradient(90deg, var(--secondary), #f6a94d 45%, var(--primary)); }
        .card-secondary h3 { color: var(--secondary-dark); }
        .card-secondary h3 > span:first-child { background: var(--secondary-soft); }
        .card-secondary .form-group label { color: var(--secondary-dark); }
        .card-secondary .btn-submit { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); box-shadow: 0 10px 26px rgba(239, 138, 28, 0.3); }
        .card-secondary .btn-submit:hover { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); box-shadow: 0 12px 30px rgba(0, 178, 169, 0.4); }
        .card-secondary .form-control:focus { border-color: var(--secondary); box-shadow: 0 0 0 4px rgba(239, 138, 28, 0.14); }

        /* AKSESIBILITAS */
        .menu-toggle:focus-visible, .btn-logout:focus-visible, .btn-submit:focus-visible,
        .btn-sm-view:focus-visible, .btn-sm-del:focus-visible,
        .sub-menu li a:focus-visible, .menu-category > a:focus-visible { outline: 3px solid rgba(239, 138, 28, 0.6); outline-offset: 2px; }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }

        /* RESPONSIVE LAYOUT */
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
            .upload-form-group { flex-direction: column !important; align-items: stretch !important; gap: 15px !important; }
            .upload-form-group .form-group { width: 100% !important; min-width: 100% !important; margin-bottom: 0 !important; }
            .upload-form-group .btn-submit { width: 100% !important; height: auto !important; padding: 14px 20px !important; }
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
                    <li><a href="laporan_ppid.php" class="active">Laporan PPID</a></li>
                    <li><a href="statistik_layanan.php">Statistik Layanan</a></li>
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
                <div class="topbar-title">Kelola Laporan Kinerja PPID</div>
            </div>
            <div class="topbar-user">
                <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </header>

        <div class="content-body">
            
            <?php if($pesan && $tipe_pesan == 'success'): ?>
                <div class="alert-success"><span>✅</span> <?php echo htmlspecialchars($pesan); ?></div>
            <?php elseif($pesan && $tipe_pesan == 'error'): ?>
                <div class="alert-error"><span>❌</span> <?php echo htmlspecialchars($pesan); ?></div>
            <?php endif; ?>

            <!-- KARTU 1: PENGATURAN TEKS -->
            <div class="card">
                <h3><span>📝</span> Teks Pengantar Laporan</h3>
                <form action="" method="POST">
                    <!-- NAMA VARIABEL DAN INPUT TIDAK DIUBAH -->
                    <input type="hidden" name="update_teks" value="1">
                    <div class="form-group">
                        <label>Teks Penjelasan Daftar Laporan</label>
                        <textarea name="teks_pengantar_laporan" class="form-control" rows="3" required><?php echo htmlspecialchars($teks_pengantar); ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit">💾 Simpan Teks</button>
                </form>
            </div>

            <!-- KARTU 2: UPLOAD LAPORAN BARU -->
            <div class="card card-secondary">
                <h3><span>📤</span> Upload Dokumen Laporan Baru</h3>
                <form action="" method="POST" enctype="multipart/form-data" class="upload-form-group" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
                        <label>Judul Laporan</label>
                        <input type="text" name="judul" class="form-control" placeholder="Contoh: Laporan Pelayanan Informasi PPID BKK" required>
                    </div>
                    <div class="form-group" style="width: 150px; margin-bottom: 0;">
                        <label>Tahun Laporan</label>
                        <input type="number" name="tahun" class="form-control" min="2020" max="2099" value="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
                        <label>File Laporan (Hanya PDF)</label>
                        <input type="file" name="file_laporan" class="form-control" accept=".pdf" style="padding: 11px;" required>
                    </div>
                    <button type="submit" name="upload_laporan" class="btn-submit" style="height: 51px; margin-bottom: 0;">☁️ Unggah PDF</button>
                </form>
            </div>

            <!-- KARTU 3: TABEL DAFTAR LAPORAN -->
            <div class="card">
                <h3><span>📚</span> Daftar Laporan Terunggah</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama / Judul Laporan</th>
                                <th width="15%">Tahun</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $laporan = $conn->query("SELECT * FROM laporan_ppid ORDER BY tahun DESC, id DESC");
                            if($laporan && $laporan->num_rows > 0):
                                while($row = $laporan->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong style="color: var(--text-dark);"><?php echo htmlspecialchars($row['judul']); ?></strong></td>
                                <td><span style="background-color: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 0.8rem; border: 1px solid #e2e8f0;"><?php echo htmlspecialchars($row['tahun']); ?></span></td>
                                <td>
                                    <a href="../<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn-sm-view">📄 Lihat</a>
                                    <a href="laporan_ppid.php?action=delete&id=<?php echo $row['id']; ?>" class="btn-sm-del" onclick="return confirm('Yakin ingin menghapus laporan tahun <?php echo htmlspecialchars($row['tahun']); ?> ini?')">🗑️ Hapus</a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else: 
                            ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px 20px; color: #9ca3af; font-style: italic;">Belum ada dokumen laporan yang diunggah.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

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