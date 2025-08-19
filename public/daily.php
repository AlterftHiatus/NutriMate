<?php
$user_id = $_SESSION['user_id'];

// ===================================
// TODO: Ambil data RANK
// ===================================
$sql_user = "
    SELECT u.id, u.name, u.exp,
           (SELECT COUNT(*) + 1 FROM users WHERE exp > u.exp) AS rank
    FROM users u
    WHERE u.id = ?
";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
if ($user_result->num_rows > 0) {
    $extra_user = $user_result->fetch_assoc();
}


// ===================================
//TODO: Ambil data user
// ===================================
$stmt = $conn->prepare("SELECT name, exp, height, weight, streak FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User tidak ditemukan.";
    exit;
}
$name = htmlspecialchars($user['name']);
$height = (int)$user['height'];
$weight = (int)$user['weight'];
$exp = (int)$user['exp'];
$streak = (int)$user['streak'];



// ===================================
//TODO: Ambil data EXP 7 hari terakhir
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


// ===================================
//TODO: Siapkan label dan data
// ===================================
$labels = [];
$expValues = [];
$days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = $days[date('w', strtotime($date))];
    $labels[] = $dayName;
    $expValues[] = $expData[$date] ?? 0;
}

// Ambil data riwayat aktivitas 7 hari terakhir
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
    body {
        background-color: #f8f9fa;
    }

    .streak-badge {
        font-size: 1.2rem;
        color: #ff5722;
        font-weight: bold;
    }

    .exp-badge {
        font-size: 1.2rem;
        color: #2196f3;
        font-weight: bold;
    }
</style>
<section  class="d-flex gap-5">
    <div class="todo-container " style="width: 70%;" >
        <div class="mb-4">
            <h2 class="fw-bold">📊 Dashboard Aktivitas</h2>
            <p class="text-muted mb-0">Pantau perkembangan olahraga & progresmu di sini</p>
        </div>
        <!--TODO: HISTORY dan DIAGRAM BATANG -->
        <div class="card p-4 shadow-sm">
            <h5 class="mb-3 text-center"><i class="bi bi-graph-up"></i> EXP 7 Hari Terakhir</h5>
            <canvas id="expChart" height="270"></canvas>
        </div>

        <div class="card p-4 shadow-sm mt-4">
        <h5 class="mb-3 text-center"><i class="bi bi-card-checklist"></i> Riwayat Aktivitas (7 Hari Terakhir)</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Aktivitas</th>
                        <th>Durasi (mnt)</th>
                        <th>Kalori</th>
                        <th>EXP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultHistory->num_rows > 0): ?>
                        <?php while ($row = $resultHistory->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($row['activity_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['activity_name']); ?></td>
                                <td><?php echo (int)$row['duration_minutes']; ?></td>
                                <td><?php echo (int)$row['calories_burned']; ?></td>
                                <td><?php echo (int)$row['exp_earned']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Belum ada aktivitas dalam 7 hari terakhir.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </div>
    </div> 
    <div class="sideSection" style="width: 30%;">

        <!-- TITLE STREAK -->
        <div class="titleStreak d-flex gap-2 align-items-center justify-content-around mb-4">

            <!-- EXP -->
            <div class="streak d-flex align-items-center">
                <img src="../assets/images/dashboard/exp.png" alt="" width="40" class="rounded-circle d-block">
                <p class="mb-0  fw-bold fs-5"><?php echo $exp; ?></p>
            </div>

            <div class="streak d-flex align-items-center">
                <img src="../assets/images/dashboard/gold.png" alt="" width="40" class="rounded-circle d-block">
                <p class="mb-0  fw-bold fs-5"><?php echo $extra_user['rank']; ?></p>
            </div>
            
            <!-- STREAK -->
            <div class="streak d-flex align-items-center">
                <img src="../assets/images/dashboard/redFire.png" alt="" width="40" class="rounded-circle d-block">
                <p class="mb-0  fw-bold fs-5"><?php echo $streak; ?></p>
            </div>

            <!-- PROFILE -->
            <div class="dropdown">
                <a class="nav-link nav-link-lg nav-link-user" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img alt="image" src="../assets/images/avatar/man2.png" width="40px" class="rounded-circle mr-1">
                </a>
                <div class="dropdown-menu dropdown-menu-left">
                    <a href="index.php?page=profil" class="dropdown-item has-icon ">Profile </a>
                    <a href="../auth/logout.php" class="dropdown-item has-icon text-danger">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                </div>
            </div>
        </div>

        <!-- BUTTON MULAI OLAHRAGA -->
        <a href="beraktivitas.php" id="start-btn" class="btn btn-info p-3 mb-3 text-white" style="width: 18rem;">Mulai untuk olahraga <i class="bi bi-play-fill"></i></a>

        <div class="character" style="width: 18rem;">
            <div class="card mb-3 pt-2 pb-2">
                <p class="fw-bold mb-1 text-center"><?php echo $user['name']; ?></p>
            <!-- Wadah ratio portrait -->
            <div class="ratio" style="--bs-aspect-ratio: 133.33%;">
            <img src="../assets/images/videos/womenNormal.gif" 
                alt="Animasi GIF" 
                class="w-100 h-100 object-fit-cover">
            </div>

            <div class="description align-items-center text-center mt-2">
            <p class="mb-1">Tinggi Badan: <?php echo $user['height']; ?> cm</p>
            <p class="mb-1">Berat Badan: <?php echo $user['weight']; ?> kg</p>
            </div>
        </div>
        </div>
        </div>
    </div>  
</section>



    <script>
        const ctx = document.getElementById('expChart').getContext('2d');

        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(33, 150, 243, 0.8)');
        gradient.addColorStop(1, 'rgba(33, 150, 243, 0.2)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'EXP',
                    data: <?php echo json_encode($expValues); ?>,
                    backgroundColor: gradient,
                    borderRadius: 6,
                    borderWidth: 1,
                    borderColor: '#1976d2',
                    hoverBackgroundColor: '#1565c0'
                }]
            },
            options: {
                responsive: true,
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' EXP';
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 10
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
