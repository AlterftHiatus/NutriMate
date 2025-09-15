<?php
$user_id = $_SESSION['user_id'];

// ===================================
// Ambil data user + rank
// ===================================
$sql_user = "
    SELECT u.id, u.name, u.exp, u.height, u.weight, u.avatar, u.bmi, u.streak,
    (SELECT COUNT(*) + 1 FROM users WHERE exp > u.exp) AS user_rank
    FROM users u
    WHERE u.id = ?
";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User tidak ditemukan.";
    exit;
}

// Fallback untuk avatar & bmi
$avatar = !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : "default";
$bmi = !empty($user['bmi']) ? htmlspecialchars($user['bmi']) : "normal";

// Sanitasi
$name = htmlspecialchars($user['name']);
$height = (int)$user['height'];
$weight = (int)$user['weight'];
$exp = (int)$user['exp'];
$streak = (int)$user['streak'];
$user_rank = (int)$user['user_rank'];

// ===================================
// Ambil data EXP 7 hari terakhir
// ===================================
$query = "
    SELECT activity_date, SUM(exp_earned) AS total_exp
    FROM user_activities
    WHERE user_id = ?
      AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY activity_date
";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

$expData = [];
while ($row = $result2->fetch_assoc()) {
    $expData[$row['activity_date']] = (int)$row['total_exp'];
}

// Label & data EXP
$labels = [];
$expValues = [];
$days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = $days[date('w', strtotime($date))];
    $labels[] = $dayName;
    $expValues[] = $expData[$date] ?? 0;
}

// ===================================
// Ambil riwayat aktivitas 7 hari terakhir
// ===================================
$queryHistory = "
    SELECT ua.activity_date, a.name AS activity_name, ua.duration_minutes, ua.calories_burned, ua.exp_earned
    FROM user_activities ua
    JOIN activities a ON ua.activity_id = a.id
    WHERE ua.user_id = ?
    AND ua.activity_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    ORDER BY ua.activity_date DESC, ua.created_at DESC
";
$stmtHistory = $conn->prepare($queryHistory);
$stmtHistory->bind_param("i", $user_id);
$stmtHistory->execute();
$resultHistory = $stmtHistory->get_result();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
  --primary: #080808ff;
  --secondary: #6c757d;
  --accent: #080808ff;
  --soft-bg: #f8f9fa;
  --card-bg: #fff;
  --shadow: 0 4px 12px rgba(0,0,0,.08);
  --radius: 12px;
}

/* Layout */
section.d-flex.gap-4 {
    width: 100% !important;
  display: flex;
  gap: 2rem;
}
.todo-container { width: 70%; }
.sideSection { width: 30%; }

/* Card */
.card {
  background: var(--card-bg);
  border: none;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  transition: transform .3s ease;
}
/* .card:hover { transform: translateY(-4px); } */

/* Title */
h2.fw-bold { color: var(--accent) !important; }
.text-muted { color: var(--secondary) !important; }

/* Streak bar */
.titleStreak {
  background: var(--card-bg);
  border-radius: 50px;
  padding: .75rem 1rem;
  box-shadow: var(--shadow);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}
.streak { display: flex; align-items: center; gap: 6px; }
.streak p { font-weight: 600; margin: 0; }

/* Start button */
#start-btn {
  /* background: #198754; */
  border-radius: 50px;
  font-weight: bold;
  box-shadow: 0 4px 10px rgba(118, 135, 25, 0.3);
}
#start-btn:hover { background: #b4a908ff; }

/* Table */
.table-responsive {
  border-radius: var(--radius);
  overflow-x: auto;   /* Biar bisa geser kiri-kanan */
  max-height: 350px;
}

.table {
  min-width: 600px;   /* Pastikan tabel tidak mengecil terlalu ekstrim */
}


