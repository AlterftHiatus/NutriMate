<?php
session_start();
require '../config/db.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt = $conn->prepare("SELECT name, exp, streak FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User tidak ditemukan.";
    exit;
}

$name = htmlspecialchars($user['name']);
$exp = (int)$user['exp'];
$streak = (int)$user['streak'];

// Ambil data EXP 7 hari terakhir
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

// Siapkan label dan data
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
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daily Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
</head>

<body class="container py-5">

    <a href="dashboard.php" class="mb-4 d-inline-block">⬅ Kembali ke dashboard</a>

    <div class="text-center mb-4">
        <h1>Hai, <?php echo $name; ?> 👋</h1>
        <p>Progress Harian Kamu</p>
    </div>

    <div class="card p-4 shadow-sm mb-4">
        <div class="mb-3">
            <span class="exp-badge">EXP: <?php echo $exp; ?></span>
        </div>
        <div class="mb-3">
            <span class="streak-badge">🔥 Streak: <?php echo $streak; ?> hari</span>
        </div>
        <a href="beraktivitas.php" class="btn btn-primary btn-lg w-100">Mulai Beraktivitas</a>
    </div>

    <div class="card p-4 shadow-sm">
        <h5 class="mb-3 text-center">📊 EXP 7 Hari Terakhir</h5>
        <canvas id="expChart" height="120"></canvas>
    </div>

    <div class="card p-4 shadow-sm mt-4">
    <h5 class="mb-3 text-center">📝 Riwayat Aktivitas (7 Hari Terakhir)</h5>
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

</body>

</html>