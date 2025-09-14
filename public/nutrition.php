<?php
require "../config/db.php";
require "../config/fatsecret_api.php";

// ================= CEK LOGIN =================
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

// ================= AMBIL DATA USER =================
$query_user = $conn->prepare("SELECT weight, height, usia, jenis_kelamin, bmi FROM users WHERE id = ?");
$query_user->bind_param("i", $user_id);
$query_user->execute();
$result_user = $query_user->get_result();
$user = $result_user->fetch_assoc();
if (!$user) die("Data user tidak ditemukan!");

// Data profil user
$berat  = $user['weight'];   // kg
$tinggi = $user['height'];   // cm
$usia   = $user['usia'];     // tahun
$gender = $user['jenis_kelamin']; // Laki-Laki / Perempuan
$bmi    = $user['bmi'];      // kurus, normal, gemuk, obesitas

// ================= HITUNG BMR =================
// Rumus Mifflin-St Jeor
$BMR = ($gender == "Laki-Laki") 
    ? (10 * $berat + 6.25 * $tinggi - 5 * $usia + 5)
    : (10 * $berat + 6.25 * $tinggi - 5 * $usia - 161);

// ================= HITUNG KALORI AKTIVITAS HARIAN =================
// Kalori olahraga per tanggal dari tabel user_activities
$query_aktivitas = $conn->prepare("SELECT SUM(calories_burned) AS total FROM user_activities WHERE user_id = ? AND activity_date = ?");
$query_aktivitas->bind_param("is", $user_id, $tanggal);
$query_aktivitas->execute();
$result_aktivitas = $query_aktivitas->get_result();
$total_kalori_aktivitas = $result_aktivitas->fetch_assoc()['total'] ?? 0;

// ================= REKOMENDASI PROTEIN BERDASARKAN GENDER & BMI =================
$protein_per_kg = 1.6; // default
$pesan = "Data rekomendasi protein belum tersedia.";
$alertClass = "alert-info"; // default warna biru

if ($gender == 'laki-laki') {
    switch ($user['bmi']) {
        case 'kurus':
            $protein_per_kg = 2.0;
            $pesan = "Kamu tergolong underweight. Tingkatkan asupan protein untuk menaikkan massa otot.";
            $alertClass = "alert-warning";
            break;
        case 'normal':
            $protein_per_kg = 1.4;
            $pesan = "Berat badanmu normal. Pertahankan pola makan seimbang dengan protein cukup.";
            $alertClass = "alert-success";
            break;
        case 'gemuk':
            $protein_per_kg = 1.2;
            $pesan = "Kamu overweight. Kurangi kalori berlebih, tapi tetap cukupkan protein.";
            $alertClass = "alert-warning";
            break;
        case 'obesitas':
            $protein_per_kg = 1.2;
            $pesan = "Kamu obesitas. Disarankan diet rendah kalori dengan protein cukup.";
            $alertClass = "alert-danger";
            break;
    }
} elseif ($gender == 'perempuan') {
    switch ($user['bmi']) {
        case 'kurus':
            $protein_per_kg = 1.8;
            $pesan = "Kamu tergolong underweight. Tingkatkan asupan protein untuk menaikkan massa otot.";
            $alertClass = "alert-warning";
            break;
        case 'normal':
            $protein_per_kg = 1.6;
            $pesan = "Berat badanmu normal. Pertahankan pola makan seimbang dengan protein cukup.";
            $alertClass = "alert-success";
            break;
        case 'gemuk':
            $protein_per_kg = 1.0;
            $pesan = "Kamu overweight. Kurangi kalori berlebih, tapi tetap cukupkan protein.";
            $alertClass = "alert-warning";
            break;
        case 'obesitas':
            $protein_per_kg = 1.0;
            $pesan = "Kamu obesitas. Disarankan diet rendah kalori dengan protein cukup.";
            $alertClass = "alert-danger";
            break;
    }
}

$rekomendasi_protein = round($berat * $protein_per_kg); // gram protein per hari


// ================= SIMPAN MAKANAN (POST) =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'], $_POST['kalori'])) {
    $nama    = $_POST['nama'];
    $desc    = $_POST['kalori'];

    // Ambil angka kalori, protein, lemak, karbo dari deskripsi FatSecret
    preg_match('/Calories:\s*([\d]+)/', $desc, $m1);
    preg_match('/Protein:\s*([\d\.]+)/', $desc, $m2);
    preg_match('/Fat:\s*([\d\.]+)/', $desc, $m3);
    preg_match('/Carbs:\s*([\d\.]+)/', $desc, $m4);

    $kalori  = $m1[1] ?? 0;
    $protein = $m2[1] ?? 0;
    $lemak   = $m3[1] ?? 0;
    $karbo   = $m4[1] ?? 0;

    // Simpan ke DB
    $stmt = $conn->prepare("INSERT INTO food_log (user_id, tanggal, makanan, kalori, protein, karbo, lemak) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdddd", $user_id, $tanggal, $nama, $kalori, $protein, $karbo, $lemak);
    $stmt->execute();

    header("Location: dashboard.php?page=nutrition&tanggal=$tanggal");
    exit;
}

