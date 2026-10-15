<!-- user/permohonan/form.php -->
<?php 
$base_url = '../../'; 
include '../../includes/header.php'; 
?>

<main>
    <!-- Header Banner Form -->
    <section class="inner-header">
        <div style="background-color: rgba(255,255,255,0.2); display: inline-block; padding: 5px 20px; border-radius: 20px; font-size: 0.8rem; margin-bottom: 15px; text-transform: uppercase; font-weight: bold;">Layanan Informasi Publik</div>
        <h1>Permohonan Informasi Publik</h1>
        <p>Ajukan permohonan informasi publik kepada PPID BKK Kelas 1 Banjarmasin secara online</p>
    </section>

    <!-- Kontainer Utama Form -->
    <div class="form-wrapper">
        <div class="form-header-line"></div>
        
        <div class="form-header-content">
            <div class="form-header-title">
                <span>PPID BKK Kelas 1 Banjarmasin</span>
                <h2>Form Permohonan Informasi Publik</h2>
            </div>
            
            <!-- Indikator Langkah -->
            <div class="form-steps">
                <div class="step-box">
                    <strong>Identitas</strong>
                    <span>Pemohon</span>
                </div>
                <div class="step-box">
                    <strong>Rincian</strong>
                    <span>Informasi</span>
                </div>
                <div class="step-box">
                    <strong>Salinan</strong>
                    <span>Preferensi</span>
                </div>
            </div>
        </div>

        <div class="form-body">
            <form action="proses.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-grid">
                    
                    <!-- ==============================================
                         KOLOM KIRI: DATA PEMOHON (DINAMIS)
                         ============================================== -->
                    <div class="form-col">
                        <div class="form-section-title">Data Pemohon</div>

                        <div class="form-group">
                            <label class="form-label">Kategori Permohonan</label>
                            <select name="kategori" id="kategoriPermohonan" class="form-control" onchange="ubahKategoriForm()" required>
                                <option value="Perorangan">Perorangan</option>
                                <option value="Lembaga / Organisasi">Lembaga / Organisasi</option>
                                <option value="Kelompok Orang">Kelompok Orang</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" id="labelNik">NIK/No. Identitas Pribadi</label>
                            <span class="text-danger" id="infoNik">( Mohon pastikan nik yang anda masukan sesuai dengan no nik ktp )</span>
                            <input type="text" name="nik" id="inputNik" class="form-control" placeholder="Masukan NIK/No.Identitas Pribadi" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" id="labelNama">Nama Lengkap</label>
                            <input type="text" name="nama" id="inputNama" class="form-control" placeholder="Masukan Nama Lengkap Anda" required>
                        </div>

                        <!-- UPLOAD KTP / IDENTITAS PIMPINAN / PEMBERI KUASA -->
                        <div class="form-group">
                            <label class="form-label" id="labelKtpUpload">Upload KTP Pribadi</label>
                            <div class="upload-area" onclick="document.getElementById('ktp-upload').click()">
                                <div class="upload-icon" id="iconUploadKtp">📥</div>
                                <div class="upload-text" id="textUploadKtp">
                                    <strong>Upload File KTP Pribadi</strong> or<br>Drag File KTP Pribadi<br>
                                    <span style="font-size: 0.8rem; color: #888;">Max Upload 3 MB</span>
                                </div>
                            </div>
                            <input type="file" name="file_ktp" id="ktp-upload" style="display: none;" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" placeholder="Masukan Nama Alamat Anda" required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Masukan Alamat Email Anda" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" name="no_telp" class="form-control" placeholder="Masukan Nomor Telepon Anda" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" name="pekerjaan" class="form-control" placeholder="Masukan Pekerjaan Anda" required>
                        </div>
                    </div>

                    <!-- ==============================================
                         KOLOM KANAN: DETAIL PERMOHONAN (DINAMIS)
                         ============================================== -->
                    <div class="form-col">
                        <!-- UPLOAD DOKUMEN UTAMA DI KANAN (AKTA / SURAT KUASA) -->
                        <div class="form-group" id="wrapperDokumenUtama" style="display: none;">
                            <label class="form-label" id="labelDokumenUtama">Upload Akta Notaris Lembaga / Organisasi</label>
                            <div class="upload-area" onclick="document.getElementById('dokumen-utama-upload').click()">
                                <div class="upload-icon" id="iconDokumenUtama">📥</div>
                                <div class="upload-text" id="textDokumenUtama">
                                    <strong>Upload File Akta Notaris</strong> or<br>Drag File Akta Notaris<br>
                                    <span style="font-size: 0.8rem; color: #888;">Max Upload 6 MB</span>
                                </div>
                            </div>
                            <input type="file" name="file_pendukung_utama" id="dokumen-utama-upload" style="display: none;" accept=".jpg,.jpeg,.png,.pdf">
                        </div>

                        <div class="form-section-title">Detail Permohonan</div>

                        <div class="form-group">
                            <label class="form-label">Rincian Informasi</label>
                            <textarea name="rincian_informasi" class="form-control" placeholder="Masukan Rincian Informasi" required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tujuan Penggunaan Informasi</label>
                            <textarea name="tujuan_penggunaan" class="form-control" placeholder="Masukan Tujuan Penggunaan Informasi" required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cara Memperoleh Informasi</label>
                            <div class="radio-group">
                                <label class="radio-item">
                                    <input type="checkbox" name="cara_memperoleh[]" value="Melihat">
                                    <span>Melihat</span>
                                </label>
                                <label class="radio-item">
                                    <input type="checkbox" name="cara_memperoleh[]" value="Membaca">
                                    <span>Membaca</span>
                                </label>
                                <label class="radio-item">
                                    <input type="checkbox" name="cara_memperoleh[]" value="Mendengarkan">
                                    <span>Mendengarkan</span>
                                </label>
                                <label class="radio-item">
                                    <input type="checkbox" name="cara_memperoleh[]" value="Mencatat">
                                    <span>Mencatat</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mendapatkan Salinan Informasi</label>
                            <div class="radio-group">
                                <label class="radio-item">
                                    <input type="radio" name="bentuk_salinan" value="Softcopy" required>
                                    <span>Softcopy</span>
                                </label>
                                <label class="radio-item">
                                    <input type="radio" name="bentuk_salinan" value="Hardcopy">
                                    <span>Hardcopy</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cara Mendapatkan Salinan Informasi</label>
                            <div class="radio-group" style="grid-template-columns: 1fr;"> 
                                <label class="radio-item">
                                    <input type="radio" name="cara_mendapatkan" value="Mengambil Langsung" required>
                                    <span>Mengambil Langsung</span>
                                </label>
                                <label class="radio-item">
                                    <input type="radio" name="cara_mendapatkan" value="Faksimili">
                                    <span>Faksimili</span>
                                </label>
                                <label class="radio-item">
                                    <input type="radio" name="cara_mendapatkan" value="Email">
                                    <span>Email</span>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="form-footer">
                    <button type="submit" class="btn-submit">AJUKAN PERMOHONAN</button>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- SCRIPT JAVASCRIPT DINAMIS & PREVIEW NAMA FILE -->
