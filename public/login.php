<?php
require_once "../functions/auth.php";

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = loginUser($_POST['email'], $_POST['password']);
    if ($result === true) {
        header("Location: dashboard.php");
        exit;
    } else {
        $message = $result;
    }
}
$registered = isset($_GET['registered']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
<h2>Login</h2>
<?php if ($registered) echo "<p style='color:green;'>Registrasi berhasil! Silakan login.</p>"; ?>
<?php if ($message) echo "<p style='color:red;'>$message</p>"; ?>
<form method="post">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
</form>
<p>Belum punya akun? <a href="register.php">Daftar</a></p>
</body>
</html>
