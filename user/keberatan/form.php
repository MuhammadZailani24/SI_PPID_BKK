<!-- user/keberatan/form.php -->
<?php 
$base_url = '../../'; 
include '../../includes/header.php'; 
?>

<main>
    <section class="inner-header">
        <div style="background-color: rgba(255,255,255,0.2); display: inline-block; padding: 5px 20px; border-radius: 20px; font-size: 0.8rem; margin-bottom: 15px; text-transform: uppercase; font-weight: bold;">Layanan Keberatan</div>
        <h1>Pengajuan Keberatan Informasi Publik</h1>
        <p>Ajukan keberatan atas tanggapan permohonan informasi publik kepada PPID BKK Kelas 1 Banjarmasin</p>
    </section>

    <div class="form-wrapper">
        <div class="form-header-line"></div>
        
        <div class="form-header-content">
            <div class="form-header-title">
                <span>PPID BKK Kelas 1 Banjarmasin</span>
                <h2>Form Pengajuan Keberatan<br>Informasi Publik</h2>
            </div>
            
            <div class="form-steps">
                <div class="step-box">
                    <strong>Validasi</strong>
                    <span>Permohonan</span>
                </div>
                <div class="step-box">
                    <strong>Alasan</strong>
                    <span>Keberatan</span>
                </div>
                <div class="step-box">
                    <strong>Berkas</strong>
                    <span>Pendukung</span>
                </div>
            </div>
        </div>

        <div class="form-body">
            <form action="proses_keberatan.php" method="POST" enctype="multipart/form-data">
                
                <div class="validation-box">
                    <div style="width: 100%;">
                        <div class="validation-box-title">Validasi Permohonan</div>
                        <div class="validation-inputs">
                            <div class="form-group">
                                <label class="form-label">Kode Permohonan*</label>
                                <input type="text" name="kode_permohonan" class="form-control" placeholder="Masukan Kode Permohonan Anda" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">NIK*</label>
                                <input type="text" name="nik" class="form-control" placeholder="Masukan Nomor Nik Anda" required>
                            </div>
                            <button type="button" class="btn-cek">CEK DATA PERMOHONAN</button>
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    
                    <div class="form-col">
                        <div class="form-section-title">Alasan Pengajuan Keberatan*</div>

                        <div class="checkbox-card-stack">
                            <label class="checkbox-card">
                                <input type="checkbox" name="alasan[]" value="Penolakan atas permintaan informasi berdasarkan alasan pengecualian">
                                <span>Penolakan atas permintaan informasi berdasarkan alasan pengecualian sebagaimana dimaksud dalam Pasal 17 UU No. 14 Tahun 2008</span>
                            </label>

                            <label class="checkbox-card">
                                <input type="checkbox" name="alasan[]" value="Tidak disediakannya informasi berkala">
                                <span>Tidak disediakannya informasi berkala</span>
                            </label>

                            <label class="checkbox-card">
                                <input type="checkbox" name="alasan[]" value="Tidak ditanggapinya permintaan informasi">
                                <span>Tidak ditanggapinya permintaan informasi</span>
                            </label>

                            <label class="checkbox-card">
                                <input type="checkbox" name="alasan[]" value="Permintaan informasi tidak ditanggapi sebagaimana yang diminta">
                                <span>Permintaan informasi tidak ditanggapi sebagaimana yang diminta</span>
                            </label>

                            <label class="checkbox-card">
                                <input type="checkbox" name="alasan[]" value="Tidak dipenuhinya permintaan informasi">
                                <span>Tidak dipenuhinya permintaan informasi</span>
                            </label>

                            <label class="checkbox-card">
                                <input type="checkbox" name="alasan[]" value="Pengenaan biaya yang tidak wajar">
                                <span>Pengenaan biaya yang tidak wajar</span>
                            </label>

                            <label class="checkbox-card">
                                <input type="checkbox" name="alasan[]" value="Penyampaian informasi yang melebihi jangka waktu">
                                <span>Penyampaian informasi yang melebihi jangka waktu yang diatur dalam UU No. 14 Tahun 2008</span>
                            </label>
                            
                            <label class="checkbox-card checkbox-card-special">
                                <input type="checkbox" name="dikuasakan" value="Ya">
                                <span style="font-weight: bold; color: var(--primary);">PENGAJUAN KEBERATAN DIKUASAKAN ( JIKA ADA )</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-section-title">Detail Kronologi & Berkas</div>

                        <div class="form-group" style="margin-bottom: 25px;">
                            <label class="form-label">Kronologi ( Penjelasan Keberatan )*</label>
                            <textarea name="kronologi" class="form-control" placeholder="Masukan Kronologi ( Penjelasan Keberatan )" style="min-height: 150px;" required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Upload Surat Keberatan*</label>
                            <div class="upload-area" onclick="document.getElementById('surat-upload').click()">
                                <div class="upload-icon">📥</div>
                                <div class="upload-text" id="textUploadSurat">
                                    <strong>Upload Surat Keberatan</strong> or<br>Drag File Surat Keberatan<br>
                                    <span style="font-size: 0.8rem; color: #888;">Max Upload 6 MB</span>
                                </div>
                            </div>
                            <input type="file" name="file_surat" id="surat-upload" style="display: none;" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                    </div>

                </div>

                <div class="form-footer">
                    <button type="submit" class="btn-submit">AJUKAN KEBERATAN</button>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- SCRIPT PREVIEW NAMA FILE SURAT KEBERATAN -->
<script>
document.getElementById('surat-upload').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        let fileName = this.files[0].name;
        document.getElementById('textUploadSurat').innerHTML = `
            <span style="color: #00b2a9; font-weight: bold; font-size: 0.95rem;">✔ File Berhasil Dipilih:</span><br>
            <strong style="color: #333; font-size: 0.9rem; word-break: break-all;">${fileName}</strong><br>
            <span style="font-size: 0.75rem; color: #ef8a1c; font-weight: 500;">(Klik untuk mengganti file)</span>
        `;
    }
});
</script>

<?php include '../../includes/footer.php'; ?>