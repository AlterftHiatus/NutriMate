<?php
ob_start();
session_start();
require_once "../functions/auth.php";
if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

// Tangkap parameter ?page, default ke 'daily'
$page = isset($_GET['page']) ? $_GET['page'] : 'daily';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="../assets/images/avatar/nut.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
  <link rel="stylesheet" href="../assets/css/chat.css">
  <link rel="stylesheet" href="../assets/css/leaderboard.css">
  <title>Dashboard - Nutrimate</title><style>
  /* Safe box-sizing */
  *, *::before, *::after { box-sizing: border-box; }

  /* Default font */
  body { font-family: "Poppins", sans-serif; }

  /* Sidebar behavior */
  #sidebar {
    z-index: 1030;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* Nav links */
  #sidebar .nav-link {
    color: #fff;
    border-radius: 12px;
    padding: 10px 12px;
    transition: all 0.2s ease-in-out;
  }

  /* Hover */
  #sidebar .nav-link:hover {
    background-color: rgba(255,255,255,0.1);
  }

  /* Active custom */
#sidebar .nav-link.active {
  background-color: transparent !important;
  outline: 2px solid #1cb0f6;   /* tidak mengubah layout */
  outline-offset: -2px;
  color: #1cb0f6 !important;
}


  /* Main content responsive */
  #mainContent {
    margin-left: 0;
    width: 100%;
  }

  @media (min-width: 768px) {
    #sidebar { width: 25%; }
    #mainContent {
      margin-left: 25%;
      width: calc(100% - 25%);
    }
  }

  @media (min-width: 992px) {
    #sidebar { width: 16.6667%; }
    #mainContent {
      margin-left: 16.6667%;
      width: calc(100% - 16.6667%);
    }
  }

  html, body { overflow-x: hidden; }
</style>

</head>
<body class="bg-light">

<div class="container-fluid">
  <div class="row flex-nowrap">
    <!-- SIDEBAR -->
    <nav id="sidebar" 
         class="d-none d-md-block bg-dark text-white shadow-lg position-fixed top-0 start-0 h-100 p-0">
      
      <!-- Logo -->
      <div class="d-flex align-items-center justify-content-center mt-4 mb-4 pb-3 border-bottom">
        <img src="../assets/images/avatar/nut.png" alt="logo" width="50" 
             class="me-2 rounded-circle border border-2 border-light">
        <h4 class="m-0 fw-bold">
          <span class="text-warning">NUT</span>RIMATE
        </h4>
      </div>

      <!-- Menu -->
      <ul class="nav nav-pills flex-column mb-auto px-3">
        <li class="nav-item mb-2 ms-1">
          <a href="?page=daily" 
             class="nav-link d-flex align-items-center gap-2 fw-semibold
                    <?= $page === 'daily' ? 'active' : '' ?>">
            <img src="../assets/images/dashboard/daily.png" width="40" alt="Aktivitas">
            <span>Aktivitas</span>
          </a>
        </li>
        <li class="nav-item mb-2 ms-1">
          <a href="?page=chat" 
             class="nav-link d-flex align-items-center gap-2 fw-semibold
                    <?= $page === 'chat' ? 'active' : '' ?>">
            <img src="../assets/images/dashboard/chatBot.png" width="40" alt="Chat">
            <span>ChatBot</span>
          </a>
        </li>
        <li class="nav-item mb-2 ms-1">
          <a href="?page=nutrition" 
             class="nav-link d-flex align-items-center gap-2 fw-semibold
                    <?= $page === 'nutrition' ? 'active' : '' ?>">
            <img src="../assets/images/dashboard/nutrition.png" width="38" alt="Nutrisi">
            <span>Nutrisi</span>
          </a>
        </li>
        <li class="nav-item mb-2 ms-1">
          <a href="?page=rank" 
             class="nav-link d-flex align-items-center gap-2 fw-semibold
                    <?= $page === 'rank' ? 'active' : '' ?>">
            <img src="../assets/images/dashboard/rank.png" width="40" alt="Skor">
            <span>Skor</span>
          </a>
        </li>
        <li class="nav-item mb-2 ms-1">
          <a href="?page=profil" 
             class="nav-link d-flex align-items-center gap-2 fw-semibold
                    <?= $page === 'profil' ? 'active' : '' ?>">
            <img src="../assets/images/dashboard/profile.png" width="40" alt="Profil">
            <span>Profil</span>
          </a>
        </li>
      </ul>
    </nav>




    <!-- KONTEN -->
    <main id="mainContent" class="px-3 py-4">
      <?php
        switch ($page) {
          case 'daily': include 'daily.php'; break;
          case 'chat': include 'chatbot.php'; break;
          case 'nutrition': include 'nutrition.php'; break;
          case 'rank': include 'leaderboard.php'; break;
          case 'profil': include 'profile.php'; break;
          default: echo '<p>Halaman tidak ditemukan</p>'; break;
        }
      ?>
    </main>
  </div>
