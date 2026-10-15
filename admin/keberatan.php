<?php 
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/koneksi.php';

// Fungsi pewarnaan badge status (Dipanggil juga oleh AJAX)
function badgeStatus($status) {
    $st = strtolower(trim($status ?? ''));
    if ($st == 'pending' || $st == 'menunggu review') return '<span class="badge badge-pending">Pending / Menunggu</span>';
    if ($st == 'diproses' || $st == 'proses') return '<span class="badge badge-process">Diproses</span>';
    if ($st == 'selesai') return '<span class="badge badge-success">Selesai</span>';
    if ($st == 'ditolak') return '<span class="badge badge-danger">Ditolak</span>';
    return '<span class="badge badge-pending">'.htmlspecialchars($status).'</span>';
}

// =========================================================================================
// [FITUR BARU] ENDPOINT AJAX UNTUK AUTO-REFRESH TABEL SAJA
// =========================================================================================
if (isset($_GET['action']) && $_GET['action'] == 'fetch_table') {
    $no = 1;
    $q_keberatan = $conn->query("SELECT * FROM keberatan_informasi ORDER BY tanggal_masuk DESC");
    
    if($q_keberatan && $q_keberatan->num_rows > 0) {
        while($row = $q_keberatan->fetch_assoc()) {
            $data_json = htmlspecialchars(json_encode([
                'id' => $row['id'],
                'nomor_registrasi' => $row['nomor_registrasi'] ?? '',
                'kode_permohonan' => $row['kode_permohonan'] ?? '',
                'nama_pemohon' => $row['nama_pemohon'] ?? '',
                'nik' => $row['nik'] ?? '',
                'kontak' => ($row['no_hp'] ?? '') . ' / ' . ($row['email'] ?? ''),
                'alasan' => $row['alasan_keberatan'] ?? '',
                'kronologi' => $row['kronologi'] ?? '',
                'surat_path' => $row['surat_keberatan_path'] ?? '',
                'status' => $row['status'] ?? 'Pending',
                'tanggapan' => $row['tanggapan'] ?? '',
                'file_jawaban_pdf' => $row['file_jawaban_pdf'] ?? ''
            ]), ENT_QUOTES, 'UTF-8');
            
            echo "<tr>
                    <td>" . $no++ . "</td>
                    <td><strong style='color:var(--secondary); font-size:1.05rem;'>" . htmlspecialchars($row['nomor_registrasi']) . "</strong></td>
                    <td style='white-space:nowrap;'>" . date('d M Y, H:i', strtotime($row['tanggal_masuk'])) . "</td>
                    <td>
                        <div style='font-weight:700; color:#1f2937;'>" . htmlspecialchars($row['nama_pemohon']) . "</div>
                        <div style='font-size:0.78rem; color:#6b7280; margin-top:4px;'>NIK: " . htmlspecialchars($row['nik']) . "</div>
                    </td>
                    <td>
                        <div style='font-weight: 700; color: #1f2937; margin-bottom: 6px;'>" . htmlspecialchars($row['kode_permohonan']) . "</div>
                        <a href='permohonan.php?cari=" . urlencode($row['kode_permohonan']) . "' target='_blank' class='btn-cek-mini'>
                            🔍 Cek Data
                        </a>
                    </td>
                    <td>" . badgeStatus($row['status']) . "</td>
                    <td>
                        <div style='display: flex; gap: 8px;'>
                            <button class='btn-action btn-edit' onclick='openModal(" . $data_json . ")'>⚖️ Proses</button>
                            <a href='keberatan.php?action=delete&id=" . $row['id'] . "' class='btn-action btn-del' onclick=\"return confirm('Apakah Anda yakin ingin menghapus data pengajuan keberatan ini?')\">🗑</a>
                        </div>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='7' style='text-align: center; color: #9ca3af; padding: 40px; font-style: italic;'>Belum ada data keberatan yang diajukan.</td></tr>";
    }
    exit; // Hentikan script untuk AJAX request
}

// =========================================================================================
// AUTO-PATCH DATABASE
// =========================================================================================
try {
    $conn->query("ALTER TABLE keberatan_informasi ADD COLUMN IF NOT EXISTS file_jawaban_pdf VARCHAR(255)");
    $conn->query("ALTER TABLE keberatan_informasi ADD COLUMN IF NOT EXISTS tanggapan TEXT");
} catch(Exception $e) {}

$pesan_aksi = '';
$tipe_pesan = '';

// ==========================================================
// 1. PROSES HAPUS KEBERATAN
// ==========================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = (int)$_GET['id'];
    $cek_data = $conn->query("SELECT surat_keberatan_path, file_jawaban_pdf FROM keberatan_informasi WHERE id = $id_hapus")->fetch_assoc();
    
    if ($cek_data) {
        if (!empty($cek_data['surat_keberatan_path']) && file_exists("../" . $cek_data['surat_keberatan_path'])) { unlink("../" . $cek_data['surat_keberatan_path']); }
        if (!empty($cek_data['file_jawaban_pdf']) && file_exists("../" . $cek_data['file_jawaban_pdf'])) { unlink("../" . $cek_data['file_jawaban_pdf']); }
    }
    
    if($conn->query("DELETE FROM keberatan_informasi WHERE id = $id_hapus")) {
        $pesan_aksi = "Data pengajuan keberatan berhasil dihapus.";
        $tipe_pesan = "success";
    } else {
        $pesan_aksi = "Gagal menghapus data.";
        $tipe_pesan = "error";
    }
}

