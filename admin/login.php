<!-- admin/login.php -->
<?php
session_start();

// Panggil file koneksi database (pastikan path-nya benar, naik satu folder ke includes)
require_once '../includes/koneksi.php';

// Jika admin sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error_msg = "";

// Proses form login ketika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // Sesuai dengan format password di database Anda (MD5)

    // Query cek user
    $sql = "SELECT * FROM admin_users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Buat Session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_nama'] = $row['nama_lengkap'];
        $_SESSION['admin_role'] = $row['role'];
        
        // Arahkan ke dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        $error_msg = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - PPID BKK Banjarmasin</title>
    <style>
        :root { --primary: #00b2a9; --hover-color: #ef8a1c; }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #f4f7f6 0%, #e0e8e7 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { background-color: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 100%; max-width: 400px; text-align: center; border-top: 6px solid var(--primary); }
        .login-container h2 { color: #333; margin-bottom: 5px; }
        .login-container p { color: #777; font-size: 0.9rem; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #555; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; box-sizing: border-box; transition: all 0.3s ease; }
        .form-control:focus { outline: none; border-color: var(--hover-color); box-shadow: 0 0 0 3px rgba(239, 138, 28, 0.15); }
        .btn-login { background-color: var(--primary); color: white; border: none; width: 100%; padding: 14px; border-radius: 8px; font-size: 1.05rem; font-weight: 700; cursor: pointer; transition: all 0.3s ease; margin-top: 10px; }
        .btn-login:hover { background-color: var(--hover-color); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(239, 138, 28, 0.3); }
        .back-link { display: inline-block; margin-top: 25px; color: var(--primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; }
        .back-link:hover { color: var(--hover-color); }
        .alert-danger { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="login-container">
        <div style="font-size: 3rem; margin-bottom: 10px;">🔐</div>
        <h2>Panel Admin PPID</h2>
        <p>Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin</p>

        <?php if($error_msg != ""): ?>
            <div class="alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <!-- Gunakan admin_ppid -->
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <!-- Gunakan admin123 -->
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn-login">Masuk Sistem</button>
        </form>

        <a href="../index.php" class="back-link">&laquo; Kembali ke Beranda</a>
    </div>

</body>
</html>