<script>
function ubahKategoriForm() {
    const kategori = document.getElementById('kategoriPermohonan').value;
    
    const labelNik = document.getElementById('labelNik');
    const infoNik = document.getElementById('infoNik');
    const inputNik = document.getElementById('inputNik');
    const labelNama = document.getElementById('labelNama');
    const inputNama = document.getElementById('inputNama');
    const labelKtpUpload = document.getElementById('labelKtpUpload');
    const textUploadKtp = document.getElementById('textUploadKtp');
    const fileKtpInput = document.getElementById('ktp-upload');

    const wrapperDokumenUtama = document.getElementById('wrapperDokumenUtama');
    const labelDokumenUtama = document.getElementById('labelDokumenUtama');
    const textDokumenUtama = document.getElementById('textDokumenUtama');
    const inputDokumenUtama = document.getElementById('dokumen-utama-upload');

    // Reset isi file input ketika kategori berubah
    fileKtpInput.value = "";
    inputDokumenUtama.value = "";

    if (kategori === 'Perorangan') {
        labelNik.innerText = "NIK/No. Identitas Pribadi";
        infoNik.style.display = "inline-block";
        inputNik.placeholder = "Masukan NIK/No.Identitas Pribadi";
        
        labelNama.innerText = "Nama Lengkap";
        inputNama.placeholder = "Masukan Nama Lengkap Anda";

        labelKtpUpload.innerText = "Upload KTP Pribadi";
        textUploadKtp.innerHTML = "<strong>Upload File KTP Pribadi</strong> or<br>Drag File KTP Pribadi<br><span style='font-size: 0.8rem; color: #888;'>Max Upload 3 MB</span>";

        wrapperDokumenUtama.style.display = "none";
        inputDokumenUtama.removeAttribute('required');

    } else if (kategori === 'Lembaga / Organisasi') {
        labelNik.innerText = "NIK/No. Identitas Pimpinan";
        infoNik.style.display = "inline-block";
        inputNik.placeholder = "Masukan NIK/No.Identitas Pimpinan";

        labelNama.innerText = "Nama Lembaga / Organisasi";
        inputNama.placeholder = "Masukan Nama Lembaga / Organisasi Anda";

        labelKtpUpload.innerText = "Upload KTP Pimpinan";
        textUploadKtp.innerHTML = "<strong>Upload File KTP Pimpinan</strong> or<br>Drag File KTP Pimpinan<br><span style='font-size: 0.8rem; color: #888;'>Max Upload 3 MB</span>";

        wrapperDokumenUtama.style.display = "block";
        labelDokumenUtama.innerText = "Upload Akta Notaris Lembaga / Organisasi";
        textDokumenUtama.innerHTML = "<strong>Upload File Akta Notaris Lembaga / Organisasi</strong> or<br>Drag File Akta Notaris Lembaga / Organisasi<br><span style='font-size: 0.8rem; color: #888;'>Max Upload 6 MB</span>";
        inputDokumenUtama.setAttribute('required', 'required');

    } else if (kategori === 'Kelompok Orang') {
        labelNik.innerText = "NIK/No. Identitas Pemberi Kuasa";
        infoNik.style.display = "inline-block";
        inputNik.placeholder = "Masukan Nomor NIK Kuasa";

        labelNama.innerText = "Nama Kelompok Orang";
        inputNama.placeholder = "Masukan Nama Kelompok Orang";

        labelKtpUpload.innerText = "Upload KTP Pemberi Kuasa";
        textUploadKtp.innerHTML = "<strong>Upload File KTP Pemberi Kuasa</strong> or<br>Drag File KTP Pemberi Kuasa<br><span style='font-size: 0.8rem; color: #888;'>Max Upload 3 MB</span>";

        wrapperDokumenUtama.style.display = "block";
        labelDokumenUtama.innerText = "Upload Surat Kuasa";
        textDokumenUtama.innerHTML = "<strong>Upload File Surat Kuasa</strong> or<br>Drag File Surat Kuasa<br><span style='font-size: 0.8rem; color: #888;'>Max Upload 6 MB</span>";
        inputDokumenUtama.setAttribute('required', 'required');
    }
}

