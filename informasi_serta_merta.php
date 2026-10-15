<!-- informasi_serta_merta.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// AMBIL DATA DARI DATABASE
// =========================================================================
$data_serta_merta = [];
$huruf_unik = [];

$query_serta = $conn->query("SELECT * FROM informasi_serta_merta ORDER BY kode_huruf ASC, id ASC");

if ($query_serta && $query_serta->num_rows > 0) {
    while ($row = $query_serta->fetch_assoc()) {
        $huruf = strtoupper(trim($row['kode_huruf']));
        $huruf_unik[$huruf] = true; // Untuk menghitung jumlah kategori unik (A, B, C, dst)
        
        $data_serta_merta[] = [
            "huruf" => $huruf,
            "judul" => $row['judul_informasi'],
            "file"  => $row['file_path']
        ];
    }
}

// Menghitung statistik otomatis
$total_kategori = count($huruf_unik);
$total_dokumen = count($data_serta_merta);
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN INFORMASI SERTA MERTA
       ========================================================= */
    .info-publik-header {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #00796b 100%);
        color: white;
        padding: 60px 20px 70px 20px;
        text-align: center;
        border-radius: 0 0 24px 24px;
        margin-bottom: -35px; 
        position: relative;
        z-index: 1;
        box-shadow: 0 10px 30px rgba(0, 178, 169, 0.15);
    }

    .info-badge {
        background-color: var(--hover-color, #ef8a1c);
        color: white;
        padding: 6px 18px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: inline-block;
        margin-bottom: 18px;
        box-shadow: 0 4px 15px rgba(239, 138, 28, 0.35);
    }

    .info-publik-header h1 {
        font-size: 2.1rem;
        margin-bottom: 15px;
        font-weight: 800;
        letter-spacing: 0.3px;
    }

    .info-publik-header p {
        max-width: 750px;
        margin: 0 auto 35px auto;
        font-size: 1.02rem;
        opacity: 0.92;
        line-height: 1.7;
    }

    .info-stats {
        display: flex;
        justify-content: center;
        gap: 25px;
        margin-top: 25px;
    }

    .info-stat-item {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 18px 30px;
        border-radius: 16px;
        min-width: 130px;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05);
    }

    .info-stat-num {
        font-size: 2rem;
        font-weight: 800;
        color: var(--hover-color, #ef8a1c);
        text-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .info-stat-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        opacity: 0.85;
        font-weight: 700;
        margin-top: 4px;
    }

    /* Container Pencarian Modern */
    .search-container {
        max-width: 650px;
        margin: 0 auto;
        position: relative;
        z-index: 3; 
        padding: 0 20px;
    }

    .search-input {
        width: 100%;
        padding: 16px 28px;
        border-radius: 35px;
        border: 1px solid #eaeaea;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        font-size: 1rem;
        outline: none;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        font-family: inherit;
    }

    .search-input:focus {
        border-color: var(--primary, #00b2a9);
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.2);
        transform: translateY(-2px);
    }

    /* List Informasi Container */
    .info-list-container {
        max-width: 950px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .info-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #f3f4f6;
        padding-bottom: 18px;
        margin-bottom: 30px;
    }

    .info-list-header h3 {
        margin: 0;
        color: #1f2937;
        font-size: 1.25rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .info-list-header h3 span {
        color: #ef4444; 
        font-size: 1.4rem;
    }

    .badge-kategori {
        background-color: #f3f4f6;
        color: #4b5563;
        padding: 6px 14px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 700;
        border: 1px solid #e5e7eb;
    }

    /* Styling Baris Dokumen Modern (Card Style) */
    .info-item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 22px 28px;
        background: #ffffff;
        border: 1px solid #eaeaea;
        border-radius: 16px;
        margin-bottom: 18px;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0,0,0,0.03);
    }

    .info-item-row::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 6px;
        height: 100%;
        background: linear-gradient(180deg, var(--primary, #00b2a9), #008f88);
        transition: background 0.35s ease;
    }

    .info-item-row:hover {
        transform: translateY(-4px) translateX(6px);
        border-color: #d1d5db;
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.12);
    }

    .info-item-row:hover::before {
        background: linear-gradient(180deg, var(--hover-color, #ef8a1c), #d97706);
    }

    .info-item-title {
        font-size: 1.02rem;
        font-weight: 700;
        color: #1f2937;
        flex-grow: 1;
        padding-right: 25px;
        line-height: 1.6;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .info-item-letter {
        background: #f0fdfa;
        color: var(--primary, #00b2a9);
        min-width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
        flex-shrink: 0;
        border: 1px solid #ccfbf1;
        transition: all 0.35s ease;
        margin-top: -2px;
    }
    
    .info-item-row:hover .info-item-letter {
        background: var(--hover-color, #ef8a1c);
        color: white;
        border-color: var(--hover-color, #ef8a1c);
        transform: scale(1.05);
    }

    /* Tombol Unduh / Lihat Modern */
    .btn-lihat {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        padding: 12px 22px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 178, 169, 0.25);
    }

    .btn-lihat:hover {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        box-shadow: 0 6px 18px rgba(239, 138, 28, 0.35);
        transform: translateY(-2px);
    }

    .btn-lihat.disabled {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
        box-shadow: none;
        border: 1px solid #e5e7eb;
    }
    .btn-lihat.disabled:hover {
        transform: none;
        background: #f3f4f6;
    }

    /* Responsif untuk HP & Tablet */
    @media (max-width: 768px) {
        .info-item-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 18px;
            padding: 20px;
        }
        .btn-lihat {
            width: 100%;
            justify-content: center;
        }
        .info-stats {
            flex-direction: column;
            gap: 15px;
        }
        .info-publik-header h1 {
            font-size: 1.6rem;
        }
    }
</style>

<main>
    <section class="info-publik-header">
        <div class="info-badge">Serta Merta</div>
        <h1>Informasi Publik yang Wajib Diumumkan secara Serta Merta</h1>
        <p>Informasi yang wajib diumumkan secara serta merta karena sifatnya yang mendesak dan perlu segera diketahui publik sesuai UU No. 14 Tahun 2008.</p>
        
        <div class="info-stats">
            <div class="info-stat-item">
                <div class="info-stat-num"><?php echo $total_kategori; ?></div>
                <div class="info-stat-label">Kategori</div>
            </div>
            <div class="info-stat-item">
                <div class="info-stat-num"><?php echo $total_dokumen; ?></div>
                <div class="info-stat-label">Total Dokumen</div>
            </div>
        </div>
    </section>

    <div class="search-container">
        <input type="text" class="search-input" placeholder="🔍 Cari informasi serta merta...">
    </div>

    <section class="info-list-container">
        <div class="info-list-header">
            <h3><span>⚠️</span> Daftar Informasi Serta Merta</h3>
            <span class="badge-kategori"><?php echo $total_dokumen; ?> dokumen</span>
        </div>

        <?php 
        // =====================================================================
        // TAMPILKAN DATA (Baris Langsung)
        // =====================================================================
        if (!empty($data_serta_merta)) {
            foreach($data_serta_merta as $info) { 
        ?>
            <div class="info-item-row">
                <div class="info-item-title">
                    <span class="info-item-letter"><?php echo htmlspecialchars($info['huruf']); ?></span> 
                    <span><?php echo htmlspecialchars($info['judul']); ?></span>
                </div>
                
                <?php if (!empty($info['file'])): ?>
                    <a href="<?php echo htmlspecialchars($info['file']); ?>" target="_blank" class="btn-lihat">📄 Lihat Dokumen</a>
                <?php else: ?>
                    <button class="btn-lihat disabled" onclick="alert('Dokumen belum diunggah oleh admin.')">📄 Belum Tersedia</button>
                <?php endif; ?>
            </div>
        <?php 
            }
        } else { 
        ?>
            <!-- Tampilan jika database kosong -->
            <div style="background: white; padding: 50px; text-align: center; border-radius: 16px; color: #6b7280; border: 1px solid #eaeaea; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                Belum ada informasi serta merta yang dipublikasikan oleh admin.
            </div>
        <?php } ?>

    </section>
</main>

<?php include 'includes/footer.php'; ?>