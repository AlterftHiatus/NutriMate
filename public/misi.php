<?php
// Perbaikan: Mencegah error 'session already active'
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi database
require "../config/db.php"; 

// user_id dari session login
$user_id = $_SESSION['user_id'] ?? 1; // default demo

// --- DEKLARASI MISI ---
$missions = [
    1 => [
        'id' => 1,
        'name' => 'Menempuh jarak 1 km',
        'target_type' => 'distance',
        'target_value' => 1.0,
        'reward' => 10
    ],
    2 => [
        'id' => 2,
        'name' => 'Menempuh jarak 5 km',
        'target_type' => 'distance',
        'target_value' => 5.0,
        'reward' => 50
    ],
    3 => [
        'id' => 3,
        'name' => 'Membakar 500 kalori',
        'target_type' => 'calories',
        'target_value' => 500,
        'reward' => 100
    ],
    4 => [
        'id' => 4,
        'name' => 'Bersepeda sejauh 4 km',
        'target_type' => 'distance',
        'target_value' => 500,
        'reward' => 100
    ]
];

// --- 1. AMBIL TOTAL AKTIVITAS USER HARI INI DARI DATABASE ---
$tanggal_hari_ini = date("Y-m-d");
$sql = "SELECT 
            SUM(`Jarak (km)`) as total_km, 
            SUM(calories_burned) as total_calories
        FROM user_activities
        WHERE user_id = ? AND DATE(activity_date) = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error prepare statement activity: " . $conn->error); 
}
$stmt->bind_param("is", $user_id, $tanggal_hari_ini);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close(); 

$total_km = (float)($result['total_km'] ?? 0);
$total_calories = (int)($result['total_calories'] ?? 0);

// --- 2. LOGIKA SESSI MISI DAN POST REQUEST ---
if (!isset($_SESSION['mission_status'])) {
    $_SESSION['mission_status'] = [];
}

// Mulai misi
if (isset($_POST['start_mission'])) {
    $misi_id = (int)$_POST['start_mission'];
    if (isset($missions[$misi_id])) {
        $_SESSION['mission_status'][$misi_id] = [
            'active' => true, 
            'claimed' => ($_SESSION['mission_status'][$misi_id]['claimed'] ?? false)
        ];
        $message = "✅ Misi telah diaktifkan! Jalankan olahraga untuk mengumpulkan progres.";
    }
}

