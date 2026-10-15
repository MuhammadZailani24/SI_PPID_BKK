<!-- profil_ppid.php -->
<?php 
require_once 'includes/koneksi.php';

// Ambil data pengaturan profil dari database
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN ('teks_latar_belakang', 'teks_profil_ppid', 'motto_ppid')");
if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Nilai cadangan (fallback) jika database belum diisi
$default_latar = "Informasi merupakan kebutuhan pokok setiap orang bagi pengembangan pribadi dan lingkungan sosialnya. Oleh karena itu, hak memperoleh informasi merupakan <strong>hak asasi manusia</strong> dan keterbukaan informasi publik menjadi sangat penting agar penyelenggaraan negara dapat diawasi publik serta dapat dipertanggungjawabkan.\n\nPemberlakuan <strong>Undang-Undang Nomor 14 Tahun 2008</strong> tentang Keterbukaan Informasi Publik merupakan momentum penting dalam mendorong transparansi di Indonesia. Setiap Badan Publik mempunyai kewajiban dalam menyediakan dan melayani permohonan informasi publik secara cepat, akurat, mudah, dan berkualitas.\n\nPejabat Pengelola Informasi dan Dokumentasi (PPID) pada <strong>Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin</strong> dibentuk dalam rangka mewujudkan penyelenggaraan negara yang transparan, efektif, efisien, akuntabel serta dapat dipertanggungjawabkan sesuai dengan peraturan perundang-undangan yang berlaku.";

$default_profil = "Pejabat Pengelola Informasi dan Dokumentasi (PPID) <strong>Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin</strong> merupakan ujung tombak pelayanan informasi kesehatan di wilayah kerja pelabuhan dan lintas wilayah. Tugas utamanya adalah mengelola, menyimpan, mendokumentasikan, dan memberikan pelayanan informasi publik kepada masyarakat secara transparan dan akuntabel.";

$default_motto = "Cepat, Akurat, Mudah dan Berkualitas";

// Ambil data atau gunakan default
$teks_latar  = $settings['teks_latar_belakang'] ?? $default_latar;
$teks_profil = $settings['teks_profil_ppid'] ?? $default_profil;
$motto       = $settings['motto_ppid'] ?? $default_motto;

include 'includes/header.php'; 
?>

<style>
    /* =========================================================
       STYLING MODERN & HIDUP UNTUK PROFIL PPID
       ========================================================= */
    .profil-wrapper {
        max-width: 950px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        display: flex;
        flex-direction: column;
        gap: 35px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .profil-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        border: 1px solid #eaeaea;
        border-left: 6px solid var(--primary, #00b2a9); /* Aksen garis kiri elegan */
        position: relative;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    /* Efek Hover Modern & Menyala */
    .profil-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0, 178, 169, 0.12);
        border-color: #d1d5db;
        border-left-color: var(--hover-color, #ef8a1c);
    }

    .profil-card-header {
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 25px;
        border-bottom: 2px dashed #f3f4f6;
        padding-bottom: 18px;
    }

    .profil-icon-box {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        box-shadow: 0 6px 15px rgba(0, 178, 169, 0.3);
        transition: all 0.3s ease;
    }

    /* Ikon berputar/membesar lembut saat card di-hover */
    .profil-card:hover .profil-icon-box {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        transform: scale(1.08) rotate(3deg);
        box-shadow: 0 8px 20px rgba(239, 138, 28, 0.4);
    }

    .profil-card-header h2 {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1f2937;
        margin: 0;
        letter-spacing: 0.3px;
    }

    .profil-card p {
        font-size: 1.02rem;
        line-height: 1.85;
        color: #4b5563;
        margin-bottom: 18px;
        text-align: justify;
    }

    .profil-card p strong {
        color: var(--primary, #00b2a9);
        font-weight: 700;
    }

    /* Banner Motto PPID Super Keren & Hidup */
    .motto-card {
        background: linear-gradient(135deg, #00b2a9 0%, #00796b 50%, #004d40 100%);
        color: white;
        border-radius: 18px;
        padding: 45px;
        text-align: center;
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.25);
        position: relative;
        overflow: hidden;
        transition: all 0.35s ease;
    }

    /* Aksen kilau latar belakang motto */
    .motto-card::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        pointer-events: none;
    }

    .motto-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 45px rgba(239, 138, 28, 0.3);
    }

    .motto-title {
        font-size: 0.9rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 3px;
        color: var(--hover-color, #ef8a1c);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .motto-title::before, .motto-title::after {
        content: '';
        width: 40px;
        height: 2px;
        background-color: var(--hover-color, #ef8a1c);
    }

    .motto-text {
        font-size: 1.8rem;
        font-weight: 800;
        font-style: italic;
        margin: 0;
        letter-spacing: 0.5px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }

    @media (max-width: 768px) {
        .profil-card { padding: 25px 20px; }
        .profil-card-header { flex-direction: column; align-items: flex-start; gap: 12px; }
        .motto-text { font-size: 1.35rem; }
        .motto-card { padding: 30px 20px; }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Profil PPID</h1>
        <p>Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin</p>
    </section>

    <div class="profil-wrapper">
        
        <!-- KARTU 1: LATAR BELAKANG -->
        <div class="profil-card">
            <div class="profil-card-header">
                <div class="profil-icon-box">ℹ</div>
                <h2>Latar Belakang Keterbukaan Informasi</h2>
            </div>
            <p>
                <?php echo nl2br($teks_latar); ?>
            </p>
        </div>

        <!-- KARTU 2: IDENTITAS INSTANSI -->
        <div class="profil-card">
            <div class="profil-card-header">
                <div class="profil-icon-box">🏛</div>
                <h2>PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin</h2>
            </div>
            <p>
                <?php echo nl2br($teks_profil); ?>
            </p>
        </div>

        <!-- KARTU 3: MOTTO PPID -->
        <div class="motto-card">
            <div class="motto-title">Motto Pelayanan PPID</div>
            <p class="motto-text">"<?php echo htmlspecialchars($motto); ?>"</p>
        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>