<?php
require "../config/db.php";
require "../config/fatsecret_api.php";
session_start();

// ================= CEK LOGIN =================
if (!isset($_SESSION['user_id'])) die("Anda harus login terlebih dahulu");

$user_id = $_SESSION['user_id'];
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

// ================= AMBIL DATA USER =================
$query_user = $conn->prepare("SELECT weight, height, usia, jenis_kelamin FROM users WHERE id = ?");
$query_user->bind_param("i", $user_id);
$query_user->execute();
$result_user = $query_user->get_result();
$user = $result_user->fetch_assoc();
if (!$user) die("Data user tidak ditemukan!");

// Data profil user
$berat  = $user['weight'];   // kg
$tinggi = $user['height'];   // cm
$usia   = $user['usia'];     // tahun
$gender = $user['jenis_kelamin']; // Laki-Laki / Perempuan

// ================= HITUNG BMR =================
// Rumus Mifflin-St Jeor
$BMR = ($gender == "Laki-Laki") 
    ? (10 * $berat + 6.25 * $tinggi - 5 * $usia + 5)
    : (10 * $berat + 6.25 * $tinggi - 5 * $usia - 161);

// ================= HITUNG KALORI AKTIVITAS HARIAN =================
// Kalori olahraga per tanggal dari tabel user_activities
$query_aktivitas = $conn->prepare("SELECT SUM(calories_burned) AS total FROM user_activities WHERE user_id = ? AND activity_date = ?");
$query_aktivitas->bind_param("is", $user_id, $tanggal);
$query_aktivitas->execute();
$result_aktivitas = $query_aktivitas->get_result();
$total_kalori_aktivitas = $result_aktivitas->fetch_assoc()['total'] ?? 0;

// ================= SIMPAN MAKANAN (POST) =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'], $_POST['kalori'])) {
    $nama    = $_POST['nama'];
    $desc    = $_POST['kalori'];

    // Ambil angka kalori dari deskripsi FatSecret
    preg_match('/Calories:\s*([\d]+)/', $desc, $matches);
    $kalori = $matches[1] ?? 0;

    // Simpan ke tabel food_log
    $stmt = $conn->prepare("INSERT INTO food_log (user_id, tanggal, makanan, kalori) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $tanggal, $nama, $kalori);
    $stmt->execute();

    // Refresh halaman
    header("Location: nutrisi.php?tanggal=$tanggal");
    exit;
}

// ================= TOTAL KALORI MAKANAN =================
$query_makanan = $conn->prepare("SELECT SUM(kalori) AS total FROM food_log WHERE user_id = ? AND tanggal = ?");
$query_makanan->bind_param("is", $user_id, $tanggal);
$query_makanan->execute();
$result_makanan = $query_makanan->get_result();
$total_kalori_makanan = $result_makanan->fetch_assoc()['total'] ?? 0;

// ================= HITUNG TDEE & BALANCE =================
$TDEE = $BMR + $total_kalori_aktivitas; // kebutuhan kalori hari itu
$kalori_balance = $total_kalori_makanan - $TDEE;

// Status kalori harian
if ($kalori_balance > 0) $status = "Surplus";
elseif ($kalori_balance < 0) $status = "Defisit";
else $status = "Seimbang";

// ================= FATSECRET SEARCH =================
$access_token = getAccessToken($client_id, $client_secret);
$foods = [];
if (isset($_GET['q'])) {
    $foods = searchFood($access_token, $_GET['q']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nutrisi Harian</title>
    <!-- Load Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="mb-4 text-center">🍎 Nutrisi Harian</h2>

    <!-- FORM PENCARIAN MAKANAN -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" class="d-flex">
                <input type="text" class="form-control me-2" name="q" placeholder="contoh: nasi goreng" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
        </div>
    </div>

    <!-- HASIL PENCARIAN -->
    <?php if (isset($foods['foods']['food'])): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Hasil Pencarian</h5>
                <ul class="list-group">
                    <?php 
                    $results = $foods['foods']['food'];
                    if (isset($results['food_id'])) $results = [$results]; // handle kalau hasil 1

                    foreach ($results as $food): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <b><?= htmlspecialchars($food['food_name']) ?></b><br>
                                <small class="text-muted"><?= htmlspecialchars($food['food_description']) ?></small>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="nama" value="<?= htmlspecialchars($food['food_name']) ?>">
                                <input type="hidden" name="kalori" value="<?= htmlspecialchars($food['food_description']) ?>">
                                <button type="submit" class="btn btn-success btn-sm">Tambah</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php elseif (isset($_GET['q'])): ?>
        <div class="alert alert-warning">Tidak ada hasil ditemukan untuk <b><?= htmlspecialchars($_GET['q']) ?></b></div>
    <?php endif; ?>

    <!-- LOG MAKANAN -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Log Makanan Hari Ini (<?= $tanggal ?>)</h5>
            <ul class="list-group">
                <?php
                $result = $conn->query("SELECT makanan, kalori FROM food_log WHERE user_id = $user_id AND tanggal = '$tanggal'");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<li class='list-group-item d-flex justify-content-between'><span>{$row['makanan']}</span><span class='badge bg-primary'>{$row['kalori']} kcal</span></li>";
                    }
                } else {
                    echo "<li class='list-group-item text-muted'>Belum ada makanan yang dicatat.</li>";
                }
                ?>
            </ul>
        </div>
    </div>

    <!-- RINGKASAN KALORI -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Ringkasan Kalori</h5>
            <ul class="list-group">
                <li class="list-group-item">BMR: <b><?= round($BMR) ?> kcal</b></li>
                <li class="list-group-item">Aktivitas: <b><?= round($total_kalori_aktivitas) ?> kcal</b></li>
                <li class="list-group-item">TDEE: <b><?= round($TDEE) ?> kcal</b></li>
                <li class="list-group-item">Makanan: <b><?= round($total_kalori_makanan) ?> kcal</b></li>
                <li class="list-group-item">Balance: 
                    <b><?= round($kalori_balance) ?> kcal</b> 
                    (<span class="text-<?= $status=='Surplus'?'danger':($status=='Defisit'?'primary':'success') ?>"><?= $status ?></span>)
                </li>
            </ul>
            <div class="mt-3">
                <?php
                if ($kalori_balance < 0) {
                    echo "<div class='alert alert-info'>⚡ Kalori harian kurang, bisa tambah asupan untuk energi.</div>";
                } elseif ($kalori_balance > 0) {
                    echo "<div class='alert alert-warning'>⚠️ Kalori harian lebih, pertimbangkan aktivitas tambahan atau kurangi makanan tinggi kalori.</div>";
                } else {
                    echo "<div class='alert alert-success'>✅ Kalori harian seimbang. Bagus!</div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
