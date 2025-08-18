<?php
include '../config/db.php'; // koneksi database

$user_id = $_SESSION['user_id'];

// Ambil data user
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user = $result_user->fetch_assoc();

// Hitung BMI jika data ada
$bmi = null;
if (!empty($user['height']) && !empty($user['weight'])) {
    $height_m = $user['height'] / 100; // cm → m
    $bmi = $user['weight'] / ($height_m * $height_m);
}

// Ambil aktivitas terakhir (5 aktivitas terbaru)
$sql_activities = "
    SELECT ua.activity_date, ua.duration_minutes, ua.calories_burned, ua.exp_earned, a.name as activity_name
    FROM user_activities ua
    JOIN activities a ON ua.activity_id = a.id
    WHERE ua.user_id = ?
    ORDER BY ua.activity_date DESC, ua.created_at DESC
    LIMIT 5
";
$stmt2 = $conn->prepare($sql_activities);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result_activities = $stmt2->get_result();
?>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">👤 Profil Saya</h2>

        <div class="row">
            <!-- Info Akun -->
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">Informasi Akun</div>
                    <div class="card-body">
                        <p><strong>Nama:</strong> <?= htmlspecialchars($user['name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>Tanggal Join:</strong> <?= date("d M Y", strtotime($user['created_at'])) ?></p>
                        <p><strong>Streak:</strong> <?= $user['streak'] ?> 🔥</p>
                        <p><strong>Total EXP:</strong> <?= $user['exp'] ?> ⭐</p>
                    </div>
                </div>
            </div>

            <!-- Data Kesehatan -->
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Data Kesehatan</div>
                    <div class="card-body">
                        <p><strong>Tinggi Badan:</strong> <?= $user['height'] ? $user['height'] . " cm" : "-" ?></p>
                        <p><strong>Berat Badan:</strong> <?= $user['weight'] ? $user['weight'] . " kg" : "-" ?></p>
                        <p><strong>BMI:</strong>
                            <?= $bmi ? number_format($bmi, 1) : "-" ?>
                            <?php
                            if ($bmi) {
                                if ($bmi < 18.5) echo "(Kurus)";
                                elseif ($bmi < 25) echo "(Normal)";
                                elseif ($bmi < 30) echo "(Gemuk)";
                                else echo "(Obesitas)";
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aktivitas Terakhir -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-success text-white">Aktivitas Terakhir</div>
            <div class="card-body">
                <?php if ($result_activities->num_rows > 0): ?>
                    <table class="table table-sm table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Aktivitas</th>
                                <th>Durasi (menit)</th>
                                <th>Kalori</th>
                                <th>EXP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result_activities->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date("d M Y", strtotime($row['activity_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['activity_name']) ?></td>
                                    <td><?= $row['duration_minutes'] ?></td>
                                    <td><?= $row['calories_burned'] ?></td>
                                    <td><?= $row['exp_earned'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">Belum ada aktivitas.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Aksi -->
        <div class="mt-4 text-center">
            <a href="logout.php" class="btn btn-danger">Logout</a>
            <a href="edit_profile.php" class="btn btn-primary">Edit Profil</a>
        </div>

    </div>

</body>

</html>