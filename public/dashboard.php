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
  <title>Dashboard - Nutrimate</title>
  <style>
  /* Safe box-sizing */
  *, *::before, *::after { box-sizing: border-box; }

  /* Default font */
  body { font-family: "Poppins", sans-serif; }


#sidebar .nav {
  padding-left: 0;
  margin: 0;
  gap: 0 !important; /* hilangkan gap */
}

#sidebar .nav-item {
  margin: 0;
}

#sidebar .nav-link {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  color: #fff;
  border-radius: 14px;
  padding: 10px 14px; /* agak rapat tapi masih nyaman */
  transition: all 0.25s ease;
}

/* Hover */
#sidebar .nav-link:hover {
  color: #ffc107 !important;
}

/* Active ala Duolingo */
#sidebar .nav-link.active {
  background-color: rgba(255, 193, 7, 0.15) !important; /* warning light */
  outline: 2px solid #ffc107;
  outline-offset: -2px;
  color: #ffc107 !important;
  font-weight: 700;
}

#sidebar .nav-item {
  margin-bottom: 0 !important;
}

/* Bottom nav modern */
#bottomNav {
  box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.1);
}

#bottomNav .nav-link {
  font-size: 12px;
  color: #6c757d;
  transition: color 0.2s ease;
}

#bottomNav .nav-link img {
  display: block;
  margin: 0 auto 2px;
}

#bottomNav .nav-link.text-primary,
#bottomNav .nav-link.fw-bold {
  color: #ffc107 !important; /* warning */
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

<div class="container-fluid ">
  <div class="row flex-nowrap">
    <!-- SIDEBAR -->
    <nav id="sidebar" 
         class="d-none d-md-block bg-dark text-white shadow-lg position-fixed top-0 start-0 h-100 px-2">
      
      <!-- Logo -->
      <div class="d-flex align-items-center justify-content-center mt-4 mb-4 pb-3 border-bottom">
        <img src="../assets/images/avatar/nut.png" alt="logo" width="50" 
             class="me-2 rounded-circle border border-3 border-light">
        <h4 class="m-0 fw-bold">
          <span class="text-warning">NUT</span>RIMATE
        </h4>
      </div>

      <!-- Menu -->
      <ul class="nav nav-pills flex-column mb-auto">
  <li class="nav-item">
    <a href="?page=daily" 
       class="nav-link 
              <?= $page === 'daily' ? 'active' : '' ?> d-flex align-items-center gap-3 fw-semibold py-2 px-2">
      <img src="../assets/images/dashboard/daily.png" width="32" alt="Aktivitas">
      <span>Aktivitas</span>
    </a>
  </li>
  <li class="nav-item">
    <a href="?page=chat" 
       class="nav-link d-flex align-items-center gap-3 fw-semibold py-2 px-2
              <?= $page === 'chat' ? 'active' : '' ?>">
      <img src="../assets/images/dashboard/chatBot.png" width="32" alt="Chat">
      Chat Nut
    </a>
  </li>
  <li class="nav-item">
    <a href="?page=nutrition" 
       class="nav-link d-flex align-items-center gap-3 fw-semibold py-2 px-2
              <?= $page === 'nutrition' ? 'active' : '' ?>">
      <img src="../assets/images/dashboard/nutrition.png" width="32" alt="Nutrisi">
      Nutrisi
    </a>
  </li>
  <li class="nav-item">
    <a href="?page=misi" 
       class="nav-link d-flex align-items-center gap-3 fw-semibold py-2 px-2
              <?= $page === 'misi' ? 'active' : '' ?>">
      <img src="../assets/images/dashboard/misi.png" width="32" alt="misi">
      Misi
    </a>
  </li>
  <li class="nav-item">
    <a href="?page=rank" 
       class="nav-link d-flex align-items-center gap-3 fw-semibold py-2 px-2
              <?= $page === 'rank' ? 'active' : '' ?>">
      <img src="../assets/images/dashboard/rank.png" width="32" alt="Skor">
      Papan Skor
    </a>
  </li>
  <li class="nav-item">
    <a href="?page=profil" 
       class="nav-link d-flex align-items-center gap-3 fw-semibold py-2 px-2
              <?= $page === 'profil' ? 'active' : '' ?>">
      <img src="../assets/images/dashboard/profile.png" width="32" alt="Profil">
      Profil
    </a>
  </li>
  <li class="nav-item">
    <a href="?page=lainnya" 
       class="nav-link d-flex align-items-center gap-3 fw-semibold py-2 px-2
              <?= $page === 'lainnya' ? 'active' : '' ?>">
      <img src="../assets/images/dashboard/lainnya.png" width="32" alt="lainnya">
      Lainnya
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
          case 'misi': include 'misi.php'; break;
          case 'rank': include 'leaderboard.php'; break;
          case 'profil': include 'profile.php'; break;
          case 'lainnya': include 'lainnya.php'; break;
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
    icon: '<?= $_SESSION['alert']['type'] === 'success' ? 'success' : 'error' ?>',
    title: '<?= $_SESSION['alert']['type'] === 'success' ? 'Berhasil!' : 'Gagal!' ?>',
    text: '<?= $_SESSION['alert']['message'] ?>',
    background: '<?= $_SESSION['alert']['type'] === 'success' ? '#a7ddfa' : '#fddede' ?>',
    iconColor: '<?= $_SESSION['alert']['type'] === 'success' ? '#03a5fc' : '#e74c3c' ?>',
    confirmButtonColor: '<?= $_SESSION['alert']['type'] === 'success' ? '#03a5fc' : '#e74c3c' ?>'
  });
</script>
<?php unset($_SESSION['alert']); endif; ?>


</body>
<?php ob_end_flush(); ?>
</html>
