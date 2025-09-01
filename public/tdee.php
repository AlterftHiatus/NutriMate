<?php
session_start();
require "../config/db.php"; // koneksi database

// pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// -------------------------------
// 1. Ambil data user
// -------------------------------
$sql = "SELECT name, usia, weight, height, jenis_kelamin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Data user tidak ditemukan.");
}

$umur   = $user['usia'];
$berat  = $user['weight'];
$tinggi = $user['height'];
$gender = $user['jenis_kelamin'];

// -------------------------------
// 2. Hitung BMR
// Rumus Harris-Benedict
// -------------------------------
if ($gender === "laki-laki") {
    $bmr = 88.362 + (13.397 * $berat) + (4.799 * $tinggi) - (5.677 * $umur);
} else {
    $bmr = 447.593 + (9.247 * $berat) + (3.098 * $tinggi) - (4.330 * $umur);
}

// -------------------------------
// 3. Ambil aktivitas olahraga hari ini
// -------------------------------
$tanggalHariIni = date("Y-m-d");
$sqlAkt = "SELECT ua.activity_id, a.name as jenis, ua.calories_burned AS kalori FROM user_activities ua JOIN activities a ON ua.activity_id = a.id WHERE user_id = ? AND activity_date = ?";
$stmtAkt = $conn->prepare($sqlAkt);
$stmtAkt->bind_param("is", $user_id, $tanggalHariIni);
$stmtAkt->execute();
$resultAkt = $stmtAkt->get_result();

$totalKaloriOlahraga = 0;
$aktivitasList = [];

while ($row = $resultAkt->fetch_assoc()) {
    $aktivitasList[] = $row;
    $totalKaloriOlahraga += $row['kalori'];
}

// -------------------------------
// 4. Hitung TDEE
// -------------------------------
$tdee = $bmr + $totalKaloriOlahraga;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perhitungan BMR & TDEE</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .card { border: 1px solid #ddd; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        h2 { margin-top: 0; }
    </style>
</head>
<body>
    <h1>Halo, <?= htmlspecialchars($user['name']) ?>!</h1>

    <div class="card">
        <h2>Data Profil</h2>
        <p><b>Umur:</b> <?= $umur ?> tahun</p>
        <p><b>Berat:</b> <?= $berat ?> kg</p>
        <p><b>Tinggi:</b> <?= $tinggi ?> cm</p>
        <p><b>Gender:</b> <?= $gender ?></p>
    </div>

    <div class="card">
        <h2>BMR (Basal Metabolic Rate)</h2>
        <p>BMR kamu: <b><?= round($bmr, 2) ?> kalori/hari</b></p>
    </div>

    <div class="card">
        <h2>Aktivitas Hari Ini (<?= $tanggalHariIni ?>)</h2>
        <?php if (count($aktivitasList) > 0): ?>
            <ul>
                <?php foreach ($aktivitasList as $a): ?>
                    <li><?= ucfirst($a['jenis']) ?>: <?= $a['kalori'] ?> kalori</li>
                <?php endforeach; ?>
            </ul>
            <p><b>Total Kalori Olahraga:</b> <?= $totalKaloriOlahraga ?> kalori</p>
        <?php else: ?>
            <p>Tidak ada aktivitas yang tercatat hari ini.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>TDEE (Total Daily Energy Expenditure)</h2>
        <p>TDEE kamu hari ini: <b><?= round($tdee, 2) ?> kalori</b></p>
        <small>(TDEE = BMR + kalori aktivitas hari ini)</small>
    </div>
</body>
</html>
