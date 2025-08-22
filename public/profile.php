<?php
include '../config/db.php'; // koneksi database

$user_id = $_SESSION['user_id'];

// Ambil user user
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user = $result_user->fetch_assoc();

$height = $user['height'];
$weight = $user['weight'];
$avatar_user = htmlspecialchars($user['avatar']);
$bmi_user = $user['bmi'];

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
<style>

</style>
<body class="bg-light">
    <div class="card shadow-sm mb-4 bg-info">
    <div class="card-body text-center">
        <div class="position-relative d-inline-block mb-3">
            <img src="../assets/images/avatar/<?= $user['avatar'] ?>.png" class=" border rounded-circle border-info" alt="Profile Picture" width="100px">
            <button class="btn btn-sm btn-info position-absolute bottom-0 end-0 rounded-circle shadow">
            <i class="bi bi-pencil-fill text-white" id="edit-foto"></i> 
            </button>           
        </div>
        <p class="card-text fs-5">Halo, <strong><?= htmlspecialchars($user['name']) ?></strong></p>
        <p class="card-text text-muted fst-italic" style="font-size: 14px;">Terdaftar sejak: <strong><?= date("d M Y", strtotime($user['created_at'])) ?></strong></p>
        <a href="#" class="btn btn-primary btn-sm rounded-pill px-4">Go somewhere</a>
    </div>
    </div>
 <div class="d-flex flex-row gap-3">

        <!-- Character GIF -->
        <div class="character flex-shrink-0" style="width: 30%; min-width: 200px;">
            <div class="card mb-3">
                <div class="ratio" style="--bs-aspect-ratio: 133.33%;">
                    <img src="../assets/images/videos/<?= $avatar_user.$bmi_user ?>.gif" 
                         alt="Animasi GIF" 
                         class="w-100 h-100 object-fit-cover"
                         loading="lazy">
                </div>
            </div>
        </div>

        <!-- Data Diri -->
        <div class="datadiri flex-grow-1">
            <h5 class="fw-bold mb-3 text-info"><i class="bi bi-person-lines-fill"></i> Data Diri</h5>
            <form action="process/simpan_profile.php" method="post">
                <div class="p-3">
                    <input type="hidden" name="id_pengguna" value="<?= $user_id ?>">
                    <div class="mb-2">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    <div class="mb-2">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    <div class="mb-2">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" id="nama" value="<?= htmlspecialchars($user['name']) ?>">
                    </div>
                    <div class="mb-2">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <input type="text" name="jenis_kelamin" class="form-control" id="jenis_kelamin" value="<?= htmlspecialchars($user['jenis_kelamin']) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan</button>
                    <a href="../auth/logout.php" class="btn btn-outline-danger mt-3"><i class="bi bi-box-arrow-left"></i> Logout</a>
                </div>
            </form>
        </div>

    </div>

</body>
    <div class="">
        <h2 class="text-center mb-4">👤 Profil Saya</h2>

        <div class="row">
    
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
                            <?= $bmi_val ? number_format($bmi_val, 1) : "-" ?>
                            (<?php
                                echo $bmi_user
                            ?>)
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

