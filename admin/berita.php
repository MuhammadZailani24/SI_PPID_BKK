<!-- admin/berita.php -->
<?php 
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/koneksi.php';

$pesan = '';
$tipe_pesan = '';

// ==========================================================
// 0. AUTO-CREATE TABEL SLIDER (Jika Belum Ada)
// ==========================================================
$conn->query("CREATE TABLE IF NOT EXISTS slider_beranda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    gambar VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// ==========================================================
// 1. PROSES KELOLA SLIDER BERANDA (FOTO)
// ==========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_slider'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul_slider']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_slider']);
    
    if (isset($_FILES['foto_slider']) && $_FILES['foto_slider']['error'] == 0) {
        $target_dir = "../uploads/slider/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . '_' . basename($_FILES['foto_slider']['name']);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = array('jpg', 'jpeg', 'png', 'webp');
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['foto_slider']['tmp_name'], $target_file)) {
                $gambar_path = "uploads/slider/" . $file_name;
                $conn->query("INSERT INTO slider_beranda (judul, deskripsi, gambar) VALUES ('$judul', '$deskripsi', '$gambar_path')");
                $pesan = "Foto Slider berhasil ditambahkan!";
                $tipe_pesan = "success";
            } else {
                $pesan = "Gagal mengunggah foto slider."; $tipe_pesan = "error";
            }
        } else {
            $pesan = "Format foto harus JPG, JPEG, PNG, atau WEBP."; $tipe_pesan = "error";
        }
    }
}

// Hapus Slider
if (isset($_GET['action']) && $_GET['action'] == 'del_slider' && isset($_GET['id'])) {
    $id_hapus = (int)$_GET['id'];
    $cek_file = $conn->query("SELECT gambar FROM slider_beranda WHERE id = $id_hapus")->fetch_assoc();
    if ($cek_file) {
        if (!empty($cek_file['gambar']) && file_exists("../" . $cek_file['gambar'])) unlink("../" . $cek_file['gambar']);
        $conn->query("DELETE FROM slider_beranda WHERE id = $id_hapus");
        $pesan = "Foto Slider berhasil dihapus!"; $tipe_pesan = "success";
    }
}

// ==========================================================
// 2. PROSES KELOLA BERITA
// ==========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_berita'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul_berita']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori_berita']);
    $status = mysqli_real_escape_string($conn, $_POST['status_berita']);
    $konten = mysqli_real_escape_string($conn, $_POST['konten_berita']);
    $penulis = mysqli_real_escape_string($conn, $_SESSION['admin_nama']);
    
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul)));
    
    if (isset($_FILES['thumbnail_berita']) && $_FILES['thumbnail_berita']['error'] == 0) {
        $target_dir = "../uploads/berita/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . '_' . basename($_FILES['thumbnail_berita']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['thumbnail_berita']['tmp_name'], $target_file)) {
            $thumbnail_path = "uploads/berita/" . $file_name;
            $query = "INSERT INTO berita (judul, slug, kategori, thumbnail, konten, status, penulis) 
                      VALUES ('$judul', '$slug', '$kategori', '$thumbnail_path', '$konten', '$status', '$penulis')";
            if($conn->query($query)){
                $pesan = "Berita/Artikel berhasil dipublikasikan!"; $tipe_pesan = "success";
            }
        }
    }
}

