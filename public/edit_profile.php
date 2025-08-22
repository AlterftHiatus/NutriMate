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
                   SET name = ?, email = ?, height = ?, weight = ?, bmi = ? 
                   WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssissi", $name, $email, $height, $weight, $bmi_category, $user_id);

    if ($stmt_update->execute()) {
        header("Location: dashboard.php?page=profil");
        exit();
    } else {
        $error = "Gagal update profil. Silakan coba lagi.";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <a href="profil.php" class="mb-4 d-inline-block">⬅ Kembali ke Profil</a>

    <h2 class="text-center mb-4">✏️ Edit Profil</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" 
                           value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tinggi Badan (cm)</label>
                    <input type="number" name="height" class="form-control" 
                           value="<?= htmlspecialchars($user['height']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Berat Badan (kg)</label>
                    <input type="number" name="weight" class="form-control" 
                           value="<?= htmlspecialchars($user['weight']) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
