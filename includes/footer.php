<!-- includes/footer.php -->
<?php
// =========================================================================
// LOGIKA PENCATATAN KUNJUNGAN REAL-TIME (BERBASIS BULAN)
// =========================================================================
if (isset($conn)) {
    $ip_pengunjung = $_SERVER['REMOTE_ADDR'];
    $tanggal_sekarang = date('Y-m-d');
    $bulan_sekarang = date('Y-m');

    // Pastikan tabel statistik_pengunjung ada
    try {
        $conn->query("CREATE TABLE IF NOT EXISTS statistik_pengunjung (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(50),
            tanggal DATE
        )");
    } catch (Exception $e) { }

    // Cek apakah IP ini sudah tercatat berkunjung pada hari ini
    $cek_ip = $conn->query("SELECT id FROM statistik_pengunjung WHERE ip_address = '$ip_pengunjung' AND tanggal = '$tanggal_sekarang'");
    
    // Jika belum ada, catat sebagai kunjungan baru
    if ($cek_ip && $cek_ip->num_rows == 0) {
        $conn->query("INSERT INTO statistik_pengunjung (ip_address, tanggal) VALUES ('$ip_pengunjung', '$tanggal_sekarang')");
    }

    // Hitung total kunjungan KHUSUS pada bulan ini
    $query_total = $conn->query("SELECT COUNT(id) as total_bulan_ini FROM statistik_pengunjung WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_sekarang'");
    $kunjungan_bulan_ini = ($query_total && $query_total->num_rows > 0) ? $query_total->fetch_assoc()['total_bulan_ini'] : 0;
} else {
    $kunjungan_bulan_ini = 0;
}
?>

