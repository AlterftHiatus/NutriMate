<?php
require_once "../functions/auth.php";
if (!isAuthenticated()) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
<h2>Selamat datang, <?php echo $_SESSION['name']; ?>!</h2>
<ul>
    <li><a href="daily.php">Daily</a></li>
    <li><a href="chatbot.php">Chatbot</a></li>
    <li><a href="leaderboard.php">Leaderboard</a></li>
    <li><a href="profile.php">Profile</a></li>
</ul>
<p><a href="logout.php">Logout</a></p>
</body>
</html>