// Hapus Berita
if (isset($_GET['action']) && $_GET['action'] == 'del_berita' && isset($_GET['id'])) {
    $id_hapus = (int)$_GET['id'];
    $cek_file = $conn->query("SELECT thumbnail FROM berita WHERE id = $id_hapus")->fetch_assoc();
    if ($cek_file) {
        if (!empty($cek_file['thumbnail']) && file_exists("../" . $cek_file['thumbnail'])) unlink("../" . $cek_file['thumbnail']);
        $conn->query("DELETE FROM berita WHERE id = $id_hapus");
        $pesan = "Berita berhasil dihapus!"; $tipe_pesan = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Berita & Foto Slider - Admin CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* =====================================================================
           TEMA: Balai Kekarantinaan Kesehatan Kelas I Banjarmasin
           Palet: Teal Kemenkes (#00b2a9) + Oranye (#ef8a1c) + Deep Teal gelap
           (disamakan dengan dashboard.php, permohonan.php, dan keberatan.php)
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

        .content-body { padding: 34px 40px 50px; max-width: 1400px; margin: 0 auto; width: 100%; display: flex; flex-direction: column; gap: 30px; }
        
        .alert-success { background-color: #dcf7ec; color: #0b8a5a; padding: 16px 20px; border-radius: var(--radius-md); border: 1px solid #b5ecd4; border-left: 5px solid #10b981; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08); }
        .alert-error { background-color: #ffe5e5; color: #b91c1c; padding: 16px 20px; border-radius: var(--radius-md); border: 1px solid #ffc9c9; border-left: 5px solid #ef4444; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(239, 68, 68, 0.08); }
        
        /* CARD & FORM STYLING */
        .card { 
            position: relative; overflow: hidden; background: #ffffff; border-radius: var(--radius-lg); padding: 34px; 
            box-shadow: var(--card-shadow); border: 1px solid var(--border-soft); border-top: 1px solid var(--border-soft);
            transition: box-shadow 0.35s var(--ease); 
        }
        .card::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; 
            background: linear-gradient(90deg, var(--primary), #4fd8d0 55%, var(--secondary));
        }
        /* Kartu Slider (yang memakai aksen oranye) */
        .card[style*="border-top-color"]::before { background: linear-gradient(90deg, var(--secondary), #f6a94d 45%, var(--primary)); }
        .card:hover { box-shadow: var(--card-hover-shadow); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px dashed rgba(0, 143, 136, 0.2); padding-bottom: 18px; }
        .card-header h3 { margin: 0; font-size: 1.2rem; font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 10px; letter-spacing: -0.2px; }
        .card-header h3 > span:first-child { 
            display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; 
            border-radius: 12px; background: var(--primary-soft); font-size: 1.05rem;
        }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.78rem; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.6px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid #d6e4e3; border-radius: 14px; font-family: inherit; font-size: 0.95rem; background: #f6fbfb; transition: all 0.3s var(--ease); color: var(--text-dark); line-height: 1.5; }
        .form-control:focus { outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.14); }
        
        .btn-submit { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; padding: 16px 28px; border-radius: 999px; font-weight: 800; font-size: 0.92rem; cursor: pointer; transition: all 0.3s var(--ease); display: inline-flex; justify-content: center; align-items: center; text-transform: uppercase; letter-spacing: 0.8px; box-shadow: 0 10px 26px rgba(0, 178, 169, 0.3); font-family: inherit; }
        .btn-submit:hover { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); box-shadow: 0 12px 30px rgba(239, 138, 28, 0.4); transform: translateY(-2px); }
        
        /* TABLE STYLING */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 12px; margin-top: 15px; }
        .admin-table { width: 100%; min-width: 700px; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
        .admin-table th, .admin-table td { padding: 16px 15px; border-bottom: 1px solid #edf3f3; text-align: left; vertical-align: middle; }
        .admin-table th { 
            background: var(--primary-soft); color: var(--primary-dark); font-weight: 700; 
            text-transform: uppercase; font-size: 0.74rem; letter-spacing: 0.7px; border-bottom: none; 
        }
        .admin-table th:first-child { border-radius: 12px 0 0 12px; }
        .admin-table th:last-child { border-radius: 0 12px 12px 0; }
        .admin-table td { color: #2c4a49; }
        .admin-table tbody tr:last-child td { border-bottom: none; }
        .admin-table tbody tr { transition: background-color 0.2s ease; }
        .admin-table tbody tr:hover td { background-color: #f4fbfa; }
        .admin-table tbody tr:hover td:first-child { box-shadow: inset 3px 0 0 var(--primary); }
        
        .img-preview { width: 90px; height: 60px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border-soft); box-shadow: 0 6px 16px rgba(4, 48, 47, 0.12); }
        .img-preview-large { width: 140px; height: 80px; object-fit: cover; border-radius: 14px; border: 1px solid var(--border-soft); box-shadow: 0 6px 16px rgba(4, 48, 47, 0.12); }
        
        .badge { 
            display: inline-flex; align-items: center; gap: 7px; padding: 6px 14px; border-radius: 999px; 
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; 
        }
        .badge::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
        .badge-success { background-color: #dcf7ec; color: #0b8a5a; border: 1px solid #b5ecd4; }
        .badge-warning { background-color: #fff4e0; color: #c26a05; border: 1px solid #ffe0ad; }
        
        .btn-action { padding: 8px 14px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s var(--ease); white-space: nowrap; font-family: inherit; }
        .btn-del { background-color: #ffe9e9; color: #d12c2c; border: 1px solid #ffd0d0; }
        .btn-del:hover { background-color: #d12c2c; color: white; border-color: #d12c2c; box-shadow: 0 8px 18px rgba(209, 44, 44, 0.28); transform: translateY(-1px); }

        /* AKSESIBILITAS */
        .menu-toggle:focus-visible, .btn-logout:focus-visible, .btn-action:focus-visible, .btn-submit:focus-visible,
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
            .topbar-user span { display: none; } /* Hide user name on small screens */
            
            .content-body { padding: 20px 15px 40px; gap: 20px; }
            .card { padding: 22px 16px; border-radius: 20px; }
            .card-header h3 { font-size: 1.1rem; }
            
            .form-grid { grid-template-columns: 1fr; gap: 15px; }
            .btn-submit { width: 100%; }
            
            .admin-table th, .admin-table td { padding: 12px 10px; font-size: 0.85rem; }
            .img-preview-large { width: 100px; height: 60px; }
        }
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>PANEL ADMIN</h3>
            <p>PPID BKK Banjarmasin</p>
        </div>
        
        <ul class="sidebar-menu">
            <li class="menu-category"><a href="dashboard.php"><span class="menu-title"><span>🏠</span> Dashboard Admin</span></a></li>

            <li class="menu-category open">
                <a onclick="toggleMenu('menu-operasional', this)" class="active-parent">
                    <span class="menu-title"><span>⚡</span> Atur Beranda & Layanan</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-operasional" style="display: block;">
                    <li><a href="permohonan.php">Data Permohonan Publik</a></li>
                    <li><a href="keberatan.php">Data Keberatan Publik</a></li>
                    <li><a href="berita.php" class="active">Push Berita Informasi & Foto</a></li>
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

            <li class="menu-category">
                <a onclick="toggleMenu('menu-kontak', this)"><span class="menu-title"><span>📞</span> Kontak</span><span class="arrow">▼</span></a>
                <ul class="sub-menu" id="menu-kontak">
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="pesan.php">Kontak / Pesan Masuk</a></li>
                </ul>
            </li>
        </ul>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">Kelola Foto Beranda & Berita</div>
            </div>
            <div class="topbar-user">
                <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </header>

        <div class="content-body">
            
            <?php if($pesan && $tipe_pesan == 'success'): ?>
                <div class="alert-success">✅ <?php echo htmlspecialchars($pesan); ?></div>
            <?php elseif($pesan && $tipe_pesan == 'error'): ?>
                <div class="alert-error">❌ <?php echo htmlspecialchars($pesan); ?></div>
            <?php endif; ?>

            <!-- KELOLA FOTO SLIDER -->
            <div class="card" style="border-top-color: var(--secondary);">
                <div class="card-header">
                    <h3 style="color: var(--secondary);"><span>📸</span> Kelola Foto Slider Beranda (Carousel)</h3>
                </div>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="tambah_slider" value="1">
                    <div class="form-grid" style="align-items: end;">
                        <div class="form-group" style="margin:0;">
                            <label>Judul Teks Foto</label>
                            <input type="text" name="judul_slider" class="form-control" placeholder="Cth: Tim Petugas Layanan" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Deskripsi Pendek</label>
                            <input type="text" name="deskripsi_slider" class="form-control" placeholder="Cth: Siap melayani dengan sepenuh hati..." required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Upload Gambar</label>
                            <input type="file" name="foto_slider" class="form-control" accept="image/*" required style="padding: 10px;">
                        </div>
                        <div>
                            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, var(--secondary) 0%, #d97706); width: 100%; box-shadow: 0 6px 20px rgba(239, 138, 28, 0.3);">Unggah Foto</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="15%">Gambar</th>
                                <th width="25%">Judul Teks</th>
                                <th>Deskripsi</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $q_slider = $conn->query("SELECT * FROM slider_beranda ORDER BY id DESC");
                            if($q_slider->num_rows > 0):
                                while($row = $q_slider->fetch_assoc()):
                            ?>
                            <tr>
                                <td><img src="../<?php echo htmlspecialchars($row['gambar']); ?>" class="img-preview-large"></td>
                                <td><strong style="color: var(--text-dark); display:block; margin-bottom:4px; font-size:1rem;"><?php echo htmlspecialchars($row['judul']); ?></strong></td>
                                <td><span style="color: #6b7280; font-size: 0.88rem; line-height:1.5; display:block;"><?php echo htmlspecialchars($row['deskripsi']); ?></span></td>
                                <td>
                                    <a href="berita.php?action=del_slider&id=<?php echo $row['id']; ?>" class="btn-action btn-del" onclick="return confirm('Hapus foto slider ini?')">🗑️ Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="4" style="text-align:center; color:#9ca3af; padding: 40px 20px; font-style: italic;">Belum ada foto slider yang diunggah.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PUSH BERITA & INFORMASI BARU -->
            <div class="card">
                <div class="card-header">
                    <h3><span>📰</span> Push Berita & Informasi Baru</h3>
                </div>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="tambah_berita" value="1">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Judul Berita</label>
                            <input type="text" name="judul_berita" class="form-control" placeholder="Masukkan judul berita..." required>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="kategori_berita" class="form-control" required>
                                <option value="Berita Utama">Berita Utama</option>
                                <option value="Pengumuman">Pengumuman</option>
                                <option value="Artikel">Artikel</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status Visibilitas</label>
                            <select name="status_berita" class="form-control" required>
                                <option value="Published">Published (Tampil)</option>
                                <option value="Draft">Draft (Sembunyikan)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Gambar / Thumbnail Berita (JPG/PNG)</label>
                            <input type="file" name="thumbnail_berita" class="form-control" accept="image/*" required style="padding: 10px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Isi Konten Berita</label>
                        <textarea name="konten_berita" class="form-control" rows="8" placeholder="Ketik isi berita secara lengkap di sini..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">🚀 Publikasikan Berita</button>
                </form>

                <div class="table-responsive" style="margin-top: 40px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 1.1rem; font-weight: 800; color: #1f2937;">Daftar Berita & Informasi</h4>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="10%">Thumbnail</th>
                                <th>Judul Berita</th>
                                <th width="15%">Kategori</th>
                                <th width="15%">Status</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $q_berita = $conn->query("SELECT * FROM berita ORDER BY id DESC");
                            if($q_berita->num_rows > 0):
                                while($row = $q_berita->fetch_assoc()):
                            ?>
                            <tr>
                                <td><img src="../<?php echo htmlspecialchars($row['thumbnail']); ?>" class="img-preview"></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-dark); font-size:1rem; margin-bottom: 6px;"><?php echo htmlspecialchars($row['judul']); ?></div>
                                    <div style="font-size: 0.8rem; color: #6b7280; font-weight: 500;">🕒 <?php echo date('d M Y', strtotime($row['created_at'])); ?> • 👤 <?php echo htmlspecialchars($row['penulis']); ?></div>
                                </td>
                                <td><span style="background: #f3f4f6; padding: 6px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; color: #4b5563; border: 1px solid #e5e7eb;"><?php echo htmlspecialchars($row['kategori']); ?></span></td>
                                <td>
                                    <?php if($row['status'] == 'Published'): ?>
                                        <span class="badge badge-success">Published</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="berita.php?action=del_berita&id=<?php echo $row['id']; ?>" class="btn-action btn-del" onclick="return confirm('Hapus berita ini secara permanen?')">🗑️ Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="5" style="text-align:center; color:#9ca3af; padding: 40px 20px; font-style: italic;">Belum ada berita yang diterbitkan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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