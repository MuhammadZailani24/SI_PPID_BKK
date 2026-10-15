<!-- index.php -->
<?php 
require_once 'includes/koneksi.php';

// =========================================================================
// Fungsi untuk mengubah tanggal menjadi Format Bahasa Indonesia
// =========================================================================
function tgl_indo_berita($tanggal) {
    $hari_array = array(
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    );
    $bulan_array = array(
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    );
    
    // Ambil nama hari bahasa inggris lalu konversi ke array indonesia
    $hari = $hari_array[date('l', strtotime($tanggal))];
    $tgl = date('d', strtotime($tanggal));
    $bln = (int)date('m', strtotime($tanggal));
    $thn = date('Y', strtotime($tanggal));
    
    // Mengembalikan hasil dengan format HURUF KAPITAL
    return strtoupper($hari . ', ' . $tgl . ' ' . $bulan_array[$bln] . ' ' . $thn);
}

// 1. AMBIL DATA KHUSUS SLIDER ATAS
$data_slider = [];
$query_slider = $conn->query("SELECT * FROM slider_beranda ORDER BY id DESC LIMIT 5");

if ($query_slider && $query_slider->num_rows > 0) {
    while ($row = $query_slider->fetch_assoc()) {
        $data_slider[] = [
            "gambar" => !empty($row['gambar']) ? $row['gambar'] : 'assets/img/default.jpg', 
            "judul"  => $row['judul'] ?? 'Informasi Publik',
            "desk"   => $row['deskripsi'] ?? ''
        ];
    }
}

if (empty($data_slider)) {
    $data_slider = [
        [
            "gambar" => "assets/img/slider-1.jpg", 
            "judul"  => "Gedung Kantor BKK Kelas 1 Banjarmasin",
            "desk"   => "Pelayanan prima kesehatan pelabuhan dan lintas wilayah."
        ]
    ];
}

// 2. AMBIL DATA KHUSUS BERITA & INFORMASI
$query_berita = $conn->query("SELECT * FROM berita WHERE status = 'Published' ORDER BY id DESC LIMIT 4");

include 'includes/header.php'; 
?>

