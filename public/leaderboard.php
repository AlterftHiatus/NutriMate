<?php
// session_start();
include '../config/db.php'; // koneksi database

$current_user_id = $_SESSION['user_id'] ?? null;

if (!$current_user_id) {
    echo "User belum login.";
    exit;
}

// ===================================
// Ambil data user + rank
// ===================================
$sql_user = "
SELECT 
    u.id, 
    u.name, 
    u.exp,
    (SELECT COUNT(*) + 1 FROM users WHERE exp > u.exp) AS user_rank,
    u.height, 
    u.weight, 
    u.avatar, 
    u.bmi, 
    u.streak
FROM users u
WHERE u.id = ?
";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    echo "User tidak ditemukan.";
    exit;
}

// Ambil data user
$name = htmlspecialchars($user['name']);
$height = isset($user['height']) ? (int)$user['height'] : 0;
$weight = isset($user['weight']) ? (int)$user['weight'] : 0;
$exp = isset($user['exp']) ? (int)$user['exp'] : 0;
$streak = isset($user['streak']) ? (int)$user['streak'] : 0;
$user_rank = isset($user['user_rank']) ? (int)$user['user_rank'] : 0;

// ===================================
// Ambil top 10 user berdasarkan EXP
// ===================================
$sql_top = "SELECT id, name, exp FROM users ORDER BY exp DESC LIMIT 10";
$result = $conn->query($sql_top);

$top_users = [];
$found_current_user = false;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['id'] == $current_user_id) {
            $found_current_user = true;
        }
        $top_users[] = $row;
    }
}

// Ambil extra user jika tidak ada di top 10
$extra_user = null;
$extra_user_rank = null;
if (!$found_current_user && $current_user_id) {
    $stmt = $conn->prepare("SELECT id, name, exp FROM users WHERE id = ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    if ($user_result->num_rows > 0) {
        $extra_user = $user_result->fetch_assoc();

        // Hitung rank
        $stmt_rank = $conn->prepare("SELECT COUNT(*) + 1 AS rank FROM users WHERE exp > ?");
        $stmt_rank->bind_param("i", $extra_user['exp']);
        $stmt_rank->execute();
        $rank_result = $stmt_rank->get_result();
        $extra_user_rank = $rank_result->fetch_assoc()['rank'] ?? 0;
    }
}
?>
<style>
    .highlight {
        background-color: #fff3cd !important;
        /* kuning soft */
        border-left: 4px solid #ffc107;
    }

    .scoreboard-card {
        background-color: #2a2a40;
        border-radius: 1rem;
        color: white;
        width: 320px;
    }

    .avatar {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        border: 2px solid #4dabf7;
        object-fit: cover;
    }

    .status-dot {
        position: absolute;
        bottom: 4px;
        right: 4px;
        width: 12px;
        height: 12px;
        background-color: #4ade80;
        border: 2px solid #2a2a40;
        border-radius: 50%;
    }

    .icon-box {
        background-color: #3a3a55;
        padding: 10px;
        border-radius: 10px;
        text-align: center;
        font-size: 20px;
    }
</style>

<div class="classContainer d-flex gap-4">
    <div class="score" style="width: 70%;">
        <div class="text-center mb-4">
            <img src="../assets/images/dashboard/gold.png" alt="Liga Emas" width="60" height="60" class="mb-2">
            <h3 class="fw-bold">LIGA NUTRIMATE</h3>
            <p class="mb-3">Selesaikan satu aktivitas untuk bergabung di papan skor minggu ini</p>
            <a href="beraktivitas.php" class="btn btn-warning btn-sm">Mulai Aktivitas</a>
        </div>
        <div class="list-group">
            <?php if (!empty($top_users) || $extra_user): ?>
                <?php $rank = 1; ?>
                <?php foreach ($top_users as $row): ?>
                    <?php $highlight = ($row['id'] == $current_user_id) ? "highlight" : ""; ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center <?= $highlight ?>">
                        <div class="d-flex align-items-center justify-content-center">
                            <?php if ($rank == 1): ?>
                                <img src="../assets/images/dashboard/juara1.png" alt="1" width="30" height="30" class="me-2">
                            <?php elseif ($rank == 2): ?>
                                <img src="../assets/images/dashboard/juara2.png" alt="2" width="30" height="30" class="me-2">
                            <?php elseif ($rank == 3): ?>
                                <img src="../assets/images/dashboard/juara3.png" alt="3" width="30" height="30" class="me-2">
                            <?php else: ?>
                                <span class="badge bg-secondary me-2"><?= $rank ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="fw-medium flex-grow-1 text-center"><?= htmlspecialchars($row['name']) ?></span>
                        <span class="fw-bold"><?= $row['exp'] ?> XP</span>
                    </div>
                    <?php $rank++; ?>
                <?php endforeach; ?>

                <?php if ($extra_user): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center highlight">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-secondary me-2"><?= $extra_user_rank ?? '-' ?></span>
                            <span class="fw-medium"><?= htmlspecialchars($extra_user['name']) ?></span>
                        </div>
                        <span class="fw-bold"><?= $extra_user['exp'] ?> XP</span>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center">Belum ada peserta.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="sideSection" style="width: 30%;">
        <div class="titleStreak d-flex gap-2 align-items-center justify-content-around mb-4">
            <!-- EXP -->
            <div class="streak d-flex align-items-center">
                <img src="../assets/images/dashboard/exp.png" alt="" width="40" class="rounded-circle d-block">
                <p class="mb-0 fw-bold fs-5"><?= $exp ?></p>
            </div>

            <!-- Rank -->
            <div class="streak d-flex align-items-center">
                <img src="../assets/images/dashboard/gold.png" alt="" width="40" class="rounded-circle d-block">
                <p class="mb-0 fw-bold fs-5"><?= $user_rank ?></p>
            </div>

            <!-- STREAK -->
            <div class="streak d-flex align-items-center">
                <img src="../assets/images/dashboard/redFire.png" alt="" width="40" class="rounded-circle d-block">
                <p class="mb-0 fw-bold fs-5"><?= $streak ?></p>
            </div>

            <!-- PROFILE -->
            <div class="dropdown">
                <a class="nav-link nav-link-lg nav-link-user" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img alt="image" src="../assets/images/avatar/<?php echo $avatar ?>.png" width="40px" class="rounded-circle mr-1">
                </a>
                <div class="dropdown-menu dropdown-menu-left">
                    <a href="index.php?page=profil" class="dropdown-item has-icon">Profile</a>
                    <a href="../auth/logout.php" class="dropdown-item has-icon text-danger">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                </div>
            </div>
        </div>
        <div class="card bg-light text-dark p-3 mb-3" style="max-width: 400px; border-radius: 12px;">
            <div class="d-flex align-items-center">
                <!-- Teks -->
                <div class="flex-grow-1">
                    <h6 class="text-uppercase text-secondary mb-1">LENCANA BULANAN</h6>
                    <h5 class="fw-bold mb-2">Dapatkan lencana pertamamu!</h5>
                    <p class="mb-0">Selesaikan tantangan setiap bulan untuk mendapatkan lencana eksklusif</p>
                </div>
                <!-- Ikon / Gambar -->
                <div class="ms-3">
                    <img src="../assets/images/avatar/nut.png" alt="Lencana" style="width:100px; height:100px;">
                </div>
            </div>
        </div>
    </div>
</div>