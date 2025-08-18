<?php
require_once "../functions/auth.php";

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = registerUser($_POST['name'], $_POST['email'], $_POST['password']);
    if ($result === true) {
        header("Location: login.php?registered=1");
        exit;
    } else {
        $message = $result;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
<h2>Register</h2>
<?php if ($message) echo "<p style='color:red;'>$message</p>"; ?>
<form method="post">
    <input type="text" name="name" placeholder="Nama" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Daftar</button>
</form>
<p>Sudah punya akun? <a href="login.php">Login</a></p>
</body>
</html>
