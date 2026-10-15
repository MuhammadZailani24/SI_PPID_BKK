<?php 
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/koneksi.php';

// =========================================================================================
// AUTO-PATCH DATABASE & ERROR HANDLING
// =========================================================================================
try {
    $conn->query("CREATE TABLE IF NOT EXISTS permohonan_informasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nomor_registrasi VARCHAR(50),
        nama_pemohon VARCHAR(150),
        status VARCHAR(50) DEFAULT 'Pending',
        tanggal_masuk TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $cek_status_p = $conn->query("SHOW COLUMNS FROM permohonan_informasi LIKE 'status'");
    if ($cek_status_p && $cek_status_p->num_rows == 0) {
        $conn->query("ALTER TABLE permohonan_informasi ADD COLUMN status VARCHAR(50) DEFAULT 'Pending'");
    }

    $conn->query("CREATE TABLE IF NOT EXISTS keberatan_informasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nomor_registrasi VARCHAR(50),
        nama_pemohon VARCHAR(150),
        status VARCHAR(50) DEFAULT 'Pending',
        tanggal_masuk TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $cek_status_k = $conn->query("SHOW COLUMNS FROM keberatan_informasi LIKE 'status'");
    if ($cek_status_k && $cek_status_k->num_rows == 0) {
        $conn->query("ALTER TABLE keberatan_informasi ADD COLUMN status VARCHAR(50) DEFAULT 'Pending'");
    }

    $conn->query("CREATE TABLE IF NOT EXISTS pesan_masuk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        judul VARCHAR(200) NOT NULL,
        pesan TEXT NOT NULL,
        tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->query("UPDATE permohonan_informasi SET status = 'Pending' WHERE status IS NULL OR TRIM(status) = ''");
    $conn->query("UPDATE keberatan_informasi SET status = 'Pending' WHERE status IS NULL OR TRIM(status) = ''");

    // Total Permohonan
    $q_total_p = $conn->query("SELECT COUNT(id) as total FROM permohonan_informasi");
    $total_permohonan = $q_total_p ? $q_total_p->fetch_assoc()['total'] : 0;

    // Permohonan Pending
    $q_pending_p = $conn->query("SELECT COUNT(id) as total FROM permohonan_informasi WHERE status = 'Pending' OR status = 'Menunggu' OR status IS NULL OR TRIM(status) = ''");
    $pending_permohonan = $q_pending_p ? $q_pending_p->fetch_assoc()['total'] : 0;

    // Total Keberatan
    $q_total_k = $conn->query("SELECT COUNT(id) as total FROM keberatan_informasi");
    $total_keberatan = $q_total_k ? $q_total_k->fetch_assoc()['total'] : 0;

    // Pesan Masuk
    $q_pesan = $conn->query("SELECT COUNT(id) as total FROM pesan_masuk");
    $total_pesan = $q_pesan ? $q_pesan->fetch_assoc()['total'] : 0;

    // Ambil Terbaru
    $latest_permohonan = $conn->query("SELECT * FROM permohonan_informasi ORDER BY id DESC LIMIT 5");
    $latest_keberatan = $conn->query("SELECT * FROM keberatan_informasi ORDER BY id DESC LIMIT 5");

} catch (Exception $e) {
    $total_permohonan = 0;
    $pending_permohonan = 0;
    $total_keberatan = 0;
    $total_pesan = 0;
    $latest_permohonan = null;
    $latest_keberatan = null;
}

// =========================================================================================
// GENERATE HTML UNTUK TABEL (AGAR BISA DIKIRIM VIA AJAX JSON DAN DIPAKAI SAAT HALAMAN LOAD)
// =========================================================================================
$html_permohonan = '';
if ($latest_permohonan && $latest_permohonan->num_rows > 0) {
    while($row_p = $latest_permohonan->fetch_assoc()) {
        $status_p = trim($row_p['status'] ?? '');
        if (empty($status_p)) { $status_p = 'Pending'; }

        $st_lower = strtolower($status_p);
        $badge_class = 'badge-pending';
        if (strpos($st_lower, 'selesai') !== false || strpos($st_lower, 'kabul') !== false) {
            $badge_class = 'badge-success';
        } elseif (strpos($st_lower, 'tolak') !== false || strpos($st_lower, 'kecuali') !== false) {
            $badge_class = 'badge-danger';
        } elseif (strpos($st_lower, 'proses') !== false) {
            $badge_class = 'badge-process';
        }

        $noreg = htmlspecialchars($row_p['nomor_registrasi'] ?? '-');
        $nama = htmlspecialchars($row_p['nama_pemohon'] ?? 'Tanpa Nama');
        $tgl = date('d M Y', strtotime($row_p['tanggal_masuk'] ?? 'now'));
        $stat = htmlspecialchars($status_p);

        $html_permohonan .= '<tr>
            <td>
                <strong style="display: block; color: #1f2937; margin-bottom: 4px;">'.$noreg.'</strong>
                <span style="font-size: 0.8rem; color: #6b7280;">'.$nama.'</span>
            </td>
            <td style="font-size: 0.82rem; color: #6b7280; white-space: nowrap;">'.$tgl.'</td>
            <td><span class="badge '.$badge_class.'">'.$stat.'</span></td>
        </tr>';
    }
} else {
    $html_permohonan = '<tr><td colspan="3" class="empty-activity">Belum ada data permohonan informasi masuk.</td></tr>';
}

$html_keberatan = '';
if ($latest_keberatan && $latest_keberatan->num_rows > 0) {
    while($row_k = $latest_keberatan->fetch_assoc()) {
        $status_k = trim($row_k['status'] ?? '');
        if (empty($status_k)) { $status_k = 'Pending'; }

        $st_k_lower = strtolower($status_k);
        $badge_class_k = 'badge-pending';
        if (strpos($st_k_lower, 'selesai') !== false || strpos($st_k_lower, 'kabul') !== false) {
            $badge_class_k = 'badge-success';
        } elseif (strpos($st_k_lower, 'tolak') !== false || strpos($st_k_lower, 'kecuali') !== false) {
            $badge_class_k = 'badge-danger';
        } elseif (strpos($st_k_lower, 'proses') !== false) {
            $badge_class_k = 'badge-process';
        }

        $noreg_k = htmlspecialchars($row_k['nomor_registrasi'] ?? '-');
        $nama_k = htmlspecialchars($row_k['nama_pemohon'] ?? 'Tanpa Nama');
        $tgl_k = date('d M Y', strtotime($row_k['tanggal_masuk'] ?? 'now'));
        $stat_k = htmlspecialchars($status_k);

        $html_keberatan .= '<tr>
            <td>
                <strong style="display: block; color: #1f2937; margin-bottom: 4px;">'.$noreg_k.'</strong>
                <span style="font-size: 0.8rem; color: #6b7280;">'.$nama_k.'</span>
            </td>
            <td style="font-size: 0.82rem; color: #6b7280; white-space: nowrap;">'.$tgl_k.'</td>
            <td><span class="badge '.$badge_class_k.'">'.$stat_k.'</span></td>
        </tr>';
    }
} else {
    $html_keberatan = '<tr><td colspan="3" class="empty-activity">Belum ada data pengajuan keberatan masuk.</td></tr>';
}

// =========================================================================================
// TANGKAP REQUEST AJAX (KHUSUS UNTUK AUTO-REFRESH)
// =========================================================================================
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $response = [
        'total_permohonan' => number_format($total_permohonan),
        'pending_permohonan' => number_format($pending_permohonan),
        'total_keberatan' => number_format($total_keberatan),
        'total_pesan' => number_format($total_pesan),
        'html_permohonan' => $html_permohonan,
        'html_keberatan' => $html_keberatan
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Admin - PPID BKK Banjarmasin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* =====================================================================
           TEMA: Balai Kekarantinaan Kesehatan Kelas I Banjarmasin
           Palet: Teal Kemenkes (#00b2a9) + Oranye (#ef8a1c) + Deep Teal gelap
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

        /* WELCOME BANNER */
        .welcome-banner { 
            position: relative; overflow: hidden; color: white; padding: 42px 44px; 
            border-radius: var(--radius-lg); margin-bottom: 34px; 
            background: 
                radial-gradient(600px 300px at 90% 0%, rgba(239, 138, 28, 0.35), transparent 65%),
                linear-gradient(120deg, #04302f 0%, #007a74 55%, #00b2a9 100%); 
            box-shadow: 0 20px 45px rgba(0, 122, 116, 0.28); 
        }
        .welcome-banner::before {
            content: ""; position: absolute; right: -60px; top: -80px; width: 280px; height: 280px; 
            border-radius: 50%; border: 36px solid rgba(255,255,255,0.07);
        }
        .welcome-banner::after {
            content: ""; position: absolute; right: 120px; bottom: -90px; width: 190px; height: 190px; 
            border-radius: 50%; background: rgba(239, 138, 28, 0.20); filter: blur(2px);
        }
        .welcome-banner h2 { position: relative; z-index: 1; margin: 0 0 12px 0; font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px; }
        .welcome-banner p { position: relative; z-index: 1; margin: 0; font-size: 1.02rem; opacity: 0.92; line-height: 1.7; max-width: 760px; }

        /* WIDGET DASHBOARD */
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 38px; }
        .stat-card { 
            position: relative; background: #ffffff; border-radius: var(--radius-lg); padding: 26px; 
            box-shadow: var(--card-shadow); display: flex; align-items: center; gap: 20px; 
            transition: transform 0.35s var(--ease), box-shadow 0.35s var(--ease); 
            border: 1px solid var(--border-soft); overflow: hidden; 
        }
        .stat-card::after {
            content: ""; position: absolute; right: -40px; top: -40px; width: 130px; height: 130px; 
            border-radius: 50%; opacity: 0.55; transition: transform 0.5s var(--ease);
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: var(--card-hover-shadow); }
        .stat-card:hover::after { transform: scale(1.25); }
        
        .stat-icon { 
            position: relative; z-index: 1; width: 64px; height: 64px; border-radius: 20px; 
            display: flex; align-items: center; justify-content: center; font-size: 1.7rem; flex-shrink: 0; 
        }
        .stat-info { position: relative; z-index: 1; display: flex; flex-direction: column; justify-content: center; }
        .stat-info h4 { margin: 0; font-size: 0.78rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; line-height: 1.4; }
        .stat-info .number { font-size: 2.25rem; font-weight: 800; color: var(--text-dark); margin-top: 6px; line-height: 1; letter-spacing: -1px; transition: 0.3s; }

        .card-blue { border-left: none; border-top: 4px solid #2f80ed; }
        .card-blue::after { background: radial-gradient(circle, rgba(47, 128, 237, 0.22), transparent 70%); }
        .card-blue .stat-icon { background: linear-gradient(135deg, #e8f1ff, #d2e5ff); color: #2f80ed; box-shadow: 0 8px 18px rgba(47, 128, 237, 0.18); }
        
        .card-orange { border-left: none; border-top: 4px solid var(--secondary); }
        .card-orange::after { background: radial-gradient(circle, rgba(239, 138, 28, 0.25), transparent 70%); }
        .card-orange .stat-icon { background: linear-gradient(135deg, #fff3e0, #ffe2b8); color: var(--secondary-dark); box-shadow: 0 8px 18px rgba(239, 138, 28, 0.22); }
        
        .card-teal { border-left: none; border-top: 4px solid var(--primary); }
        .card-teal::after { background: radial-gradient(circle, rgba(0, 178, 169, 0.24), transparent 70%); }
        .card-teal .stat-icon { background: linear-gradient(135deg, #e2f8f6, #c3f0ec); color: var(--primary-dark); box-shadow: 0 8px 18px rgba(0, 178, 169, 0.2); }
        
        .card-red { border-left: none; border-top: 4px solid #ef4444; }
        .card-red::after { background: radial-gradient(circle, rgba(239, 68, 68, 0.2), transparent 70%); }
        .card-red .stat-icon { background: linear-gradient(135deg, #fff0f0, #ffd9d9); color: #ef4444; box-shadow: 0 8px 18px rgba(239, 68, 68, 0.18); }

        /* SECTION AKTIVITAS TERBARU */
        .activity-section-title { 
            font-size: 1.2rem; font-weight: 800; color: var(--text-dark); margin-bottom: 22px; 
            display: flex; align-items: center; gap: 12px; letter-spacing: -0.2px;
        }
        .activity-section-title > span { 
            display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; 
            border-radius: 12px; background: linear-gradient(135deg, var(--secondary), #f6a94d); 
            box-shadow: 0 8px 16px rgba(239, 138, 28, 0.3); font-size: 1.05rem;
        }
        .activity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
        
        .activity-card { 
            background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-soft); 
            box-shadow: var(--card-shadow); padding: 30px; overflow: hidden; position: relative;
        }
        .activity-card::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; 
            background: linear-gradient(90deg, var(--primary), #4fd8d0 55%, var(--secondary));
        }
        .activity-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; border-bottom: 1px dashed rgba(0, 143, 136, 0.2); padding-bottom: 16px; gap: 12px; }
        .activity-card-header h3 { margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 10px; }
        .activity-card-header h3 > span { 
            display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; 
            border-radius: 10px; background: var(--primary-soft); font-size: 0.95rem;
        }
        .btn-view-all { 
            font-size: 0.82rem; font-weight: 700; color: var(--primary-dark); text-decoration: none; 
            transition: all 0.3s var(--ease); padding: 8px 16px; background: var(--primary-soft); 
            border: 1px solid rgba(0, 178, 169, 0.25); border-radius: 999px; white-space: nowrap;
        }
        .btn-view-all:hover { color: white; background: var(--primary); border-color: var(--primary); box-shadow: 0 8px 18px rgba(0, 178, 169, 0.3); transform: translateY(-1px); }

        /* TABEL STYLING */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .admin-table { width: 100%; min-width: 450px; border-collapse: separate; border-spacing: 0; text-align: left; font-size: 0.9rem; }
        .admin-table th { 
            background: var(--primary-soft); color: var(--primary-dark); font-weight: 700; padding: 13px 16px; 
            border-bottom: none; font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.7px; 
        }
        .admin-table th:first-child { border-radius: 12px 0 0 12px; }
        .admin-table th:last-child { border-radius: 0 12px 12px 0; }
        .admin-table td { padding: 15px 16px; border-bottom: 1px solid #edf3f3; color: #2c4a49; vertical-align: middle; transition: background 0.25s; }
        .admin-table tbody tr:last-child td { border-bottom: none; }
        .admin-table tr:hover td { background: #f4fbfa; }
        .admin-table tbody tr:hover td:first-child { box-shadow: inset 3px 0 0 var(--primary); }
        
        .badge { 
            display: inline-flex; align-items: center; justify-content: center; gap: 7px; 
            padding: 6px 14px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; 
            text-transform: uppercase; letter-spacing: 0.4px; 
        }
        .badge::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
        .badge-pending { background: #fff4e0; color: #c26a05; border: 1px solid #ffe0ad; }
        .badge-process { background: #e4f1ff; color: #1b64c8; border: 1px solid #c4dffc; }
        .badge-success { background: #dcf7ec; color: #0b8a5a; border: 1px solid #b5ecd4; }
        .badge-danger { background: #ffe5e5; color: #cf2a2a; border: 1px solid #ffc9c9; }

        .empty-activity { text-align: center; color: #86a3a1; padding: 44px 20px; font-style: italic; font-size: 0.93rem; }

        /* AKSESIBILITAS */
        .menu-toggle:focus-visible, .btn-logout:focus-visible, .btn-view-all:focus-visible, 
        .sub-menu li a:focus-visible, .menu-category > a:focus-visible { outline: 3px solid rgba(239, 138, 28, 0.6); outline-offset: 2px; }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }

        /* RESPONSIVE DESIGN UNTUK TABLET & HP */
        @media (max-width: 1200px) {
            .activity-grid { grid-template-columns: 1fr; }
        }

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
            
            .welcome-banner { padding: 28px 22px; border-radius: 20px; margin-bottom: 26px; }
            .welcome-banner h2 { font-size: 1.4rem; }
            .welcome-banner p { font-size: 0.93rem; }

            .dashboard-grid { grid-template-columns: 1fr; gap: 14px; margin-bottom: 30px; }
            .stat-card { padding: 20px; border-radius: 20px; }
            .stat-icon { width: 56px; height: 56px; font-size: 1.5rem; border-radius: 16px; }
            .stat-info .number { font-size: 1.9rem; }
            
            .activity-card { padding: 22px 15px; border-radius: 20px; }
            .activity-card-header { flex-direction: column; align-items: flex-start; gap: 14px; }
            .btn-view-all { width: 100%; text-align: center; }
            
            .admin-table th, .admin-table td { padding: 12px 10px; font-size: 0.85rem; }
        }
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>PANEL ADMIN</h3>
            <p>PPID BKK Banjarmasin</p>
        </div>
        
        <ul class="sidebar-menu">
            <li class="menu-category"><a href="dashboard.php" class="active-parent"><span class="menu-title"><span>🏠</span> Dashboard Admin</span></a></li>

            <li class="menu-category">
                <a onclick="toggleMenu('menu-operasional', this)">
                    <span class="menu-title"><span>⚡</span> Atur Beranda & Layanan</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-operasional">
                    <li><a href="permohonan.php">Data Permohonan Publik</a></li>
                    <li><a href="keberatan.php">Data Keberatan Publik</a></li>
                    <li><a href="berita.php">Push Berita & Informasi</a></li>
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
                <a onclick="toggleMenu('menu-kontak', this)">
                    <span class="menu-title"><span>📞</span> Kontak</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="sub-menu" id="menu-kontak">
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="pesan.php">Kelola Kontak & Pesan</a></li>
                </ul>
            </li>
        </ul>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">Dashboard Admin</div>
            </div>
            <div class="topbar-user">
                <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Administrator'); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </header>

        <div class="content-body">
            
            <div class="welcome-banner">
                <h2>Selamat Datang di Panel Admin PPID!</h2>
                <p>Kelola seluruh informasi publik, pantau permohonan, keberatan, serta perbarui data layanan dari satu pintu utama dengan mudah dan efisien.</p>
            </div>

            <!-- STATISTIK WIDGET -->
            <div class="dashboard-grid">
                
                <div class="stat-card card-blue">
                    <div class="stat-icon">📥</div>
                    <div class="stat-info">
                        <h4>Total Permohonan</h4>
                        <div class="number" id="val_total_permohonan"><?php echo number_format($total_permohonan); ?></div>
                    </div>
                </div>
                
                <div class="stat-card card-orange">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <h4>Permohonan Pending</h4>
                        <div class="number" id="val_pending_permohonan"><?php echo number_format($pending_permohonan); ?></div>
                    </div>
                </div>
                
                <div class="stat-card card-red">
                    <div class="stat-icon">⚖️</div>
                    <div class="stat-info">
                        <h4>Total Keberatan</h4>
                        <div class="number" id="val_total_keberatan"><?php echo number_format($total_keberatan); ?></div>
                    </div>
                </div>

                <div class="stat-card card-teal">
                    <div class="stat-icon">💬</div>
                    <div class="stat-info">
                        <h4>Pesan Masuk Baru</h4>
                        <div class="number" id="val_total_pesan"><?php echo number_format($total_pesan); ?></div>
                    </div>
                </div>

            </div>

            <!-- BAGIAN AKTIVITAS TERBARU (PERMOHONAN & KEBERATAN) -->
            <div>
                <div class="activity-section-title">
                    <span>⚡</span> Aktivitas Layanan Terbaru
                </div>

                <div class="activity-grid">
                    
                    <!-- TABEL PERMOHONAN TERBARU -->
                    <div class="activity-card">
                        <div class="activity-card-header">
                            <h3><span>📥</span> Permohonan Informasi Terbaru</h3>
                            <a href="permohonan.php" class="btn-view-all">Lihat Semua &raquo;</a>
                        </div>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>No. Registrasi / Nama</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_permohonan">
                                    <?php echo $html_permohonan; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TABEL KEBERATAN TERBARU -->
                    <div class="activity-card">
                        <div class="activity-card-header">
                            <h3><span>⚖️</span> Pengajuan Keberatan Terbaru</h3>
                            <a href="keberatan.php" class="btn-view-all">Lihat Semua &raquo;</a>
                        </div>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>No. Registrasi / Nama</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_keberatan">
                                    <?php echo $html_keberatan; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
    
    <script>
        // FUNGSI UNTUK MENU SIDEBAR
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

        // ====================================================================
        // FUNGSI AUTO REFRESH (AJAX) - Refresh setiap 5 Detik
        // ====================================================================
        function fetchDashboardData() {
            fetch('dashboard.php?ajax=1')
                .then(response => response.json())
                .then(data => {
                    // Update 4 Card Statistik
                    document.getElementById('val_total_permohonan').textContent = data.total_permohonan;
                    document.getElementById('val_pending_permohonan').textContent = data.pending_permohonan;
                    document.getElementById('val_total_keberatan').textContent = data.total_keberatan;
                    document.getElementById('val_total_pesan').textContent = data.total_pesan;

                    // Update 2 Tabel Aktivitas Terbaru
                    document.getElementById('tbody_permohonan').innerHTML = data.html_permohonan;
                    document.getElementById('tbody_keberatan').innerHTML = data.html_keberatan;
                })
                .catch(error => console.error('Error fetching dashboard data:', error));
        }

        // Jalankan fungsi fetch setiap 5 detik (5000 milliseconds)
        setInterval(fetchDashboardData, 5000);
    </script>
</body>
</html>