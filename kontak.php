<!-- kontak.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// 1. AMBIL DATA KONTAK & GOOGLE MAPS DARI DATABASE (Tabel pengaturan_web)
// =========================================================================
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan LIKE 'kontak_%'");
if ($query_set) {
    while ($row = $query_set->fetch_assoc()) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
}

// Nilai default jika belum diatur di admin
$kontak_instansi = $settings['kontak_instansi'] ?? "PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin";
$kontak_alamat   = $settings['kontak_alamat'] ?? "Jl. P.M. Noor No.1, Pelambuan, Kec. Banjarmasin Barat, Kota Banjarmasin, Kalimantan Selatan 70119";
$kontak_telepon  = $settings['kontak_telepon'] ?? "(0511) 1234567";
$kontak_email    = $settings['kontak_email'] ?? "ppid@bkkbanjarmasin.go.id";
$kontak_map_url  = $settings['kontak_map_url'] ?? "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.136894056262!2d114.57502431475713!3d-3.316829997585094!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de423d2427f7db5%3A0x8035fb14197c36a4!2sKantor%20Kesehatan%20Pelabuhan%20(KKP)%20Kelas%20II%20Banjarmasin!5e0!3m2!1sid!2sid!4v1675200000000!5m2!1sid!2sid";

// =========================================================================
// 2. PROSES KIRIM PESAN DARI PENGUNJUNG KE DATABASE
// =========================================================================
$pesan_terkirim = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['kirim_pesan'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);

    // Cek apakah tabel pesan_masuk sudah memiliki kolom 'nama', jika belum buat ulang tabelnya
    $cek_kolom = $conn->query("SHOW COLUMNS FROM pesan_masuk LIKE 'nama'");
    if (!$cek_kolom || $cek_kolom->num_rows == 0) {
        $conn->query("DROP TABLE IF EXISTS pesan_masuk");
    }

    // Buat tabel pesan_masuk dengan struktur lengkap secara otomatis
    $conn->query("CREATE TABLE IF NOT EXISTS pesan_masuk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        judul VARCHAR(200) NOT NULL,
        pesan TEXT NOT NULL,
        tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $insert = $conn->query("INSERT INTO pesan_masuk (nama, email, judul, pesan) VALUES ('$nama', '$email', '$judul', '$pesan')");
    if ($insert) {
        $pesan_terkirim = true;
    }
}
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN KONTAK KAMI
       ========================================================= */
    .kontak-wrapper {
        max-width: 1100px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 30px;
        align-items: start;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .kontak-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.04);
        border: 1px solid #eaeaea;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        margin-bottom: 25px;
        position: relative;
    }

    .kontak-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.1);
        border-color: #d1d5db;
    }

    .k-card-header {
        background: #f8fafc;
        padding: 22px 28px;
        display: flex;
        align-items: center;
        gap: 16px;
        border-bottom: 1px solid #f0f0f0;
    }

    .k-header-icon {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        box-shadow: 0 6px 18px rgba(0, 178, 169, 0.25);
        transition: all 0.35s ease;
        flex-shrink: 0;
    }

    .kontak-card:hover .k-header-icon {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        box-shadow: 0 6px 18px rgba(239, 138, 28, 0.35);
        transform: scale(1.05) rotate(3deg);
    }

    .k-header-text h3 {
        margin: 0 0 4px 0;
        font-size: 1.15rem;
        color: #1f2937;
        font-weight: 800;
        letter-spacing: 0.3px;
    }

    .k-header-text p {
        margin: 0;
        font-size: 0.88rem;
        color: #6b7280;
        line-height: 1.4;
    }

    .k-form-body {
        padding: 35px 28px;
    }

    .form-group {
        margin-bottom: 22px;
    }

    .form-label {
        display: block;
        font-size: 0.92rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 14px 18px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        background-color: #f9fafb;
        font-size: 0.98rem;
        font-family: inherit;
        transition: all 0.3s ease;
        box-sizing: border-box;
        color: #1f2937;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary, #00b2a9);
        background-color: white;
        box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.12);
    }

    .btn-kirim {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        border: none;
        width: 100%;
        padding: 15px;
        border-radius: 12px;
        font-size: 1.02rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 6px 20px rgba(0, 178, 169, 0.25);
    }

    .btn-kirim:hover {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        box-shadow: 0 8px 25px rgba(239, 138, 28, 0.35);
        transform: translateY(-2px);
    }

    .alert-success {
        background-color: #f0fdf4;
        color: #166534;
        padding: 18px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        border: 1px solid #bbf7d0;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(22, 101, 52, 0.05);
    }

    .k-info-body {
        padding: 25px 28px;
    }

    .info-list-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid #f3f4f6;
        transition: transform 0.3s ease;
    }

    .info-list-item:hover {
        transform: translateX(4px);
    }

    .info-list-item:last-child {
        border-bottom: none;
    }

    .i-icon {
        background: #f0fdfa;
        color: var(--primary, #00b2a9);
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
        border: 1px solid #ccfbf1;
        transition: all 0.3s ease;
    }

    .info-list-item:hover .i-icon {
        background: var(--hover-color, #ef8a1c);
        color: white;
        border-color: transparent;
    }

    .i-icon.orange-def {
        background: #fff7ed;
        color: var(--hover-color, #ef8a1c);
        border-color: #ffedd5;
    }

    .info-list-item:hover .i-icon.orange-def {
        background: var(--primary, #00b2a9);
        color: white;
        border-color: transparent;
    }

    .i-text-wrap {
        flex-grow: 1;
    }

    .i-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    .i-value {
        font-size: 0.98rem;
        color: #1f2937;
        font-weight: 600;
        line-height: 1.6;
    }

    .map-container {
        padding: 18px;
    }
    
    .map-container iframe {
        border-radius: 12px;
        width: 100%;
        height: 270px;
        display: block;
        border: 1px solid #e5e7eb;
    }

    @media (max-width: 768px) {
        .kontak-wrapper {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .k-form-body { padding: 25px 20px; }
        .k-info-body { padding: 20px; }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Kontak Kami</h1>
        <p>Hubungi PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin</p>
    </section>

    <div class="kontak-wrapper">
        
        <!-- KOLOM KIRI: FORM KIRIM PESAN -->
        <div class="kontak-form-col">
            <div class="kontak-card">
                
                <div class="k-card-header">
                    <div class="k-header-icon">💬</div>
                    <div class="k-header-text">
                        <h3>Kirim Pesan</h3>
                        <p>Isi formulir di bawah untuk mengirimkan pesan</p>
                    </div>
                </div>

                <div class="k-form-body">
                    <?php if($pesan_terkirim): ?>
                        <div class="alert-success">
                            <strong>✅ Berhasil!</strong> Pesan Anda telah terkirim ke sistem kami.
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <input type="hidden" name="kirim_pesan" value="1">
                        
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap Anda" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Masukkan email Anda" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Judul Pesan</label>
                            <input type="text" name="judul" class="form-control" placeholder="Masukkan judul pesan Anda" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Isi Pesan</label>
                            <textarea name="pesan" class="form-control" rows="5" placeholder="Tuliskan isi pesan Anda" required></textarea>
                        </div>

                        <button type="submit" class="btn-kirim">
                            <span>🚀</span> Kirim Pesan
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <!-- KOLOM KANAN: INFO KONTAK & MAPS -->
        <div class="kontak-info-col">
            
            <!-- KARTU INFORMASI KONTAK -->
            <div class="kontak-card">
                <div class="k-card-header">
                    <div class="k-header-icon">📍</div>
                    <div class="k-header-text">
                        <h3>Informasi Kontak</h3>
                        <p><?php echo htmlspecialchars($kontak_instansi); ?></p>
                    </div>
                </div>

                <div class="k-info-body">
                    
                    <div class="info-list-item">
                        <div class="i-icon">🏢</div>
                        <div class="i-text-wrap">
                            <div class="i-label">Alamat</div>
                            <div class="i-value"><?php echo htmlspecialchars($kontak_alamat); ?></div>
                        </div>
                    </div>

                    <div class="info-list-item">
                        <div class="i-icon orange-def">📞</div>
                        <div class="i-text-wrap">
                            <div class="i-label">Telepon</div>
                            <div class="i-value"><?php echo htmlspecialchars($kontak_telepon); ?></div>
                        </div>
                    </div>

                    <div class="info-list-item">
                        <div class="i-icon orange-def">✉️</div>
                        <div class="i-text-wrap">
                            <div class="i-label">Email</div>
                            <div class="i-value"><?php echo htmlspecialchars($kontak_email); ?></div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- KARTU LOKASI KAMI (MAPS DINAMIS) -->
            <div class="kontak-card">
                <div class="k-card-header">
                    <div class="k-header-icon">🗺️</div>
                    <div class="k-header-text">
                        <h3>Lokasi Kami</h3>
                        <p><?php echo htmlspecialchars($kontak_instansi); ?></p>
                    </div>
                </div>
                
                <div class="map-container">
                    <iframe 
                        src="<?php echo htmlspecialchars($kontak_map_url); ?>" 
                        frameborder="0" 
                        style="border:0;" 
                        allowfullscreen="" 
                        aria-hidden="false" 
                        tabindex="0">
                    </iframe>
                </div>
            </div>

        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>