<style>
    .hero-container {
        display: grid;
        grid-template-columns: 1.2fr 1fr; 
        gap: 40px;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }

    .hero-slider-box {
        background-color: rgba(255, 255, 255, 0.15);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 16px;
        padding: 15px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        position: relative;
    }

    .carousel-container {
        position: relative;
        width: 100%;
        height: 300px; 
        border-radius: 10px;
        overflow: hidden;
    }

    .carousel-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 0.8s ease-in-out;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .carousel-slide.active {
        opacity: 1;
        z-index: 2;
    }

    /* Gambar Slide Otomatis Memenuhi Ruangan (Cover) */
    .carousel-slide img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .carousel-caption {
        position: relative;
        z-index: 3;
        background: linear-gradient(to top, rgba(0,0,0,0.85), transparent);
        color: white;
        padding: 25px 15px 15px 15px;
        text-align: left;
    }

    .carousel-caption h5 {
        font-size: 1rem;
        font-weight: bold;
        margin: 0 0 3px 0;
        color: var(--hover-color, #ef8a1c);
    }

    .carousel-caption p {
        font-size: 0.8rem;
        margin: 0;
        opacity: 0.9;
    }

    .carousel-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        border: none;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: background 0.3s;
    }

    .carousel-btn:hover { background-color: var(--hover-color, #ef8a1c); }
    .carousel-btn.prev { left: 10px; }
    .carousel-btn.next { right: 10px; }

    .carousel-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 12px;
    }

    .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.4);
        cursor: pointer;
        transition: background 0.3s;
    }

    .dot.active {
        background-color: var(--hover-color, #ef8a1c);
        width: 25px;
        border-radius: 5px;
    }

    /* =========================================================
       STYLING LIGHTBOX / ZOOM GAMBAR BERITA
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

    /* =========================================================
       STYLING CARD BERITA TERBARU
       ========================================================= */
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
        box-shadow: 0 12px 25px rgba(0,0,0,0.08);
        border-color: #d1d5db;
    }

    .news-img {
        width: 100%; 
        height: 180px; 
        object-fit: cover; 
        cursor: pointer; 
        transition: opacity 0.3s;
        border-bottom: 1px solid #f3f4f6;
    }

    .news-img:hover {
        opacity: 0.9;
    }

    .news-content {
        padding: 20px 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .news-date {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 8px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .news-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #1f2937;
        line-height: 1.4;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clam: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Tombol Baca Selengkapnya */
    .btn-baca-selengkapnya {
        margin-top: auto;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #ffffff;
        background-color: #00b2a9;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        width: max-content;
        transition: all 0.2s ease;
    }

    .btn-baca-selengkapnya:hover {
        background-color: #008f88;
        transform: translateX(4px);
        box-shadow: 0 4px 10px rgba(0, 178, 169, 0.2);
        color: #ffffff;
    }

    .btn-baca-selengkapnya::after {
        content: '→';
        font-size: 1.1rem;
        font-weight: bold;
    }

    @media (max-width: 900px) {
        .hero-container {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        .carousel-container {
            height: 240px;
        }
    }
</style>

<main>
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-info">
                <h4>Selamat Datang di Portal</h4>
                <h1>PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin</h1>
                
                <div class="contact-info-grid">
                    <div class="info-item">
                        <div class="info-icon">📅</div>
                        <div class="info-text">
                            <strong>Waktu Pelayanan</strong>
                            <span>Senin s/d Kamis, Pukul 07:30 - 16:00 WITA<br>Jumat, Pukul 07:30 - 16:30 WITA</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">📍</div>
                        <div class="info-text">
                            <strong>Ruang Pelayanan Informasi</strong>
                            <span>Jl.Belitung Darat No.118A, Belitung Selatan,Kecamatan Banjarmasin Barat, Kalimantan Selatan</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">📞</div>
                        <div class="info-text">
                            <strong>Kontak & Email</strong>
                            <span>(0511) 3353948 | balaikarkesbanjarmasin@kemkes.go.id</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-slider-box">
                <div class="carousel-container" id="heroCarousel">
                    <?php 
                    $no = 0;
                    foreach($data_slider as $slide) { 
                        $active_class = ($no == 0) ? 'active' : '';
                        $gambar_url = htmlspecialchars($slide['gambar']);
                    ?>
                        <div class="carousel-slide <?php echo $active_class; ?>">
                            <!-- Foto Utama (Cover penuh) -->
                            <img src="<?php echo $gambar_url; ?>" alt="Slider Image" onerror="this.src='https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80'">
                            
                            <div class="carousel-caption">
                                <h5><?php echo htmlspecialchars($slide['judul']); ?></h5>
                                <p><?php echo htmlspecialchars($slide['desk']); ?></p>
                            </div>
                        </div>
                    <?php 
                        $no++;
                    } 
                    ?>
                    <button class="carousel-btn prev" onclick="moveSlide(-1)">&#10094;</button>
                    <button class="carousel-btn next" onclick="moveSlide(1)">&#10095;</button>
                </div>
                <div class="carousel-dots" id="carouselDots">
                    <?php for($i = 0; $i < count($data_slider); $i++) { ?>
                        <span class="dot <?php echo ($i == 0) ? 'active' : ''; ?>" onclick="currentSlide(<?php echo $i; ?>)"></span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>

    <!-- FLOATING ACTIONS -->
    <div class="floating-actions">
        <a href="form_permohonan.php" class="main-action-card">
            <div class="main-action-icon">📝</div>
            <h3>Form Permohonan<br>Informasi Publik</h3>
        </a>
        
        <a href="form_keberatan.php" class="main-action-card">
            <div class="main-action-icon">⚖️</div>
            <h3>Form Pengajuan<br>Keberatan</h3>
        </a>
    </div>

    <!-- AKSES CEPAT (SERVICES GRID) -->
    <section class="services-section">
        <h2 class="section-title">Layanan Informasi Publik</h2>
        <p class="section-subtitle">Akses cepat ke layanan utama — transparan, akuntabel, dan mengutamakan hak pemohon informasi.</p>
        
        <div class="services-grid">
            <a href="informasi_publik.php" class="service-card">
                <div class="service-icon-wrapper">🗂️</div>
                <h4>Daftar<br>Informasi Publik</h4>
            </a>
            
            <a href="cek_status.php?jenis_layanan=permohonan" class="service-card">
                <div class="service-icon-wrapper">🔍</div>
                <h4>Cek Status<br>Permohonan Informasi Publik</h4>
            </a>
            
            <a href="cek_status.php?jenis_layanan=keberatan" class="service-card">
                <div class="service-icon-wrapper">🔎</div>
                <h4>Cek Status<br>Pengajuan Keberatan</h4>
            </a>
            
            <a href="statistik_layanan.php" class="service-card">
                <div class="service-icon-wrapper">📊</div>
                <h4>Statistik<br>Layanan Informasi Publik</h4>
            </a>
            
            <a href="kontak.php" class="service-card">
                <div class="service-icon-wrapper">📢</div>
                <h4>Kanal<br>Pengaduan Resmi</h4>
            </a>
        </div>
    </section>

    <!-- BERITA & INFORMASI PUBLIK -->
    <section class="news-section">
        <div class="news-header">
            <h2>Berita & Informasi Publik</h2>
            <a href="berita.php" class="btn-lihat-semua">Lihat Semua &raquo;</a>
        </div>
        
        <div class="news-grid">
            <?php 
            if ($query_berita && $query_berita->num_rows > 0):
                while($b = $query_berita->fetch_assoc()):
                    $thumbnail = !empty($b['thumbnail']) ? $b['thumbnail'] : 'assets/img/default.jpg';
                    $tanggal_indo = tgl_indo_berita($b['created_at']);
                    $judul_berita = htmlspecialchars($b['judul']);
                    $id_berita = $b['id'];
            ?>
                <div class="news-card">
                    <img src="<?php echo htmlspecialchars($thumbnail); ?>" 
                         alt="Thumbnail" 
                         class="news-img" 
                         onclick="openLightbox('<?php echo htmlspecialchars($thumbnail); ?>', '<?php echo addslashes($judul_berita); ?>')"
                         onerror="this.src='https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=400&q=80'">
                    
                    <div class="news-content">
                        <div class="news-date"><?php echo $tanggal_indo; ?></div>
                        <div class="news-title"><?php echo $judul_berita; ?></div>
                        
                        <a href="detail_berita.php?id=<?php echo $id_berita; ?>" class="btn-baca-selengkapnya">
                            Baca Selengkapnya
                        </a>
                    </div>
                </div>
            <?php 
                endwhile; 
            else:
            ?>
                <p style="grid-column: 1 / -1; text-align: center; color: #6b7280; padding: 20px;">Belum ada berita atau informasi publik yang dipublikasikan.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- MODAL LIGHTBOX UNTUK ZOOM GAMBAR -->
<div id="imageModal" class="image-modal" onclick="closeLightbox()">
    <span class="modal-close">&times;</span>
    <img class="modal-content-img" id="lightboxImg">
    <div id="lightboxCaption"></div>
</div>

<script>
// Slider Hero / Carousel
let slideIndex = 0;
const slides = document.querySelectorAll('.carousel-slide');
const dots = document.querySelectorAll('.dot');

function showSlide(index) {
    if (slides.length === 0) return;
    if (index >= slides.length) { slideIndex = 0; }
    if (index < 0) { slideIndex = slides.length - 1; }

    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));

    if (slides[slideIndex]) slides[slideIndex].classList.add('active');
    if (dots[slideIndex]) dots[slideIndex].classList.add('active');
}

function moveSlide(n) {
    slideIndex += n;
    showSlide(slideIndex);
}

function currentSlide(n) {
    slideIndex = n;
    showSlide(slideIndex);
}

if (slides.length > 1) {
    setInterval(function() {
        slideIndex++;
        showSlide(slideIndex);
    }, 4000);
}

// Fungsi Lightbox / Perbesar Gambar Berita
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