<!-- form_permohonan.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

try {
    $conn->query("CREATE TABLE IF NOT EXISTS permohonan_informasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nomor_registrasi VARCHAR(50),
        status VARCHAR(50) DEFAULT 'Pending',
        tanggapan TEXT,
        tanggal_masuk TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $kolom_baru = [
        'kode_permohonan' => 'VARCHAR(50)',
        'kategori_permohonan' => 'VARCHAR(100)',
        'nama_pemohon' => 'VARCHAR(150)',
        'nik' => 'VARCHAR(50)',
        'ktp_path' => 'VARCHAR(255)',
        'dokumen_tambahan_path' => 'VARCHAR(255)',
        'alamat' => 'TEXT',
        'email' => 'VARCHAR(150)',
        'no_hp' => 'VARCHAR(50)',
        'pekerjaan' => 'VARCHAR(150)',
        'rincian_informasi' => 'TEXT',
        'tujuan_penggunaan' => 'TEXT',
        'cara_memperoleh' => 'VARCHAR(100)',
        'bentuk_salinan' => 'VARCHAR(100)',
        'cara_mendapatkan' => 'VARCHAR(100)'
    ];

    foreach ($kolom_baru as $kolom => $tipe) {
        $cek = $conn->query("SHOW COLUMNS FROM permohonan_informasi LIKE '$kolom'");
        if ($cek && $cek->num_rows == 0) {
            $conn->query("ALTER TABLE permohonan_informasi ADD COLUMN $kolom $tipe");
        }
    }
} catch (Exception $e) { }

$pesan_sukses = false;
$kode_unik = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['kirim_permohonan'])) {
    
    $kode_unik = "REQ-" . date('Ymd') . "-" . rand(1000, 9999) . "-" . substr(uniqid(), -4);
    
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori_permohonan']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_pemohon']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $pekerjaan = mysqli_real_escape_string($conn, $_POST['pekerjaan']);
    
    $rincian = mysqli_real_escape_string($conn, $_POST['rincian_informasi']);
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan_penggunaan']);
    $cara_memperoleh = mysqli_real_escape_string($conn, $_POST['cara_memperoleh'] ?? '');
    $bentuk_salinan = mysqli_real_escape_string($conn, $_POST['bentuk_salinan'] ?? '');
    $cara_mendapatkan = mysqli_real_escape_string($conn, $_POST['cara_mendapatkan'] ?? '');

    $upload_dir = 'uploads/dokumen/';
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
    
    $ktp_path = '';
    if(isset($_FILES['file_ktp']) && $_FILES['file_ktp']['error'] == 0) {
        $ktp_path = $upload_dir . time() . '_KTP_' . basename($_FILES['file_ktp']['name']);
        move_uploaded_file($_FILES['file_ktp']['tmp_name'], $ktp_path);
    }
    
    $dok_path = '';
    if(isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] == 0) {
        $dok_path = $upload_dir . time() . '_DOK_' . basename($_FILES['file_dokumen']['name']);
        move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $dok_path);
    }

    try {
        $q_insert = "INSERT INTO permohonan_informasi 
                     (nomor_registrasi, kode_permohonan, kategori_permohonan, nama_pemohon, nik, ktp_path, dokumen_tambahan_path, alamat, email, no_hp, pekerjaan, rincian_informasi, tujuan_penggunaan, cara_memperoleh, bentuk_salinan, cara_mendapatkan, status) 
                     VALUES ('$kode_unik', '$kode_unik', '$kategori', '$nama', '$nik', '$ktp_path', '$dok_path', '$alamat', '$email', '$no_hp', '$pekerjaan', '$rincian', '$tujuan', '$cara_memperoleh', '$bentuk_salinan', '$cara_mendapatkan', 'Pending')";
                     
        if ($conn->query($q_insert)) {
            $pesan_sukses = true;
            
            // ====================================================
            // NOTIFIKASI OTOMATIS KE TELEGRAM ADMIN
            // ====================================================
            $tele_token = '8838750735:AAER7EhJnD7Po8xO6ptEZNp59jgdwv3-E2o';
            $admin_chat_id = '7279608699';

            $pesan_tele = "🚨 *PERMOHONAN INFORMASI BARU* 🚨\n\n"
                         . "Ada permohonan informasi publik baru masuk di website!\n\n"
                         . "📌 Reg: `$kode_unik`\n"
                         . "👤 Nama: *$nama*\n"
                         . "📁 Kategori: *$kategori*\n"
                         . "📞 No HP: `$no_hp`\n"
                         . "📧 Email: `$email`\n\n"
                         . "💡 _Kirim pesan *cek permohonan* ke bot untuk memproses._";

            $ch_tel = curl_init();
            curl_setopt_array($ch_tel, array(
                CURLOPT_URL => "https://api.telegram.org/bot" . $tele_token . "/sendMessage",
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => json_encode(array('chat_id' => $admin_chat_id, 'text' => $pesan_tele, 'parse_mode' => 'Markdown')),
                CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false
            ));
            curl_exec($ch_tel);
            curl_close($ch_tel);

        } else {
            $error_msg = "Terjadi kesalahan saat menyimpan data. Silakan coba lagi.";
        }
    } catch (mysqli_sql_exception $e) {
        $error_msg = "Terjadi kesalahan sistem. Mohon coba kirim ulang formulir Anda.";
    }
}
?>

