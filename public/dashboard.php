<?php
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="../assets/images/avatar/nut.png" type="image/x-icon" width="60px">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
  <link rel="stylesheet" href="../assets/css/chat.css">
  <title>Dashboard - Nutrimate</title>
</head>
<body>
   
<!-- LOADING -->
  <div id="loader-wrapper">
    <img src="../assets/images/videos/nut.gif" alt="Loading...">
        <div id="loading-text">Loading</div>
  </div>

<div class="d-flex h-100 w-100">
  <!-- SIDEBAR -->
 <div id="sidebar" class="sidebar-menu position-fixed h-100" style="background-color: #3498DB;">
    <div class="title d-flex justify-content-center align-items-center m-3 pe-3 border-bottom pb-3">
      <img class="img-title" src="../assets/images/avatar/nut.png" alt="" width="60px" >
      <h4 class="text-white text-center fw-bold ms-2">
        <span style="color: yellow;">N</span>UTRI<span style="color: rgb(235, 41, 102);">M</span>ATE
      </h4>
    </div>
    <!-- tombol toggle -->
    <div class="text-center mb-3">
      <button id="toggleSidebar" class="btn btn-sm btn-warning text-white fw-bold">☰</button>
    </div>
      <ul class="list-unstyled m-2">
        <li class="<?= $page === 'daily' ? 'active' : '' ?> pt-2 pb-2">
          <a href="?page=daily" class="d-flex align-items-center gap-2 w-100">
            <img src="../assets/images/dashboard/daily.png" alt="" width="40px">
            <span>Aktivitas</span>
          </a>
        </li>

        <li class="<?= $page === 'chat' ? 'active' : '' ?> pt-2 pb-2">
          <a href="?page=chat" class="d-flex align-items-center gap-2 w-100">
            <img src="../assets/images/dashboard/chatBot.png" alt="" width="40px">
            <span>Tanya Nut</span>
          </a>
        </li>

        <li class="<?= $page === 'nutrition' ? 'active' : '' ?> pt-2 pb-2">
          <a href="?page=nutrition" class="d-flex align-items-center gap-2 w-100">
            <img src="../assets/images/dashboard/nutrition.png" alt="" width="40px">
            <span>Nutrisi</span>
          </a>
        </li>

        <li class="<?= $page === 'rank' ? 'active' : '' ?> pt-2 pb-2">
          <a href="?page=rank" class="d-flex align-items-center gap-2 w-100">
            <img src="../assets/images/dashboard/rank.png" alt="" width="40px">
            <span>Papan Skor</span>
          </a>
        </li>

        <li class="<?= $page === 'profil' ? 'active' : '' ?> pt-2 pb-2">
          <a href="?page=profil" class="d-flex align-items-center gap-2 w-100">
            <img src="../assets/images/dashboard/profile.png" alt="" width="40px">
            <span>Profil</span>
          </a>
        </li>
      </ul>

  </div>


  <!-- KONTEN UTAMA -->
  <div id="mainContent" class="content">
    <?php
      switch ($page) {
        case 'daily':
          include 'daily.php';
          break;


        case 'chat':
          include 'chatbot.php';
          break;

        case 'nutrition':
          include 'nutrition.php';
          break;

        case 'rank':
          include 'leaderboard.php';
          break;

        case 'profil':
          include 'profile.php';
          break;

        default:
          echo '<p>Halaman tidak ditemukan</p>';
          break;
      }
    ?>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
const sidebar = document.getElementById("sidebar");
const content = document.getElementById("mainContent");
const toggleBtn = document.getElementById("toggleSidebar");

// saat tombol diklik
toggleBtn.addEventListener("click", function () {
  sidebar.classList.toggle("sidebar-collapsed");
  content.classList.toggle("expanded");

  // simpan state ke localStorage
  if (sidebar.classList.contains("sidebar-collapsed")) {
    localStorage.setItem("sidebarState", "collapsed");
  } else {
    localStorage.setItem("sidebarState", "expanded");
  }
});

// saat halaman di-load kembali
window.addEventListener("DOMContentLoaded", function () {
  const state = localStorage.getItem("sidebarState");
  if (state === "collapsed") {
    sidebar.classList.add("sidebar-collapsed");
    content.classList.add("expanded");
  }
});
</script>

<?php if (isset($_SESSION['alert'])): ?>
  <script>
    <?php if ($_SESSION['alert'] === 'success'): ?>
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Profilmu telah diperbarui dengan manis!',
        background: '#a7ddfa',
        iconColor: '#03a5fc',
        confirmButtonColor: '#03a5fc'
      });
    <?php elseif ($_SESSION['alert'] === 'error'): ?>
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat memperbarui profilmu.',
        background: '#fddede',
        iconColor: '#e74c3c',
        confirmButtonColor: '#e74c3c'
      });
    <?php endif; ?>
  </script>
<?php unset($_SESSION['alert']); endif; ?>


  <script>
    // Animasi titik-titik berjalan
    const loadingText = document.getElementById("loading-text");
    let dots = 0;

    setInterval(() => {
      dots = (dots + 1) % 4; // 0,1,2,3
      loadingText.textContent = "Loading" + ".".repeat(dots);
    }, 500);

    // Hilangkan loader setelah halaman selesai
    window.addEventListener("load", function () {
      const loader = document.getElementById("loader-wrapper");
      loader.style.display = "none";
    });
</script>
</body>
</html>
