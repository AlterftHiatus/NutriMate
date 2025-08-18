<?php
session_start();
include '../config/db.php'; // koneksi database

// var_dump($_SESSION['user_id']);


// Ambil top 10 user berdasarkan exp
$sql = "SELECT id, name, exp FROM users ORDER BY exp DESC LIMIT 10";
$result = $conn->query($sql);

$current_user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .highlight td {
            background-color: #ffe08a !important;
            /* warna kuning */
            font-weight: bold !important;
            color: #000 !important;
            /* teks tetap terbaca */
        }
    </style>
</head>

<body class="bg-light">

    <a href="index.php" class="mb-4 d-inline-block">⬅ Kembali ke index</a>

    <div class="container mt-5">
        <h2 class="text-center mb-4">🏆 Leaderboard Top 10</h2>
        <table class="table table-striped table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Peringkat</th>
                    <th>Nama</th>
                    <th>EXP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rank = 1;
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $highlight = ($row['id'] == $current_user_id) ? "highlight" : "";
                        echo "<tr class='{$highlight}'>
                                <td>{$rank}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['exp']}</td>
                              </tr>";
                        $rank++;
                    }
                } else {
                    echo "<tr><td colspan='3'>Belum ada data</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>