</div>

<!-- NAVBAR BAWAH (Mobile only) -->
<nav id="bottomNav" class="d-md-none bg-white border-top fixed-bottom">
  <div class="d-flex justify-content-around align-items-center py-2">
    <a href="?page=daily" class="nav-link text-center flex-fill <?= $page === 'daily' ? 'text-primary fw-bold' : 'text-secondary' ?>">
      <img src="../assets/images/dashboard/daily.png" width="24" alt="">
      <div style="font-size: 12px;">Aktivitas</div>
    </a>
    <a href="?page=chat" class="nav-link text-center flex-fill <?= $page === 'chat' ? 'text-primary fw-bold' : 'text-secondary' ?>">
      <img src="../assets/images/dashboard/chatBot.png" width="24" alt="">
      <div style="font-size: 12px;">Tanya Nut</div>
    </a>
    <a href="?page=nutrition" class="nav-link text-center flex-fill <?= $page === 'nutrition' ? 'text-primary fw-bold' : 'text-secondary' ?>">
      <img src="../assets/images/dashboard/nutrition.png" width="24" alt="">
      <div style="font-size: 12px;">Nutrisi</div>
    </a>
    <a href="?page=rank" class="nav-link text-center flex-fill <?= $page === 'rank' ? 'text-primary fw-bold' : 'text-secondary' ?>">
      <img src="../assets/images/dashboard/rank.png" width="24" alt="">
      <div style="font-size: 12px;">Skor</div>
    </a>
    <a href="?page=profil" class="text-center flex-fill <?= $page === 'profil' ? 'text-primary fw-bold' : 'text-secondary' ?>">
      <img src="../assets/images/dashboard/profile.png" width="24" alt="">
      <div style="font-size: 12px;">Profil</div>
    </a>
  </div>
</nav>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Loader animasi
  const loadingText = document.getElementById("loading-text");
  let dots = 0;
  setInterval(() => {
    dots = (dots + 1) % 4;
    loadingText.textContent = "Loading" + ".".repeat(dots);
  }, 500);

  window.addEventListener("load", function () {
    document.getElementById("loader-wrapper").style.display = "none";
  });
</script>

<?php if (isset($_SESSION['alert'])): ?>
<script>
  Swal.fire({
    icon: '<?= $_SESSION['alert'] === 'success' ? 'success' : 'error' ?>',
    title: '<?= $_SESSION['alert'] === 'success' ? 'Berhasil!' : 'Gagal!' ?>',
    text: '<?= $_SESSION['alert'] === 'success' ? 'Profilmu telah diperbarui dengan manis!' : 'Terjadi kesalahan saat memperbarui profilmu.' ?>',
    background: '<?= $_SESSION['alert'] === 'success' ? '#a7ddfa' : '#fddede' ?>',
    iconColor: '<?= $_SESSION['alert'] === 'success' ? '#03a5fc' : '#e74c3c' ?>',
    confirmButtonColor: '<?= $_SESSION['alert'] === 'success' ? '#03a5fc' : '#e74c3c' ?>'
  });
</script>
<?php unset($_SESSION['alert']); endif; ?>

</body>
<?php ob_end_flush(); ?>
</html>
