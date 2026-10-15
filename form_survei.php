<!-- form_survei.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// 1. AMBIL PENGATURAN DROPDOWN DAN PERTANYAAN DARI DATABASE
// =========================================================================
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan IN ('survei_jenis_layanan', 'survei_q1', 'survei_q2', 'survei_q3')");
if ($query_set) {
    while($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Opsi Layanan Default jika belum diatur admin
$def_layanan = "Permohonan Informasi Publik\nPengajuan Keberatan Informasi\nKonsultasi Layanan PPID";
$raw_layanan = $settings['survei_jenis_layanan'] ?? $def_layanan;
$array_layanan = array_filter(array_map('trim', explode("\n", $raw_layanan)));

// Pertanyaan Kuesioner Default
$q1_text = $settings['survei_q1'] ?? "Bagaimana pendapat Anda tentang kesesuaian persyaratan pelayanan dengan jenis pelayanannya?";
$q2_text = $settings['survei_q2'] ?? "Bagaimana pendapat Anda tentang kecepatan waktu dalam memberikan pelayanan informasi?";
$q3_text = $settings['survei_q3'] ?? "Bagaimana pendapat Anda tentang sikap, kesopanan, dan keramahan petugas pelayanan?";

// =========================================================================
// 2. PROSES PENYIMPANAN RESPONDEN KE DATABASE
// =========================================================================
$pesan_sukses = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama    = mysqli_real_escape_string($conn, $_POST['nama']);
    $kontak  = mysqli_real_escape_string($conn, $_POST['kontak']);
    $layanan = mysqli_real_escape_string($conn, $_POST['layanan']);
    $q1      = (int)$_POST['q1'];
    $q2      = (int)$_POST['q2'];
    $q3      = (int)$_POST['q3'];
    $saran   = mysqli_real_escape_string($conn, $_POST['saran']);

    // Pastikan tabel survei_responden ada
    $conn->query("CREATE TABLE IF NOT EXISTS survei_responden (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(150) NOT NULL,
        kontak VARCHAR(100),
        layanan VARCHAR(150) NOT NULL,
        q1 INT NOT NULL,
        q2 INT NOT NULL,
        q3 INT NOT NULL,
        saran TEXT,
        tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $insert = $conn->query("INSERT INTO survei_responden (nama, kontak, layanan, q1, q2, q3, saran) VALUES ('$nama', '$kontak', '$layanan', $q1, $q2, $q3, '$saran')");
    
    if ($insert) {
        $pesan_sukses = true;
    }
}
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN FORMULIR SURVEI
       ========================================================= */
    .form-wrapper { 
        max-width: 900px; 
        margin: 50px auto 90px auto; 
        padding: 0 20px; 
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .form-card { 
        background: #ffffff; 
        border-radius: 20px; 
        padding: 45px; 
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.04); 
        border: 1px solid #eaeaea; 
        position: relative; 
        overflow: hidden; 
    }

    .form-card::before { 
        content: ''; 
        position: absolute; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 6px; 
        background: linear-gradient(90deg, var(--primary, #00b2a9), #008f88); 
    }

    .alert-success { 
        background: #f0fdf4; 
        color: #166534; 
        padding: 30px; 
        border-radius: 16px; 
        margin-bottom: 30px; 
        border: 1px solid #bbf7d0; 
        text-align: center; 
        box-shadow: 0 4px 15px rgba(22, 101, 52, 0.05);
    }

    .alert-success h3 { 
        margin: 0 0 10px 0; 
        color: #15803d; 
        font-size: 1.5rem;
        font-weight: 800;
    }

    .form-group { 
        margin-bottom: 25px; 
    }

    .form-label { 
        display: block; 
        font-weight: 700; 
        margin-bottom: 10px; 
        color: #1f2937; 
        font-size: 0.95rem;
    }

    .form-label span.required { 
        color: #ef4444; 
    }

    .form-control { 
        width: 100%; 
        padding: 14px 18px; 
        border: 1px solid #d1d5db; 
        border-radius: 12px; 
        font-size: 1rem; 
        font-family: inherit; 
        transition: all 0.3s ease; 
        box-sizing: border-box; 
        background-color: #f9fafb; 
        color: #1f2937;
    }

    .form-control:focus { 
        outline: none; 
        border-color: var(--primary, #00b2a9); 
        background-color: white; 
        box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.12); 
    }

    .section-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1f2937;
        border-bottom: 2px solid #f3f4f6;
        padding-bottom: 12px;
        margin-top: 40px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .survey-question { 
        background: #f9fafb; 
        padding: 24px; 
        border-radius: 16px; 
        margin-bottom: 20px; 
        border: 1px solid #eaeaea; 
        transition: all 0.3s ease; 
    }

    .survey-question:hover { 
        border-color: #cbd5e1; 
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03); 
        background: #ffffff;
    }

    .question-text { 
        font-weight: 700; 
        color: #374151; 
        margin-bottom: 16px; 
        font-size: 1.02rem;
        line-height: 1.5;
    }

    .rating-options { 
        display: grid; 
        grid-template-columns: repeat(4, 1fr); 
        gap: 12px; 
    }

    .rating-label { 
        display: flex; 
        flex-direction: column;
        align-items: center; 
        justify-content: center;
        gap: 8px; 
        cursor: pointer; 
        font-size: 0.9rem; 
        font-weight: 600;
        color: #4b5563; 
        background: #ffffff;
        border: 1px solid #e5e7eb;
        padding: 12px 10px;
        border-radius: 12px;
        transition: all 0.25s ease;
        text-align: center;
    }

    .rating-label:hover {
        border-color: var(--primary, #00b2a9);
        color: var(--primary, #00b2a9);
        background: #f0fdfa;
    }

    .rating-label input[type="radio"] { 
        accent-color: var(--primary, #00b2a9); 
        width: 18px; 
        height: 18px; 
        cursor: pointer; 
    }

    .btn-submit { 
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%); 
        color: white; 
        border: none; 
        padding: 16px 40px; 
        border-radius: 14px; 
        font-size: 1.05rem; 
        font-weight: 700; 
        cursor: pointer; 
        width: 100%; 
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); 
        box-shadow: 0 6px 20px rgba(0, 178, 169, 0.25); 
    }

    .btn-submit:hover { 
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%); 
        transform: translateY(-2px); 
        box-shadow: 0 8px 25px rgba(239, 138, 28, 0.35); 
    }

    .btn-kembali { 
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 25px; 
        color: #4b5563; 
        text-decoration: none; 
        font-weight: 700; 
        transition: color 0.3s ease; 
    }

    .btn-kembali:hover { 
        color: var(--primary, #00b2a9); 
    }

    @media (max-width: 768px) { 
        .form-card { padding: 30px 20px; } 
        .rating-options { grid-template-columns: repeat(2, 1fr); } 
    }
</style>

<main>
    <section class="inner-header">
        <h1>Formulir Survei Layanan</h1>
        <p>Bantu kami meningkatkan kualitas pelayanan informasi publik</p>
    </section>

    <div class="form-wrapper">
        <div class="form-card">
            
            <?php if ($pesan_sukses): ?>
                <div class="alert-success">
                    <h3>✅ Terima Kasih!</h3>
                    <p>Penilaian dan masukan Anda telah berhasil kami simpan dan langsung memperbarui Indeks Kepuasan Masyarakat (IKM).</p>
                </div>
                <div style="text-align: center;">
                    <a href="survei_layanan.php" class="btn-submit" style="text-decoration: none; display: inline-block; width: auto;">Kembali ke Halaman Survei</a>
                </div>
            <?php else: ?>

                <form action="" method="POST">
                    
                    <div class="section-title">
                        <span>👤</span> Data Responden
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama Anda" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor WhatsApp / Email</label>
                        <input type="text" name="kontak" class="form-control" placeholder="Opsional">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jenis Layanan yang Diterima <span class="required">*</span></label>
                        <select name="layanan" class="form-control" required>
                            <option value="">-- Pilih Jenis Layanan --</option>
                            <?php foreach ($array_layanan as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="section-title" style="margin-top: 45px;">
                        <span>⭐</span> Penilaian Kualitas Layanan
                    </div>
                    <p style="color: #4b5563; font-size: 0.95rem; margin-bottom: 25px; line-height: 1.6;">Silakan berikan penilaian Anda dengan memilih salah satu opsi dari <strong>Sangat Buruk (1)</strong> hingga <strong>Sangat Baik (4)</strong>.</p>

                    <!-- Pertanyaan 1 -->
                    <div class="survey-question">
                        <div class="question-text">1. <?php echo htmlspecialchars($q1_text); ?> <span class="required">*</span></div>
                        <div class="rating-options">
                            <label class="rating-label"><input type="radio" name="q1" value="1" required> <span>Sangat Buruk</span> <strong>(1)</strong></label>
                            <label class="rating-label"><input type="radio" name="q1" value="2"> <span>Buruk</span> <strong>(2)</strong></label>
                            <label class="rating-label"><input type="radio" name="q1" value="3"> <span>Baik</span> <strong>(3)</strong></label>
                            <label class="rating-label"><input type="radio" name="q1" value="4"> <span>Sangat Baik</span> <strong>(4)</strong></label>
                        </div>
                    </div>

                    <!-- Pertanyaan 2 -->
                    <div class="survey-question">
                        <div class="question-text">2. <?php echo htmlspecialchars($q2_text); ?> <span class="required">*</span></div>
                        <div class="rating-options">
                            <label class="rating-label"><input type="radio" name="q2" value="1" required> <span>Sangat Buruk</span> <strong>(1)</strong></label>
                            <label class="rating-label"><input type="radio" name="q2" value="2"> <span>Buruk</span> <strong>(2)</strong></label>
                            <label class="rating-label"><input type="radio" name="q2" value="3"> <span>Baik</span> <strong>(3)</strong></label>
                            <label class="rating-label"><input type="radio" name="q2" value="4"> <span>Sangat Baik</span> <strong>(4)</strong></label>
                        </div>
                    </div>

                    <!-- Pertanyaan 3 -->
                    <div class="survey-question">
                        <div class="question-text">3. <?php echo htmlspecialchars($q3_text); ?> <span class="required">*</span></div>
                        <div class="rating-options">
                            <label class="rating-label"><input type="radio" name="q3" value="1" required> <span>Sangat Buruk</span> <strong>(1)</strong></label>
                            <label class="rating-label"><input type="radio" name="q3" value="2"> <span>Buruk</span> <strong>(2)</strong></label>
                            <label class="rating-label"><input type="radio" name="q3" value="3"> <span>Baik</span> <strong>(3)</strong></label>
                            <label class="rating-label"><input type="radio" name="q3" value="4"> <span>Sangat Baik</span> <strong>(4)</strong></label>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 30px;">
                        <label class="form-label">Saran dan Masukan (Opsional)</label>
                        <textarea name="saran" class="form-control" rows="4" placeholder="Tuliskan kritik, saran, atau masukan Anda di sini..."></textarea>
                    </div>

                    <div style="margin-top: 40px;">
                        <button type="submit" class="btn-submit">Kirim Penilaian 🚀</button>
                    </div>

                </form>
            <?php endif; ?>

        </div>
        
        <div>
            <a href="survei_layanan.php" class="btn-kembali">&laquo; Kembali ke Halaman Survei</a>
        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>