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
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login — Dua Kolom Gradient</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    html, body { height: 100%; margin: 0; font-family: "Poppins", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }

    /* Baris yang membagi layar */
    .split-row { min-height: 100vh; }

    /* KIRI: gradient gelap */
    .left-side {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2.5rem;
      /* gradient sendiri untuk sisi kiri */
      background: linear-gradient(135deg, #000000 0%, #141414 50%, #2a2a2a 100%);
      color: #fff;
    }

    /* kanan: gradient kuning */
    .right-side {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2.5rem;
      /* gradient sendiri untuk sisi kanan */
      background: linear-gradient(135deg, #FFD93D 0%, #FFC107 50%, #FFB300 100%);
    }

    /* bungkus avatar pada sisi kiri */
    .avatar-wrap {
      background: rgba(255,255,255,0.03);
      border-radius: 18px;
      padding: 1.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 30px rgba(0,0,0,0.6);
    }

    .avatar {
      display: block;
      width: 340px;
      max-width: 100%;
      border-radius: 14px;
      object-fit: contain;
    }

    /* kartu login di sisi kanan */
    .login-card {
      width: 100%;
      max-width: 420px;
      background: #ffffff;
      padding: 1.75rem;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    }

    .logo {
      width: 120px;
      display: block;
      margin: 0 auto 0.75rem;
    }

    .btn-google {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .6rem;
      border: 1px solid rgba(0,0,0,0.08);
      background: #fff;
      color: #111827;
      font-weight: 500;
    }

    .btn-google img { width: 18px; height: auto; }

    /* responsive tweaks */
    @media (max-width: 767.98px) {
      .avatar { width: 260px; }
      .left-side, .right-side { padding: 1.5rem; }
    }
  </style>
</head>
<body>

<div class="container-fluid g-0">
  <div class="row split-row g-0">
    <!-- KIRI -->
    <div class="col-12 col-md-6 left-side">
      <div class="avatar-wrap">
        <!-- ganti path gambar avatar sesuai project kamu -->
        <img src="../aset/Gemini_Generated_Image_lu74eglu74eglu74-removebg-preview.png" alt="Avatar Nut" class="avatar" style="width: 500px;">
      </div>
    </div>

    <!-- KANAN -->
    <div class="col-12 col-md-6 right-side">
      <div class="login-card">
        <img src="../aset/logo-removebg-preview.png" alt="Logo" class="logo">

        <h4 class="mb-1 text-dark text-center">Log in to your account</h4>
        <p class="text-muted small text-center mb-4">Welcome back! Please enter your details.</p>

        <?php if ($registered): ?>
          <div class="alert alert-success">Registrasi berhasil! Silakan login.</div>
        <?php endif; ?>
        <?php if ($message): ?>
          <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>

        <form method="post" class="text-start">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password" placeholder="••••••••" required>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="remember" name="remember">
              <label class="form-check-label small" for="remember">Remember for 30 days</label>
            </div>
            <a href="#" class="small text-decoration-none">Forgot password</a>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary mb-3">Sign in</button>

            <button type="button" class="btn btn-google">
              <img src="https://www.svgrepo.com/show/355037/google.svg" alt="Google">
              Sign in with Google
            </button>
          </div>
        </form>

        <p class="small mt-3 mb-0 text-center">Don’t have an account? <a href="register.php" class="text-decoration-none">Sign up</a></p>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




