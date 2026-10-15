<!-- user/keberatan/proses_keberatan.php -->
<?php
session_start();
require_once '../../includes/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Tangkap dan amankan data teks
    $kode_permohonan = mysqli_real_escape_string($conn, $_POST['kode_permohonan']);
    $nik             = mysqli_real_escape_string($conn, $_POST['nik']);
    $kronologi       = mysqli_real_escape_string($conn, $_POST['kronologi']);
    $dikuasakan      = isset($_POST['dikuasakan']) ? 'Ya' : 'Tidak';

    // Gabungkan alasan keberatan (checkbox array) menjadi string
    $alasan_arr      = isset($_POST['alasan']) ? $_POST['alasan'] : [];
    $alasan_keberatan = mysqli_real_escape_string($conn, implode(", ", $alasan_arr));

    if (empty($alasan_keberatan)) {
        die("GAGAL: Anda harus memilih minimal satu alasan pengajuan keberatan.");
    }

    // 2. Generate Kode Keberatan Unik (Format: OBJ-YYYYMMDD-XXXX)
    $tanggal_hari_ini = date("Ymd");
    $random_angka     = rand(1000, 9999);
    $kode_keberatan   = "OBJ-" . $tanggal_hari_ini . "-" . $random_angka;

    // 3. Proses Upload Surat Keberatan
    $nama_file_asli = $_FILES['file_surat']['name'];
    $ukuran_file    = $_FILES['file_surat']['size'];
    $tmp_file       = $_FILES['file_surat']['tmp_name'];

    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'pdf'];
    $ekstensi_file      = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));

    if (!in_array($ekstensi_file, $ekstensi_diizinkan)) {
        die("GAGAL: Ekstensi file surat tidak diizinkan. Hanya JPG, JPEG, PNG, atau PDF.");
    }

    // Maksimal ukuran 6 MB sesuai keterangan form
    if ($ukuran_file > 6000000) {
        die("GAGAL: Ukuran file surat keberatan melebihi 6 MB.");
    }

    // Buat folder otomatis jika belum ada
    $folder_upload = "../../uploads/surat_keberatan/";
    if (!is_dir($folder_upload)) {
        mkdir($folder_upload, 0777, true);
    }

    $nama_file_baru = $nik . "_SURAT_" . time() . "." . $ekstensi_file;
    $folder_tujuan  = $folder_upload . $nama_file_baru;

    if (move_uploaded_file($tmp_file, $folder_tujuan)) {
        
        $path_database = "uploads/surat_keberatan/" . $nama_file_baru;

        // 4. Simpan ke Database
        $query = "INSERT INTO pengajuan_keberatan (
                    kode_keberatan, kode_permohonan, nik, alasan_keberatan, 
                    dikuasakan, kronologi, file_surat, status
                  ) VALUES (
                    '$kode_keberatan', '$kode_permohonan', '$nik', '$alasan_keberatan', 
                    '$dikuasakan', '$kronologi', '$path_database', 'Menunggu Review'
                  )";

        if (mysqli_query($conn, $query)) {
            header("Location: sukses.php?kode=" . $kode_keberatan);
            exit();
        } else {
            echo "Error Database: " . mysqli_error($conn);
        }

    } else {
        die("GAGAL: Terjadi kesalahan saat mengunggah surat keberatan.");
    }
} else {
    header("Location: form.php");
    exit();
}
?>