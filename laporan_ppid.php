<!-- laporan_ppid.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// 1. Ambil Teks Pengantar dari Database (Tabel pengaturan_web)
// =========================================================================
$settings = [];
$query_set = $conn->query("SELECT * FROM pengaturan_web WHERE kunci_pengaturan = 'teks_pengantar_laporan'");
if ($query_set && $query_set->num_rows > 0) {
    $row_set = $query_set->fetch_assoc();
    $settings['teks_pengantar_laporan'] = $row_set['nilai_pengaturan'];
}

$default_pengantar = "Sebagai bentuk pertanggungjawaban dan transparansi, Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin secara rutin menerbitkan Laporan Layanan Informasi Publik setiap tahunnya.";
$teks_pengantar = $settings['teks_pengantar_laporan'] ?? $default_pengantar;

// =========================================================================
// 2. Ambil Daftar Laporan dari Database (Tabel laporan_ppid)
// =========================================================================
$data_laporan = [];
$query_laporan = $conn->query("SELECT * FROM laporan_ppid ORDER BY tahun DESC, id DESC");
if ($query_laporan && $query_laporan->num_rows > 0) {
    while ($row = $query_laporan->fetch_assoc()) {
        $data_laporan[] = [
            "judul" => $row['judul'], 
            "tahun" => $row['tahun'],
            "file"  => $row['file_path']
        ];
    }
}
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN LAPORAN PPID
       ========================================================= */
    .laporan-wrapper {
        max-width: 1000px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .laporan-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #eaeaea;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        padding: 45px;
        position: relative;
    }

    .laporan-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
        background: linear-gradient(90deg, var(--primary, #00b2a9), #008f88);
    }

    .laporan-card-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 18px;
        position: relative;
        padding-bottom: 15px;
        border-bottom: 2px solid #f3f4f6;
        letter-spacing: 0.3px;
    }

    .laporan-desc {
        font-size: 1.02rem;
        line-height: 1.75;
        color: #4b5563;
        margin-bottom: 35px;
    }

    /* Tabel Laporan Modern */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .laporan-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.95rem;
    }

    .laporan-table th {
        background-color: #f8fafc;
        color: #374151;
        font-weight: 800;
        padding: 16px 20px;
        border-bottom: 2px solid #e5e7eb;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .laporan-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
        color: #4b5563;
        vertical-align: middle;
    }

    .laporan-table tr:last-child td {
        border-bottom: none;
    }

    .laporan-table tr {
        transition: background-color 0.2s ease;
    }

    .laporan-table tr:hover {
        background-color: #fdfbf7;
    }

    .btn-lihat {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(0, 178, 169, 0.2);
    }

    .btn-lihat:hover {
        background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%);
        color: white;
        box-shadow: 0 6px 18px rgba(239, 138, 28, 0.3);
        transform: translateY(-2px);
    }

    .empty-state {
        text-align: center;
        padding: 50px;
        color: #9ca3af;
        font-style: italic;
        background-color: #f9fafb;
        border-radius: 12px;
        border: 1px dashed #d1d5db;
        margin-top: 15px;
        font-size: 0.98rem;
    }

    @media (max-width: 768px) {
        .laporan-card { padding: 25px 20px; }
        .laporan-table th, .laporan-table td { padding: 12px 15px; }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Laporan PPID</h1>
        <p>Laporan Tahunan Pelaksanaan Keterbukaan Informasi Publik</p>
    </section>

    <div class="laporan-wrapper">
        <div class="laporan-card">
            <h2 class="laporan-card-title">Daftar Laporan Kinerja PPID</h2>
            
            <p class="laporan-desc">
                <?php echo nl2br(htmlspecialchars($teks_pengantar)); ?>
            </p>

            <?php if (!empty($data_laporan)): ?>
                <div class="table-responsive">
                    <table class="laporan-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">No</th>
                                <th style="width: 50%;">Nama Laporan</th>
                                <th style="width: 20%;">Tahun</th>
                                <th style="width: 20%; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach ($data_laporan as $lap): 
                            ?>
                                <tr>
                                    <td><?php echo sprintf("%02d", $no++); ?></td>
                                    <td style="font-weight: 700; color: #1f2937;"><?php echo htmlspecialchars($lap['judul']); ?></td>
                                    <td><span style="background: #f3f4f6; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; color: #374151;"><?php echo htmlspecialchars($lap['tahun']); ?></span></td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($lap['file'])): ?>
                                            <a href="<?php echo htmlspecialchars($lap['file']); ?>" target="_blank" class="btn-lihat">
                                                📄 Lihat File
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #9ca3af; font-size: 0.85rem; font-weight: 600;">File Kosong</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    Belum ada dokumen laporan yang diunggah oleh admin.
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>