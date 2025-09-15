<?php
session_start();
include '../config/db.php';

$current_user_id = $_SESSION['user_id'] ?? null;
if (!$current_user_id) exit("Unauthorized");

if (isset($_POST['status_emot'])) {
    $status = $_POST['status_emot'];

    $stmt = $conn->prepare("UPDATE users SET status_emot = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $current_user_id);
    $stmt->execute();

    echo "OK";
}
