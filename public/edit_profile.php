<?php
session_start();
include '../config/db.php'; // koneksi database

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user saat ini
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user = $result_user->fetch_assoc();

// Proses update jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name   = trim($_POST['nama']);
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
    $height = !empty($_POST['tinggi_badan']) ? intval($_POST['tinggi_badan']) : null;
    $weight = !empty($_POST['berat_badan']) ? intval($_POST['berat_badan']) : null;

    $avatar = null;
    if ($jenis_kelamin == 'laki-laki') {
        $avatar = 'man1_';
    }else{
        $avatar = 'women1_';
    }
    // Hitung BMI & kategorinya
    $bmi_category = null;
    if (!empty($height) && !empty($weight) && $height > 0) {
        $height_m = $height / 100; 
        $bmi_val = $weight / ($height_m * $height_m);

        if ($bmi_val < 18.5) {
            $bmi_category = 'kurus';
        } elseif ($bmi_val < 25) {
            $bmi_category = 'normal';
        } elseif ($bmi_val < 30) {
            $bmi_category = 'gemuk';
        } else {
            $bmi_category = 'obesitas';
        }
    }

    $sql_update = "UPDATE users 
                   SET name = ?, height = ?, weight = ?, bmi = ?, avatar = ?, jenis_kelamin = ? 
                   WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sddsssi", $name, $height, $weight, $bmi_category, $avatar, $jenis_kelamin, $user_id);

if ($stmt_update->execute()) {
    $_SESSION['alert'] = [
        'type' => 'success',
        'message' => 'Profil berhasil diperbarui!'
    ];
    header("Location: dashboard.php?page=profil");
    exit();
} else {
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'Gagal update profil. Silakan coba lagi.'
    ];
    header("Location: dashboard.php?page=profil");
    exit();
}
}
?>