// Event Listener untuk Preview Nama File KTP di dalam Box
document.getElementById('ktp-upload').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        let fileName = this.files[0].name;
        document.getElementById('textUploadKtp').innerHTML = `
            <span style="color: #00b2a9; font-weight: bold; font-size: 0.95rem;">✔ File Berhasil Dipilih:</span><br>
            <strong style="color: #333; font-size: 0.9rem; word-break: break-all;">${fileName}</strong><br>
            <span style="font-size: 0.75rem; color: #ef8a1c; font-weight: 500;">(Klik untuk mengganti file)</span>
        `;
    }
});

// Event Listener untuk Preview Nama File Dokumen Utama (Akta/Surat Kuasa) di dalam Box
document.getElementById('dokumen-utama-upload').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        let fileName = this.files[0].name;
        document.getElementById('textDokumenUtama').innerHTML = `
            <span style="color: #00b2a9; font-weight: bold; font-size: 0.95rem;">✔ File Berhasil Dipilih:</span><br>
            <strong style="color: #333; font-size: 0.9rem; word-break: break-all;">${fileName}</strong><br>
            <span style="font-size: 0.75rem; color: #ef8a1c; font-weight: 500;">(Klik untuk mengganti file)</span>
        `;
    }
});
</script>

<?php include '../../includes/footer.php'; ?>