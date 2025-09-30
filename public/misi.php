<?php
// Perbaikan: Mencegah error 'session already active'
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asumsi file ini ada dan membuat koneksi $conn (MySQLi connection object)
require "../config/db.php";

$user_id = 1; // Contoh user login (seharusnya diambil dari $_SESSION['user_id'])

// --- DEKLARASI MISI ---
$missions = [
    [
        'id' => 1,
        'name' => 'Menempuh jarak 1 km',
        'target_type' => 'distance',
        'target_value' => 1.0,
        'reward' => 10
    ],
    [
        'id' => 2,
        'name' => 'Menempuh jarak 5 km',
        'target_type' => 'distance',
        'target_value' => 5.0,
        'reward' => 50
    ],
    [
        'id' => 3,
        'name' => 'Membakar 500 kalori',
        'target_type' => 'calories',
        'target_value' => 500,
        'reward' => 100
    ]
];

// --- AMBIL TOTAL AKTIVITAS USER HARI INI (DIHITUNG DULU SEBELUM LOGIKA POST) ---
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
$stmt->close(); // Tutup statement setelah digunakan

$total_km = (float)($result['total_km'] ?? 0);
$total_calories = (int)($result['total_calories'] ?? 0);

// --- LOGIKA SESSI MISI DAN POST REQUEST ---

// Inisialisasi status misi di session jika belum ada
if (!isset($_SESSION['mission_status'])) {
    // Menyimpan status: { misi_id: {active: true/false, claimed: true/false} }
    $_SESSION['mission_status'] = [];
}

// Logika untuk memulai misi
if (isset($_POST['start_mission'])) {
    $misi_id = (int)$_POST['start_mission'];
    // Cek jika ID misi valid
    if (array_key_exists($misi_id, array_column($missions, null, 'id'))) {
        $_SESSION['mission_status'][$misi_id] = ['active' => true, 'claimed' => false];
        $message = "✅ Misi telah dimulai!";
    }
}

// Logika untuk mengklaim reward dan UPDATE DATABASE
if (isset($_POST['claim_reward'])) {
    $misi_id = (int)$_POST['claim_reward'];
    // Mencari misi berdasarkan ID
    $misi_index = array_search($misi_id, array_column($missions, 'id'));
    
    // Memastikan misi aktif, belum diklaim, dan ID misi valid
    if (isset($_SESSION['mission_status'][$misi_id]) && $_SESSION['mission_status'][$misi_id]['active'] && $misi_index !== false) {
        
        $misi_target = $missions[$misi_index];
        
        // Menentukan progress saat ini
        $progress_value = ($misi_target['target_type'] == 'distance') ? $total_km : $total_calories;
        
        if ($progress_value >= $misi_target['target_value'] && !$_SESSION['mission_status'][$misi_id]['claimed']) {
            
            $reward_points = $misi_target['reward'];
            
            // --- IMPLEMENTASI UPDATE POIN KE DATABASE (Tabel 'users') ---
            $sql_update_points = "UPDATE users SET points = points + ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($sql_update_points);
            
            if ($stmt_update) {
                $stmt_update->bind_param("ii", $reward_points, $user_id);
                
                if ($stmt_update->execute()) {
                    // BERHASIL: Tandai misi sebagai sudah diklaim
                    $_SESSION['mission_status'][$misi_id]['claimed'] = true;
                    $message = "🎉 Berhasil mengklaim *{$reward_points} point*! Poin Anda telah ditambahkan.";
                } else {
                    $message = "❌ Gagal memperbarui poin user di database: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                $message = "❌ Error prepare statement update poin: " . $conn->error;
            }
            // -----------------------------------------------------------
            
        } else {
             $message = "❌ Misi belum selesai atau sudah diklaim.";
        }
    }
    // Lakukan redirect untuk mencegah form resubmission saat refresh
    // header("Location: " . $_SERVER['PHP_SELF']); 
    // exit();
}
?>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px; }
        .misi-box { background: #fff; border-radius: 12px; padding: 20px; width: 400px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .misi-title { font-size: 16px; font-weight: bold; margin-bottom: 8px; }
        .progress-bar { background: #eee; border-radius: 20px; overflow: hidden; height: 25px;
            margin-top: 10px; margin-bottom: 10px; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #4caf50, #66bb6a);
            text-align: center; line-height: 25px; color: white; font-size: 14px;
            transition: width 0.5s; }
        .reward { font-size: 14px; color: #555; }
        .status { margin-top: 10px; font-weight: bold; }
        .btn-start { background: #2196f3; }
        .btn-claim { background: #ff9800; }
        .btn-claimed { background: #9e9e9e; cursor: not-allowed; }
        button { border: none; color: white; padding: 6px 12px;
            font-size: 14px; border-radius: 6px; cursor: pointer; }
        button:hover:not(:disabled) { opacity: 0.8; }
    </style>


<h2>📌 Misi Harian</h2>

<?php if (isset($message)): ?>
    <div style="background: #e0f7fa; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 5px solid #00bcd4;"><?= $message ?></div>
<?php endif; ?>

<?php foreach ($missions as $misi): 
    $misi_id = $misi['id'];
    $misi_status = $_SESSION['mission_status'][$misi_id] ?? ['active' => false, 'claimed' => false];
?>
    <div class="misi-box">
        <div class="misi-title">🎯 <?= $misi['name'] ?></div>
        <div class="reward">Reward: <?= $misi['reward'] ?> Point</div>

        <?php if (!$misi_status['active']): ?>
            <form method="post">
                <button type="submit" name="start_mission" value="<?= $misi_id ?>" class="btn-start">▶ Mulai Misi</button>
            </form>
        <?php else: 
            // Sudah mulai -> hitung progress
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
                    ✅ Selesai & *sudah diklaim!* (+<?= $misi['reward'] ?> Poin)
                </div>
                <button disabled class="btn-claimed">Klaim (Sudah)</button>
            <?php elseif ($misi_selesai): ?>
                <div class="status" style="color:green">
                    🎉 Misi selesai! Klaim sekarang untuk *<?= $misi['reward'] ?> point*.
                </div>
                <form method="post">
                    <button type="submit" name="claim_reward" value="<?= $misi_id ?>" class="btn-claim">💰 Klaim Reward</button>
                </form>
            <?php else: ?>
                <div class="status" style="color:orange">
                    ⏳ Progress: *<?= round($progress_value, 2) . $unit ?>* / <?= $misi['target_value'] . $unit ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
<?php endforeach; ?>
