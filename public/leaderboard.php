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
    u.streak,
    u.status_emot
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
$sql_top = "SELECT id, name, exp, status_emot, avatar FROM users ORDER BY exp DESC LIMIT 10";
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
    $stmt = $conn->prepare("SELECT id, name, exp, status_emot, avatar FROM users WHERE id = ?");
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
.emot-bubble {
  position: absolute;
  top: -5px;   /* sedikit masuk ke avatar */
  right: -5px;
  background: #fff;
  border-radius: 50%;
  font-size: 14px;   /* kecilin */
  padding: 4px;
  line-height: 1;
  box-shadow: 0 0 3px rgba(0,0,0,0.3);
}
.position-relative {
  width: 50px;
  height: 50px;   /* fix tinggi */
}
.titleStreaklead img {
  display: block;
}
.titleStreaklead span {
  font-weight: 600;
  font-size: 0.95rem;
}
.status-avatar-wrapper {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;      /* biar center */
  margin-bottom: 1rem;
}

.status-avatar-wrapper img {
  display: block;
  border-radius: 50%;
  border: 2px solid #ddd;
}


#user-emot:empty {
  display: none;
}

.user-emot:empty {
  display: none; /* otomatis hilang kalau kosong */
}

</style>
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
                  <!-- Rank agak berjauhan -->
                  <div class="me-3">
                    <?php if ($rank == 1): ?>
                      <img src="../assets/images/dashboard/juara1.png" alt="1" width="30" height="30">
                    <?php elseif ($rank == 2): ?>
                      <img src="../assets/images/dashboard/juara2.png" alt="2" width="30" height="30">
                    <?php elseif ($rank == 3): ?>
                      <img src="../assets/images/dashboard/juara3.png" alt="3" width="30" height="30">
                    <?php else: ?>
                      <span class="badge bg-secondary"><?= $rank ?></span>
                    <?php endif; ?>
                  </div>

                  <!-- Avatar + Emot Bubble -->
                  <!-- dalam loop top users -->
                  <div class="position-relative me-3 align-items-center justify-content-center">
                    <img src="../assets/images/avatar/<?= htmlspecialchars($row['avatar']) ?>.png" 
                        alt="avatar" width="50" height="50" 
                        class="rounded-circle border">

                    <!-- Bubble selalu ada, punya id unik per user -->
                    <span
                      class="emot-bubble user-emot"
                      id="user-emot-<?= $row['id'] ?>"
                      <?= empty($row['status_emot']) ? 'style="display:none"' : '' ?>
                    >
                      <?= htmlspecialchars($row['status_emot'] ?? '') ?>
                    </span>
                  </div>


                  <!-- Nama -->
                  <span><?= htmlspecialchars($row['name']) ?></span>
                </div>

                <!-- XP -->
                <span class="fw-bold"><?= $row['exp'] ?> XP</span>
              </div>
            <?php $rank++; ?>
          <?php endforeach; ?>

            <?php if ($extra_user): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center bg-gradient bg-warning text-dark">
                <div class="d-flex align-items-center">
                  <span class="badge bg-dark me-3"><?= $extra_user_rank ?></span>

                  <!-- Avatar + Emot Bubble (extra user) -->
