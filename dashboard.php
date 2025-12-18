<?php
include 'koneksi.php'; 

// Cek apakah user sudah login. Jika tidak, arahkan kembali ke halaman login.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Aplikasi</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: #e6f7ff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); width: 100%; max-width: 600px; border-left: 5px solid #007bff; }
        h1 { color: #007bff; margin-bottom: 15px; }
        p { color: #333; line-height: 1.6; }
        .success { color: #28a745; font-size: 1.1em; margin-bottom: 20px; }
        a { color: #dc3545; text-decoration: none; font-weight: bold; transition: color 0.3s; }
        a:hover { color: #c82333; text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h1>Selamat Datang di Dashboard!</h1>
    <p class="success">Anda berhasil login sebagai: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
    
    <p>Ini adalah halaman terproteksi. Keberhasilan masuk ke sini (baik melalui login normal atau SQL Injection) membuktikan status sesi Anda.</p>

    <p><a href="logout.php">Klik di sini untuk Logout</a></p>
</div>

</body>
</html>