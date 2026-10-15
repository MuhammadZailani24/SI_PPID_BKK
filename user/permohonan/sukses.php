<!-- user/permohonan/sukses.php -->
<?php 
$base_url = '../../'; 
include '../../includes/header.php'; 

$kode = isset($_GET['kode']) ? $_GET['kode'] : '';

if(empty($kode)) {
    header("Location: ../../index.php");
    exit();
}
?>

<main style="background-color: var(--bg-light); padding: 80px 5%; min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    
    <div style="background: white; max-width: 650px; width: 100%; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; text-align: center;">
        
        <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; padding: 40px 30px;">
            <div style="font-size: 4rem; margin-bottom: 15px;">✅</div>
            <h1 style="font-size: 1.8rem; font-weight: bold; margin-bottom: 10px;">Permohonan Berhasil Dikirim!</h1>
            <p style="opacity: 0.9; font-size: 0.95rem;">Terima kasih, permohonan informasi publik Anda telah masuk ke dalam sistem PPID Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin.</p>
        </div>

        <div style="padding: 40px 30px;">
            <p style="color: var(--text-dark); margin-bottom: 15px; font-weight: 500;">Simpan Kode Permohonan Anda:</p>
            
            <div style="background-color: #fff9f0; border: 2px dashed var(--hover-color); padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <span style="display: block; font-size: 0.85rem; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Kode Tracking Anda:</span>
                <strong style="font-size: 2.2rem; color: var(--hover-color); letter-spacing: 2px;"><?php echo htmlspecialchars($kode); ?></strong>
            </div>

            <!-- TOMBOL DOWNLOAD INVOICE / BUKTI -->
            <div style="margin-bottom: 30px;">
                <a href="cetak_bukti.php?kode=<?php echo htmlspecialchars($kode); ?>" target="_blank" style="display: inline-block; background-color: var(--hover-color); color: white; padding: 14px 30px; border-radius: 8px; font-weight: bold; font-size: 1rem; box-shadow: 0 4px 15px rgba(239, 138, 28, 0.3); transition: all 0.3s;">
                    📥 Unduh Bukti Permohonan (PDF)
                </a>
            </div>

            <p style="font-size: 0.9rem; color: #666; margin-bottom: 35px; line-height: 1.6;">
                Petugas kami akan memverifikasi berkas Anda. Pemberitahuan tertulis akan diberikan selambat-lambatnya 10 hari kerja.
            </p>

            <div style="display: flex; justify-content: center; gap: 15px;">
                <a href="../../index.php" style="padding: 12px 25px; border: 1px solid var(--border-color); color: var(--text-dark); border-radius: 6px; font-weight: bold; transition: all 0.3s;">Kembali ke Beranda</a>
            </div>
        </div>

    </div>

</main>

<?php include '../../includes/footer.php'; ?>