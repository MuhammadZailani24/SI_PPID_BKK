<!-- survei_layanan.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// 1. AMBIL PENGATURAN TEKS PENGANTAR DARI DATABASE
// =========================================================================
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan = 'survei_teks_pengantar'");
if ($query_set && $query_set->num_rows > 0) {
    $row_set = $query_set->fetch_assoc();
    $settings['survei_teks_pengantar'] = $row_set['nilai_pengaturan'];
}

$default_pengantar = "Partisipasi Anda sangat berarti bagi evaluasi pelayanan kami. Jika Anda pernah menggunakan layanan informasi publik PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin, silakan luangkan waktu sejenak untuk mengisi kuesioner pada tautan di bawah ini.";
$teks_pengantar = $settings['survei_teks_pengantar'] ?? $default_pengantar;

// =========================================================================
// 2. HITUNG REAL-TIME IKM & JUMLAH RESPONDEN DARI TABEL survei_responden
// =========================================================================
$total_responden = 0; 
$nilai_ikm = 0;
$kategori = "Belum Ada Data";
$bintang = "⭐⭐⭐";

$cek_tabel = $conn->query("SHOW TABLES LIKE 'survei_responden'");
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    $q_stat = $conn->query("SELECT COUNT(*) as total, AVG((q1 + q2 + q3) / 3) as rata_rata FROM survei_responden");
    if ($q_stat && $row_stat = $q_stat->fetch_assoc()) {
        $total_responden = (int)$row_stat['total'];
        if ($total_responden > 0 && $row_stat['rata_rata'] !== null) {
            // Formula IKM standar: Rata-rata skor (skala 1-4) dikali 25 = skala 0-100
            $nilai_ikm = (float)$row_stat['rata_rata'] * 25;
        }
    }
}

// Penentuan Kategori Mutu Pelayanan Berdasarkan Nilai IKM
if ($total_responden > 0) {
    if ($nilai_ikm >= 88.31) {
        $kategori = "SANGAT BAIK";
        $bintang = "⭐⭐⭐⭐⭐";
    } elseif ($nilai_ikm >= 76.61) {
        $kategori = "BAIK";
        $bintang = "⭐⭐⭐⭐";
    } elseif ($nilai_ikm >= 65.00) {
        $kategori = "KURANG BAIK";
        $bintang = "⭐⭐⭐";
    } else {
        $kategori = "TIDAK BAIK";
        $bintang = "⭐⭐";
    }
}
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN SURVEI LAYANAN
       ========================================================= */
    .survei-wrapper {
        max-width: 1000px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        display: flex;
        flex-direction: column;
        gap: 35px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    /* Kartu IKM (Indeks Kepuasan Masyarakat) Modern */
    .ikm-card {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #00796b 100%);
        color: white;
        border-radius: 20px;
        padding: 50px 35px;
        text-align: center;
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.2);
        position: relative;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .ikm-card::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .ikm-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 45px rgba(0, 178, 169, 0.3);
    }

    .ikm-title {
        font-size: 1.1rem;
        font-weight: 800;
        margin-bottom: 18px;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        opacity: 0.9;
    }

    .ikm-score {
        font-size: 4.8rem;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 12px;
        text-shadow: 0 4px 15px rgba(0,0,0,0.15);
        color: var(--hover-color, #ef8a1c);
    }

    .ikm-category {
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        margin-bottom: 20px;
        background: rgba(0, 0, 0, 0.12);
        display: inline-block;
        padding: 8px 24px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .ikm-respondents {
        font-size: 0.95rem;
        opacity: 0.9;
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(5px);
        display: inline-block;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
    }

    /* Kartu Ajakan Survei (CTA) Modern */
    .cta-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 45px;
        text-align: center;
        border: 1px solid #eaeaea;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .cta-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
        background: linear-gradient(90deg, var(--hover-color, #ef8a1c), #d97706);
    }

    .cta-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(239, 138, 28, 0.12);
        border-color: #d1d5db;
    }

    .cta-card h2 {
        font-size: 1.45rem;
        color: #1f2937;
        font-weight: 800;
        margin-bottom: 15px;
        letter-spacing: 0.3px;
    }

    .cta-card p {
        font-size: 1.02rem;
        color: #4b5563;
        line-height: 1.75;
        margin-bottom: 35px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    .btn-survei {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        text-decoration: none;
        font-size: 1.05rem;
        font-weight: 700;
        padding: 15px 35px;
        border-radius: 14px;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 6px 20px rgba(0, 178, 169, 0.3);
    }

    .btn-survei:hover {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(239, 138, 28, 0.35);
        color: white;
    }

    @media (max-width: 768px) {
        .ikm-score { font-size: 3.8rem; }
        .cta-card { padding: 30px 20px; }
        .ikm-category { font-size: 1.1rem; padding: 6px 16px; }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Survei Layanan PPID</h1>
        <p>Indeks Kepuasan Masyarakat (IKM) dan Form Penilaian Layanan</p>
    </section>

    <div class="survei-wrapper">
        
        <!-- KARTU IKM (REAL-TIME DARI DATABASE) -->
        <div class="ikm-card">
            <div class="ikm-title">Indeks Kepuasan Masyarakat Saat Ini</div>
            
            <div class="ikm-score"><?php echo $total_responden > 0 ? number_format($nilai_ikm, 2) : '0'; ?></div>
            
            <div class="ikm-category">
                MUTU PELAYANAN: <?php echo $kategori; ?><br>
                <span style="font-size: 1.4rem; margin-top: 8px; display: inline-block; letter-spacing: 4px;"><?php echo $bintang; ?></span>
            </div>
            
            <div>
                <div class="ikm-respondents">
                    Berdasarkan penilaian dari <strong><?php echo $total_responden; ?> responden</strong>
                </div>
            </div>
        </div>

        <!-- KARTU AJAKAN MENGISI SURVEI (DINAMIS DARI ADMIN) -->
        <div class="cta-card">
            <h2>Bantu Kami Menjadi Lebih Baik 📈</h2>
            <p>
                <?php echo nl2br(htmlspecialchars($teks_pengantar)); ?>
            </p>
            
            <a href="form_survei.php" class="btn-survei">
                <span>📝</span> Isi Survei Layanan Sekarang &raquo;
            </a>
        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>