<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

try {
    $conn->query("CREATE TABLE IF NOT EXISTS keberatan_informasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nomor_registrasi VARCHAR(50),
        status VARCHAR(50) DEFAULT 'Pending',
        tanggapan TEXT,
        tanggal_masuk TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $kolom_baru_k = [
        'kode_permohonan' => 'VARCHAR(50)',
        'nama_pemohon' => 'VARCHAR(150)',
        'nik' => 'VARCHAR(50)',
        'email' => 'VARCHAR(150)',
        'no_hp' => 'VARCHAR(50)',
        'alasan_keberatan' => 'VARCHAR(255)',
        'dikuasakan' => 'VARCHAR(20)',
        'kronologi' => 'TEXT',
        'surat_keberatan_path' => 'VARCHAR(255)'
    ];

    foreach ($kolom_baru_k as $kolom =>$tipe) {
        $cek =$conn->query("SHOW COLUMNS FROM keberatan_informasi LIKE '$kolom'");
        if ($cek && $cek->num_rows == 0) {$conn->query("ALTER TABLE keberatan_informasi ADD COLUMN $kolom$tipe");
        }
    }
} catch (Exception $e) { }

$pesan_sukses = false;
$pesan_error = "";
$kode_unik = "";
$data_ditemukan = false;
$info_pemohon = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cek_permohonan'])) {
    $kode_cek = mysqli_real_escape_string($conn, trim($_POST['kode_permohonan']));$nik_cek = mysqli_real_escape_string($conn, trim($_POST['nik']));

    $q_cari =$conn->query("SELECT * FROM permohonan_informasi WHERE nomor_registrasi = '$kode_cek' AND nik = '$nik_cek'");
    if ($q_cari &&$q_cari->num_rows > 0) {
        $info_pemohon =$q_cari->fetch_assoc();
        
        $status_permohonan = strtolower(trim($info_pemohon['status'] ?? ''));
        
        $is_pending  = strpos($status_permohonan, 'pending') !== false;
        $is_menunggu = strpos($status_permohonan, 'menunggu') !== false;
        $is_proses   = strpos($status_permohonan, 'proses') !== false;
        $is_empty    = ($status_permohonan == '');

        if ($is_pending OR$is_menunggu OR $is_proses OR$is_empty) {
            $status_tampil = !empty($info_pemohon['status']) ? htmlspecialchars($info_pemohon['status']) : 'Pending';$pesan_error = "Permohonan Anda sedang dalam proses (Status: $status_tampil). Silakan tunggu hingga permohonan selesai diproses untuk dapat mengajukan keberatan.";
            $data_ditemukan = false;
        } else {
            $data_ditemukan = true;
        }
    } else {
        $pesan_error = "Data permohonan dengan Kode dan NIK tersebut tidak ditemukan. Pastikan data yang dimasukkan benar.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['kirim_keberatan'])) {$kode_unik = "KBR-" . date('Ymd') . "-" . rand(1000, 9999) . "-" . substr(uniqid(), -4);
    
    $kode_permohonan = mysqli_real_escape_string($conn,$_POST['kode_permohonan']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);$alasan = mysqli_real_escape_string($conn,$_POST['alasan_keberatan'] ?? '');
    $dikuasakan = mysqli_real_escape_string($conn, $_POST['dikuasakan'] ?? 'Tidak');$kronologi = mysqli_real_escape_string($conn,$_POST['kronologi']);

    $q_nama =$conn->query("SELECT nama_pemohon, email, no_hp FROM permohonan_informasi WHERE nomor_registrasi = '$kode_permohonan'");
    $d_nama = ($q_nama && $q_nama->num_rows > 0) ?$q_nama->fetch_assoc() : ['nama_pemohon' => 'Pemohon', 'email' => '', 'no_hp' => ''];
    $nama_pemohon =$d_nama['nama_pemohon'];
    $email =$d_nama['email'];
    $no_hp =$d_nama['no_hp'];

    $upload_dir = 'uploads/keberatan/';
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }$surat_path = '';
    if(isset($_FILES['file_surat']) &&$_FILES['file_surat']['error'] == 0) {
        // Amankan penamaan file agar masuk sempurna ke database
        $ext = pathinfo($_FILES['file_surat']['name'], PATHINFO_EXTENSION);
        $nama_file_baru = time() . '_SK_' . substr(md5(uniqid()), 0, 5) . '.' . $ext;
        $surat_path = $upload_dir .$nama_file_baru;
        move_uploaded_file($_FILES['file_surat']['tmp_name'],$surat_path);
    }

    $q_insert = "INSERT INTO keberatan_informasi 
                 (nomor_registrasi, kode_permohonan, nama_pemohon, nik, email, no_hp, alasan_keberatan, dikuasakan, kronologi, surat_keberatan_path, status) 
                 VALUES ('$kode_unik', '$kode_permohonan', '$nama_pemohon', '$nik', '$email', '$no_hp', '$alasan', '$dikuasakan', '$kronologi', '$surat_path', 'Pending')";
                 
    if ($conn->query($q_insert)) {$pesan_sukses = true;
        
        $tele_token = '8838750735:AAER7EhJnD7Po8xO6ptEZNp59jgdwv3-E2o';$admin_chat_id = '7279608699';

        $pesan_tele = "⚖️ *PENGAJUAN KEBERATAN BARU* ⚖️\n\n"
                     . "Ada pengajuan keberatan informasi baru masuk di website!\n\n"
                     . "📌 Reg: `$kode_unik`\n"
                     . "👤 Nama: *$nama_pemohon*\n"
                     . "🔗 Req Terkait: `$kode_permohonan`\n"
                     . "📞 No HP: `$no_hp`\n"
                     . "📧 Email: `$email`\n\n"
                     . "💡 _Kirim pesan *cek keberatan* ke bot untuk memproses._";

        $ch_tel = curl_init();
        curl_setopt_array($ch_tel, array(
            CURLOPT_URL => "https://api.telegram.org/bot" . $tele_token . "/sendMessage",
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode(array('chat_id' => $admin_chat_id, 'text' =>$pesan_tele, 'parse_mode' => 'Markdown')),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ));
        curl_exec($ch_tel);
        curl_close($ch_tel);
    }
}
?>

