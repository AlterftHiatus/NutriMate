<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "health_tracker";

// Koneksi MySQLi (untuk auth.php)
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi MySQLi gagal: " . $conn->connect_error);
}

// Koneksi PDO (untuk save_activity.php)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi PDO gagal: " . $e->getMessage());
}

// SET TIMEZONE PHP + MySQL
date_default_timezone_set('Asia/Jakarta');
mysqli_query($conn, "SET time_zone = '+07:00'");
?>
