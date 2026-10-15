<?php
// cetak_bukti_permohonan.php
require_once 'includes/koneksi.php';

if (!isset($_GET['kode']) || empty($_GET['kode'])) {
    die("Kode permohonan tidak ditemukan.");
}

$kode = $conn->real_escape_string($_GET['kode']);
$query = $conn->query("SELECT * FROM permohonan_informasi WHERE nomor_registrasi = '$kode' LIMIT 1");

if ($query->num_rows == 0) {
    die("Data permohonan tidak ditemukan.");
}

$data = $query->fetch_assoc();

// Format Tanggal Indonesia
$bulan_array = array(
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
);
$tgl_masuk = strtotime($data['tanggal_masuk']);
$tgl_indo = date('d', $tgl_masuk) . ' ' . $bulan_array[(int)date('m', $tgl_masuk)] . ' ' . date('Y', $tgl_masuk);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Permohonan - <?php echo htmlspecialchars($data['nomor_registrasi']); ?></title>
    <style>
        /* Pengaturan Kertas A4 & Base Font */
        @page {
            size: A4;
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        
        /* Kop Surat Flexbox */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 12px;
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
        .logo-kiri {
            width: 110px;
            object-fit: contain;
        }
        .logo-kanan {
            width: 75px;
            object-fit: contain;
        }
        .kop-teks {
            text-align: center;
            flex-grow: 1;
            padding: 0 10px;
        }
        .kop-teks h1 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
        }
        .kop-teks h2 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
        }
        .kop-teks p {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
        }

        /* Judul Dokumen */
        .judul-dokumen {
            text-align: center;
            margin-bottom: 12px;
        }
        .judul-dokumen h3 {
            margin: 0;
            font-size: 12pt;
            text-decoration: underline;
        }
        .judul-dokumen p {
            margin: 3px 0 0 0;
            font-weight: bold;
            font-size: 10pt;
        }

        /* Tabel Data 2 Kolom */
        .tabel-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .tabel-info td {
            padding: 4px 3px;
            vertical-align: top;
            font-size: 10pt;
        }
        .lbl { width: 17%; font-weight: bold; }
        .cln { width: 2%; text-align: center; }
        .val { width: 31%; }

        /* Kotak Teks yang Disempurnakan */
        .box-container {
            margin-bottom: 10px;
            width: 100%;
        }
        .box-title {
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-size: 9.5pt;
        }
        .box-content {
            border: 1px solid #000;
            padding: 8px 10px;
            min-height: 32px;
            text-align: justify;
            font-size: 10pt;
            background-color: #fff;
            width: 100%;
            word-wrap: break-word;
        }

        /* Tanda Tangan & Stempel */
        .ttd-box {
            width: 100%;
            margin-top: 15px;
        }
        .ttd-right {
            float: right;
            text-align: center;
            width: 260px;
        }
        .ttd-right p {
            margin: 2px 0;
            font-size: 10.5pt;
        }
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

        /* Sembunyikan tombol cetak saat diprint */
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

    <div class="container">
        <!-- KOP SURAT (Dengan 2 Logo) -->
        <div class="kop-surat">
            <img src="assets/images/logo_bkk.webp" alt="Kemenkes" class="logo-kiri" onerror="this.style.display='none'">
            
            <div class="kop-teks">
                <h1>KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</h1>
                <h2>BALAI KEKARANTINAAN KESEHATAN KELAS I BANJARMASIN</h2>
                <p>Jl. Belitung Darat No.118A, Belitung Selatan, Kec. Banjarmasin Barat, Kalimantan Selatan</p>
                <p>Telepon: (0511) 3353948 | Email: balaikarkesbanjarmasin@kemkes.go.id</p>
            </div>
            
            <img src="assets/images/karantina.webp" alt="Karantina" class="logo-kanan" onerror="this.style.display='none'">
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="judul-dokumen">
            <h3>TANDA BUKTI REGISTRASI PERMOHONAN INFORMASI PUBLIK</h3>
            <p>Nomor: <?php echo htmlspecialchars($data['nomor_registrasi']); ?></p>
        </div>

        <p style="text-align: justify; margin-bottom: 10px; font-size: 10pt;">
            Telah diterima permohonan informasi publik melalui Sistem PPID Balai Kekarantinaan Kesehatan Kelas I Banjarmasin dengan rincian data pemohon sebagai berikut:
        </p>
        
        <!-- TABEL DATA (2 Kolom) -->
        <table class="tabel-info">
            <tr>
                <td class="lbl">Tgl Registrasi</td><td class="cln">:</td><td class="val"><strong><?php echo $tgl_indo; ?></strong></td>
                <td class="lbl">Kategori</td><td class="cln">:</td><td class="val"><?php echo htmlspecialchars($data['kategori_permohonan']); ?></td>
            </tr>
            <tr>
                <td class="lbl">Nama Lengkap</td><td class="cln">:</td><td class="val"><strong><?php echo htmlspecialchars($data['nama_pemohon']); ?></strong></td>
                <td class="lbl">NIK/Identitas</td><td class="cln">:</td><td class="val"><?php echo htmlspecialchars($data['nik']); ?></td>
            </tr>
            <tr>
                <td class="lbl">Nomor HP/Telp</td><td class="cln">:</td><td class="val"><?php echo htmlspecialchars($data['no_hp']); ?></td>
                <td class="lbl">Email</td><td class="cln">:</td><td class="val"><?php echo htmlspecialchars($data['email']); ?></td>
            </tr>
            <tr>
                <td class="lbl">Pekerjaan</td><td class="cln">:</td><td class="val"><?php echo htmlspecialchars($data['pekerjaan']); ?></td>
                <td class="lbl">Cara Memperoleh</td><td class="cln">:</td><td class="val"><?php echo htmlspecialchars($data['cara_memperoleh']); ?></td>
            </tr>
            <tr>
                <td class="lbl">Bentuk Salinan</td><td class="cln">:</td><td class="val"><?php echo htmlspecialchars($data['bentuk_salinan']); ?></td>
                <td class="lbl">Penyampaian</td><td class="cln">:</td><td class="val"><?php echo htmlspecialchars($data['cara_mendapatkan']); ?></td>
            </tr>
            <tr>
                <td class="lbl">Alamat Lengkap</td><td class="cln">:</td><td class="val" colspan="4"><?php echo htmlspecialchars($data['alamat']); ?></td>
            </tr>
        </table>

        <!-- BOX RINCIAN INFORMASI -->
        <div class="box-container">
            <div class="box-title">Rincian Informasi yang Dibutuhkan:</div>
            <div class="box-content">
                <?php echo nl2br(htmlspecialchars($data['rincian_informasi'])); ?>
            </div>
        </div>

        <!-- BOX TUJUAN PENGGUNAAN -->
        <div class="box-container">
            <div class="box-title">Tujuan Penggunaan Informasi:</div>
            <div class="box-content">
                <?php echo nl2br(htmlspecialchars($data['tujuan_penggunaan'])); ?>
            </div>
        </div>

        <p style="text-align: justify; margin-top: 10px; font-size: 9pt; color: #444;">
            <em>* Bukti registrasi ini dicetak secara otomatis oleh sistem sebagai tanda terima yang sah. Silakan gunakan Nomor Registrasi Anda untuk melacak status layanan melalui portal PPID BKK Kelas I Banjarmasin.</em>
        </p>

        <!-- TANDA TANGAN & STEMPEL -->
        <div class="ttd-box">
            <div class="ttd-right">
                <p>Banjarmasin, <?php echo $tgl_indo; ?></p>
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

    </div>

    <!-- Script auto-print saat halaman dibuka -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>