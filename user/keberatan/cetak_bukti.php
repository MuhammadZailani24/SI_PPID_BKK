<!-- user/keberatan/cetak_bukti.php -->
<?php
require_once '../../includes/koneksi.php';

$kode = isset($_GET['kode']) ? mysqli_real_escape_string($conn, $_GET['kode']) : '';

// Ambil data dari database berdasarkan kode keberatan
$query = "SELECT * FROM pengajuan_keberatan WHERE kode_keberatan = '$kode'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("<h3 style='text-align:center; margin-top:50px; color:red;'>Data keberatan tidak ditemukan di dalam database.</h3>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Keberatan - <?php echo $data['kode_keberatan']; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; margin: 0; padding: 20px; line-height: 1.6; background-color: #f9f9f9; }
        .invoice-box { max-width: 800px; margin: auto; padding: 40px; border: 1px solid #e0e0e0; box-shadow: 0 4px 15px rgba(0,0,0,0.05); background: #fff; border-radius: 8px; }
        .header { text-align: center; border-bottom: 3px solid #00b2a9; padding-bottom: 20px; margin-bottom: 25px; }
        .header h2 { margin: 0; color: #00b2a9; font-size: 1.3rem; text-transform: uppercase; }
        .header h3 { margin: 5px 0 0; color: #333; font-size: 1.1rem; }
        .header p { margin: 5px 0 0; font-size: 0.85rem; color: #666; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .info-table th, .info-table td { padding: 12px 15px; border: 1px solid #e0e0e0; font-size: 0.9rem; text-align: left; }
        .info-table th { background-color: #f4f7f6; color: #333; width: 32%; font-weight: 600; }
        
        .badge { background: #ef8a1c; color: #fff; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; display: inline-block; }
        
        .btn-container { text-align: center; margin-top: 35px; }
        .btn-print { background-color: #00b2a9; color: white; border: none; padding: 12px 30px; font-size: 1rem; font-weight: bold; border-radius: 6px; cursor: pointer; transition: background 0.3s; box-shadow: 0 4px 10px rgba(0,178,169,0.3); }
        .btn-print:hover { background-color: #ef8a1c; }
        
        .footer-note { margin-top: 30px; font-size: 0.8rem; color: #777; text-align: center; border-top: 1px dashed #ddd; padding-top: 15px; }

        @media print {
            .btn-container { display: none; }
            body { background: white; padding: 0; }
            .invoice-box { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="invoice-box">
        <div class="header">
            <h2>KEMENTERIAN KESEHATAN RI</h2>
            <h3>BALAI KEKARANTINAAN KESEHATAN KELAS 1 BANJARMASIN</h3>
            <p>Bukti Tanda Terima Pengajuan Keberatan Informasi Publik (PPID)</p>
        </div>

        <table class="info-table">
            <tr>
                <th>Kode Tiket Keberatan</th>
                <td><strong style="color: #ef8a1c; font-size: 1.1rem;"><?php echo $data['kode_keberatan']; ?></strong></td>
            </tr>
            <tr>
                <th>Kode Permohonan Asal</th>
                <td><?php echo $data['kode_permohonan']; ?></td>
            </tr>
            <tr>
                <th>Tanggal Pengajuan</th>
                <td><?php echo $data['tanggal_masuk']; ?></td>
            </tr>
            <tr>
                <th>Status Review</th>
                <td><span class="badge"><?php echo $data['status']; ?></span></td>
            </tr>
            <tr>
                <th>NIK Pemohon</th>
                <td><?php echo $data['nik']; ?></td>
            </tr>
            <tr>
                <th>Alasan Keberatan</th>
                <td><?php echo $data['alasan_keberatan']; ?></td>
            </tr>
            <tr>
                <th>Dikuasakan</th>
                <td><?php echo $data['dikuasakan']; ?></td>
            </tr>
            <tr>
                <th>Kronologi / Penjelasan</th>
                <td><?php echo nl2br($data['kronologi']); ?></td>
            </tr>
        </table>

        <div class="footer-note">
            Dokumen ini dicetak secara elektronik melalui Portal PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin dan sah sebagai bukti pengajuan keberatan informasi.
        </div>

        <div class="btn-container">
            <button class="btn-print" onclick="window.print()">🖨️ Cetak / Unduh Bukti Keberatan (PDF)</button>
        </div>
    </div>

</body>
</html>