// ================= HAPUS DATA HARI KEMARIN =================
$kemarin = date('Y-m-d', strtotime('-1 day', strtotime($tanggal)));
$stmt_del = $conn->prepare("DELETE FROM food_log WHERE user_id = ? AND tanggal = ?");
$stmt_del->bind_param("is", $user_id, $kemarin);
$stmt_del->execute();




// ================= TOTAL KALORI MAKANAN =================
$query_makanan = $conn->prepare("SELECT SUM(kalori) AS total FROM food_log WHERE user_id = ? AND tanggal = ?");
$query_makanan->bind_param("is", $user_id, $tanggal);
$query_makanan->execute();
$result_makanan = $query_makanan->get_result();
$total_kalori_makanan = $result_makanan->fetch_assoc()['total'] ?? 0;

// ================= HITUNG TDEE & BALANCE =================
$TDEE = $BMR + $total_kalori_aktivitas; // kebutuhan kalori hari itu
$kalori_balance = $total_kalori_makanan - $TDEE;

// Status kalori harian
if ($kalori_balance > 0) $status = "Surplus";
elseif ($kalori_balance < 0) $status = "Defisit";
else $status = "Seimbang";

// ================= FATSECRET SEARCH =================
$access_token = getAccessToken($client_id, $client_secret);
$foods = [];
if (isset($_GET['q'])) {
    $foods = searchFood($access_token, $_GET['q']);
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="container-fluid mb-4">
<h2 class="mb-2 text-center fw-bold"> Nutrisi Harian</h2>
<p class="text-center text-muted mb-3">
    Lacak asupan makanan, kalori, protein, karbohidrat, dan lemak harianmu. 
    Dapatkan rekomendasi nutrisi sesuai kebutuhan tubuh untuk menjaga pola hidup sehat.
</p>

<!-- Rekomendasi Asupan -->
<div class="card border-0 shadow-lg rounded-4 mt-4 mb-4">

</div>

    <!-- FORM PENCARIAN -->
<div class="my-4 d-flex justify-content-center">
    <form method="GET" action="dashboard.php" 
          class="d-flex w-100" 
          style="max-width: 500px;">
        <input type="hidden" name="page" value="nutrition">

        <input type="text" 
               class="form-control form-control-sm rounded-start-3" 
               name="q" 
               placeholder="masukkan nama makanan (contoh: nasi goreng)" 
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

        <button type="submit" class="btn btn-primary btn-sm rounded-end-3">
            <i class="bi bi-search"></i> Cari
        </button>
    </form>
</div>



    <!-- HASIL PENCARIAN -->
    <?php if (isset($foods['foods']['food'])): ?>
        <div class="card mb-4 border-0 shadow-lg rounded-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-list-ul"></i> Hasil Pencarian</h5>
                <div class="list-group list-group-flush">
                    <?php 
                    $results = $foods['foods']['food'];
                    if (isset($results['food_id'])) $results = [$results];
                    foreach ($results as $food): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <b><?= htmlspecialchars($food['food_name']) ?></b><br>
                                <small class="text-muted"><?= htmlspecialchars($food['food_description']) ?></small>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="nama" value="<?= htmlspecialchars($food['food_name']) ?>">
                                <input type="hidden" name="kalori" value="<?= htmlspecialchars($food['food_description']) ?>">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-plus-circle"></i> Tambah
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- LOG MAKANAN -->
    <div class="card mb-4 border-0 shadow-lg rounded-4">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-journal-text"></i> Log Makanan (<?= $tanggal ?>)</h5>
                <ul class="list-group list-group-flush">
                    <?php
                    $result = $conn->query("SELECT makanan, kalori, protein, karbo, lemak 
                                            FROM food_log 
                                            WHERE user_id = $user_id AND tanggal = '$tanggal'");
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<li class='list-group-item'>
                                    <div class='d-flex justify-content-between'>
                                        <span class='text-dark fw-bold'>{$row['makanan']}</span>
                                        <span class='badge bg-primary'>{$row['kalori']} kcal</span>
                                    </div>
                                    <small class='text-muted'>
                                        Protein: {$row['protein']}g | Karbo: {$row['karbo']}g | Lemak: {$row['lemak']}g
                                    </small>
                                </li>";
                        }
                    } else {
                        echo "<li class='list-group-item text-muted'>Belum ada makanan dicatat.</li>";
                    }
                    ?>
                </ul>

        </div>
    </div>

    <!-- RINGKASAN KALORI -->
    <!-- RINGKASAN KALORI -->
<div class="card border-0 shadow-lg rounded-4">
    <div class="card-body">
<h5 class="card-title"><i class="bi bi-bar-chart-line"></i> Ringkasan Kalori</h5>
        <div class="mt-3">
            <?php
            if ($kalori_balance < 0) {
                echo "
                <div class='alert alert-info alert-dismissible fade show' role='alert'>
                    ⚡ Kalori kurang, tambah asupan sehat untuk energi optimal.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
            } elseif ($kalori_balance > 0) {
                echo "
                <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                    ⚠️ Kalori lebih, pertimbangkan aktivitas tambahan.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
            } else {
                echo "
                <div class='alert alert-success alert-dismissible fade show' role='alert'>
                    ✅ Kalori seimbang. Pertahankan pola makanmu!
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
            }
            ?>
        </div>

        <!-- Chart Container -->
        <div class="row">
            <div class="col-md-6">
                <canvas id="donutKalori"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="barKalori"></canvas>
            </div>
        </div>