<style>
    :root {
        --primary: #00b2a9; 
        --primary-dark: #008f88;
        --secondary: #ef8a1c;
        --secondary-dark: #d97706;
    }

    .form-wrapper { max-width: 1100px; margin: 40px auto; padding: 0 20px; font-family: 'Inter', sans-serif; }
    .form-card { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border-top: 5px solid var(--primary); transition: box-shadow 0.3s ease; }
    .form-header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 1px solid #eaeaea; padding-bottom: 20px; }
    .form-header-top h1 { margin: 0; color: var(--primary); font-size: 1.8rem; font-weight: 800; }
    .form-header-top .subtitle { font-size: 0.85rem; color: var(--secondary); font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; display: block; }
    .tracker-steps { display: flex; gap: 10px; }
    .step-box { border: 1px solid #e5e7eb; padding: 10px 15px; border-radius: 6px; text-align: center; font-size: 0.75rem; color: #6b7280; background: #f9fafb; }
    .step-box strong { display: block; color: #1f2937; font-size: 0.85rem; }
    .section-title { font-size: 0.95rem; color: var(--primary-dark); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; }
    .validasi-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 25px; margin-bottom: 35px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: center; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 700; font-size: 0.8rem; margin-bottom: 8px; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-control { width: 100%; padding: 12px 15px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box; background: white; transition: all 0.3s ease; }
    .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0, 178, 169, 0.15); }
    .btn-cek { background: var(--primary); color: white; border: none; padding: 14px 25px; border-radius: 8px; font-size: 0.95rem; font-weight: 800; cursor: pointer; width: 100%; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 6px rgba(0, 178, 169, 0.2); }
    .btn-cek:hover { background: var(--primary-dark); box-shadow: 0 6px 15px rgba(0, 178, 169, 0.3); transform: translateY(-2px); }
    .alert-error { background: #fee2e2; color: #dc2626; padding: 12px 15px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; margin-bottom: 20px; }
    .alert-verified { background: #f0fdfa; border: 1px solid #ccfbf1; color: #0f766e; padding: 15px; border-radius: 8px; font-weight: 600; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; }
    .alasan-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 35px; }
    .alasan-option { display: flex; align-items: flex-start; padding: 15px; border: 1px solid #d1d5db; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; font-size: 0.85rem; font-weight: 600; color: #374151; background: white; }
    .alasan-option:hover { border-color: var(--primary); background: #f0fdfa; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .alasan-option input[type="radio"], .alasan-option input[type="checkbox"] { margin-right: 12px; margin-top: 2px; transform: scale(1.2); accent-color: var(--primary); cursor: pointer; }
    .upload-box { border: 2px dashed #9ca3af; background: #f9fafb; border-radius: 8px; padding: 25px; text-align: center; cursor: pointer; transition: all 0.3s ease; position: relative; }
    .upload-box:hover { border-color: var(--primary); background: #f0fdfa; }
    .upload-box input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 5; }
    .upload-icon { font-size: 2rem; color: var(--primary); margin-bottom: 8px; }
    .upload-text { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
    .upload-text strong { display: block; margin-bottom: 4px; color: #374151; }
    .btn-submit { background: var(--secondary); color: white; border: none; padding: 16px; border-radius: 8px; font-size: 1rem; font-weight: 800; cursor: pointer; width: 100%; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 6px rgba(239, 138, 28, 0.2); }
    .btn-submit:hover { background: var(--secondary-dark); box-shadow: 0 8px 15px rgba(239, 138, 28, 0.35); transform: translateY(-3px); }
    .alert-success-box { background: #f0fdfa; border: 2px dashed var(--primary); padding: 40px; border-radius: 12px; text-align: center; margin-bottom: 30px; }
    .code-highlight { display: inline-block; background: white; border: 2px solid var(--primary); color: var(--primary); font-size: 2rem; font-weight: 800; padding: 12px 30px; border-radius: 8px; margin: 20px 0; letter-spacing: 2px; }
    .btn-lacak { display: inline-block; background: var(--secondary); color: white; text-decoration: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; transition: 0.3s; margin: 5px; }
    .btn-pdf { background: #10b981; }
    .btn-pdf:hover { background: #059669; }
    @media (max-width: 900px) { .validasi-card, .alasan-grid { grid-template-columns: 1fr; } }
</style>

<main style="background-color: #f3f4f6; padding-top: 20px; padding-bottom: 40px;">
    <div class="form-wrapper">
        
        <?php if ($pesan_sukses): ?>
            <div class="alert-success-box">
                <h3 style="color: var(--primary-dark);">⚖️ Pengajuan Keberatan Berhasil Dikirim!</h3>
                <p>Pengajuan keberatan Anda telah masuk ke sistem. Harap simpan <strong>Nomor Registrasi Keberatan</strong> di bawah ini:</p>
                <div class="code-highlight"><?php echo $kode_unik; ?></div>
                <p style="margin-bottom: 20px;">Anda dapat mengunduh bukti pengajuan keberatan ini atau melacak statusnya.</p>
                
                <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                    <a href="cetak_bukti_keberatan.php?kode=<?php echo $kode_unik; ?>" target="_blank" class="btn-lacak btn-pdf">
                        📄 Unduh Bukti PDF
                    </a>
                    <a href="cek_status.php?jenis_layanan=keberatan&kode=<?php echo $kode_unik; ?>" class="btn-lacak">
                        Lacak Status Keberatan &raquo;
                    </a>
                </div>
            </div>
        <?php else: ?>

            <div class="form-card">
                <div class="form-header-top">
                    <div>
                        <span class="subtitle">— PPID BKK BANJARMASIN</span>
                        <h1>Form Pengajuan Keberatan Informasi Publik</h1>
                    </div>
                    <div class="tracker-steps">
                        <div class="step-box"><strong>Validasi</strong>PERMOHONAN</div>
                        <div class="step-box"><strong>Alasan</strong>KEBERATAN</div>
                        <div class="step-box"><strong>Berkas</strong>PENDUKUNG</div>
                    </div>
                </div>

                <?php if(!empty($pesan_error)): ?>
                    <div class="alert-error">⚠️ <?php echo $pesan_error; ?></div>
                <?php endif; ?>

                <?php if (!$data_ditemukan): ?>
                    <form action="" method="POST">
                        <div class="section-title">VALIDASI PERMOHONAN AWAL</div>
                        <div class="validasi-card">
                            <div>
                                <div class="form-group">
                                    <label>KODE PERMOHONAN *</label>
                                    <input type="text" name="kode_permohonan" class="form-control" placeholder="Contoh: REQ-20260928-1234" required>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>NIK PEMOHON *</label>
                                    <input type="text" name="nik" class="form-control" placeholder="Masukkan 16 digit NIK Anda" required>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 15px;">Masukkan nomor registrasi permohonan dan NIK Anda untuk melanjutkan pengajuan keberatan.</p>
                                <button type="submit" name="cek_permohonan" class="btn-cek">Cek Data Permohonan 🔍</button>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert-verified">
                        <span>✅ Data Permohonan Ditemukan atas nama: <strong><?php echo htmlspecialchars($info_pemohon['nama_pemohon']); ?></strong> (Reg: <?php echo htmlspecialchars($info_pemohon['nomor_registrasi']); ?>)</span>
                        <a href="form_keberatan.php" style="font-size: 0.85rem; color: #0f766e; text-decoration: underline;">Ganti Data</a>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="kode_permohonan" value="<?php echo htmlspecialchars($info_pemohon['nomor_registrasi']); ?>">
                        <input type="hidden" name="nik" value="<?php echo htmlspecialchars($info_pemohon['nik']); ?>">
                        
                        <div class="section-title">ALASAN PENGAJUAN KEBERATAN *</div>
                        
                        <div class="alasan-grid">
                            <label class="alasan-option">
                                <input type="radio" name="alasan_keberatan" value="Penolakan atas permintaan informasi berdasarkan alasan pengecualian" required>
                                Penolakan atas permintaan informasi berdasarkan alasan pengecualian sebagaimana dimaksud dalam Pasal 17 UU No. 14 Tahun 2008
                            </label>
                            
                            <label class="alasan-option">
                                <input type="radio" name="alasan_keberatan" value="Tidak disediakannya informasi berkala">
                                Tidak disediakannya informasi berkala
                            </label>

                            <label class="alasan-option">
                                <input type="radio" name="alasan_keberatan" value="Tidak ditanggapinya permintaan informasi">
                                Tidak ditanggapinya permintaan informasi
                            </label>

                            <label class="alasan-option">
                                <input type="radio" name="alasan_keberatan" value="Permintaan informasi tidak ditanggapi sebagaimana yang diminta">
                                Permintaan informasi tidak ditanggapi sebagaimana yang diminta
                            </label>

                            <label class="alasan-option">
                                <input type="radio" name="alasan_keberatan" value="Tidak dipenuhinya permintaan informasi">
                                Tidak dipenuhinya permintaan informasi
                            </label>

                            <label class="alasan-option">
                                <input type="radio" name="alasan_keberatan" value="Pengenaan biaya yang tidak wajar">
                                Pengenaan biaya yang tidak wajar
                            </label>

                            <label class="alasan-option">
                                <input type="radio" name="alasan_keberatan" value="Penyampaian informasi melebihi jangka waktu yang diatur dalam UU">
                                Penyampaian informasi yang melebihi jangka waktu yang diatur dalam UU No. 14 Tahun 2008
                            </label>

                            <label class="alasan-option" style="align-items: center;">
                                <input type="checkbox" name="dikuasakan" value="Ya">
                                Pengajuan keberatan dikuasakan ( Jika ada )
                            </label>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                            <div class="form-group">
                                <label>KRONOLOGI ( PENJELASAN KEBERATAN ) *</label>
                                <textarea name="kronologi" class="form-control" rows="6" placeholder="Masukan Kronologi ( Penjelasan Keberatan )..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label>UPLOAD SURAT KEBERATAN *</label>
                                <div class="upload-box" id="areaUpload" style="height: 145px; display: flex; flex-direction: column; justify-content: center; position: relative;">
                                    
                                    <!-- Input file utama -->
                                    <input type="file" name="file_surat" id="inputFileSurat" accept=".jpg,.jpeg,.png,.pdf" required>
                                    
                                    <!-- Tampilan Awal (Belum ada file) -->
                                    <div id="tampilanAwal">
                                        <div class="upload-icon">📥</div>
                                        <div class="upload-text">
                                            <strong>Upload Surat Keberatan or Drag File</strong>
                                            Max Upload 6 MB (JPG/PNG/PDF)
                                        </div>
                                    </div>

                                    <!-- Tampilan Setelah File Dipilih (Awalnya disembunyikan) -->
                                    <div id="tampilanFileTerpilih" style="display: none; flex-direction: column; align-items: center; justify-content: center; position: relative; z-index: 10;">
                                        <div class="upload-icon" style="color: #10b981; margin-bottom: 5px;">📄</div>
                                        <div class="upload-text">
                                            <strong id="namaFileTampil" style="color: #0f766e; padding: 0 10px; word-break: break-all;">nama_file.pdf</strong>
                                        </div>
                                        <!-- Tombol Hapus -->
                                        <button type="button" id="btnHapusFile" style="margin-top: 10px; background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; padding: 6px 15px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; cursor: pointer; pointer-events: auto;">
                                            ✖ Hapus File
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div style="text-align: center; margin-top: 30px;">
                            <button type="submit" name="kirim_keberatan" class="btn-submit" style="max-width: 400px;">AJUKAN KEBERATAN</button>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </div>
</main>

<!-- SCRIPT UNTUK PREVIEW NAMA FILE DAN TOMBOL HAPUS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputSurat = document.getElementById('inputFileSurat');
    const tampilanAwal = document.getElementById('tampilanAwal');
    const tampilanFileTerpilih = document.getElementById('tampilanFileTerpilih');
    const namaFileTampil = document.getElementById('namaFileTampil');
    const btnHapusFile = document.getElementById('btnHapusFile');

    if (inputSurat) {
        // Ketika file dipilih oleh user
        inputSurat.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                // Tampilkan nama file dan tombol hapus
                tampilanAwal.style.display = 'none';
                tampilanFileTerpilih.style.display = 'flex';
                namaFileTampil.textContent = this.files[0].name;
                
                // Turunkan z-index input agar tombol hapus bisa diklik
                inputSurat.style.zIndex = '-1'; 
            }
        });

        // Ketika tombol hapus diklik
        btnHapusFile.addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah form tersubmit secara tidak sengaja
            
            // Reset input file
            inputSurat.value = ''; 
            
            // Kembalikan ke tampilan awal (kosong)
            tampilanAwal.style.display = 'block';
            tampilanFileTerpilih.style.display = 'none';
            
            // Naikkan kembali z-index input agar bisa diklik untuk upload file baru
            inputSurat.style.zIndex = '5';
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>