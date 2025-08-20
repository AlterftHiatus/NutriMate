<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function registerUser($name, $email, $password, $height, $weight) {
    global $conn;

    if (!$name || !$email || !$password || !$height || !$weight) {
        return "Harap isi semua field.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Format email tidak valid.";
    }

    // Cek apakah email sudah ada
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) return "Email sudah terdaftar.";

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Sesuaikan query INSERT dengan kolom di tabel users
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, height, weight) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $name, $email, $hashed, $height, $weight);

    return $stmt->execute() ? true : "Gagal registrasi.";
}


function loginUser($email, $password) {
    global $conn;

    if (!$email || !$password) return "Harap isi semua field.";

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            return true;
        }
        return "Password salah.";
    }
    return "Email tidak ditemukan.";
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function logoutUser() {
    session_destroy();
    header("Location: ../index.php");
    exit;
}
?>