<div class="row g-3">
  <!-- Ringkasan Kalori -->
  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title mb-3"><i class="bi bi-bar-chart-line"></i> Ringkasan Kalori</h5>

        <div class="d-flex flex-column gap-2">
          <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded"
               data-bs-toggle="tooltip" title="Jumlah kalori minimum yang dibutuhkan tubuh untuk fungsi vital saat istirahat total.">
            <span><i class="bi bi-clipboard-pulse"></i> BMR</span>
            <b><?= round($BMR) ?> kcal</b>
          </div>

          <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded"
               data-bs-toggle="tooltip" title="Kalori tambahan yang dibakar karena aktivitas fisik sehari-hari.">
            <span><i class="bi bi-person-arms-up"></i> Aktivitas</span>
            <b><?= round($total_kalori_aktivitas) ?> kcal</b>
          </div>

          <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded"
               data-bs-toggle="tooltip" title="Total kebutuhan energi harian, gabungan dari BMR dan aktivitas.">
            <span>⚡ TDEE</span>
            <b><?= round($TDEE) ?> kcal</b>
          </div>

          <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded"
               data-bs-toggle="tooltip" title="Jumlah kalori yang masuk dari makanan.">
            <span><i class="bi bi-fork-knife"></i> Makanan</span>
            <b><?= round($total_kalori_makanan) ?> kcal</b>
          </div>

          <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded"
               data-bs-toggle="tooltip" title="Selisih antara kalori yang masuk (makanan) dan yang dibutuhkan (TDEE).">
            <span>⚖️ Balance</span>
            <div>
              <b><?= round($kalori_balance) ?> kcal</b> 
              <span class="ms-2 text-<?= $status=='Surplus'?'danger':($status=='Defisit'?'primary':'success') ?>">
                (<?= $status ?>)
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Rekomendasi Protein -->
  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title mb-3"><i class="bi bi-heart-pulse"></i> Rekomendasi Asupan Protein</h5>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show mt-3" role="alert">
            <?= $pesan ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="d-flex flex-column gap-2">
          <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
            <span>Berat badan</span>
            <b><?= $berat ?> kg</b>
          </div>

          <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
            <span>Protein per kg</span>
            <b><?= $protein_per_kg ?> g/kg</b>
          </div>

          <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
            <span>Kebutuhan protein harian</span>
            <b><?= $rekomendasi_protein ?> g</b>
          </div>

          <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
            <span>Alternatif (30% dari kalori)</span>
            <b><?= round(($TDEE * 0.30) / 4) ?> g</b>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

    </div>
    </div>
</div>
            
</div>
<!-- Script Chart.js -->
<script>
    // Data PHP ke JS
    const TDEE   = <?= round($TDEE) ?>;
    const makanan = <?= round($total_kalori_makanan) ?>;
    const aktivitas = <?= round($total_kalori_aktivitas) ?>;
    const BMR = <?= round($BMR) ?>;
    const balance = <?= round($kalori_balance) ?>;

    // Donut Chart - Perbandingan Makanan vs TDEE
    const ctxDonut = document.getElementById('donutKalori').getContext('2d');
    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: ['Asupan Makanan', 'Sisa Kebutuhan'],
            datasets: [{
                data: [makanan, Math.max(TDEE - makanan, 0)],
                backgroundColor: ['#0d6efd', '#dee2e6'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Distribusi Kalori'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Bar Chart - Detail Energi
    const ctxBar = document.getElementById('barKalori').getContext('2d');
    new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: ['BMR', 'Aktivitas', 'TDEE', 'Makanan', 'Balance'],
        datasets: [{
            label: 'Kalori (kcal)',
            data: [BMR, aktivitas, TDEE, makanan, balance],
            backgroundColor: [
                '#db1488ff',
                '#0dcaf0',
                '#ffc107',
                '#0d6efd',
                balance >= 0 ? '#dc3545' : '#28e28bff'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Detail Perhitungan Kalori'
            },
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        let value = context.raw;
                        let desc = "";

                        switch (context.label) {
                            case "BMR":
                                desc = "Jumlah kalori minimum yang dibutuhkan tubuh untuk fungsi vital saat istirahat.";
                                break;
                            case "Aktivitas":
                                desc = "Kalori tambahan yang dibakar karena aktivitas fisik harian.";
                                break;
                            case "TDEE":
                                desc = "Total kebutuhan kalori harian (BMR + aktivitas).";
                                break;
                            case "Makanan":
                                desc = "Jumlah kalori yang masuk dari makanan yang dikonsumsi.";
                                break;
                            case "Balance":
                                desc = "Selisih antara kalori masuk dan kalori keluar.";
                                break;
                        }

                        return `${context.label}: ${value} kcal\n${desc}`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })
})
</script>