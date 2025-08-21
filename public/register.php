<?php
require_once "../functions/auth.php";

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = registerUser(
        $_POST['name'],
        $_POST['email'],
        $_POST['password'],
        $_POST['height'],
        $_POST['weight'],
        $_POST['jenis_kelamin'] // tambah field baru
    );
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
    <input type="number" name="height" placeholder="Tinggi Badan (cm)" min="50" max="250" required><br>
    <input type="number" name="weight" placeholder="Berat Badan (kg)" min="20" max="300" required><br>

    <!-- Tambahkan radio button -->
    <label>Jenis Kelamin:</label><br>
    <input type="radio" name="jenis_kelamin" value="laki-laki" required> Laki-Laki<br>
    <input type="radio" name="jenis_kelamin" value="perempuan" required> Perempuan<br><br>

    <button type="submit">Daftar</button>
</form>
<p>Sudah punya akun? <a href="login.php">Login</a></p>
</body>
</html>