<!-- extra_user -->
                <div class="position-relative me-3 align-items-center justify-content-center">
                  <img src="../assets/images/avatar/<?= htmlspecialchars($extra_user['avatar']) ?>.png" 
                      alt="avatar" width="50" height="50" 
                      class="rounded-circle border">

                  <span
                    class="emot-bubble user-emot"
                    id="user-emot-<?= $extra_user['id'] ?>"
                    <?= empty($extra_user['status_emot']) ? 'style="display:none"' : '' ?>
                  >
                    <?= htmlspecialchars($extra_user['status_emot'] ?? '') ?>
                  </span>
                </div>

                  <!-- Nama -->
                  <span class="fw-bold"><?= htmlspecialchars($extra_user['name']) ?></span>
                </div>

                <!-- XP -->
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
        <div class="titleStreaklead d-flex align-items-center justify-content-around gap-4 p-2 px-4 bg-white rounded-pill shadow-sm">
          <!-- Exp -->
          <div class="d-flex align-items-center gap-2">
            <img src="../assets/images/dashboard/exp.png" width="28">
            <span class="fw-bold"><?= $exp; ?></span>
          </div>

          <!-- Rank / Gold -->
          <div class="d-flex align-items-center gap-2">
            <img src="../assets/images/dashboard/gold.png" width="28">
            <span class="fw-bold"><?= $user_rank; ?></span>
          </div>

          <!-- Streak -->
          <div class="d-flex align-items-center gap-2">
            <img src="../assets/images/dashboard/<?= $streak ? "redFire" : "blackFire"; ?>.png" width="28">
            <span class="fw-bold"><?= $streak; ?></span>
          </div>

          <!-- Avatar -->
          <div class="dropdown">
            <a href="#" data-bs-toggle="dropdown">
              <img src="../assets/images/avatar/<?= $avatar ?>.png" width="32" class="rounded-circle">
            </a>
            <div class="dropdown-menu dropdown-menu-end">
              <a href="index.php?page=profil" class="dropdown-item">Profil</a>
              <a href="logout.php" class="dropdown-item text-danger">
                <i class="fas fa-sign-out-alt"></i> Keluar
              </a>
            </div>
          </div>
        </div>


      <!-- Daily Challenge -->
      <div class="card shadow-sm mt-4">
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
      <div class="card shadow-sm mt-3">
        <div class="card-body">
          <h6 class="text-uppercase text-secondary mb-2">Pasang Statusmu</h6>
          <div class="status-avatar-wrapper my-4">
            <div class="position-relative" style="width:80px; height:80px;">
              <img src="../assets/images/avatar/<?= $avatar ?>.png" 
                  alt="avatar" width="80" height="80">

              <!-- bubble milik user saat ini -->
              <span
                class="emot-bubble user-emot"
                id="my-emot"
                <?= empty($user['status_emot']) ? 'style="display:none"' : '' ?>
              >
                <?= htmlspecialchars($user['status_emot'] ?? '') ?>
              </span>
            </div>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <?php 
            $emots = ["😎","🎉","💪","👀","🍿","🇺🇸","😈","💯","💩","🏆","🍟","👓"];
            foreach ($emots as $emot): ?>
              <button class="btn btn-light border choose-emot" data-emot="<?= $emot ?>"><?= $emot ?></button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<script>
const currentUserId = <?= json_encode($current_user_id) ?>;

document.querySelectorAll('.choose-emot').forEach(btn => {
  btn.addEventListener('click', function() {
    let emot = this.getAttribute('data-emot');
    fetch('../process/update_status.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'status_emot=' + encodeURIComponent(emot)
    })
    .then(res => res.text())
    .then(data => {
      // server masih mengembalikan "OK" (sesuaikan bila kamu ubah ke JSON)
      if (data.trim() === "OK") {
        // update bubble pada scoreboard (jika ada)
        let listBubble = document.querySelector(`#user-emot-${currentUserId}`);
        if (listBubble) {
          listBubble.textContent = emot;
          listBubble.style.display = 'inline-block';
        } else {
          // jika tidak ada (mis. belum tampil di scoreboard), kita bisa buatnya secara dinamis:
          const playerDiv = document.querySelector(`.list-group-item[data-user-id="${currentUserId}"]`);
          if (playerDiv) {
            const newSpan = document.createElement('span');
            newSpan.id = `user-emot-${currentUserId}`;
            newSpan.className = 'emot-bubble user-emot';
            newSpan.textContent = emot;
            playerDiv.querySelector('.position-relative')?.appendChild(newSpan);
          }
        }

        // update bubble di sidebar (my-emot)
        let myBubble = document.getElementById('my-emot');
        if (myBubble) {
          myBubble.textContent = emot;
          myBubble.style.display = 'inline-block';
        }
      } else {
        alert("Gagal update status!");
      }
    });
  });
});
</script>
