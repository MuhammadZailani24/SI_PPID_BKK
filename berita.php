<!-- berita.php -->
<?php 
require_once 'includes/koneksi.php';
include 'includes/header.php'; 

// =========================================================================
// Fungsi untuk mengubah tanggal menjadi Format Bahasa Indonesia
// =========================================================================
function tgl_indo_berita_arsip($tanggal) {
    $hari_array = array(
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    );
    $bulan_array = array(
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    );
    
    // Ambil komponen tanggal dari database
    $hari = $hari_array[date('l', strtotime($tanggal))];
    $tgl = date('d', strtotime($tanggal));
    $bln = (int)date('m', strtotime($tanggal));
    $thn = date('Y', strtotime($tanggal));
    
    // Hasil: "Senin, 28 September 2026"
    return $hari . ', ' . $tgl . ' ' . $bulan_array[$bln] . ' ' . $thn;
}

// Ambil semua berita yang berstatus Published dari database
$query_berita = $conn->query("SELECT * FROM berita WHERE status = 'Published' ORDER BY id DESC");
?>

<style>
    .berita-container {
        max-width: 1200px;
        margin: 40px auto 80px auto;
        padding: 0 20px;
        font-family: 'Inter', sans-serif;
    }

    .berita-header-title {
        text-align: center;
        margin-bottom: 40px;
    }

    .berita-header-title h1 {
        color: var(--primary, #00b2a9);
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .berita-header-title p {
        color: #6b7280;
        font-size: 1rem;
    }

    .berita-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
    }

    .news-card {
        background: white; 
        border-radius: 12px; 
        overflow: hidden; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        border: 1px solid #eaeaea;
    }

    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .news-img {
        width: 100%; 
        height: 190px; 
        object-fit: cover; 
        cursor: pointer; 
        transition: opacity 0.3s;
    }

    .news-img:hover {
        opacity: 0.9;
    }

    .news-content {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .news-date {
        font-size: 0.8rem;
        color: #ef8a1c;
        font-weight: 700;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .news-title {
        font-size: 1.05rem;
        font-weight: bold;
        color: #1f2937;
        line-height: 1.5;
        margin-bottom: 15px;
    }

    /* Styling Tombol Baca Selengkapnya */
    .btn-baca-selengkapnya {
        margin-top: auto;
        display: inline-block;
        background-color: var(--primary, #00b2a9);
        color: white;
        text-align: center;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.3s ease, transform 0.2s ease;
    }

    .btn-baca-selengkapnya:hover {
        background-color: var(--hover-color, #ef8a1c);
        transform: translateY(-2px);
    }

    /* =========================================================
       STYLING LIGHTBOX / ZOOM GAMBAR
       ========================================================= */
    .image-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.85);
        align-items: center;
        justify-content: center;
        flex-direction: column;
        backdrop-filter: blur(5px);
    }

    .modal-content-img {
        max-width: 90%;
        max-height: 80vh;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        animation: zoomIn 0.3s ease;
        object-fit: contain;
    }

    .modal-close {
        position: absolute;
        top: 25px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }

    .modal-close:hover { color: var(--hover-color, #ef8a1c); }

    #lightboxCaption {
        color: white;
        margin-top: 20px;
        font-size: 1.1rem;
        text-align: center;
        padding: 0 20px;
        font-weight: 600;
        max-width: 800px;
    }

    @keyframes zoomIn {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
</style>

<main>
    <div class="berita-container">
        <div class="berita-header-title">
            <h1>Arsip Berita & Informasi Publik</h1>
            <p>Daftar lengkap pengumuman, kegiatan, dan informasi resmi dari PPID BKK Kelas 1 Banjarmasin.</p>
        </div>

        <div class="berita-grid">
            <?php 
            if ($query_berita && $query_berita->num_rows > 0):
                while($b = $query_berita->fetch_assoc()):
                    $thumbnail = !empty($b['thumbnail']) ? $b['thumbnail'] : 'assets/img/default.jpg';
                    // Gunakan fungsi bahasa indonesia
                    $tanggal_indo = tgl_indo_berita_arsip($b['created_at']);
                    $judul_berita = htmlspecialchars($b['judul']);
                    $id_berita = $b['id'];
            ?>
                <div class="news-card">
                    <!-- Thumbnail bisa diklik untuk memperbesar gambar -->
                    <img src="<?php echo htmlspecialchars($thumbnail); ?>" 
                         alt="Thumbnail Berita" 
                         class="news-img" 
                         onclick="openLightbox('<?php echo htmlspecialchars($thumbnail); ?>', '<?php echo addslashes($judul_berita); ?>')"
                         onerror="this.src='https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=400&q=80'">
                    
                    <div class="news-content">
                        <div class="news-date"><?php echo $tanggal_indo; ?></div>
                        <div class="news-title"><?php echo $judul_berita; ?></div>
                        <!-- Tombol Menuju Detail Berita -->
                        <a href="detail_berita.php?id=<?php echo $id_berita; ?>" class="btn-baca-selengkapnya">Baca Selengkapnya &raquo;</a>
                    </div>
                </div>
            <?php 
                endwhile; 
            else:
            ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: white; border-radius: 12px; border: 1px solid #eaeaea;">
                    <p style="color: #6b7280; font-size: 1.1rem; margin: 0;">Belum ada berita atau informasi publik yang dipublikasikan saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- MODAL LIGHTBOX UNTUK ZOOM GAMBAR -->
<div id="imageModal" class="image-modal" onclick="closeLightbox()">
    <span class="modal-close">&times;</span>
    <img class="modal-content-img" id="lightboxImg">
    <div id="lightboxCaption"></div>
</div>

<script>
function openLightbox(imgSrc, captionText) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('lightboxImg');
    const caption = document.getElementById('lightboxCaption');

    modal.style.display = 'flex';
    modalImg.src = imgSrc;
    caption.innerText = captionText;
}

function closeLightbox() {
    document.getElementById('imageModal').style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>