.table thead th {
  background: var(--primary);
  color: #fff;
  border: none;
}
.table-hover tbody tr:hover { background: #f1f1f1; }

/* Mobile */
@media (max-width: 768px) {
  section.d-flex.gap-4 {
    display: grid !important; /* paksa grid, override bootstrap */
    grid-template-areas:
      "titleStreak"
      "header"
      "startbtn"
      "chart"
      "history";
    gap: 1rem;
    max-width: 100% !important;
    padding: 0 10px;
  }

  /* hilangkan container wrapper agar child bisa ikut grid */
  .todo-container,
  .sideSection {
    display: contents;
  }

  /* mapping elemen ke grid */
  .titleStreak { grid-area: titleStreak; }
  #headerDashboard { grid-area: header; }
  #start-btn { grid-area: startbtn; }
  .todo-container .card:first-of-type { grid-area: chart; }
  .todo-container .card:last-of-type { grid-area: history; }

  .character { display: none; } /* opsional */

  /* tabel responsif */
  .table thead {
    display: none;
  }
  .table, .table tbody, .table tr, .table td {
    display: block;
    width: 100%;
  }
  .table tr {
    margin-bottom: 1rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 0.5rem;
    box-shadow: var(--shadow);
  }
  .table td {
    text-align: left;
    padding: .5rem;
    border: none;
    display: flex;
    justify-content: space-between;
  }
  .table td::before {
    content: attr(data-label);
    font-weight: 600;
    color: var(--secondary);
  }
}

</style>

<section class="d-flex gap-4">
  <!-- Main -->
  <div class="todo-container">
    <div class="mb-4" id="headerDashboard">
      <h2 class="fw-bold">Dashboard Aktivitas</h2>
      <p class="text-muted mb-0">Pantau progres olahraga dan perkembanganmu</p>
    </div>

    <div class="card p-4 mb-4">
      <h5 class="mb-3 text-center fw-semibold"><i class="bi bi-graph-up fw-bold me-2"></i>XP 7 Hari Terakhir</h5>
      <canvas id="expChart" height="270"></canvas>
    </div>

    <div class="card p-4">
      <h5 class="mb-3 text-center fw-semibold"><i class="bi bi-card-checklist fw-bold me-2"></i>Riwayat Aktivitas</h5>
      <div class="table-responsive">
        <table class="table table-striped table-hover text-center">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Aktivitas</th>
              <th>Durasi</th>
              <th>Kalori</th>
              <th>XP</th>
            </tr>
          </thead>
            <tbody>
              <?php if ($resultHistory->num_rows > 0): ?>
                <?php while ($row = $resultHistory->fetch_assoc()): ?>
                  <tr>
                    <td data-label="Tanggal"><?= date('d M Y', strtotime($row['activity_date'])); ?></td>
                    <td data-label="Aktivitas"><?= htmlspecialchars($row['activity_name']); ?></td>
                    <td data-label="Durasi"><?= (int)$row['duration_minutes']; ?> mnt</td>
                    <td data-label="Kalori"><?= (int)$row['calories_burned']; ?> kcal</td>
                    <td data-label="EXP"><?= (int)$row['exp_earned']; ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5">Belum ada aktivitas.</td></tr>
              <?php endif; ?>
            </tbody>

        </table>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="sideSection">
    <div class="titleStreak mb-4 justify-content-around">
      <div class="streak"><img src="../assets/images/dashboard/exp.png" width="32"><p><?= $exp; ?></p></div>
      <div class="streak"><img src="../assets/images/dashboard/gold.png" width="32"><p><?= $user_rank; ?></p></div>
      <div class="streak">
        <img src="../assets/images/dashboard/<?= $streak ? "redFire" : "blackFire"; ?>.png" width="32">
        <p><?= $streak; ?></p>
      </div>
      <div class="dropdown">
        <a href="#" data-bs-toggle="dropdown">
          <img src="../assets/images/avatar/<?= $avatar ?>.png" width="32" class="rounded-circle">
        </a>
        <div class="dropdown-menu dropdown-menu-end">
          <a href="index.php?page=profil" class="dropdown-item">Profil</a>
          <a href="logout.php" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
      </div>
    </div>

    <a href="beraktivitas.php" id="start-btn" class="btn btn-warning p-3 w-100 mb-3 text-white">
      Mulai Olahraga <i class="bi bi-play-fill"></i>
    </a>

    <div class="character">
      <div class="card p-2 text-center">
        <p class="fw-bold mb-1"><?= $name; ?></p>
        <div class="ratio" style="--bs-aspect-ratio:133.33%;">
          <img src="../assets/images/videos/<?= $avatar.$bmi; ?>.gif" class="w-100 h-100 object-fit-cover">
        </div>
        <div class="mt-2">
          <p class="mb-1">Tinggi: <?= $height; ?> cm</p>
          <p class="mb-1">Berat: <?= $weight; ?> kg</p>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
const ctx = document.getElementById('expChart').getContext('2d');

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($labels); ?>,
    datasets: [{
      label: 'EXP',
      data: <?= json_encode($expValues); ?>,
      backgroundColor: [
        '#007bff', // biru
        '#28a745', // hijau
        '#ffc107', // kuning
        '#dc3545', // merah
        '#6f42c1', // ungu
        '#20c997', // toska
        '#fd7e14'  // oranye
      ],
      borderRadius: 10
    }]
  },
  options: {
    responsive: true,
    plugins: { 
      legend: { display: false }, 
      tooltip: { callbacks: { label: ctx => ctx.parsed.y + ' EXP' } }
    },
    scales: { 
      y: { beginAtZero: true, ticks: { stepSize: 10 } } 
    }
  }
});

</script>
