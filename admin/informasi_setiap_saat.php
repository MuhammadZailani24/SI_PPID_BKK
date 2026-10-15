<?php 
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Panggil file koneksi database
require_once '../includes/koneksi.php';

$pesan_sukses = false;
$pesan_teks = "";

// =========================================================================
// PROSES TAMBAH & EDIT INFORMASI SETIAP SAAT
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];
    $judul_informasi = mysqli_real_escape_string($conn, $_POST['judul_informasi']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    // Handle Upload File PDF/Dokumen
    $file_path = "";
    if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] == 0) {
        $target_dir = "../uploads/informasi_setiap_saat/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['file_dokumen']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $target_file)) {
            $file_path = "uploads/informasi_setiap_saat/" . $file_name;
        }
    }

    if ($aksi == 'tambah') {
        // Otomatis generate huruf kode berikutnya (A, B, C, D, E, dst.)
        $query_last = $conn->query("SELECT kode_huruf FROM informasi_setiap_saat ORDER BY id DESC LIMIT 1");
        if($query_last && $query_last->num_rows > 0) {
            $last_row = $query_last->fetch_assoc();
            $last_char = trim($last_row['kode_huruf']);
            if(!empty($last_char)) {
                $kode_huruf = chr(ord(strtoupper($last_char)) + 1);
            } else {
                $kode_huruf = 'A';
            }
        } else {
            $kode_huruf = 'A';
        }

        $sql = "INSERT INTO informasi_setiap_saat (kode_huruf, judul_informasi, deskripsi, file_path) 
                VALUES ('$kode_huruf', '$judul_informasi', '$deskripsi', '$file_path')";
        if ($conn->query($sql) === TRUE) {
            $pesan_sukses = true;
            $pesan_teks = "Informasi setiap saat baru berhasil ditambahkan dengan kode otomatis ($kode_huruf)!";
        }
    } elseif ($aksi == 'edit') {
        $id = intval($_POST['id_setiap']);
        if ($file_path != "") {
            $old = $conn->query("SELECT file_path FROM informasi_setiap_saat WHERE id = $id")->fetch_assoc();
            if (!empty($old['file_path']) && file_exists("../" . $old['file_path'])) {
                unlink("../" . $old['file_path']);
            }
            $sql = "UPDATE informasi_setiap_saat SET judul_informasi = '$judul_informasi', deskripsi = '$deskripsi', file_path = '$file_path' WHERE id = $id";
        } else {
            $sql = "UPDATE informasi_setiap_saat SET judul_informasi = '$judul_informasi', deskripsi = '$deskripsi' WHERE id = $id";
        }
        
        if ($conn->query($sql) === TRUE) {
            $pesan_sukses = true;
            $pesan_teks = "Informasi setiap saat berhasil diperbarui!";
        }
    }
}

// =========================================================================
// PROSES HAPUS DATA
// =========================================================================
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $old = $conn->query("SELECT file_path FROM informasi_setiap_saat WHERE id = $id")->fetch_assoc();
    if (!empty($old['file_path']) && file_exists("../" . $old['file_path'])) {
        unlink("../" . $old['file_path']);
    }
    $conn->query("DELETE FROM informasi_setiap_saat WHERE id = $id");
    header("Location: informasi_setiap_saat.php?status=deleted");
    exit;
}

if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
    $pesan_sukses = true;
    $pesan_teks = "Data berhasil dihapus dari sistem.";
}

