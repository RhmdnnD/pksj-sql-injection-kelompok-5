<?php
include 'koneksi.php'; 

$error = '';
// Pesan yang akan ditampilkan, menunjukkan bahwa Prepared Statement digunakan.
$sql_executed = 'Query aman dieksekusi menggunakan Prepared Statement dan input divalidasi.';

// Jika pengguna sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. INPUT VALIDATION / SANITIZATION (Pertahanan Sekunder)
    // Membersihkan input dari spasi dan karakter khusus HTML (mencegah XSS).
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim(htmlspecialchars($_POST['password']));

    // Cek apakah input kosong setelah trim
    if (empty($username) || empty($password)) {
        $error = "Username dan Password tidak boleh kosong.";
    } else {

        // 2. QUERY YANG AMAN: Menggunakan tanda tanya (?) sebagai placeholder (Prepared Statement).
        $sql = "SELECT id, username, password FROM users WHERE username = ? AND password = ?";
        
        // 3. Persiapan Statement (Prepare)
        if ($stmt = $conn->prepare($sql)) {
            
            // 4. Binding Parameter (Bind): Mengikat nilai input yang sudah bersih
            // "ss" berarti dua parameter yang terikat adalah string.
            $stmt->bind_param("ss", $username, $password);
            
            // 5. Eksekusi Statement
            $stmt->execute();
            
            // 6. Ambil Hasil
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                // Login Berhasil
                $row = $result->fetch_assoc();
                
                // ** SET SESSION UNTUK LOGIN **
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                
                // Redirect ke halaman dashboard
                header("location: dashboard.php");
                exit;
            } else {
                // Login Gagal
                $error = "❌ Username atau Password salah.";
            }
            
            // Tutup statement
            $stmt->close();
        } else {
            $error = "ERROR: Could not prepare query.";
        }
        
        // Tutup koneksi
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Aplikasi Login AMAN (Mitigasi)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; border-top: 5px solid #28a745; /* Green border for secure app */ }
        h2 { color: #28a745; margin-bottom: 25px; text-align: center; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; transition: border-color 0.3s; }
        input[type="submit"] { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 700; transition: background-color 0.3s; }
        input[type="submit"]:hover { background-color: #1e7e34; }
        .error { color: #dc3545; font-weight: bold; text-align: center; margin-bottom: 15px; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 6px; }
        .query-box { margin-top: 30px; padding: 15px; background: #e9ecef; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; overflow-x: auto; color: #007bff; }
        .query-box h3 { margin-top: 0; font-size: 14px; color: #343a40; }
    </style>
</head>
<body>

<div class="container">
    <h2>Login Aplikasi AMAN (Mitigasi)</h2>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <!-- Formulir HTML -->
    <form method="post" action="login.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        
        <input type="submit" value="Login">
    </form>
    
    <div class="query-box">
        <h3>Metode Query:</h3>
        <p><?php echo $sql_executed; ?></p>
    </div>

</div>
</body>
</html>