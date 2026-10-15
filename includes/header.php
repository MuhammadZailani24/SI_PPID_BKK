<!-- includes/header.php -->
<?php 
// Mendeteksi apakah base_url sudah didefinisikan dari file yang memanggil. 
$base_url = isset($base_url) ? $base_url : ''; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPID BKK Kelas 1 Banjarmasin</title>
    <!-- CSS Path Dinamis -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    <style>
        /* =========================================================
           STYLING LOGO BRAND DI NAVBAR
           ========================================================= */
        .brand-logo {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .brand-logo:hover {
            transform: translateY(-2px);
            opacity: 0.95;
        }

        .brand-logo img {
            height: 45px;
            width: auto;
            object-fit: contain;
        }

        /* =========================================================
           STYLING KHUSUS TOMBOL LOGIN ADMIN DI NAVBAR
           ========================================================= */
        .btn-login-admin {
            margin-left: 15px;
        }

        .btn-login-admin a {
            background-color: transparent !important;
            border: 2px solid var(--primary, #00b2a9) !important;
            color: var(--primary, #00b2a9) !important;
            padding: 8px 18px !important;
            border-radius: 25px;
            font-weight: 700 !important;
            transition: all 0.3s ease !important;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-login-admin a:hover {
            background-color: var(--hover-color, #ef8a1c) !important;
            border-color: var(--hover-color, #ef8a1c) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(239, 138, 28, 0.3);
            transform: translateY(-2px);
        }

        /* Penyesuaian di layar HP */
        @media (max-width: 768px) {
            .btn-login-admin {
                margin-left: 0;
                margin-top: 10px;
                text-align: center;
            }
            .btn-login-admin a {
                justify-content: center;
                display: flex;
            }
        }
    </style>
</head>
<body>

<header>
    <nav class="navbar">
        <!-- Logo Gambar Menggantikan Teks -->
        <div class="logo">
            <a href="<?php echo $base_url; ?>index.php" class="brand-logo">
                <img src="<?php echo $base_url; ?>assets/images/logo_bkk.webp" alt="Logo PPID BKK Banjarmasin">
            </a>
        </div>
        
        <!-- Tombol Hamburger dengan pemanggil fungsi toggleMenu() -->
        <div class="hamburger" id="hamburger" onclick="toggleMenu()">☰</div>

        <ul class="nav-links" id="navLinks">
            <li class="nav-item">
                <a href="<?php echo $base_url; ?>index.php">Beranda</a>
            </li>
            
            <li class="nav-item" onclick="toggleDropdown(this)">
                <a href="#">Profil </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>profil_ppid.php">Profil PPID</a></li>
                    <li><a href="<?php echo $base_url; ?>visi_misi.php">Visi dan Misi</a></li>
                    <li><a href="<?php echo $base_url; ?>struktur_organisasi.php">Struktur Organisasi PPID</a></li>
                    <li><a href="<?php echo $base_url; ?>tugas_fungsi.php">Tugas dan Fungsi</a></li>
                    <li><a href="<?php echo $base_url; ?>dasar_hukum.php">Dasar Hukum PPID</a></li>
                </ul>
            </li>

            <li class="nav-item" onclick="toggleDropdown(this)">
                <a href="#">Informasi Publik </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>informasi_publik.php">Informasi Publik</a></li>
                    <li><a href="<?php echo $base_url; ?>informasi_serta_merta.php">Informasi Serta Merta</a></li>
                    <li><a href="<?php echo $base_url; ?>informasi_setiap_saat.php">Informasi Setiap Saat</a></li>
                </ul>
            </li>

            <li class="nav-item" onclick="toggleDropdown(this)">
                <a href="#">Standar Layanan </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>prosedur_pelayanan.php">Prosedur Pelayanan Informasi Publik</a></li>
                    <li><a href="<?php echo $base_url; ?>prosedur_keberatan.php">Prosedur Pengelolaan Keberatan Informasi Publik</a></li>
                    <li><a href="<?php echo $base_url; ?>kanal_layanan.php">Kanal Layanan Informasi</a></li>
                    <li><a href="<?php echo $base_url; ?>waktu_biaya_layanan.php">Waktu dan Biaya Layanan</a></li>
                    <li><a href="<?php echo $base_url; ?>maklumat_layanan.php">Maklumat Layanan</a></li>
                </ul>
            </li>

            <li class="nav-item" onclick="toggleDropdown(this)">
                <a href="#">Laporan </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>laporan_ppid.php">Laporan PPID</a></li>
                    <li><a href="<?php echo $base_url; ?>statistik_layanan.php">Statistik Layanan Informasi Publik</a></li>
                    <li><a href="<?php echo $base_url; ?>survei_layanan.php">Survei Layanan PPID</a></li>
                </ul>
            </li>

            <li class="nav-item" onclick="toggleDropdown(this)">
                <a href="#">Kontak </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>faq.php">FAQ</a></li>
                    <li><a href="<?php echo $base_url; ?>kontak.php">Kontak</a></li>
                </ul>
            </li>

            <!-- TOMBOL LOGIN ADMIN -->
            <li class="nav-item btn-login-admin">
                <a href="<?php echo $base_url; ?>admin/login.php">🔒 Login Admin</a>
            </li>
        </ul>
    </nav>
</header>

<!-- JAVASCRIPT UNTUK MENGAKTIFKAN MENU DI HP -->
<script>
    function toggleMenu() {
        var nav = document.getElementById("navLinks");
        var hamburger = document.getElementById("hamburger");
        
        nav.classList.toggle("active");
        nav.classList.toggle("show"); 
        
        if(nav.classList.contains("active") || nav.classList.contains("show")) {
            hamburger.innerHTML = "✖";
        } else {
            hamburger.innerHTML = "☰";
        }
    }

    function toggleDropdown(element) {
        if (window.innerWidth <= 768) {
            var allItems = document.querySelectorAll('.nav-item');
            allItems.forEach(function(item) {
                if(item !== element && !item.classList.contains('btn-login-admin')) {
                    item.classList.remove('active');
                    item.classList.remove('show');
                }
            });
            
            element.classList.toggle('active');
            element.classList.toggle('show');
        }
    }
</script>