// Ambil data dari database
$query_setiap = $conn->query("SELECT * FROM informasi_setiap_saat ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Informasi Setiap Saat - Admin CMS</title>
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

        .content-body { padding: 34px 40px 50px; max-width: 1400px; margin: 0 auto; width: 100%; box-sizing: border-box; }
        
        .alert-success { background-color: #dcf7ec; color: #0b8a5a; padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 25px; border: 1px solid #b5ecd4; border-left: 5px solid #10b981; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08); }
        
        /* CARD & TABLE */
        .card { 
            position: relative; overflow: hidden; background: #ffffff; border-radius: var(--radius-lg); padding: 34px; 
            box-shadow: var(--card-shadow); border: 1px solid var(--border-soft); 
            transition: box-shadow 0.35s var(--ease); 
        }
        .card::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; 
            background: linear-gradient(90deg, var(--primary), #4fd8d0 55%, var(--secondary));
        }
        .card:hover { box-shadow: var(--card-hover-shadow); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px dashed rgba(0, 143, 136, 0.2); padding-bottom: 18px; gap: 15px; }
        .card-header h2 { margin: 0; font-size: 1.2rem; color: var(--text-dark); font-weight: 800; display: flex; align-items: center; gap: 10px; letter-spacing: -0.2px; }
        .card-header h2 > span:first-child { 
            display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; 
            border-radius: 12px; background: var(--primary-soft); font-size: 1.05rem; flex-shrink: 0;
        }
        
        .btn-tambah { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; padding: 12px 22px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s var(--ease); text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 8px 20px rgba(0, 178, 169, 0.28); font-family: inherit; white-space: nowrap; }
        .btn-tambah:hover { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); transform: translateY(-2px); box-shadow: 0 12px 26px rgba(239, 138, 28, 0.4); }

        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 12px; }
        .data-table { width: 100%; min-width: 700px; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
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

        .badge-huruf { background: linear-gradient(135deg, var(--primary-soft), #c3f0ec); color: var(--primary-dark); padding: 6px 14px; border-radius: 12px; font-size: 0.9rem; font-weight: 800; display: inline-block; text-align: center; min-width: 42px; border: 1px solid rgba(0, 178, 169, 0.3); box-shadow: 0 6px 14px rgba(0, 178, 169, 0.15); }

        .btn-action { padding: 8px 14px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s var(--ease); white-space: nowrap; font-family: inherit; }
        .btn-edit { background-color: var(--secondary-soft); color: var(--secondary-dark); border: 1px solid rgba(239, 138, 28, 0.35); margin-right: 5px; }
        .btn-edit:hover { background-color: var(--secondary); color: white; border-color: var(--secondary); box-shadow: 0 8px 18px rgba(239, 138, 28, 0.32); transform: translateY(-1px); }
        .btn-hapus { background-color: #ffe9e9; color: #d12c2c; border: 1px solid #ffd0d0; }
        .btn-hapus:hover { background-color: #d12c2c; color: white; border-color: #d12c2c; box-shadow: 0 8px 18px rgba(209, 44, 44, 0.28); transform: translateY(-1px); }

        /* MODAL POP-UP (MODERNIZED) */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(3, 31, 32, 0.62); display: none; align-items: center; justify-content: center; z-index: 2000; -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); opacity: 0; transition: opacity 0.3s; }
        .modal-overlay.active { display: flex; opacity: 1; }
        
        .modal-content { position: relative; overflow: hidden; background-color: white; padding: 35px; border-radius: 28px; width: 90%; max-width: 550px; box-shadow: 0 30px 80px rgba(3, 31, 32, 0.35); border: 1px solid var(--border-soft); transform: scale(0.9); transition: transform 0.35s var(--ease); }
        .modal-content::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; 
            background: linear-gradient(90deg, var(--primary), #4fd8d0 55%, var(--secondary));
        }
        .modal-content.show { transform: scale(1); }
        
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px dashed rgba(0, 143, 136, 0.25); padding-bottom: 15px; gap: 12px; }
        .modal-header h3 { margin: 0; font-size: 1.2rem; color: var(--text-dark); display: flex; align-items: center; gap: 10px; font-weight: 800; letter-spacing: -0.2px; }
        .close-btn { background: var(--primary-soft); border: none; width: 38px; height: 38px; border-radius: 50%; font-size: 1.4rem; cursor: pointer; color: var(--primary-dark); transition: all 0.25s var(--ease); padding: 0; line-height: 1; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .close-btn:hover { background: #d12c2c; color: white; transform: rotate(90deg); }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; font-size: 0.78rem; margin-bottom: 8px; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.6px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid #d6e4e3; border-radius: 14px; font-family: inherit; font-size: 0.95rem; box-sizing: border-box; transition: all 0.3s var(--ease); background-color: #f6fbfb; color: var(--text-dark); line-height: 1.5; }
        .form-control:focus { outline: none; border-color: var(--primary); background-color: white; box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.14); }
        
        .form-text { display: block; margin-top: 6px; font-size: 0.78rem; color: var(--text-muted); line-height: 1.4; }
        
        .btn-submit { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; padding: 15px 20px; border-radius: 999px; font-weight: 800; cursor: pointer; width: 100%; margin-top: 10px; transition: all 0.3s var(--ease); font-size: 0.92rem; text-transform: uppercase; letter-spacing: 0.8px; box-shadow: 0 10px 26px rgba(0, 178, 169, 0.3); font-family: inherit; }
        .btn-submit:hover { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); box-shadow: 0 12px 30px rgba(239, 138, 28, 0.4); transform: translateY(-2px); }

        /* AKSESIBILITAS */
        .menu-toggle:focus-visible, .btn-logout:focus-visible, .btn-action:focus-visible, .btn-submit:focus-visible,
        .btn-tambah:focus-visible, .close-btn:focus-visible,
        .sub-menu li a:focus-visible, .menu-category > a:focus-visible { outline: 3px solid rgba(239, 138, 28, 0.6); outline-offset: 2px; }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }

        /* RESPONSIVE DESIGN */
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
            .card { padding: 22px 16px; border-radius: 20px; }
            .card-header { flex-direction: column; align-items: flex-start; gap: 15px; margin-bottom: 20px; padding-bottom: 15px; }
            .card-header h2 { font-size: 1.1rem; }
            .btn-tambah { width: 100%; justify-content: center; }
            
            .modal-content { padding: 25px 20px; border-radius: 22px; }
            .data-table th, .data-table td { padding: 12px 10px; font-size: 0.85rem; }
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

            <!-- INFORMASI PUBLIK (AKTIF DI HALAMAN INI) -->
            <li class="menu-category open">
                <a onclick="toggleMenu('menu-info', this)" class="active-parent">
                    <span class="menu-title"><span>📢</span> Informasi Publik</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-info" style="display: block;">
                    <li><a href="informasi_publik.php">Informasi Publik</a></li>
                    <li><a href="informasi_serta_merta.php">Informasi Serta Merta</a></li>
                    <li><a href="informasi_setiap_saat.php" class="active">Informasi Setiap Saat</a></li>
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

    <!-- AREA KONTEN KANAN -->
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">Kelola Halaman: Informasi Setiap Saat</div>
            </div>
            <div class="topbar-user">
                <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </header>

        <div class="content-body">
            
            <?php if($pesan_sukses): ?>
                <div class="alert-success">
                    <span>✅</span> <?php echo htmlspecialchars($pesan_teks); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2><span>🕒</span> Daftar Informasi Publik yang Tersedia Setiap Saat</h2>
                    <button class="btn-tambah" onclick="openModal('tambah')">➕ Tambah Informasi</button>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="10%">Kode</th>
                                <th width="45%">Judul / Topik Informasi</th>
                                <th width="25%">File Terlampir</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($query_setiap->num_rows > 0) {
                                while($row = $query_setiap->fetch_assoc()) { 
                            ?>
                            <tr>
                                <td>
                                    <span class="badge-huruf"><?php echo htmlspecialchars($row['kode_huruf']); ?></span>
                                </td>
                                <td>
                                    <strong style="color: var(--text-dark); display: block; margin-bottom: 4px;"><?php echo htmlspecialchars($row['judul_informasi']); ?></strong>
                                    <span style="font-size: 0.85rem; color: #6b7280; line-height: 1.4; display: block;"><?php echo (strlen($row['deskripsi']) > 60) ? htmlspecialchars(substr($row['deskripsi'], 0, 60)).'...' : htmlspecialchars($row['deskripsi']); ?></span>
                                </td>
                                <td>
                                    <?php if(!empty($row['file_path'])): ?>
                                        <a href="../<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" style="font-size: 0.8rem; color: var(--primary); text-decoration: none; font-weight: 600;">📄 Lihat Dokumen</a>
                                    <?php else: ?>
                                        <span style="color: #9ca3af; font-size: 0.8rem;">Tidak ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn-action btn-edit" onclick="openModal('edit', '<?php echo $row['id']; ?>', '<?php echo addslashes($row['judul_informasi']); ?>', '<?php echo addslashes($row['deskripsi']); ?>')">✏️ Edit</button>
                                    <a href="informasi_setiap_saat.php?hapus=<?php echo $row['id']; ?>" class="btn-action btn-hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus informasi ini?');">🗑️ Hapus</a>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center; color:#9ca3af; padding: 40px 20px; font-style: italic;'>Belum ada data informasi setiap saat yang tersimpan.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL POP-UP TAMBAH/EDIT -->
    <div class="modal-overlay" id="setiapModal">
        <div class="modal-content" id="modalContent">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Informasi Setiap Saat</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data" id="formSetiap">
                <!-- NAMA VARIABEL DAN ID TIDAK DIUBAH SAMA SEKALI -->
                <input type="hidden" name="id_setiap" id="modal_id">
                <input type="hidden" name="aksi" id="modal_aksi" value="tambah">

                <div class="form-group">
                    <label>Judul / Topik Informasi</label>
                    <input type="text" name="judul_informasi" id="modal_judul" class="form-control" placeholder="Contoh: Daftar Informasi Publik (DIP)..." required>
                </div>

                <div class="form-group">
                    <label>Deskripsi / Keterangan Singkat</label>
                    <textarea name="deskripsi" id="modal_deskripsi" class="form-control" rows="3" placeholder="Penjelasan mengenai informasi setiap saat..."></textarea>
                </div>

                <div class="form-group">
                    <label>Upload File Dokumen Pendukung (Opsional)</label>
                    <input type="file" name="file_dokumen" id="modal_file" class="form-control" accept=".pdf,.doc,.docx" style="padding: 10px;">
                    <span class="form-text" id="helpTextFile">*Format: PDF, DOC, DOCX. Maksimal 5MB.</span>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmitModal">Simpan Informasi</button>
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

        // Interaksi Modal
        const modal = document.getElementById('setiapModal');
        const modalContent = document.getElementById('modalContent');
        const modalTitle = document.getElementById('modalTitle');
        const btnSubmitModal = document.getElementById('btnSubmitModal');

        function openModal(aksi, id = '', judul = '', deskripsi = '') {
            document.getElementById('modal_aksi').value = aksi;
            
            if (aksi === 'tambah') {
                modalTitle.innerHTML = "<span>➕</span> Tambah Informasi Setiap Saat (Huruf Otomatis)";
                document.getElementById('modal_id').value = "";
                document.getElementById('modal_judul').value = "";
                document.getElementById('modal_deskripsi').value = "";
                btnSubmitModal.innerText = "Simpan Informasi Baru";
            } else if (aksi === 'edit') {
                modalTitle.innerHTML = "<span>✏️</span> Edit Informasi Setiap Saat";
                document.getElementById('modal_id').value = id;
                document.getElementById('modal_judul').value = judul;
                document.getElementById('modal_deskripsi').value = deskripsi;
                btnSubmitModal.innerText = "Simpan Perubahan";
            }

            modal.style.display = 'flex';
            // Trigger reflow & animation
            setTimeout(() => {
                modal.classList.add('active');
                modalContent.classList.add('show');
            }, 10);
        }

        function closeModal() {
            modalContent.classList.remove('show');
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

</body>
</html>