<?php
// Pastikan tidak ada whitespace/karakter sebelum <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "auth.php";
require_once "../config/db.php";

// Verifikasi koneksi PDO
if (!isset($pdo)) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Invalid JSON data"]);
    exit;
}

// Pastikan data yang diperlukan ada
$required = ['activity', 'duration', 'calories', 'exp'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        echo json_encode(["success" => false, "message" => "Missing field: $field"]);
        exit;
    }
}

try {
    $userId = $_SESSION['user_id'];
    $activityMap = ['walk' => 1, 'jog' => 2, 'bike' => 3];
    $activityId = $activityMap[$data['activity']] ?? 0;

    if ($activityId === 0) {
        throw new Exception("Invalid activity type");
    }

    $pdo->beginTransaction();

    // Simpan aktivitas
    $stmt = $pdo->prepare("INSERT INTO user_activities 
        (user_id, activity_id, duration_minutes, calories_burned, exp_earned, activity_date) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $activityId,
        ceil($data['duration'] / 60), // Convert to minutes
        $data['calories'],
        $data['exp'],
        date('Y-m-d')
    ]);

    // Update user stats (EXP & streak)
    $stmt = $pdo->prepare("SELECT streak, last_activity_date, exp FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $newStreak = 1; // default

        if ($user['last_activity_date'] === $yesterday) {
            $newStreak = $user['streak'] + 1; // teruskan streak
        } elseif ($user['last_activity_date'] === $today) {
            $newStreak = $user['streak']; // sudah aktivitas hari ini
        }

        $newExp = $user['exp'] + (int)$data['exp'];

        $updateUser = $pdo->prepare("UPDATE users 
        SET exp = ?, streak = ?, last_activity_date = ? 
        WHERE id = ?");
        $updateUser->execute([$newExp, $newStreak, $today, $userId]);
    }


    // Commit transaction
    $pdo->commit();
    echo json_encode([
        "success" => true,
        "message" => "Activity saved",
        "data" => $data
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
