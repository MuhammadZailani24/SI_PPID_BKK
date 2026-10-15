<?php
// admin/cetak_laporan_permohonan.php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

require_once '../includes/koneksi.php';

// Format Tanggal Indonesia Hari Ini
$bulan_array = array(
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
);
$tgl_sekarang = date('d') . ' ' . $bulan_array[(int)date('m')] . ' ' . date('Y');

// =========================================================================
// LOGIKA FILTER TANGGAL (Menerima parameter GET dari permohonan.php)
// =========================================================================
$filter_tipe = isset($_GET['filter_tipe']) ? $_GET['filter_tipe'] : 'semua';
$where_sql = "1=1"; // Default (Semua)
$teks_filter = "";

if ($filter_tipe == 'hari') {
    $where_sql = "DATE(tanggal_masuk) = CURDATE()";
    $teks_filter = "Rentang Data: Hari Ini (" . date('d-m-Y') . ")";
} elseif ($filter_tipe == 'bulan') {
    $where_sql = "MONTH(tanggal_masuk) = MONTH(CURDATE()) AND YEAR(tanggal_masuk) = YEAR(CURDATE())";
    $teks_filter = "Rentang Data: Bulan Ini (" . date('F Y') . ")";
} elseif ($filter_tipe == 'tahun') {
    $where_sql = "YEAR(tanggal_masuk) = YEAR(CURDATE())";
    $teks_filter = "Rentang Data: Tahun Ini (" . date('Y') . ")";
} elseif ($filter_tipe == 'kustom') {
    $start_date = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';
    if (!empty($start_date) && !empty($end_date)) {
        $where_sql = "DATE(tanggal_masuk) BETWEEN '$start_date' AND '$end_date'";
        $teks_filter = "Rentang Data: " . date('d-m-Y', strtotime($start_date)) . " s/d " . date('d-m-Y', strtotime($end_date));
    }
}

