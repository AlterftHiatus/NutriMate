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
        $_POST['jenis_kelamin']
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - NutriMate</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fff 0%, #fff7d1 40%, #FFD60A 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-register {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.15);
            padding: 35px;
            max-width: 480px;
            width: 100%;
            animation: fadeIn 0.8s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .card-register h2 {
            font-weight: 700;
            text-align: center;
            color: #000;
            margin-bottom: 25px;
        }
        .form-control {
            border-radius: 12px;
            padding-left: 40px;
        }
        .input-group-text {
            border-radius: 12px 0 0 12px;
            background: #FFD60A;
            color: #000;
            font-weight: bold;
            border: none;
        }
        .btn-register {
            background: #FFD60A;
            border: none;
            color: #000;
            font-weight: 600;
            border-radius: 12px;
            padding: 12px;
            transition: all .3s ease;
        }
        .btn-register:hover {
            background: #000;
            color: #FFD60A;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
        }
        .alert {
            border-radius: 10px;
        }
        .link-login a {
            font-weight: 600;
            color: #000;
            text-decoration: none;
            transition: color .3s;
        }
        .link-login a:hover {
            color: #FFD60A;
        }
    </style>
</head>
<body>

<div class="card-register">
    <h2>Daftar Akun</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" required>
        </div>
        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3 input-group">
                <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                <input type="number" name="height" class="form-control" placeholder="Tinggi (cm)" min="50" max="250" required>
            </div>
            <div class="col-md-6 mb-3 input-group">
                <span class="input-group-text"><i class="bi bi-body-text"></i></span>
                <input type="number" name="weight" class="form-control" placeholder="Berat (kg)" min="20" max="300" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Jenis Kelamin</label><br>
            <div class="form-check form-check-inline">
                <input type="radio" name="jenis_kelamin" value="laki-laki" class="form-check-input" required>
                <label class="form-check-label">Laki-Laki</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" name="jenis_kelamin" value="perempuan" class="form-check-input" required>
                <label class="form-check-label">Perempuan</label>
            </div>
        </div>

        <button type="submit" class="btn btn-register w-100">Daftar</button>
    </form>

    <p class="text-center mt-3 link-login">
        Sudah punya akun? <a href="login.php">Login di sini</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
