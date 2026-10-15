<!-- user/permohonan/proses.php -->
<?php
// Mulai session dan panggil koneksi database
session_start();
require_once '../../includes/koneksi.php';

// Pastikan form dikirim melalui metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Tangkap dan bersihkan data teks dari form
    $kategori          = mysqli_real_escape_string($conn, $_POST['kategori']);
    $nik               = mysqli_real_escape_string($conn, $_POST['nik']);
    $nama              = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat            = mysqli_real_escape_string($conn, $_POST['alamat']);
    $email             = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telp           = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $pekerjaan         = mysqli_real_escape_string($conn, $_POST['pekerjaan']);
    $rincian_informasi = mysqli_real_escape_string($conn, $_POST['rincian_informasi']);
    $tujuan_penggunaan = mysqli_real_escape_string($conn, $_POST['tujuan_penggunaan']);
    $bentuk_salinan    = mysqli_real_escape_string($conn, $_POST['bentuk_salinan']);
    $cara_mendapatkan  = mysqli_real_escape_string($conn, $_POST['cara_mendapatkan']);

    // Menggabungkan array cara_memperoleh (checkbox) menjadi satu string dipisah koma
    $cara_memperoleh_arr = isset($_POST['cara_memperoleh']) ? $_POST['cara_memperoleh'] : [];
    $cara_memperoleh     = mysqli_real_escape_string($conn, implode(", ", $cara_memperoleh_arr));

    // 2. Generate Kode Permohonan Unik (Format: REQ-YYYYMMDD-XXXX)
    $tanggal_hari_ini = date("Ymd");
    $random_angka     = rand(1000, 9999);
    $kode_permohonan  = "REQ-" . $tanggal_hari_ini . "-" . $random_angka;

    // 3. Proses Upload File KTP
    $nama_file_asli = $_FILES['file_ktp']['name'];
    $ukuran_file    = $_FILES['file_ktp']['size'];
    $error_file     = $_FILES['file_ktp']['error'];
    $tmp_file       = $_FILES['file_ktp']['tmp_name'];

    // Validasi ekstensi file
    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'pdf'];
    $ekstensi_file      = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));

    if (!in_array($ekstensi_file, $ekstensi_diizinkan)) {
        die("GAGAL: Ekstensi file tidak diizinkan. Hanya JPG, JPEG, PNG, atau PDF.");
    }

    // Validasi ukuran file (Max 3MB)
    if ($ukuran_file > 3000000) {
        die("GAGAL: Ukuran file melebihi 3 MB.");
    }

    // === PERBAIKAN: Buat folder otomatis jika belum ada ===
    $folder_upload = "../../uploads/ktp/";
    if (!is_dir($folder_upload)) {
        // Buat folder dengan permission 0777 (read, write, execute), true untuk membuat folder bersarang
        mkdir($folder_upload, 0777, true); 
    }

    // Buat nama file baru agar tidak bentrok di folder (Contoh: NIK_KTP_timestamp.jpg)
    $nama_file_baru = $nik . "_KTP_" . time() . "." . $ekstensi_file;
    $folder_tujuan  = $folder_upload . $nama_file_baru;

    // Pindahkan file dari penampungan sementara ke folder tujuan
    if (move_uploaded_file($tmp_file, $folder_tujuan)) {
        
        // 4. Simpan Path File untuk Database
        // Kita simpan path relatif dari root agar mudah dipanggil nanti
        $path_database = "uploads/ktp/" . $nama_file_baru;

        // 5. Insert Data ke Database
        $query = "INSERT INTO permohonan_informasi (
                    kode_permohonan, kategori_pemohon, nik, nama_lengkap, file_ktp, 
                    alamat, email, no_telp, pekerjaan, rincian_informasi, 
                    tujuan_penggunaan, cara_memperoleh, bentuk_salinan, cara_mendapatkan, status
                  ) VALUES (
                    '$kode_permohonan', '$kategori', '$nik', '$nama', '$path_database', 
                    '$alamat', '$email', '$no_telp', '$pekerjaan', '$rincian_informasi', 
                    '$tujuan_penggunaan', '$cara_memperoleh', '$bentuk_salinan', '$cara_mendapatkan', 'Menunggu Verifikasi'
                  )";

        if (mysqli_query($conn, $query)) {
            // Jika berhasil, alihkan ke halaman sukses dengan membawa kode permohonan
            header("Location: sukses.php?kode=" . $kode_permohonan);
            exit();
        } else {
            echo "Error Database: " . $query . "<br>" . mysqli_error($conn);
        }

    } else {
        die("GAGAL: Terjadi kesalahan saat memindahkan file KTP ke folder server.");
    }
} else {
    // Jika file diakses langsung tanpa lewat form, tendang balik ke form
    header("Location: form.php");
    exit();
}
?>