// ==========================================================
// PROSES HAPUS SEMUA DATA KEBERATAN
// ==========================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete_all') {
    $q_all = $conn->query("SELECT surat_keberatan_path, file_jawaban_pdf FROM keberatan_informasi");
    
    if ($q_all && $q_all->num_rows > 0) {
        while ($cek_data = $q_all->fetch_assoc()) {
            if (!empty($cek_data['surat_keberatan_path']) && file_exists("../" . $cek_data['surat_keberatan_path'])) { unlink("../" . $cek_data['surat_keberatan_path']); }
            if (!empty($cek_data['file_jawaban_pdf']) && file_exists("../" . $cek_data['file_jawaban_pdf'])) { unlink("../" . $cek_data['file_jawaban_pdf']); }
        }
    }
    
    if($conn->query("TRUNCATE TABLE keberatan_informasi")) {
        $pesan_aksi = "Semua data pengajuan keberatan berhasil dihapus.";
        $tipe_pesan = "success";
    } else {
        $pesan_aksi = "Gagal menghapus semua data keberatan.";
        $tipe_pesan = "error";
    }
}

// ==========================================================
// 2. PROSES UPDATE STATUS, TANGGAPAN & UPLOAD PDF JAWABAN
// ==========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $id_keberatan = (int)$_POST['id_keberatan'];
    $status_baru = mysqli_real_escape_string($conn, $_POST['status']);
    $tanggapan = mysqli_real_escape_string($conn, $_POST['tanggapan']);
    
    $pdf_query_part = "";
    if (isset($_FILES['file_jawaban']) && $_FILES['file_jawaban']['error'] == 0) {
        $upload_dir = '../uploads/jawaban_keberatan/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $nama_file_pdf = time() . '_JAWABAN_KBR_' . basename($_FILES['file_jawaban']['name']);
        $target_path = $upload_dir . $nama_file_pdf;
        
        if (move_uploaded_file($_FILES['file_jawaban']['tmp_name'], $target_path)) {
            $db_pdf_path = 'uploads/jawaban_keberatan/' . $nama_file_pdf;
            $pdf_query_part = ", file_jawaban_pdf = '$db_pdf_path'";
        }
    }
    
    $query_update = "UPDATE keberatan_informasi SET status = '$status_baru', tanggapan = '$tanggapan' $pdf_query_part WHERE id = $id_keberatan";
    
    if($conn->query($query_update)) {
        $pesan_aksi = "Status keberatan berhasil diperbarui.";
        $tipe_pesan = "success";

        // ---- NOTIFIKASI OTOMATIS KE TELEGRAM SAAT UPDATE VIA WEB ----
        $q_noreg = $conn->query("SELECT nomor_registrasi, nama_pemohon FROM keberatan_informasi WHERE id = $id_keberatan");
        if($q_noreg && $d_noreg = $q_noreg->fetch_assoc()) {
            $bot_token = '8838750735:AAER7EhJnD7Po8xO6ptEZNp59jgdwv3-E2o';
            $admin_chat_id = '7279608699';
            $teks_notif = "🌐 *UPDATE VIA WEB ADMIN*\n\nKeberatan milik *" . $d_noreg['nama_pemohon'] . "* (`" . $d_noreg['nomor_registrasi'] . "`) diperbarui lewat Web.\nStatus baru: *$status_baru*";
            
            $ch_tele = curl_init();
            curl_setopt($ch_tele, CURLOPT_URL, "https://api.telegram.org/bot" . $bot_token . "/sendMessage?chat_id=" . $admin_chat_id . "&text=" . urlencode($teks_notif) . "&parse_mode=Markdown");
            curl_setopt($ch_tele, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_tele, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch_tele, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch_tele);
            curl_close($ch_tele);
        }
        // -------------------------------------------------------------

    } else {
        $pesan_aksi = "Gagal memperbarui status keberatan.";
        $tipe_pesan = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Keberatan Publik - Admin CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* =====================================================================
           TEMA: Balai Kekarantinaan Kesehatan Kelas I Banjarmasin
           Palet: Teal Kemenkes (#00b2a9) + Oranye (#ef8a1c) + Deep Teal gelap
           (disamakan dengan dashboard.php dan permohonan.php)
           Halaman keberatan memakai oranye sebagai aksen aksi utama.
           ===================================================================== */
        :root { 
            --primary: #00b2a9; 
            --primary-dark: #008f88;
            --primary-deep: #04302f;
            --primary-soft: #e6f7f6;
            --secondary: #ef8a1c; 
            --secondary-dark: #d4730a;
            --secondary-soft: #fff4e5;
            --sidebar-bg: linear-gradient(180deg, #05383a 0%, #04302f 55%, #031f20 100%); 
            --sidebar-text: #9fc4c2;
            --sidebar-hover: rgba(255, 255, 255, 0.07);
            --bg-light: #eef5f5; 
            --text-dark: #12302f;
            --text-muted: #5f7a79;
            --border-soft: rgba(0, 143, 136, 0.12);
            --card-shadow: 0 1px 2px rgba(4, 48, 47, 0.04), 0 8px 24px rgba(4, 48, 47, 0.06);
            --card-hover-shadow: 0 2px 4px rgba(4, 48, 47, 0.05), 0 18px 40px rgba(0, 143, 136, 0.16);
            --radius-lg: 24px;
            --radius-md: 16px;
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
        }
        
        * { box-sizing: border-box; }
        body { 
            margin: 0; padding: 0; 
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif; 
            background-color: var(--bg-light);
            background-image:
                radial-gradient(900px 500px at 105% -10%, rgba(0, 178, 169, 0.14), transparent 60%),
                radial-gradient(700px 450px at -5% 110%, rgba(239, 138, 28, 0.10), transparent 60%);
            background-attachment: fixed;
            color: var(--text-dark); 
            display: flex; min-height: 100vh; overflow-x: hidden; 
            -webkit-font-smoothing: antialiased;
        }
        
        /* SIDEBAR MODERN */
        .sidebar { 
            width: 280px; background: var(--sidebar-bg); color: white; 
            display: flex; flex-direction: column; position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; 
            transition: transform 0.35s var(--ease); 
            box-shadow: 8px 0 30px rgba(3, 31, 32, 0.25); 
            border-right: 1px solid rgba(255,255,255,0.04);
        }
        .sidebar::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; z-index: 2;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(0, 178, 169, 0.35); border-radius: 5px; }
        
        .sidebar-header { 
            padding: 34px 22px 26px; text-align: center; 
            border-bottom: 1px solid rgba(255,255,255,0.07); 
            background: radial-gradient(circle at 50% 0%, rgba(0, 178, 169, 0.22) 0%, transparent 70%); 
        }
        .sidebar-header h3 { margin: 0; font-size: 1.35rem; color: #ffffff; font-weight: 800; letter-spacing: 2px; }
        .sidebar-header p { 
            display: inline-block; margin: 10px 0 0 0; padding: 5px 14px; font-size: 0.7rem; 
            color: #7fe3dc; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; 
            background: rgba(0, 178, 169, 0.14); border: 1px solid rgba(0, 178, 169, 0.3); border-radius: 20px; 
        }
        
        .sidebar-menu { list-style: none; padding: 16px 12px 24px; margin: 0; }
        .menu-category { margin-bottom: 4px; }
        .menu-category > a { 
            display: flex; justify-content: space-between; align-items: center; 
            color: var(--sidebar-text); text-decoration: none; padding: 12px 14px; 
            font-size: 0.9rem; font-weight: 500; border-radius: 12px; 
            transition: all 0.25s var(--ease); cursor: pointer; position: relative;
        }
        .menu-category > a:hover { background-color: var(--sidebar-hover); color: white; transform: translateX(4px); }
        .menu-category > a.active-parent { 
            background: linear-gradient(90deg, rgba(0, 178, 169, 0.28) 0%, rgba(0, 178, 169, 0.06) 100%); 
            color: #ffffff; font-weight: 700; 
            box-shadow: inset 3px 0 0 var(--secondary);
        }
        
        .menu-title { display: flex; align-items: center; gap: 12px; }
        .menu-title > span:first-child { 
            display: inline-flex; align-items: center; justify-content: center; 
            width: 32px; height: 32px; border-radius: 10px; 
            background: rgba(255,255,255,0.06); font-size: 0.95rem; 
        }
        .menu-category > a.active-parent .menu-title > span:first-child { background: rgba(0, 178, 169, 0.35); }
        .arrow { font-size: 0.65rem; transition: transform 0.3s ease; opacity: 0.7; }
        .menu-category.open .arrow { transform: rotate(180deg); color: var(--secondary); opacity: 1; }
        
        .sub-menu { list-style: none; padding: 6px 0 6px 14px; margin: 0; display: none; overflow: hidden; }
        .sub-menu li a { 
            display: flex; align-items: center; gap: 10px; color: #7fa3a1; text-decoration: none; 
            padding: 10px 15px 10px 38px; font-size: 0.84rem; border-radius: 10px; 
            transition: all 0.2s ease; position: relative; 
        }
        .sub-menu li a:hover, .sub-menu li a.active { color: white; background-color: var(--sidebar-hover); }
        .sub-menu li a.active { font-weight: 700; }
        .sub-menu li a::before { 
            content: ""; position: absolute; left: 17px; top: 50%; transform: translateY(-50%); 
            width: 6px; height: 6px; background-color: #3c6563; border-radius: 50%; transition: 0.3s; 
        }
        .sub-menu li a:hover::before, .sub-menu li a.active::before { background-color: var(--secondary); box-shadow: 0 0 10px var(--secondary); }

        .menu-label { 
            font-size: 0.68rem; color: #5f8a88; text-transform: uppercase; letter-spacing: 1.6px; 
            padding: 20px 16px 8px; font-weight: 700; display: flex; align-items: center; gap: 10px; 
        }
        .menu-label::after { content: ""; flex: 1; height: 1px; background: rgba(255,255,255,0.08); }

        /* KONTEN UTAMA */
        .main-content { flex: 1; margin-left: 280px; display: flex; flex-direction: column; min-width: 0; transition: margin-left 0.35s var(--ease); }
        
        .topbar { 
            background-color: rgba(255, 255, 255, 0.78); 
            -webkit-backdrop-filter: saturate(180%) blur(14px); backdrop-filter: saturate(180%) blur(14px);
            height: 72px; padding: 0 36px; display: flex; align-items: center; justify-content: space-between; 
            border-bottom: 1px solid var(--border-soft);
            box-shadow: 0 4px 20px rgba(4, 48, 47, 0.04); position: sticky; top: 0; z-index: 990; 
        }
        .topbar-left { display: flex; align-items: center; gap: 15px; }
        .menu-toggle { 
            display: none; background: var(--primary-soft); border: none; font-size: 1.3rem; color: var(--primary-dark); 
            cursor: pointer; padding: 6px 12px; border-radius: 10px; 
        }
        .topbar-title { font-size: 1.2rem; font-weight: 800; color: var(--text-dark); letter-spacing: -0.2px; }
        
        .topbar-user { display: flex; align-items: center; gap: 14px; font-size: 0.9rem; font-weight: 600; color: var(--text-muted); }
        .topbar-user span { 
            padding: 8px 16px; background: var(--primary-soft); color: var(--primary-dark); 
            border-radius: 999px; font-weight: 700; 
        }
        .btn-logout { 
            background: linear-gradient(135deg, #fff1f1, #ffe0e0); color: #d12c2c; padding: 9px 20px; 
            border-radius: 999px; text-decoration: none; font-size: 0.83rem; font-weight: 700; 
            border: 1px solid #ffd0d0; transition: all 0.3s var(--ease); 
        }
        .btn-logout:hover { background: #d12c2c; color: white; border-color: #d12c2c; box-shadow: 0 8px 18px rgba(209, 44, 44, 0.25); transform: translateY(-1px); }

        .content-body { padding: 34px 40px 50px; max-width: 1400px; margin: 0 auto; width: 100%; box-sizing: border-box; }
        
        .alert-success { background-color: #dcf7ec; color: #0b8a5a; padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 25px; border: 1px solid #b5ecd4; border-left: 5px solid #10b981; font-weight: 600; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08); }
        .alert-error { background-color: #ffe5e5; color: #b91c1c; padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 25px; border: 1px solid #ffc9c9; border-left: 5px solid #ef4444; font-weight: 600; box-shadow: 0 8px 20px rgba(239, 68, 68, 0.08); }
        
        /* CARD & TABLE */
        .card { 
            position: relative; overflow: hidden; background: #ffffff; border-radius: var(--radius-lg); padding: 34px; 
            box-shadow: var(--card-shadow); border: 1px solid var(--border-soft); transition: box-shadow 0.35s var(--ease); 
        }
        .card::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; 
            background: linear-gradient(90deg, var(--secondary), #f6a94d 45%, var(--primary));
        }
        .card:hover { box-shadow: var(--card-hover-shadow); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px dashed rgba(0, 143, 136, 0.2); padding-bottom: 18px; flex-wrap: wrap; gap: 15px; }
        .card-header h3 { margin: 0; font-size: 1.2rem; color: var(--text-dark); font-weight: 800; display: flex; align-items: center; gap: 10px; letter-spacing: -0.2px; flex-wrap: wrap; }
        .card-header h3 > span:first-child { 
            display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; 
            border-radius: 12px; background: var(--secondary-soft); font-size: 1.05rem;
        }
        
        /* Sync Indicator untuk AJAX */
        .sync-indicator { font-size: 0.75rem; color: var(--secondary-dark); font-weight: 700; display: flex; align-items: center; gap: 5px; opacity: 0; transition: opacity 0.5s; background: var(--secondary-soft); padding: 5px 12px; border-radius: 999px; border: 1px solid rgba(239, 138, 28, 0.3); }
        .sync-indicator.active { opacity: 1; }

        .btn-group-header { display: flex; gap: 10px; flex-wrap: wrap; }

        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 12px; }
        .admin-table { width: 100%; min-width: 850px; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
        .admin-table th, .admin-table td { padding: 16px 15px; border-bottom: 1px solid #edf3f3; text-align: left; vertical-align: middle; }
        .admin-table th { 
            background: var(--primary-soft); color: var(--primary-dark); font-weight: 700; 
            text-transform: uppercase; font-size: 0.74rem; letter-spacing: 0.7px; border-bottom: none; 
        }
        .admin-table th:first-child { border-radius: 12px 0 0 12px; }
        .admin-table th:last-child { border-radius: 0 12px 12px 0; }
        .admin-table td { color: #2c4a49; }
        .admin-table tbody tr:last-child td { border-bottom: none; }
        .admin-table tbody tr { transition: background-color 0.2s ease; }
        .admin-table tbody tr:hover td { background-color: #fffaf1; }
        .admin-table tbody tr:hover td:first-child { box-shadow: inset 3px 0 0 var(--secondary); }
        
        .badge { 
            display: inline-flex; align-items: center; gap: 7px; padding: 6px 14px; border-radius: 999px; 
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; 
        }
        .badge::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
        .badge-pending { background-color: #fff4e0; color: #c26a05; border: 1px solid #ffe0ad; }
        .badge-process { background-color: #e4f1ff; color: #1b64c8; border: 1px solid #c4dffc; }
        .badge-success { background-color: #dcf7ec; color: #0b8a5a; border: 1px solid #b5ecd4; }
        .badge-danger { background-color: #ffe5e5; color: #cf2a2a; border: 1px solid #ffc9c9; }

        .btn-action { padding: 8px 14px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.3s var(--ease); white-space: nowrap; font-family: inherit; }
        .btn-edit { background-color: var(--secondary-soft); color: var(--secondary-dark); border: 1px solid rgba(239, 138, 28, 0.35); }
        .btn-edit:hover { background-color: var(--secondary); color: white; border-color: var(--secondary); box-shadow: 0 8px 18px rgba(239, 138, 28, 0.32); transform: translateY(-1px); }
        .btn-del { background-color: #ffe9e9; color: #d12c2c; border: 1px solid #ffd0d0; }
        .btn-del:hover { background-color: #d12c2c; color: white; border-color: #d12c2c; box-shadow: 0 8px 18px rgba(209, 44, 44, 0.28); transform: translateY(-1px); }
        
        /* Tombol Filter Unduh Laporan */
        .btn-filter-download { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; padding: 11px 20px; font-size: 0.86rem; box-shadow: 0 8px 20px rgba(0, 178, 169, 0.28); border-radius: 999px; font-weight: 700; }
        .btn-filter-download:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(0, 178, 169, 0.4); color: white; }

        .btn-delete-all { background: linear-gradient(135deg, #e04040 0%, #a31d1d 100%); color: white; padding: 11px 20px; font-size: 0.86rem; box-shadow: 0 8px 20px rgba(209, 44, 44, 0.25); border-radius: 999px; font-weight: 700; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s var(--ease);}
        .btn-delete-all:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(209, 44, 44, 0.4); color: white; }
        
        /* Tombol Cek Tabel */
        .btn-cek-mini { font-size: 0.74rem; background: linear-gradient(135deg, #3b8cf5, #1d5fc4); color: white; padding: 5px 12px; border-radius: 999px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 700; transition: all 0.3s var(--ease); border: none; }
        .btn-cek-mini:hover { color: white; box-shadow: 0 8px 16px rgba(47, 128, 237, 0.35); transform: translateY(-1px); }

        /* MODAL STYLING */
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(3, 31, 32, 0.62); align-items: center; justify-content: center; -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); }
        .modal.active { display: flex; }
        .modal-content { position: relative; background-color: white; border-radius: 28px; padding: 35px; width: 90%; max-width: 750px; box-shadow: 0 30px 80px rgba(3, 31, 32, 0.35); max-height: 85vh; overflow-y: auto; border: 1px solid var(--border-soft); animation: modalZoom 0.35s var(--ease); }
        .modal-content.small { max-width: 500px; }
        .modal-content::-webkit-scrollbar { width: 6px; }
        .modal-content::-webkit-scrollbar-thumb { background: rgba(0, 178, 169, 0.35); border-radius: 10px; }
        
        @keyframes modalZoom {
            from { transform: scale(0.92) translateY(10px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }

        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px dashed rgba(0, 143, 136, 0.25); padding-bottom: 15px; position: sticky; top: -35px; background: white; z-index: 10; margin-top: -10px; padding-top: 10px; }
        .modal-header h3 { margin: 0; font-size: 1.3rem; color: var(--text-dark); font-weight: 800; letter-spacing: -0.2px; }
        .close-btn { background: var(--primary-soft); border: none; width: 38px; height: 38px; border-radius: 50%; font-size: 1.4rem; cursor: pointer; color: var(--primary-dark); display: flex; align-items: center; justify-content: center; transition: all 0.25s var(--ease); }
        .close-btn:hover { background: #d12c2c; color: white; transform: rotate(90deg); }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.78rem; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.6px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid #d6e4e3; border-radius: 14px; font-family: inherit; font-size: 0.95rem; box-sizing: border-box; background: #f6fbfb; transition: all 0.3s var(--ease); color: var(--text-dark); line-height: 1.5; }
        .form-control:focus { outline: none; border-color: var(--secondary); background: white; box-shadow: 0 0 0 4px rgba(239, 138, 28, 0.14); }
        
        .btn-submit { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); color: white; border: none; padding: 16px 25px; border-radius: 999px; font-weight: 800; font-size: 0.92rem; cursor: pointer; width: 100%; transition: all 0.3s var(--ease); margin-top: 15px; text-transform: uppercase; letter-spacing: 0.8px; box-shadow: 0 10px 26px rgba(239, 138, 28, 0.32); font-family: inherit; }
        .btn-submit:hover { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); box-shadow: 0 12px 30px rgba(0, 178, 169, 0.4); transform: translateY(-2px); }

        .btn-excel-submit { background: linear-gradient(135deg, #2f80ed 0%, #1d5fc4 100%); color: white; border: none; padding: 14px; border-radius: 999px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 10px; font-family: inherit; box-shadow: 0 8px 20px rgba(47, 128, 237, 0.25); transition: all 0.3s var(--ease); }
        .btn-excel-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(47, 128, 237, 0.4); }
        .btn-pdf-submit { background: linear-gradient(135deg, #e04040 0%, #a31d1d 100%); color: white; border: none; padding: 14px; border-radius: 999px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 10px; font-family: inherit; box-shadow: 0 8px 20px rgba(209, 44, 44, 0.25); transition: all 0.3s var(--ease); }
        .btn-pdf-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(209, 44, 44, 0.4); }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: linear-gradient(135deg, #fffaf1, #fff4e0); padding: 25px; border-radius: 20px; margin-bottom: 25px; border: 1px solid rgba(239, 138, 28, 0.3); }
        .info-item span { display: block; font-size: 0.72rem; color: var(--secondary-dark); font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 5px; }
        .info-item div { font-size: 0.95rem; font-weight: 600; color: var(--text-dark); line-height: 1.5; word-break: break-word; }
        .full-width { grid-column: span 2; }

        /* AKSESIBILITAS */
        .menu-toggle:focus-visible, .btn-logout:focus-visible, .btn-action:focus-visible, .btn-submit:focus-visible,
        .btn-excel-submit:focus-visible, .btn-pdf-submit:focus-visible, .close-btn:focus-visible, .btn-cek-mini:focus-visible,
        .sub-menu li a:focus-visible, .menu-category > a:focus-visible { outline: 3px solid rgba(0, 178, 169, 0.6); outline-offset: 2px; }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }

        /* RESPONSIVE DESIGN UNTUK TABLET & HP */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            .menu-toggle { display: block; }
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(3, 31, 32, 0.55); z-index: 999; backdrop-filter: blur(3px); }
            .sidebar-overlay.active { display: block; }
        }

        @media (max-width: 768px) {
            .topbar { padding: 0 15px; height: 66px; }
            .topbar-title { font-size: 1.05rem; }
            .topbar-user span { display: none; }
            
            .content-body { padding: 20px 15px 40px; }
            .card { padding: 22px 16px; border-radius: 20px; }
            .card-header h3 { font-size: 1.05rem; }
            .btn-group-header { width: 100%; }
            .btn-group-header .btn-action { flex: 1; }
            
            .admin-table th, .admin-table td { padding: 12px 10px; font-size: 0.85rem; }
            
            .modal-content { padding: 25px 20px; width: 95%; max-height: 90vh; border-radius: 22px; }
            .info-grid { grid-template-columns: 1fr; gap: 15px; padding: 20px; }
            .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>PANEL ADMIN</h3>
            <p>PPID BKK Banjarmasin</p>
        </div>
        
        <ul class="sidebar-menu">
            <li class="menu-category"><a href="dashboard.php"><span class="menu-title"><span>🏠</span> Dashboard Admin</span></a></li>

            <li class="menu-category open">
                <a onclick="toggleMenu('menu-operasional', this)" class="active-parent">
                    <span class="menu-title"><span>⚡</span> Atur Beranda & Layanan</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-operasional" style="display: block;">
                    <li><a href="permohonan.php">Data Permohonan Publik</a></li>
                    <li><a href="keberatan.php" class="active">Data Keberatan Publik</a></li>
                    <li><a href="berita.php">Push Berita Informasi & Foto</a></li>
                </ul>
            </li>

            <div class="menu-label">Kelola Konten Website</div>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-profil', this)"><span class="menu-title"><span>🏢</span> Profil</span><span class="arrow">▼</span></a>
                <ul class="sub-menu" id="menu-profil">
                    <li><a href="profil_ppid.php">Profil PPID</a></li>
                    <li><a href="visi_misi.php">Visi dan Misi</a></li>
                    <li><a href="struktur_tugas.php">Struktur & Tugas Fungsi</a></li>
                    <li><a href="dasar_hukum.php">Dasar Hukum PPID</a></li>
                </ul>
            </li>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-info', this)"><span class="menu-title"><span>📢</span> Informasi Publik</span><span class="arrow">▼</span></a>
                <ul class="sub-menu" id="menu-info">
                    <li><a href="informasi_publik.php">Informasi Publik</a></li>
                    <li><a href="informasi_serta_merta.php">Informasi Serta Merta</a></li>
                    <li><a href="informasi_setiap_saat.php">Informasi Setiap Saat</a></li>
                </ul>
            </li>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-standar', this)"><span class="menu-title"><span>⚖️</span> Standar Layanan</span><span class="arrow">▼</span></a>
                <ul class="sub-menu" id="menu-standar">
                    <li><a href="prosuder_layanan.php">Prosedur & Kanal Layanan</a></li>
                    <li><a href="waktu_biaya.php">Waktu dan Biaya Layanan</a></li>
                    <li><a href="maklumat_layanan.php">Maklumat Layanan</a></li>
                </ul>
            </li>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-laporan', this)"><span class="menu-title"><span>📊</span> Laporan</span><span class="arrow">▼</span></a>
                <ul class="sub-menu" id="menu-laporan">
                    <li><a href="laporan_ppid.php">Laporan PPID</a></li>
                    <li><a href="statistik_layanan.php">Statistik Layanan</a></li>
                    <li><a href="hasil_survei.php">Survei Layanan PPID</a></li>
                </ul>
            </li>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-kontak', this)"><span class="menu-title"><span>📞</span> Kontak</span><span class="arrow">▼</span></a>
                <ul class="sub-menu" id="menu-kontak">
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="pesan.php">Kontak / Pesan Masuk</a></li>
                </ul>
            </li>
        </ul>
    </aside>

    <!-- KONTEN KANAN -->
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">Kelola Keberatan Informasi</div>
            </div>
            <div class="topbar-user">
                <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </header>

        <div class="content-body">
            
            <?php if($pesan_aksi && $tipe_pesan == 'success'): ?>
                <div class="alert-success">✅ <?php echo htmlspecialchars($pesan_aksi); ?></div>
            <?php elseif($pesan_aksi && $tipe_pesan == 'error'): ?>
                <div class="alert-error">❌ <?php echo htmlspecialchars($pesan_aksi); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><span>⚖️</span> Daftar Pengajuan Keberatan Publik <span id="syncIndicator" class="sync-indicator">🔄 Auto-Sync</span></h3>
                    <!-- TOMBOL UNDUH DENGAN FILTER -->
                    <div class="btn-group-header">
                        <button type="button" class="btn-action btn-filter-download" onclick="openDownloadModal()">📥 Unduh & Filter Laporan</button>
                        <a href="keberatan.php?action=delete_all" class="btn-action btn-delete-all" onclick="return confirm('PERINGATAN!\n\nApakah Anda benar-benar yakin ingin MENGHAPUS SEMUA DATA KEBERATAN beserta semua lampiran filenya?\n\nTindakan ini tidak dapat dibatalkan.')">🗑️ Hapus Semua Data</a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Registrasi</th>
                                <th>Tanggal Masuk</th>
                                <th>Nama Pemohon</th>
                                <th>Kode Permohonan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <!-- ID tabelDataKeberatan PENTING UNTUK AJAX -->
                        <tbody id="tabelDataKeberatan">
                            <?php 
                            $no = 1;
                            $q_keberatan = $conn->query("SELECT * FROM keberatan_informasi ORDER BY tanggal_masuk DESC");
                            
                            if($q_keberatan && $q_keberatan->num_rows > 0):
                                while($row = $q_keberatan->fetch_assoc()):
                                    $data_json = htmlspecialchars(json_encode([
                                        'id' => $row['id'],
                                        'nomor_registrasi' => $row['nomor_registrasi'] ?? '',
                                        'kode_permohonan' => $row['kode_permohonan'] ?? '',
                                        'nama_pemohon' => $row['nama_pemohon'] ?? '',
                                        'nik' => $row['nik'] ?? '',
                                        'kontak' => ($row['no_hp'] ?? '') . ' / ' . ($row['email'] ?? ''),
                                        'alasan' => $row['alasan_keberatan'] ?? '',
                                        'kronologi' => $row['kronologi'] ?? '',
                                        'surat_path' => $row['surat_keberatan_path'] ?? '',
                                        'status' => $row['status'] ?? 'Pending',
                                        'tanggapan' => $row['tanggapan'] ?? '',
                                        'file_jawaban_pdf' => $row['file_jawaban_pdf'] ?? ''
                                    ]), ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong style="color:var(--secondary); font-size:1.05rem;"><?php echo htmlspecialchars($row['nomor_registrasi']); ?></strong></td>
                                <td style="white-space:nowrap;"><?php echo date('d M Y, H:i', strtotime($row['tanggal_masuk'])); ?></td>
                                <td>
                                    <div style="font-weight:700; color:#1f2937;"><?php echo htmlspecialchars($row['nama_pemohon']); ?></div>
                                    <div style="font-size:0.78rem; color:#6b7280; margin-top:4px;">NIK: <?php echo htmlspecialchars($row['nik']); ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #1f2937; margin-bottom: 6px;"><?php echo htmlspecialchars($row['kode_permohonan']); ?></div>
                                    <a href="permohonan.php?cari=<?php echo urlencode($row['kode_permohonan']); ?>" target="_blank" class="btn-cek-mini">
                                        🔍 Cek Data
                                    </a>
                                </td>
                                <td><?php echo badgeStatus($row['status']); ?></td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <button class="btn-action btn-edit" onclick="openModal(<?php echo $data_json; ?>)">⚖️ Proses</button>
                                        <a href="keberatan.php?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-del" onclick="return confirm('Apakah Anda yakin ingin menghapus data pengajuan keberatan ini?')">🗑</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="7" style="text-align: center; color: #9ca3af; padding: 40px; font-style: italic;">Belum ada data keberatan yang diajukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL FILTER DOWNLOAD -->
    <div class="modal" id="modalDownload">
        <div class="modal-content small">
            <div class="modal-header">
                <h3>📥 Filter Unduh Laporan Keberatan</h3>
                <button class="close-btn" onclick="closeDownloadModal()">×</button>
            </div>
            <form id="formDownload" target="_blank" method="GET">
                <div class="form-group">
                    <label>Pilih Rentang Waktu</label>
                    <select name="filter_tipe" id="filter_tipe" class="form-control" onchange="toggleCustomDate()">
                        <option value="semua">Semua Data</option>
                        <option value="hari">Hari Ini</option>
                        <option value="bulan">Bulan Ini</option>
                        <option value="tahun">Tahun Ini</option>
                        <option value="kustom">Pilih Rentang Tanggal (Kustom)</option>
                    </select>
                </div>
                
                <div id="wrap_kustom_tanggal" style="display: none; background: #f8fafc; padding: 15px; border-radius: 12px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn-excel-submit" onclick="submitDownload('excel')">📊 Unduh Excel</button>
                    <button type="button" class="btn-pdf-submit" onclick="submitDownload('pdf')">📄 Unduh PDF</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL POPUP PROSES & UPLOAD JAWABAN PDF KEBERATAN -->
    <div class="modal" id="modalProses">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Proses Keberatan: <span id="mod_kode" style="color:var(--secondary);"></span></h3>
                <button class="close-btn" onclick="closeModal()">×</button>
            </div>
            
            <div class="info-grid">
                <div class="info-item"><span>Nama Pemohon</span><div id="mod_nama"></div></div>
                <div class="info-item"><span>NIK / Identitas</span><div id="mod_nik"></div></div>
                <div class="info-item"><span>Kontak (HP / Email)</span><div id="mod_kontak"></div></div>
                
                <!-- BAGIAN KODE PERMOHONAN & TOMBOL CEK PERMOHONAN DI MODAL -->
                <div class="info-item">
                    <span>Kode Permohonan Terkait</span>
                    <div id="mod_kode_permohonan" style="font-weight: 800; font-size:1.05rem; color: #1f2937; margin-bottom: 8px;"></div>
                    <a id="link_ke_permohonan" href="permohonan.php" target="_blank" class="btn-cek-mini">🔍 Cek Permohonan</a>
                </div>

                <div class="info-item full-width"><span>Alasan Keberatan</span><div id="mod_alasan"></div></div>
                <div class="info-item full-width"><span>Kronologi / Penjelasan</span><div id="mod_kronologi"></div></div>
                <div class="info-item full-width">
                    <span>Surat Keberatan Pemohon</span>
                    <div style="margin-top:8px;">
                        <a id="mod_surat_link" href="#" target="_blank" style="font-size:0.85rem; background:var(--secondary); color:white; padding:6px 14px; border-radius:8px; text-decoration:none; font-weight:700; display:inline-block; box-shadow: 0 4px 10px rgba(239,138,28,0.2);">📄 Lihat Surat Keberatan</a>
                    </div>
                </div>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="id_keberatan" id="mod_id">
                
                <div class="form-group">
                    <label>Ubah Status Keberatan</label>
                    <select name="status" id="mod_status" class="form-control" onchange="toggleFormOpsi()" required>
                        <option value="Pending">Pending / Menunggu</option>
                        <option value="Diproses">Diproses (Sedang Ditinjau)</option>
                        <option value="Selesai">Selesai (Kasus Ditutup)</option>
                        <option value="Ditolak">Ditolak</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Tanggapan / Catatan PPID</label>
                    <textarea name="tanggapan" id="mod_tanggapan" class="form-control" rows="4" placeholder="Tuliskan tanggapan atau keputusan penanganan keberatan..."></textarea>
                </div>

                <!-- Opsi Upload PDF Jawaban Keberatan -->
                <div class="form-group" id="wrap_upload_pdf" style="display:none; background: #fffbeb; padding: 20px; border-radius: 16px; border: 1.5px dashed var(--secondary); margin-top: 10px;">
                    <label style="color: #d97706; font-weight: 800; font-size:0.9rem; display:flex; align-items:center; gap:8px;">📎 Unggah Dokumen Tanggapan / Putusan Keberatan (PDF)</label>
                    <input type="file" name="file_jawaban" accept=".pdf" class="form-control" style="background: white; padding: 12px; margin-top:10px;">
                    <small style="color: #6b7280; display: block; margin-top: 8px;">Opsional: Unggah file putusan resmi agar pemohon dapat mengunduhnya secara langsung.</small>
                    <div id="existing_pdf" style="margin-top: 12px; font-size: 0.88rem; font-weight: 600;"></div>
                </div>
                
                <button type="submit" class="btn-submit">Simpan Perubahan & Kirim Tanggapan</button>
            </form>
        </div>
    </div>

    <script>
        // UI & MODAL SCRIPTS
        function toggleMenu(menuId, element) {
            const submenu = document.getElementById(menuId);
            const parentLi = element.parentElement;
            if (submenu.style.display === "block") {
                submenu.style.display = "none";
                parentLi.classList.remove('open');
                element.classList.remove('active-parent');
            } else {
                document.querySelectorAll('.sub-menu').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.menu-category').forEach(el => el.classList.remove('open'));
                document.querySelectorAll('.menu-category > a').forEach(el => el.classList.remove('active-parent'));
                submenu.style.display = "block";
                parentLi.classList.add('open');
                element.classList.add('active-parent');
            }
        }
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }

        // Script Modal Proses
        const modal = document.getElementById('modalProses');
        function openModal(data) {
            document.getElementById('mod_id').value = data.id;
            document.getElementById('mod_kode').innerText = data.nomor_registrasi;
            document.getElementById('mod_nama').innerText = data.nama_pemohon;
            document.getElementById('mod_nik').innerText = data.nik;
            document.getElementById('mod_kontak').innerText = data.kontak;
            
            document.getElementById('mod_kode_permohonan').innerText = data.kode_permohonan;
            document.getElementById('link_ke_permohonan').href = 'permohonan.php?cari=' + encodeURIComponent(data.kode_permohonan);
            
            document.getElementById('mod_alasan').innerText = data.alasan;
            document.getElementById('mod_kronologi').innerText = data.kronologi;
            
            document.getElementById('mod_status').value = data.status || 'Pending';
            document.getElementById('mod_tanggapan').value = data.tanggapan || '';
            
            const suratLink = document.getElementById('mod_surat_link');
            if(data.surat_path) {
                suratLink.href = '../' + data.surat_path;
                suratLink.parentElement.style.display = 'block';
            } else {
                suratLink.parentElement.style.display = 'none';
            }

            const existingPdf = document.getElementById('existing_pdf');
            if(data.file_jawaban_pdf) {
                existingPdf.innerHTML = `File PDF Putusan Saat Ini: <a href="../${data.file_jawaban_pdf}" target="_blank" style="color:var(--secondary); font-weight:800; text-decoration:none; margin-left:5px;">Lihat PDF Terunggah</a>`;
            } else {
                existingPdf.innerHTML = `<span style="color:#9ca3af;">Belum ada file PDF putusan yang diunggah.</span>`;
            }
            
            toggleFormOpsi();
            modal.classList.add('active');
        }
        function closeModal() { modal.classList.remove('active'); }

        function toggleFormOpsi() {
            const status = document.getElementById('mod_status').value;
            const wrapUploadPdf = document.getElementById('wrap_upload_pdf');
            if (status === 'Selesai') { wrapUploadPdf.style.display = 'block'; } 
            else { wrapUploadPdf.style.display = 'none'; }
        }

        // Script Modal Download Filter
        const modalDl = document.getElementById('modalDownload');
        function openDownloadModal() { modalDl.classList.add('active'); }
        function closeDownloadModal() { modalDl.classList.remove('active'); }
        
        function toggleCustomDate() {
            const tipe = document.getElementById('filter_tipe').value;
            const wrap = document.getElementById('wrap_kustom_tanggal');
            wrap.style.display = (tipe === 'kustom') ? 'block' : 'none';
        }
        
        function submitDownload(jenis) {
            const form = document.getElementById('formDownload');
            if (jenis === 'excel') { form.action = 'cetak_laporan_keberatan_excel.php'; } 
            else { form.action = 'cetak_laporan_keberatan.php'; }
            form.submit();
            closeDownloadModal();
        }

        window.onclick = function(event) {
            if (event.target == modal) { closeModal(); }
            if (event.target == modalDl) { closeDownloadModal(); }
        }

        // ==========================================
        // FITUR AJAX AUTO-REFRESH TABEL (10 DETIK)
        // ==========================================
        function autoRefreshTable() {
            const indicator = document.getElementById('syncIndicator');
            indicator.classList.add('active');
            
            fetch('keberatan.php?action=fetch_table')
            .then(response => response.text())
            .then(html => {
                document.getElementById('tabelDataKeberatan').innerHTML = html;
                setTimeout(() => { indicator.classList.remove('active'); }, 1000);
            })
            .catch(error => {
                console.error("Gagal refresh data:", error);
                indicator.classList.remove('active');
            });
        }
        setInterval(autoRefreshTable, 10000);
    </script>
</body>
</html>