<style>
    /* =========================================================
       STYLING FOOTER UTAMA MODERN & KEKINIAN
       ========================================================= */
    .site-footer {
        background: linear-gradient(135deg, #009688 0%, #00796b 100%);
        color: white;
        padding: 60px 5% 30px 5%;
        margin-top: 80px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        position: relative;
        overflow: hidden;
        box-shadow: 0 -10px 30px rgba(0,0,0,0.05);
    }

    .site-footer::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 250px;
        height: 250px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .footer-top-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 35px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        padding-bottom: 20px;
    }

    .footer-brand-title {
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .footer-brand-title::before {
        content: '';
        width: 8px;
        height: 24px;
        background-color: var(--hover-color, #ef8a1c);
        border-radius: 4px;
        display: inline-block;
    }

    /* Grid Logo Partner Modern */
    .footer-logo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 15px;
        margin-bottom: 50px;
    }

    .footer-logo-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 65px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .footer-logo-card img {
        max-height: 42px;
        max-width: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .footer-logo-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        background: #ffffff;
    }

    .footer-logo-card:hover img {
        transform: scale(1.08);
    }

    /* Grid 3 Kolom Informasi Modern */
    .footer-content-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr;
        gap: 30px;
        margin-bottom: 40px;
    }

    .footer-widget {
        background: rgba(255, 255, 255, 0.07);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
    }

    .footer-widget:hover {
        border-color: rgba(255, 255, 255, 0.25);
    }

    .footer-widget h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: white;
        position: relative;
        padding-bottom: 10px;
    }

    .footer-widget h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 30px;
        height: 3px;
        background-color: var(--hover-color, #ef8a1c);
        border-radius: 2px;
    }

    .footer-widget p {
        font-size: 0.92rem;
        line-height: 1.7;
        opacity: 0.9;
        margin-bottom: 12px;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 10px;
    }

    .footer-links li a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        font-size: 0.92rem;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .footer-links li a:hover {
        color: var(--hover-color, #ef8a1c);
        transform: translateX(5px);
    }

    /* Statistik Pengunjung Widget */
    .stat-box {
        background: rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .stat-label {
        font-size: 0.85rem;
        opacity: 0.85;
    }

    .stat-number {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--hover-color, #ef8a1c);
        letter-spacing: 0.5px;
    }

    /* Media Sosial Ikon Modern */
    .social-group {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .social-btn {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .social-btn svg {
        width: 20px;
        height: 20px;
        fill: currentColor;
    }

    .social-btn:hover {
        background-color: var(--hover-color, #ef8a1c);
        border-color: var(--hover-color, #ef8a1c);
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 6px 20px rgba(239, 138, 28, 0.4);
    }

    /* Copyright Bawah */
    .footer-bottom {
        text-align: center;
        border-top: 1px solid rgba(255, 255, 255, 0.15);
        padding-top: 25px;
        font-size: 0.85rem;
        opacity: 0.8;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    @media (max-width: 992px) {
        .footer-content-grid { grid-template-columns: 1fr; gap: 20px; }
    }

    @media (max-width: 600px) {
        .footer-bottom { justify-content: center; text-align: center; }
    }
</style>

<footer class="site-footer">
    
    <!-- Bagian Atas: Header -->
    <div class="footer-top-header">
        <div class="footer-brand-title">
            PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin
        </div>
    </div>

    <!-- Grid Kartu Logo Partner -->
    <div class="footer-logo-grid">
        <a href="#" class="footer-logo-card" title="Kementerian Kesehatan">
            <img src="assets/images/logo_kemenkes.webp" alt="Kemenkes" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <span style="display:none; color:#1f2937; font-size:0.75rem; font-weight:bold;">Kemenkes</span>
        </a>
        <a href="#" class="footer-logo-card" title="BKK Banjarmasin">
            <img src="assets/images/logo_bkk.webp" alt="BKK Banjarmasin" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <span style="display:none; color:#1f2937; font-size:0.75rem; font-weight:bold;">BKK Banjarmasin</span>
        </a>
        <a href="#" class="footer-logo-card" title="Satu Data Indonesia">
            <img src="assets/images/satudataindonesia.webp" alt="Satu Data" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <span style="display:none; color:#1f2937; font-size:0.75rem; font-weight:bold;">Satu Data</span>
        </a>
        <a href="#" class="footer-logo-card" title="Layanan Aspirasi dan Pengaduan Online Rakyat">
            <img src="assets/images/lapor.webp" alt="Lapor!" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <span style="display:none; color:#1f2937; font-size:0.75rem; font-weight:bold;">Lapor!</span>
        </a>
        <a href="#" class="footer-logo-card" title="Gerakan Masyarakat Hidup Sehat">
            <img src="assets/images/germas.webp" alt="Germas" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <span style="display:none; color:#1f2937; font-size:0.75rem; font-weight:bold;">Germas</span>
        </a>
        <a href="#" class="footer-logo-card" title="Keterbukaan Informasi Publik Kalsel">
            <img src="assets/images/KIP kalsel.webp" alt="KIP Kalsel" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <span style="display:none; color:#1f2937; font-size:0.75rem; font-weight:bold;">KIP Kalsel</span>
        </a>
    </div>

    <!-- 3 Kolom Widget Utama -->
    <div class="footer-content-grid">
        
        <!-- Kolom 1: Informasi & Alamat -->
        <div class="footer-widget">
            <h4>Tentang PPID</h4>
            <p><strong>Alamat:</strong> Jl. Belitung Darat No.118A, Belitung Selatan, Kecamatan Banjarmasin Barat, Kalimantan Selatan</p>
            <p style="margin-top: 10px;">
                <strong>Telp:</strong> (0511) 3353948<br>
                <strong>Email:</strong> balaikarkesbanjarmasin@kemkes.go.id
            </p>
        </div>

        <!-- Kolom 2: Tautan Cepat -->
        <div class="footer-widget">
            <h4>Tautan Cepat</h4>
            <ul class="footer-links">
                <li><a href="index.php">&rsaquo; Beranda Utama</a></li>
                <li><a href="profil_ppid.php">&rsaquo; Profil PPID</a></li>
                <li><a href="informasi_publik.php">&rsaquo; Informasi Publik</a></li>
                <li><a href="prosedur_pelayanan.php">&rsaquo; Prosedur Layanan</a></li>
                <li><a href="kontak.php">&rsaquo; Hubungi Kami</a></li>
            </ul>
        </div>

        <!-- Kolom 3: Statistik & Media Sosial -->
        <div class="footer-widget">
            <h4>Interaksi Publik</h4>
            
            <!-- Real-time Statistik Pengunjung -->
            <div class="stat-box">
                <span class="stat-label">Kunjungan Bulan Ini</span>
                <span class="stat-number"><?php echo number_format($kunjungan_bulan_ini, 0, ',', '.'); ?></span>
            </div>
            
            <h4 style="font-size: 0.95rem; margin-top: 20px; margin-bottom: 12px;">Media Sosial Resmi</h4>
            <div class="social-group">
                
                <!-- Website Link -->
                <a href="#" class="social-btn" title="Website Resmi">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                </a>
                
                <!-- Facebook Link -->
                <a href="#" class="social-btn" title="Facebook">
                    <svg viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg>
                </a>
                
                <!-- Instagram Link -->
                <a href="#" class="social-btn" title="Instagram">
                    <svg viewBox="0 0 24 24"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8 1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3z"/></svg>
                </a>
                
                <!-- Youtube Link -->
                <a href="#" class="social-btn" title="Youtube">
                    <svg viewBox="0 0 24 24"><path d="M21.58 7.19c-.23-.86-.91-1.54-1.77-1.77C18.25 5 12 5 12 5s-6.25 0-7.81.42c-.86.23-1.54.91-1.77 1.77C2 8.75 2 12 2 12s0 3.25.42 4.81c.23.86.91 1.54 1.77 1.77C5.75 19 12 19 12 19s6.25 0 7.81-.42c.86-.23 1.54-.91 1.77-1.77C22 15.25 22 12 22 12s0-3.25-.42-4.81zM10 15V9l5.2 3-5.2 3z"/></svg>
                </a>
                
            </div>
        </div>

    </div>

    <!-- Bagian Bawah: Copyright -->
    <div class="footer-bottom">
        <div>&copy; <?php echo date('Y'); ?> Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin. All Rights Reserved.<br>Code By : Muhammad Zailani</div>
        <div style="opacity: 0.7; font-size: 0.8rem;">Layanan Informasi Publik Transparan & Akuntabel</div>
    </div>

</footer>

<!-- SKRIP OTOMATIS PENCATAT UNDUHAN DOKUMEN (REAL-TIME) -->
<script>
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a, button');
        if (!target) return;

        if (target.classList.contains('dh-btn') || 
            target.classList.contains('btn-lihat') || 
            target.classList.contains('file-link') || 
            (target.href && target.href.toLowerCase().endsWith('.pdf'))) {
            
            let judulDokumen = target.innerText.trim() || target.getAttribute('href');
            navigator.sendBeacon('record_download.php?judul=' + encodeURIComponent(judulDokumen));
        }
    });
</script>