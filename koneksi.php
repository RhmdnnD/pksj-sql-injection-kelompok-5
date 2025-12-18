<?php
// MENGAKTIFKAN SESSION: Harus selalu di baris pertama
session_start(); 

// --- KONFIGURASI DATABASE ---
// Ganti dengan kredensial database lokal Anda
$servername = "localhost";
$username_db = "root";     // Username default XAMPP/MAMP
$password_db = "";         // Password default XAMPP/MAMP
$dbname = "proyek_login"; // Nama database yang sudah Anda buat di Langkah 2

// Buat koneksi menggunakan MySQLi
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    // Hentikan eksekusi jika koneksi gagal
    die("Koneksi Database Gagal: " . $conn->connect_error);
}
// echo "Koneksi berhasil!"; // Dapat dihapus
?>