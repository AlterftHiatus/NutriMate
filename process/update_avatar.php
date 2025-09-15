<?php
include '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['id_pengguna'] ?? null;
    $avatar = $_POST['avatar'] ?? null;

    if ($user_id && $avatar) {
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $avatar, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Avatar berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "Gagal update avatar.";
        }
        $stmt->close();
    }
}
header("Location: ../public/dashboard.php?page=profil"); // ganti sesuai halaman profil/dashboard
exit;
