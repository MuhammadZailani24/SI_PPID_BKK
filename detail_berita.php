<!-- detail_berita.php -->
<?php 
require_once 'includes/koneksi.php';

// =========================================================================
// Fungsi untuk mengubah tanggal menjadi Format Bahasa Indonesia (Lengkap dengan Jam)
// =========================================================================
function tgl_indo_lengkap($tanggal) {
    $hari_array = array(
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    );
    $bulan_array = array(
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    );
    
    // Ambil komponen tanggal dan waktu
    $hari = $hari_array[date('l', strtotime($tanggal))];
    $tgl = date('d', strtotime($tanggal));
    $bln = (int)date('m', strtotime($tanggal));
    $thn = date('Y', strtotime($tanggal));
    $jam = date('H:i', strtotime($tanggal));
    
    // Hasil: "Senin, 28 September 2026 - 14:30"
    return $hari . ', ' . $tgl . ' ' . $bulan_array[$bln] . ' ' . $thn . ' - ' . $jam;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data berita berdasarkan ID
$query = $conn->query("SELECT * FROM berita WHERE id = $id AND status = 'Published'");
if (!$query || $query->num_rows == 0) {
    // Jika berita tidak ditemukan, kembalikan ke halaman berita
    header("Location: berita.php");
    exit;
}

$b = $query->fetch_assoc();
$thumbnail = !empty($b['thumbnail']) ? $b['thumbnail'] : 'assets/img/default.jpg';

// Gunakan fungsi tanggal Indonesia lengkap
$tanggal_indo = tgl_indo_lengkap($b['created_at']);

include 'includes/header.php'; 
?>

<style>
    .detail-container {
        max-width: 900px;
        margin: 40px auto 80px auto;
        padding: 0 20px;
        font-family: 'Inter', sans-serif;
    }

    /* Container untuk Tombol Navigasi Kembali */
    .navigasi-kembali {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--primary, #00b2a9);
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid var(--primary, #00b2a9);
        background-color: transparent;
        transition: all 0.3s ease;
    }

    .back-link:hover {
        background-color: var(--primary, #00b2a9);
        color: white;
        box-shadow: 0 4px 10px rgba(0, 178, 169, 0.2);
    }

    .back-link-home {
        border-color: #ef8a1c;
        color: #ef8a1c;
    }

    .back-link-home:hover {
        background-color: #ef8a1c;
        color: white;
        box-shadow: 0 4px 10px rgba(239, 138, 28, 0.2);
    }

    .detail-card {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #eaeaea;
    }

    .detail-title {
        font-size: 2rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 15px;
        line-height: 1.3;
    }

    .detail-date {
        font-size: 0.9rem;
        color: #ef8a1c;
        font-weight: 700;
        margin-bottom: 25px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-img-container {
        width: 100%;
        max-height: 450px;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 30px;
        background: #f3f4f6;
    }

    .detail-img {
        width: 100%;
        height: 100%;
        max-height: 450px;
        object-fit: cover;
    }

    .detail-content {
        font-size: 1.05rem;
        color: #374151;
        line-height: 1.8;
        word-break: break-word;
    }

    .detail-content p {
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .detail-card {
            padding: 20px;
        }
        .detail-title {
            font-size: 1.5rem;
        }
    }
</style>

<main>
    <div class="detail-container">
        
        <!-- Tambahan 2 Tombol Navigasi Kembali -->
        <div class="navigasi-kembali">
            <a href="index.php" class="back-link back-link-home">🏠 Kembali ke Beranda</a>
            <a href="berita.php" class="back-link">&laquo; Arsip Berita</a>
        </div>

        <div class="detail-card">
            <div class="detail-date">📅 Dipublikasikan pada: <?php echo $tanggal_indo; ?> WITA</div>
            <h1 class="detail-title"><?php echo htmlspecialchars($b['judul']); ?></h1>

            <div class="detail-img-container">
                <img src="<?php echo htmlspecialchars($thumbnail); ?>" 
                     alt="Gambar Berita" 
                     class="detail-img" 
                     onerror="this.src='https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80'">
            </div>

            <div class="detail-content">
                <!-- Menampilkan isi lengkap berita -->
                <?php 
                $isi_berita = !empty($b['konten']) ? $b['konten'] : ($b['deskripsi'] ?? 'Tidak ada isi berita.');
                echo nl2br(htmlspecialchars($isi_berita)); 
                ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>