// Klaim reward
if (isset($_POST['claim_reward'])) {
    $misi_id = (int)$_POST['claim_reward'];
    $misi_target = $missions[$misi_id] ?? null;

    if ($misi_target && isset($_SESSION['mission_status'][$misi_id]) 
        && $_SESSION['mission_status'][$misi_id]['active'] 
        && !$_SESSION['mission_status'][$misi_id]['claimed']) {
        
        $progress_value = ($misi_target['target_type'] == 'distance') ? $total_km : $total_calories;
        
        if ($progress_value >= $misi_target['target_value']) {
            $reward_points = $misi_target['reward'];
            
            $sql_update_points = "UPDATE users SET exp = exp + ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update_points);
            
            if ($stmt_update) {
                $stmt_update->bind_param("ii", $reward_points, $user_id);
                if ($stmt_update->execute()) {
                    $_SESSION['mission_status'][$misi_id]['claimed'] = true;
                    $message = "🎉 Berhasil klaim *{$reward_points} EXP*! Nilai EXP Anda bertambah.";
                } else {
                    $message = "❌ Gagal memperbarui EXP: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                $message = "❌ Error prepare statement update EXP: " . $conn->error;
            }
        } else {
             $message = "❌ Misi belum selesai. Progres saat ini: " . round($progress_value, 2) . " / " . $misi_target['target_value'];
        }
    } else {
        $message = "❌ Misi tidak valid, belum diaktifkan, atau sudah diklaim.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Misi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .main-card {
      background: #9b59b6;
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 30px;
      color: #fff;
    }
    .main-card .badge {
      background: #fff;
      color: #9b59b6;
      font-weight: bold;
      border-radius: 8px;
      padding: 4px 10px;
      font-size: 12px;
    }
    .main-title {
      font-size: 22px;
      font-weight: bold;
      margin-top: 10px;
    }
    .main-sub {
      font-size: 14px;
      opacity: 0.9;
      margin-bottom: 20px;
    }
    .progress-box {
      background: #1a1a1a;
      padding: 16px;
      border-radius: 12px;
    }
    .progress-label {
      font-size: 15px;
      font-weight: 600;
      margin-bottom: 12px;
    }
    .progress {
      height: 18px;
      border-radius: 10px;
      background: #333;
    }
    .progress-bar {
      background: #00f2fe;
      font-size: 12px;
      font-weight: bold;
      color: #000;
    }
    /* --- Card Misi --- */
    .misi-card {
      background: #222;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      height: 100%;
    }
    .misi-title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 10px;
    }
    .misi-sub {
      font-size: 13px;
      opacity: 0.85;
      margin-bottom: 15px;
    }
    .missions-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center; /* biar rata tengah */
        margin-top: 20px;
    }

    .misi-box {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        width: calc(50% - 20px); /* 2 kolom */
        box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    @media (max-width: 768px) {
        .misi-box {
            width: 100%; /* di HP jadi 1 kolom */
        }
    }

    .misi-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 8px;
    }
    .progress-bar {
        background: #eee;
        border-radius: 20px;
        overflow: hidden;
        height: 25px;
        margin: 10px 0;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #4caf50, #66bb6a);
        text-align: center;
        line-height: 25px;
        color: white;
        font-size: 14px;
        transition: width 0.5s;
    }
    .reward {
        font-size: 14px;
        color: #555;
    }
    .status {
        margin-top: 10px;
        font-weight: bold;
    }
    .btn-start { background: #2196f3; }
    .btn-claim { background: #ff9800; }
    .btn-claimed { background: #9e9e9e; cursor: not-allowed; }
    button {
        border: none;
        color: white;
        padding: 6px 12px;
        font-size: 10px;
        border-radius: 6px;
        cursor: pointer;
        margin-top: 10px;
        align-self: flex-start;
    }
    button:hover:not(:disabled) {
        opacity: 0.8;
    }
  </style>
</head>
<body>

  <!-- Card Utama -->
  <div class="main-card">
    <span class="badge">OKTOBER</span>
    <div class="main-title">Rumah Hantu Lili</div>
    <div class="main-sub">⏱ 29 HARI</div>
    <div class="progress-box">
      <div class="progress-label">Taklukkan 20 misi</div>
      <div class="progress">
        <div class="progress-bar" role="progressbar" style="width: 0%">0 / 20</div>
      </div>
    </div>
  </div>

  <!-- Grid Misi -->
<?php if (isset($message)): ?>
    <div style="background: #e0f7fa; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 5px solid #00bcd4;"><?= $message ?></div>
<?php endif; ?>

<div class="missions-container">
<?php foreach ($missions as $misi): 
    $misi_id = $misi['id'];
    $misi_status = $_SESSION['mission_status'][$misi_id] ?? ['active' => false, 'claimed' => false];
?>
    <div class="misi-box bg-dark">
        <div>
            <div class="misi-title">🎯 <?= $misi['name'] ?></div>
            <div class="reward text-white">Reward: <?= $misi['reward'] ?> EXP</div>

            <?php 
                if ($misi['target_type'] == 'distance') {
                    $progress_value = $total_km;
                    $unit = " km";
                } elseif ($misi['target_type'] == 'calories') {
                    $progress_value = $total_calories;
                    $unit = " kalori";
                }

                $progress_percent = ($progress_value / $misi['target_value']) * 100;
                if ($progress_percent > 100) $progress_percent = 100;
                $misi_selesai = $progress_value >= $misi['target_value'];
            ?>

            <div class="progress-bar">
                <div class="progress-fill" style="width:<?= $progress_percent ?>%">
                    <?= round($progress_percent) ?>%
                </div>
            </div>
            
            <?php if ($misi_status['claimed']): ?>
                <div class="status" style="color:green">
                    ✅ Selesai & sudah diklaim! (+<?= $misi['reward'] ?> EXP)
                </div>
                <button disabled class="btn-claimed">Klaim (Sudah)</button>

            <?php elseif ($misi_selesai && $misi_status['active']): ?>
                <div class="status" style="color:green">
                    🎉 Misi selesai! Klaim sekarang untuk <?= $misi['reward'] ?> EXP.
                </div>
                <form method="post">
                    <button type="submit" name="claim_reward" value="<?= $misi_id ?>" class="btn-claim">💰 Klaim Reward</button>
                </form>

            <?php elseif ($misi_selesai && !$misi_status['active']): ?>
                <div class="status" style="color:green; font-size: 13px !important;">
                    ✅ Misi selesai! Klik Mulai Misi untuk mengaktifkan klaim.
                </div>
                <form method="post">
                    <button type="submit" name="start_mission" value="<?= $misi_id ?>" class="btn-start">▶ Mulai Misi</button>
                </form>
                
            <?php else: ?>
                <div class="status" style="color:orange; font-size: 13px !important;">
                    ⏳ Progress: <?= round($progress_value, 2) . $unit ?> / <?= $misi['target_value'] . $unit ?>
                </div>
                <?php if (!$misi_status['active']): ?>
                    <form method="post">
                        <button type="submit" name="start_mission" value="<?= $misi_id ?>" class="btn-start bg-warning text-dark fw-semibold" style="font-size: 14px !important;">▶ Mulai Misi</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>