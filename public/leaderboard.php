<?php
// session_start();
include '../config/db.php'; // koneksi database

$current_user_id = $_SESSION['user_id'] ?? null;

// Ambil top 10 user berdasarkan EXP
$sql = "SELECT id, name, exp FROM users ORDER BY exp DESC LIMIT 10";
$result = $conn->query($sql);

$top_users = [];
$found_current_user = false;

// Simpan top 10 ke array
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if($row['id'] == $current_user_id) {
            $found_current_user = true;
        }
        $top_users[] = $row;
    }
}

// Ambil data current user jika tidak ada di top 10
$extra_user = null;
if (!$found_current_user && $current_user_id) {
    $sql_user = "SELECT id, name, exp FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    if ($user_result->num_rows > 0) {
        $extra_user = $user_result->fetch_assoc();
    }
}

// Hitung rank sebenarnya untuk extra user
if ($extra_user) {
    $sql_rank = "SELECT COUNT(*) + 1 AS rank FROM users WHERE exp > ?";
    $stmt = $conn->prepare($sql_rank);
    $stmt->bind_param("i", $extra_user['exp']);
    $stmt->execute();
    $rank_result = $stmt->get_result();
    $extra_user_rank = $rank_result->fetch_assoc()['rank'];
}
?>
<style>
.highlight {
    background-color: #fff3cd !important; /* kuning soft */
    border-left: 4px solid #ffc107;
}
</style>
<div class="classContainer d-flex gap-4">
    <div class="score" style="width: 70%;">
        <div class="text-center mb-4">
            <img src="../assets/images/dashboard/gold.png" alt="Liga Emas" width="60" height="60" class="mb-2">
            <h3 class="fw-bold">LIGA EMAS</h3>
            <p class="mb-3">Selesaikan satu aktivitas untuk bergabung di papan skor minggu ini</p>
            <button class="btn btn-warning btn-sm">Mulai Aktivitas</button>
        </div>
        <div class="">
        <div class="list-group">
            <?php if (!empty($top_users) || $extra_user): ?>
            <?php $rank = 1; ?>
            <?php foreach($top_users as $row): ?>
                <?php 
                $highlight = ($row['id'] == $current_user_id) ? "highlight" : "";
                ?>
                <div class="list-group-item d-flex justify-content-between align-items-center <?= $highlight ?>">
                <div class="d-flex align-items-center">
                    <?php if ($rank == 1): ?>
                    <img src="../assets/images/dashboard/juara1.png" alt="1" width="29" height="29" class="me-2">
                    <?php elseif ($rank == 2): ?>
                    <img src="../assets/images/dashboard/juara2.png" alt="2" width="29" height="29" class="me-2">
                    <?php elseif ($rank == 3): ?>
                    <img src="../assets/images/dashboard/juara3.png" alt="3" width="29" height="29" class="me-2">
                    <?php else: ?>
                    <span class="badge bg-secondary me-2"><?= $rank ?></span>
                    <?php endif; ?>
                    <span class="fw-medium ms-3 "><?= htmlspecialchars($row['name']) ?></span>
                </div>
                <span class="fw-bold"><?= $row['exp'] ?> XP</span>
                </div>
                <?php $rank++; ?>
            <?php endforeach; ?>

            <?php if ($extra_user): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center highlight">
                <div class="d-flex align-items-center">
                    <span class="badge bg-secondary me-2"><?= $extra_user_rank ?></span>
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
    </div>
    <div class="sideSection" style="width: 30%;">
    </div>
</div>