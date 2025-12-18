<?php
include 'koneksi.php'; 

$error = '';
$sql_executed = ''; // Variabel untuk menyimpan query yang dieksekusi

// Jika pengguna sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: dashboard.php");
    exit;
}

// Data yang diekstrak dari UNION attack akan disimpan di sini
$stolen_data = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Ambil input dari form (MENTAH - SENGAJA RENTAN)
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 2. QUERY YANG SENGARAJA RENTAN (Target untuk UNION/UPDATE)
    // Input pengguna dimasukkan langsung ke dalam string query.
    $sql = "SELECT id, username FROM users WHERE username = '$username' AND password = '$password'";
    
    $sql_executed = $sql; // Simpan query untuk ditampilkan

    // --- LOGIKA BARU: MENGHANDLE SERANGAN MULTIPLE STATEMENT (UPDATE/DELETE) ---
    // Cek apakah payload mengandung titik koma, yang menandakan multiple statement attack.
    if (strpos($sql, ';') !== false) {
        
        // ** PENTING: MENGGUNAKAN MULTI_QUERY UNTUK MENGEKSEKUSI BEBERAPA PERINTAH **
        if ($conn->multi_query($sql)) {
            $error = "✅ PERHATIAN: Perintah SQL Ganda berhasil dikirim. Cek database Anda (phpMyAdmin).";
            
            // Bersihkan hasil query
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
            
        } else {
            $error = "❌ Query Gagal total saat Multi-Query: " . $conn->error;
        }

    } else {
        // --- LOGIKA LAMA: SINGLE QUERY (UNTUK LOGIN BYPASS/UNION ATTACK) ---
        $result = $conn->query($sql);

        // 4. Cek hasil
        if ($result) {
            if ($result->num_rows > 0) {
                // Kita harus mengambil data sebagai array numerik untuk mendapatkan kolom yang diinjeksi
                $row_assoc = $result->fetch_assoc();
                $result->data_seek(0); // Reset pointer untuk fetch array
                $row_num = $result->fetch_array(MYSQLI_NUM);
                
                // Cek apakah UNION attack berhasil (2 kolom)
                if (count($row_num) == 2 && strpos($username, 'UNION') !== false) {
                    
                    // ASUMSI: Kolom kedua (index 1) dari UNION SELECT adalah target kita (Password)
                    $stolen_data = "<h2>Data Sensitif Berhasil Dicuri (Password):</h2>";
                    
                    $result->data_seek(0); // Reset pointer lagi
                    while ($row = $result->fetch_assoc()) {
                        $temp_row = array_values($row); 
                        $stolen_data .= "<p>Password yang Terekspos: <strong>" . htmlspecialchars($temp_row[1]) . "</strong> (Milik user: " . htmlspecialchars($temp_row[0]) . ")</p>";
                    }
                    
                } else if (count($row_assoc) > 2) { 
                     // LOGIKA LAMA (untuk fallback jika UNION SELECT 3 kolom)
                    $stolen_data = "<h2>Data Berhasil Diekstrak (UNION Attack):</h2>";
                    foreach ($row_assoc as $key => $value) {
                        $stolen_data .= "<p><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</p>";
                    }
                } else {
                    // Login Berhasil Normal/Bypass
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id'] = $row_assoc['id'];
                    $_SESSION['username'] = $row_assoc['username'];
                    header("location: dashboard.php");
                    exit;
                }
            } else {
                // Login Gagal
                $error = "❌ Username atau Password salah.";
            }
        } else {
            // Jika query gagal (misalnya karena sintaks error)
            $error = "❌ Query Gagal: " . $conn->error;
        }
    }
    // --- AKHIR LOGIKA QUERY ---
    
    // Tutup koneksi setelah selesai
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Aplikasi Login Rentan (Target Eksploitasi)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; border-top: 5px solid #dc3545; /* Red border for vulnerable app */ }
        h2 { color: #dc3545; margin-bottom: 25px; text-align: center; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; transition: border-color 0.3s; }
        input[type="submit"] { width: 100%; padding: 12px; background-color: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 700; transition: background-color 0.3s; }
        input[type="submit"]:hover { background-color: #c82333; }
        .error { color: #dc3545; font-weight: bold; text-align: center; margin-bottom: 15px; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 6px; }
        .query-box { margin-top: 30px; padding: 15px; background: #e9ecef; border: 1px solid #ced4da; border-radius: 6px; font-size: 12px; overflow-x: auto; color: #343a40; }
        .query-box h3 { margin-top: 0; font-size: 14px; color: #343a40; }
        .stolen-data { margin-top: 30px; padding: 15px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 6px; color: #856404; font-size: 16px;}
    </style>
</head>
<body>

<div class="container">
    <h2>Login Aplikasi RENTAN (Data Target)</h2>

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
    
    <?php if ($stolen_data): ?>
        <div class="stolen-data">
            <?php echo $stolen_data; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($sql_executed)): ?>
        <div class="query-box">
            <h3>Query yang Dieksekusi (Analisis Tahap 3):</h3>
            <pre><?php echo htmlspecialchars($sql_executed); ?></pre>
        </div>
    <?php endif; ?>

</div>
</body>
</html>