<style>
    .form-wrapper { max-width: 1100px; margin: 50px auto 90px auto; padding: 0 20px; font-family: 'Inter', 'Segoe UI', sans-serif; }
    .form-card { background: #ffffff; border-radius: 20px; padding: 45px; box-shadow: 0 10px 35px rgba(0, 0, 0, 0.04); border: 1px solid #eaeaea; position: relative; overflow: hidden; transition: all 0.35s ease; }
    .form-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: linear-gradient(90deg, var(--primary, #00b2a9), #008f88); }
    .form-header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #f3f4f6; padding-bottom: 20px; flex-wrap: wrap; gap: 20px; }
    .form-header-top h1 { margin: 0; color: #1f2937; font-size: 1.65rem; font-weight: 800; letter-spacing: 0.3px; }
    .form-header-top .subtitle { font-size: 0.82rem; color: var(--hover-color, #ef8a1c); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; display: block; }
    .tracker-steps { display: flex; gap: 12px; }
    .step-box { border: 1px solid #e5e7eb; padding: 10px 16px; border-radius: 10px; text-align: center; font-size: 0.72rem; color: #6b7280; background: #f9fafb; font-weight: 600; }
    .step-box strong { display: block; color: #1f2937; font-size: 0.82rem; font-weight: 800; }
    .form-grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
    .section-title { font-size: 0.95rem; color: var(--primary, #00b2a9); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 22px; border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; display: flex; align-items: center; gap: 8px; }
    .form-group { margin-bottom: 22px; }
    .form-group label { display: block; font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; }
    .warning-text { color: #dc2626; font-size: 0.72rem; background: #fef2f2; padding: 6px 10px; border-radius: 6px; display: inline-block; margin-bottom: 8px; font-weight: 600; border: 1px solid #fecaca; }
    .form-control { width: 100%; padding: 13px 16px; border: 1px solid #d1d5db; border-radius: 12px; font-size: 0.95rem; box-sizing: border-box; background: #f9fafb; color: #1f2937; font-family: inherit; }
    .form-control:focus { outline: none; border-color: var(--primary, #00b2a9); background: white; box-shadow: 0 0 0 4px rgba(0, 178, 169, 0.12); }
    .upload-box { border: 2px dashed #cbd5e1; background: #f9fafb; border-radius: 14px; padding: 25px 20px; text-align: center; cursor: pointer; position: relative; }
    .upload-box input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; }
    .upload-icon { font-size: 2rem; color: var(--primary, #00b2a9); margin-bottom: 8px; }
    .upload-text { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
    .upload-text strong { display: block; margin-bottom: 4px; color: #1f2937; font-weight: 700; }
    .upload-file-info { display: flex; flex-direction: column; align-items: center; gap: 8px; position: relative; z-index: 3; }
    .file-name-display { font-size: 0.9rem; font-weight: 700; color: var(--primary-dark, #008f88); word-break: break-all; background: white; padding: 6px 14px; border-radius: 8px; border: 1px solid #ccfbf1; display: inline-block; }
    .btn-remove-file { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; padding: 6px 14px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; cursor: pointer; }
    .radio-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .radio-grid-full { display: grid; grid-template-columns: 1fr; gap: 10px; }
    .radio-option { display: flex; align-items: center; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 12px; cursor: pointer; font-size: 0.9rem; font-weight: 600; color: #4b5563; background: #ffffff; }
    .radio-option input[type="radio"] { margin-right: 12px; transform: scale(1.2); accent-color: var(--primary, #00b2a9); cursor: pointer; }
    .btn-submit { background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%); color: white; border: none; padding: 16px; border-radius: 14px; font-size: 1.02rem; font-weight: 800; cursor: pointer; width: 100%; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 6px 20px rgba(0, 178, 169, 0.25); }
    .alert-success-box { background: #f0fdf4; border: 2px dashed #22c55e; padding: 50px 30px; border-radius: 20px; text-align: center; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.05); }
    .alert-success-box h3 { color: #15803d; margin: 0 0 12px 0; font-size: 1.65rem; font-weight: 800; }
    .code-highlight { display: inline-block; background: white; border: 2px solid #22c55e; color: #15803d; font-size: 2.2rem; font-weight: 900; padding: 14px 35px; border-radius: 14px; margin: 20px 0; letter-spacing: 2px; user-select: all; box-shadow: 0 6px 18px rgba(34, 197, 94, 0.1); }
    .btn-lacak { display: inline-block; background: linear-gradient(135deg, var(--hover-color, #ef8a1c) 0%, #d97706 100%); color: white; text-decoration: none; padding: 14px 30px; border-radius: 12px; font-weight: 800; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(239, 138, 28, 0.3); margin: 5px; }
    .btn-pdf { background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3); }
    .alert-error { background-color: #fee2e2; color: #b91c1c; padding: 15px 20px; border-radius: 12px; border: 1px solid #fecaca; margin-bottom: 25px; font-weight: 600; text-align: center; }
    @media (max-width: 900px) { .form-grid-layout { grid-template-columns: 1fr; gap: 30px; } }
</style>

<main style="background-color: #f8fafc; padding-top: 20px; padding-bottom: 40px;">
    <div class="form-wrapper">
        
        <?php if (!empty($error_msg)): ?>
            <div class="alert-error">⚠️ <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if ($pesan_sukses): ?>
            <div class="alert-success-box">
                <h3>✅ Permohonan Berhasil Dikirim!</h3>
                <p>Permohonan Anda telah terdaftar. Harap simpan <strong>Nomor Registrasi</strong> di bawah ini untuk melacak status layanan Anda:</p>
                <div class="code-highlight"><?php echo $kode_unik; ?></div>
                <p style="margin-bottom: 25px; color: #4b5563;">Anda dapat mengunduh bukti permohonan ini atau langsung melacak statusnya.</p>
                
                <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                    <a href="cetak_bukti_permohonan.php?kode=<?php echo $kode_unik; ?>" target="_blank" class="btn-lacak btn-pdf">
                        📄 Unduh Bukti PDF
                    </a>
                    <a href="cek_status.php?jenis_layanan=permohonan&kode=<?php echo $kode_unik; ?>" class="btn-lacak">
                        Lacak Status Sekarang &raquo;
                    </a>
                </div>
            </div>
        <?php else: ?>

            <div class="form-card">
                <div class="form-header-top">
                    <div>
                        <span class="subtitle">— PPID BKK BANJARMASIN</span>
                        <h1>Form Permohonan Informasi Publik</h1>
                    </div>
                    <div class="tracker-steps">
                        <div class="step-box"><strong>Identitas</strong>PEMOHON</div>
                        <div class="step-box"><strong>Rincian</strong>INFORMASI</div>
                        <div class="step-box"><strong>Salinan</strong>PREFERENSI</div>
                    </div>
                </div>

                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="kirim_permohonan" value="1">
                    
                    <div class="form-grid-layout">
                        <div>
                            <div class="section-title"><span>👤</span> Data Pemohon</div>
                            
                            <div class="form-group">
                                <label>Kategori Permohonan</label>
                                <select name="kategori_permohonan" id="kategori" class="form-control" onchange="updateFormUI()" required>
                                    <option value="Perorangan">Perorangan</option>
                                    <option value="Lembaga / Organisasi">Lembaga / Organisasi</option>
                                    <option value="Kelompok Orang">Kelompok Orang</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label id="lbl_nik">NIK/No. Identitas Pribadi</label>
                                <div class="warning-text">⚠️ Mohon pastikan NIK yang Anda masukan sesuai dengan nomor KTP</div>
                                <input type="text" name="nik" class="form-control" placeholder="Masukan NIK / No. Identitas..." required>
                            </div>

                            <div class="form-group">
                                <label id="lbl_nama">Nama Lengkap</label>
                                <input type="text" name="nama_pemohon" class="form-control" placeholder="Masukan Nama Lengkap Anda..." required>
                            </div>

                            <div class="form-group">
                                <label id="lbl_ktp">Upload KTP Pribadi</label>
                                <div class="upload-box" id="box_ktp">
                                    <input type="file" name="file_ktp" id="input_ktp" accept=".jpg,.jpeg,.png,.pdf" required onchange="handleFileSelect(this, 'ktp')">
                                    <div class="upload-default-content" id="default_ktp">
                                        <div class="upload-icon">📥</div>
                                        <div class="upload-text">
                                            <strong>Upload File KTP atau Seret File ke Sini</strong>
                                            Maksimal Ukuran 3 MB (JPG/PNG/PDF)
                                        </div>
                                    </div>
                                    <div class="upload-file-info" id="info_ktp" style="display: none;">
                                        <div class="file-name-display">📄 <span id="name_ktp"></span></div>
                                        <button type="button" class="btn-remove-file" onclick="removeFile(event, 'input_ktp', 'default_ktp', 'info_ktp', true)">✕ Ganti / Hapus File</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control" rows="3" placeholder="Masukan Alamat Lengkap Anda..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Masukan Alamat Email Anda..." required>
                            </div>

                            <div class="form-group">
                                <label>Nomor Telepon</label>
                                <input type="text" name="no_hp" class="form-control" placeholder="Masukan Nomor Telepon / WhatsApp..." required>
                            </div>

                            <div class="form-group">
                                <label>Pekerjaan</label>
                                <input type="text" name="pekerjaan" class="form-control" placeholder="Masukan Pekerjaan Anda..." required>
                            </div>
                        </div>

                        <div>
                            <div class="section-title"><span>📋</span> Detail Permohonan</div>

                            <div class="form-group" id="box_dokumen_tambahan" style="display: none;">
                                <label id="lbl_dokumen">Upload Akta Notaris / Surat Kuasa</label>
                                <div class="upload-box" id="box_dok" style="border-color: var(--secondary, #ef8a1c); background: #fffaf0;">
                                    <input type="file" name="file_dokumen" id="input_dokumen" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect(this, 'dok')">
                                    <div class="upload-default-content" id="default_dok">
                                        <div class="upload-icon" style="color: var(--secondary, #ef8a1c);">📤</div>
                                        <div class="upload-text">
                                            <strong>Upload Dokumen Tambahan</strong>
                                            Maksimal Ukuran 6 MB (JPG/PNG/PDF)
                                        </div>
                                    </div>
                                    <div class="upload-file-info" id="info_dok" style="display: none;">
                                        <div class="file-name-display" style="color: var(--secondary, #ef8a1c); border-color: #ffedd5;">📄 <span id="name_dok"></span></div>
                                        <button type="button" class="btn-remove-file" onclick="removeFile(event, 'input_dokumen', 'default_dok', 'info_dok', false)">✕ Ganti / Hapus File</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Rincian Informasi</label>
                                <textarea name="rincian_informasi" class="form-control" rows="4" placeholder="Masukan Rincian Informasi yang Dibutuhkan..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Tujuan Penggunaan Informasi</label>
                                <textarea name="tujuan_penggunaan" class="form-control" rows="3" placeholder="Masukan Tujuan Penggunaan Informasi..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Cara Memperoleh Informasi</label>
                                <div class="radio-grid">
                                    <label class="radio-option"><input type="radio" name="cara_memperoleh" value="Melihat" required> Melihat</label>
                                    <label class="radio-option"><input type="radio" name="cara_memperoleh" value="Membaca"> Membaca</label>
                                    <label class="radio-option"><input type="radio" name="cara_memperoleh" value="Mendengarkan"> Mendengarkan</label>
                                    <label class="radio-option"><input type="radio" name="cara_memperoleh" value="Mencatat"> Mencatat</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Mendapatkan Salinan Informasi</label>
                                <div class="radio-grid">
                                    <label class="radio-option"><input type="radio" name="bentuk_salinan" value="Softcopy" required> Softcopy</label>
                                    <label class="radio-option"><input type="radio" name="bentuk_salinan" value="Hardcopy"> Hardcopy</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Cara Mendapatkan Salinan Informasi</label>
                                <div class="radio-grid-full">
                                    <label class="radio-option"><input type="radio" name="cara_mendapatkan" value="Mengambil Langsung" required> Mengambil Langsung</label>
                                    <label class="radio-option"><input type="radio" name="cara_mendapatkan" value="Email"> Email</label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 35px;">
                        <button type="submit" class="btn-submit" style="max-width: 400px;">Ajukan Permohonan 🚀</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function updateFormUI() {
    const kategori = document.getElementById('kategori').value;
    const lblNik = document.getElementById('lbl_nik');
    const lblNama = document.getElementById('lbl_nama');
    const lblKtp = document.getElementById('lbl_ktp');
    const boxDokumen = document.getElementById('box_dokumen_tambahan');
    const lblDokumen = document.getElementById('lbl_dokumen');
    const inputDokumen = document.getElementById('input_dokumen');

    if (kategori === 'Perorangan') {
        lblNik.innerText = 'NIK/No. Identitas Pribadi';
        lblNama.innerText = 'Nama Lengkap';
        lblKtp.innerText = 'Upload KTP Pribadi';
        boxDokumen.style.display = 'none';
        inputDokumen.required = false;
    } else if (kategori === 'Lembaga / Organisasi') {
        lblNik.innerText = 'NIK/No. Identitas Pimpinan';
        lblNama.innerText = 'Nama Lembaga / Organisasi';
        lblKtp.innerText = 'Upload KTP Pimpinan';
        boxDokumen.style.display = 'block';
        lblDokumen.innerText = 'Upload Akta Notaris Lembaga / Organisasi';
        inputDokumen.required = true;
    } else if (kategori === 'Kelompok Orang') {
        lblNik.innerText = 'NIK/No. Identitas Pemberi Kuasa';
        lblNama.innerText = 'Nama Kelompok Orang';
        lblKtp.innerText = 'Upload KTP Pemberi Kuasa';
        boxDokumen.style.display = 'block';
        lblDokumen.innerText = 'Upload Surat Kuasa';
        inputDokumen.required = true;
    }
}

function handleFileSelect(input, type) {
    if (input.files && input.files[0]) {
        const fileName = input.files[0].name;
        document.getElementById('name_' + type).innerText = fileName;
        document.getElementById('default_' + type).style.display = 'none';
        document.getElementById('info_' + type).style.display = 'flex';
    }
}

function removeFile(event, inputId, defaultId, infoId, isRequired) {
    event.stopPropagation();
    document.getElementById(inputId).value = ''; 
    document.getElementById(defaultId).style.display = 'block';
    document.getElementById(infoId).style.display = 'none';
}

document.addEventListener("DOMContentLoaded", function() {
    updateFormUI();
});
</script>

<?php include 'includes/footer.php'; ?>