// Ambil semua data permohonan DENGAN FILTER
$query = $conn->query("SELECT * FROM permohonan_informasi WHERE $where_sql ORDER BY tanggal_masuk DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Permohonan Informasi - PPID BKK</title>
    <style>
        /* Mengatur orientasi kertas ke Landscape untuk tabel yang lebar */
        @page {
            size: A4 landscape;
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }
        
        /* Kop Surat Flexbox */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
            position: relative;
            width: 100%;
        }
        .kop-surat::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            border-bottom: 1px solid #000;
        }
        .logo-kiri { width: 110px; object-fit: contain; }
        .logo-kanan { width: 75px; object-fit: contain; }
        .kop-teks { text-align: center; flex-grow: 1; padding: 0 10px; }
        .kop-teks h1 { margin: 0; font-size: 14pt; text-transform: uppercase; }
        .kop-teks h2 { margin: 0; font-size: 13pt; font-weight: bold; }
        .kop-teks p { margin: 3px 0 0 0; font-size: 9.5pt; }

        /* Judul Dokumen */
        .judul-dokumen {
            text-align: center;
            margin-bottom: 20px;
        }
        .judul-dokumen h3 { margin: 0; font-size: 12pt; text-decoration: underline; text-transform: uppercase; }
        .judul-dokumen p { margin: 5px 0 0 0; font-size: 10pt; font-weight: bold; color: #444; }

        /* Tabel Data Laporan */
        .tabel-laporan {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }
        .tabel-laporan th, .tabel-laporan td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        .tabel-laporan th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }

        /* Tanda Tangan & Stempel */
        .ttd-box {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .ttd-right {
            float: right;
            text-align: center;
            width: 260px;
        }
        .ttd-right p { margin: 2px 0; font-size: 10.5pt; }
        .ttd-space {
            height: 75px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Desain Stempel PPID */
        .stempel-ppid {
            position: absolute;
            width: 85px;
            height: 85px;
            border: 3px solid rgba(29, 78, 216, 0.65);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-15deg);
            z-index: -1;
        }
        .stempel-inner {
            border: 1px solid rgba(29, 78, 216, 0.65);
            width: 73px;
            height: 73px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(29, 78, 216, 0.75);
            font-family: Arial, sans-serif;
            line-height: 1.1;
        }
        .stempel-inner span:nth-child(1) { font-size: 11pt; font-weight: 900; letter-spacing: 1px; }
        .stempel-inner span:nth-child(2) { font-size: 6.5pt; font-weight: bold; }
        .stempel-inner span:nth-child(3) { font-size: 5.5pt; font-weight: bold; margin-top: 2px; border-top: 1px solid rgba(29, 78, 216, 0.65); padding-top: 2px; }

        /* Tombol Cetak (Sembunyi saat diprint) */
        @media print {
            .btn-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; }
        }
        .btn-print {
            display: block;
            width: 220px;
            margin: 15px auto;
            padding: 10px;
            text-align: center;
            background: #00b2a9;
            color: white;
            text-decoration: none;
            font-family: Arial, sans-serif;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-print:hover { background: #008f88; }
    </style>
</head>
<body>
    
    <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

    <!-- KOP SURAT -->
    <div class="kop-surat">
        <img src="../assets/images/logo_bkk.webp" alt="Kemenkes" class="logo-kiri" onerror="this.style.display='none'">
        
        <div class="kop-teks">
            <h1>KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</h1>
            <h2>BALAI KEKARANTINAAN KESEHATAN KELAS I BANJARMASIN</h2>
            <p>Jl. Belitung Darat No.118A, Belitung Selatan, Kec. Banjarmasin Barat, Kalimantan Selatan</p>
            <p>Telepon: (0511) 3353948 | Email: balaikarkesbanjarmasin@kemkes.go.id</p>
        </div>
        
        <img src="../assets/images/karantina.webp" alt="Karantina" class="logo-kanan" onerror="this.style.display='none'">
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="judul-dokumen">
        <h3>LAPORAN REKAPITULASI DATA PERMOHONAN INFORMASI PUBLIK</h3>
        <?php if (!empty($teks_filter)): ?>
            <p><?php echo htmlspecialchars($teks_filter); ?></p>
        <?php endif; ?>
    </div>

    <!-- TABEL DATA LAPORAN -->
    <table class="tabel-laporan">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="15%">No. Registrasi</th>
                <th width="10%">Tgl Masuk</th>
                <th width="15%">Nama Pemohon</th>
                <th width="13%">Kategori / NIK</th>
                <th width="14%">Kontak</th>
                <th width="20%">Rincian Permintaan</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($query->num_rows > 0) {
                $no = 1;
                while ($row = $query->fetch_assoc()) {
                    $tanggal = date('d/m/Y', strtotime($row['tanggal_masuk']));
                    $status = empty($row['status']) ? 'Pending' : $row['status'];
                    
                    echo "<tr>";
                    echo "<td class='text-center'>" . $no++ . "</td>";
                    echo "<td><strong>" . htmlspecialchars($row['nomor_registrasi']) . "</strong></td>";
                    echo "<td class='text-center'>" . $tanggal . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_pemohon']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['kategori_permohonan']) . "<br><small>NIK: " . htmlspecialchars($row['nik']) . "</small></td>";
                    echo "<td>" . htmlspecialchars($row['no_hp']) . "<br><small>" . htmlspecialchars($row['email']) . "</small></td>";
                    echo "<td>" . nl2br(htmlspecialchars(substr($row['rincian_informasi'], 0, 150))) . (strlen($row['rincian_informasi']) > 150 ? "..." : "") . "</td>";
                    echo "<td class='text-center'><strong>" . strtoupper(htmlspecialchars($status)) . "</strong></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8' class='text-center' style='padding:20px; font-style:italic;'>Data tidak ditemukan untuk rentang waktu yang dipilih.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- TANDA TANGAN & STEMPEL -->
    <div class="ttd-box">
        <div class="ttd-right">
            <p>Banjarmasin, <?php echo $tgl_sekarang; ?></p>
            <p><strong>PPID BKK Kelas I Banjarmasin</strong></p>
            
            <div class="ttd-space">
                <!-- Efek Stempel PPID -->
                <div class="stempel-ppid">
                    <div class="stempel-inner">
                        <span>PPID</span>
                        <span>BKK KELAS I</span>
                        <span>BANJARMASIN</span>
                    </div>
                </div>
            </div>
            
            <p style="text-decoration: underline; font-weight: bold;">Tim Pelayanan Informasi Publik</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>