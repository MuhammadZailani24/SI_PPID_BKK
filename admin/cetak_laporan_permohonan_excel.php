<?php
// admin/cetak_laporan_permohonan_excel.php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/koneksi.php';

// =========================================================================
// LOGIKA FILTER TANGGAL (Menerima parameter GET dari permohonan.php)
// =========================================================================
$filter_tipe = isset($_GET['filter_tipe']) ? $_GET['filter_tipe'] : 'semua';
$where_sql = "1=1"; // Default (Semua)
$teks_filter = "";

if ($filter_tipe == 'hari') {
    $where_sql = "DATE(tanggal_masuk) = CURDATE()";
    $teks_filter = "HARI INI (" . date('d-m-Y') . ")";
} elseif ($filter_tipe == 'bulan') {
    $where_sql = "MONTH(tanggal_masuk) = MONTH(CURDATE()) AND YEAR(tanggal_masuk) = YEAR(CURDATE())";
    $teks_filter = "BULAN INI (" . date('F Y') . ")";
} elseif ($filter_tipe == 'tahun') {
    $where_sql = "YEAR(tanggal_masuk) = YEAR(CURDATE())";
    $teks_filter = "TAHUN INI (" . date('Y') . ")";
} elseif ($filter_tipe == 'kustom') {
    $start_date = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';
    if (!empty($start_date) && !empty($end_date)) {
        $where_sql = "DATE(tanggal_masuk) BETWEEN '$start_date' AND '$end_date'";
        $teks_filter = "RENTANG TANGGAL: " . date('d-m-Y', strtotime($start_date)) . " s/d " . date('d-m-Y', strtotime($end_date));
    }
}

// Atur header agar browser mengunduh file sebagai Excel (.xls)
header("Content-Type: application/vnd-ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Laporan_Permohonan_PPID_" . date('Y-m-d') . ".xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false);

// Struktur dasar HTML yang dibaca oleh Excel
echo "<html xmlns:x=\"urn:schemas-microsoft-com:office:excel\">";
echo "<head>";
echo "<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan Permohonan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->";
echo "<meta charset='UTF-8'>";
echo "<style>
        table { border-collapse: collapse; font-family: Arial, sans-serif; }
        th { background-color: #00b2a9; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #000000; text-align: center; vertical-align: middle; }
        td { padding: 8px; border: 1px solid #000000; vertical-align: top; }
        .text-center { text-align: center; }
        .title { font-size: 18px; font-weight: bold; text-align: center; }
        .subtitle { font-size: 16px; font-weight: bold; text-align: center; }
        .filter-title { font-size: 14px; font-weight: bold; text-align: center; color: #555; }
      </style>";
echo "</head>";
echo "<body>";
echo "<table>";

// Header Judul Excel
echo "<tr><td colspan='11' class='title' style='border:none;'>LAPORAN DATA PERMOHONAN INFORMASI PUBLIK</td></tr>";
echo "<tr><td colspan='11' class='subtitle' style='border:none;'>PPID BALAI KEKARANTINAAN KESEHATAN KELAS 1 BANJARMASIN</td></tr>";

// Tampilkan teks filter jika ada
if (!empty($teks_filter)) {
    echo "<tr><td colspan='11' class='filter-title' style='border:none;'>" . $teks_filter . "</td></tr>";
}

echo "<tr><td colspan='11' style='border:none;'></td></tr>"; // Baris Kosong sebagai pemisah

// Header Kolom Tabel
echo "<tr>";
echo "<th>NO</th>";
echo "<th>NOMOR REGISTRASI</th>";
echo "<th>TANGGAL MASUK</th>";
echo "<th>NAMA PEMOHON</th>";
echo "<th>NIK</th>";
echo "<th>NO. HP</th>";
echo "<th>EMAIL</th>";
echo "<th>KATEGORI</th>";
echo "<th>RINCIAN INFORMASI</th>";
echo "<th>TUJUAN PENGGUNAAN</th>";
echo "<th>STATUS</th>";
echo "</tr>";

// Mengambil dan me-looping data dari database (DENGAN FILTER)
$q = $conn->query("SELECT * FROM permohonan_informasi WHERE $where_sql ORDER BY tanggal_masuk DESC");
$no = 1;

if($q && $q->num_rows > 0) {
    while($r = $q->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='text-center'>" . $no++ . "</td>";
        
        // CSS mso-number-format:"\@" memastikan Excel membaca tipe datanya sebagai string murni
        echo "<td style='mso-number-format:\"\\@\";'>" . htmlspecialchars($r['nomor_registrasi']) . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($r['tanggal_masuk'])) . "</td>";
        echo "<td>" . htmlspecialchars($r['nama_pemohon']) . "</td>";
        
        // Mencegah NIK terpotong / jadi format Scientific
        echo "<td style='mso-number-format:\"\\@\";'>" . htmlspecialchars($r['nik']) . "</td>";
        
        // Mencegah No HP angka 0 di depan hilang
        echo "<td style='mso-number-format:\"\\@\";'>" . htmlspecialchars($r['no_hp']) . "</td>";
        
        echo "<td>" . htmlspecialchars($r['email']) . "</td>";
        echo "<td>" . htmlspecialchars($r['kategori_permohonan']) . "</td>";
        echo "<td>" . htmlspecialchars($r['rincian_informasi']) . "</td>";
        echo "<td>" . htmlspecialchars($r['tujuan_penggunaan']) . "</td>";
        
        $status_tampil = empty($r['status']) ? 'Pending' : $r['status'];
        echo "<td>" . htmlspecialchars($status_tampil) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='11' class='text-center'>Data tidak ditemukan untuk rentang waktu yang dipilih.</td></tr>";
}

echo "</table>";
echo "</body>";
echo "</html>";
?>