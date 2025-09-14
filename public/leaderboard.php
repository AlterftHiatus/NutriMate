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
$avatar = $user['avatar'];
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
<div class="container-fluid">
  <div class="row g-4">
    <!-- Bagian Scoreboard -->
    <div class="col-lg-8">
      <div class="text-center mb-4">
        <img src="../assets/images/dashboard/gold.png" alt="Liga Emas" width="60" height="60" class="mb-2">
        <h3 class="fw-bold">LIGA NUTRIMATE</h3>
        <p class="mb-3">Selesaikan satu aktivitas untuk bergabung di papan skor minggu ini</p>
        <a href="beraktivitas.php" class="btn btn-warning btn-sm">Mulai Aktivitas</a>
      </div>

      <div class="list-group shadow-sm">
        <?php if (!empty($top_users) || $extra_user): ?>
          <?php $rank = 1; ?>
          <?php foreach ($top_users as $row): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center <?= $row['id'] == $current_user_id ? 'bg-warning-subtle fw-bold' : '' ?>">
              <div class="d-flex align-items-center">
                <?php if ($rank == 1): ?>
                  <img src="../assets/images/dashboard/juara1.png" alt="1" width="30" height="30" class="me-2">
                <?php elseif ($rank == 2): ?>
                  <img src="../assets/images/dashboard/juara2.png" alt="2" width="30" height="30" class="me-2">
                <?php elseif ($rank == 3): ?>
                  <img src="../assets/images/dashboard/juara3.png" alt="3" width="30" height="30" class="me-2">
                <?php else: ?>
                  <span class="badge bg-secondary me-2"><?= $rank ?></span>
                <?php endif; ?>
                <span><?= htmlspecialchars($row['name']) ?></span>
              </div>
              <span class="fw-bold"><?= $row['exp'] ?> XP</span>
            </div>
            <?php $rank++; ?>
          <?php endforeach; ?>

          <?php if ($extra_user): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center bg-gradient bg-warning text-dark">
              <div class="d-flex align-items-center">
                <span class="badge bg-dark me-3"><?= $extra_user_rank ?></span>
                <span class="fw-bold"><?= htmlspecialchars($extra_user['name']) ?></span>
              </div>
              <span class="fw-bold"><?= $extra_user['exp'] ?> XP</span>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <p class="text-center">Belum ada peserta.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Bagian Samping -->
    <div class="col-lg-4">
      <div class="d-flex justify-content-around align-items-center mb-4">
        <!-- EXP -->
        <div class="text-center">
          <img src="../assets/images/dashboard/exp.png" alt="" width="40" class="rounded-circle mb-1">
          <p class="mb-0 fw-bold"><?= $exp ?></p>
        </div>

        <!-- Rank -->
        <div class="text-center">
          <img src="../assets/images/dashboard/gold.png" alt="" width="40" class="rounded-circle mb-1">
          <p class="mb-0 fw-bold"><?= $user_rank ?></p>
        </div>

        <!-- Streak -->
        <div class="text-center">
          <?php if ($streak == 0): ?>
            <img src="../assets/images/dashboard/blackFire.png" alt="" width="40" class="rounded-circle mb-1">
          <?php else: ?>
            <img src="../assets/images/dashboard/redFire.png" alt="" width="40" class="rounded-circle mb-1">
          <?php endif; ?>
          <p class="mb-0 fw-bold"><?= $streak ?></p>
        </div>

        <!-- Profile -->
        <div class="dropdown">
          <a class="d-block" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img alt="image" src="../assets/images/avatar/<?= $avatar ?>.png" width="40" class="rounded-circle">
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="index.php?page=profil">Profile</a></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
          </ul>
        </div>
      </div>

      <!-- Daily Challenge -->
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="text-uppercase text-secondary mb-0">Daily Challenge</h6>
            <a href="dashboard.php?page=daily" class="text-decoration-none small text-primary fw-bold">Ayo Aktivitas</a>
          </div>
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h5 class="fw-bold mb-2">Dapatkan lencana pertamamu!</h5>
              <p class="mb-0">Selesaikan tantangan setiap bulan untuk mendapatkan lencana eksklusif</p>
            </div>
            <div class="ms-3">
              <img src="../assets/images/avatar/nut_smile.png" alt="Lencana" width="80">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
