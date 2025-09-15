<?php
include '../config/db.php'; // koneksi database
// session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "User belum login.";
    exit;
}

// 🔹 Ambil data user + ranking
$sql_user = "
    SELECT 
        u.id, 
        u.name, 
        u.email,
        u.exp,
        u.height,
        u.weight,
        u.avatar,
        u.bmi,
        u.jenis_kelamin,
        u.streak,
        u.created_at,
        (SELECT COUNT(*) + 1 FROM users WHERE exp > u.exp) AS user_rank
    FROM users u
    WHERE u.id = ?
";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    echo "User tidak ditemukan.";
    exit;
}

$user_rank = (int)($user['user_rank'] ?? 0);

// 🔹 Hitung BMI manual (kalau height & weight ada)
$height = $user['height'];
$weight = $user['weight'];
$avatar_user = htmlspecialchars($user['avatar']);
$bmi_user = $user['bmi']; // kategori dari DB

$bmi_val = null;
$bmi_category = null;
if (!empty($height) && !empty($weight) && $height > 0) {
    $height_m = $height / 100;
    $bmi_val = $weight / ($height_m * $height_m);
}

// 🔹 Ambil aktivitas terakhir (5 aktivitas terbaru)
$sql_activities = "
    SELECT ua.activity_date, ua.duration_minutes, ua.calories_burned, ua.exp_earned, a.name as activity_name
    FROM user_activities ua
    JOIN activities a ON ua.activity_id = a.id
    WHERE ua.user_id = ?
    ORDER BY ua.activity_date DESC, ua.created_at DESC
    LIMIT 5
";
$stmt2 = $conn->prepare($sql_activities);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result_activities = $stmt2->get_result();
?>
<style>
    .dashboard-section {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .statistik, .character, .datadiri {
        flex: 1 1 30%;
        min-width: 280px;
    }

    .statistik .card, .datadiri .card, .character .card {
        border-radius: 15px;
        transition: 0.3s;
    }

    .statistik .card:hover, .datadiri .card:hover, .character .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }

    .statistik h5, .datadiri h5 {
        border-bottom: 2px solid #0dcaf0;
        padding-bottom: 5px;
    }

    .form-label {
        font-weight: 600;
        font-size: 14px;
    }

    .form-control {
        border-radius: 10px;
    }

    /* grid 2 kolom untuk statistik */
    .statistik .statistik-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    .avatar-option {
  transition: transform 0.2s ease;
}
.avatar-option:hover {
  transform: scale(1.1);
}



</style>
<body class="bg-light">
    <div class="card shadow-sm mb-4 bg-info">
    <div class="card-body text-center">
        <div class="position-relative d-inline-block mb-3">
            <img src="../assets/images/avatar/<?= $user['avatar'] ?>.png" class=" border rounded-circle border-info" alt="Profile Picture" width="100px">
            <button class="btn btn-sm btn-info position-absolute bottom-0 end-0 rounded-circle shadow">
            <i class="bi bi-pencil-fill text-white" id="edit-foto"></i> 
            </button>           
        </div>
        <p class="card-text fs-5">Halo, <strong><?= htmlspecialchars($user['name']) ?></strong></p>
        <p class="card-text text-muted fst-italic" style="font-size: 14px;">Terdaftar sejak: <strong><?= date("d M Y", strtotime($user['created_at'])) ?></strong></p>
        <button type="button" class="btn btn-sm rounded-pill px-4 fw-medium" data-bs-toggle="modal" data-bs-target="#updateProfileModal" style="background-color: white; color: #3498DB;">
        Update Profil
    </div>
    </div>
<div class="dashboard-section">
    <!-- Statistik -->
        <div class="statistik">
        <h5 class="fw-bold mb-3 text-info">
            <i class="bi bi-graph-up fw-bold"></i> Statistik
        </h5>

        <div class="row g-3">
            <!-- Card 1 -->
            <div class="col-6 col-lg-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <img src="../assets/images/dashboard/redFire.png" alt="Icon" width="45px">
                        <div>
                            <div class="h5 fw-bold" style="color: rgb(235, 41, 102);"><?= $user['streak'] ?></div>
                            <div class="text-muted small">Runtunan hari</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-6 col-lg-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <img src="../assets/images/dashboard/gold.png" alt="Icon" width="45px">
                        <div>
                            <div class="h5 text-warning fw-bold"><?= $user_rank ?></div>
                            <div class="text-muted small">Posisi <?= $user_rank ?> Besar</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-6 col-lg-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <img src="../assets/images/dashboard/exp.png" alt="Icon" width="45px">
                        <div>
                            <div class="h5 text-info fw-bold"><?= $user['exp'] ?></div>
                            <div class="text-muted small">Total XP</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="col-6 col-lg-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <img src="../assets/images/dashboard/barbel.png" alt="Icon" width="45px">
                        <div>
                            <div class="h5 text-danger fw-bold"><?= $user['bmi'] ?></div>
                            <div class="text-muted small">Kesehatan badan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>





    <!-- Character GIF -->
    <div class="character">
        <h5 class="fw-bold mb-3 text-info"><i class="bi bi-person-bounding-box"></i> Avatar</h5>
        <div class="card shadow-sm">
            <div class="ratio" style="--bs-aspect-ratio: 133.33%;">
                <img src="../assets/images/videos/<?= $avatar_user.$bmi_user ?>.gif" 
                     alt="Animasi GIF" 
                     class="w-100 h-100 object-fit-cover rounded">
            </div>
        </div>
    </div>

    <!-- Data Diri -->
    <div class="datadiri">
    <h5 class="fw-bold mb-3 text-info"><i class="bi bi-person-lines-fill"></i> Data Kesehatan</h5>
    <div class="card shadow-sm">
        <div class="card-body">
            <form>
                <input type="hidden" name="id_pengguna" value="<?= $user_id ?>">

                <div class="mb-2">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" id="namaField"
                           value="<?= htmlspecialchars($user['name']) ?>" readonly>
                </div>

                <div class="mb-2">
                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                    <input type="text" name="jenis_kelamin" class="form-control" id="genderField"
                           value="<?= $user['jenis_kelamin'] ?>" readonly>
                </div>

                <div class="mb-2">
                    <label for="tinggi_badan" class="form-label">Tinggi Badan</label>
                    <input type="text" name="tinggi_badan" class="form-control" id="tinggiField"
                           value="<?= $height ?>" readonly>
                </div>

                <div class="mb-2">
                    <label for="berat_badan" class="form-label">Berat Badan</label>
                    <input type="text" name="berat_badan" class="form-control" id="beratField"
                           value="<?= $weight ?>" readonly>
                </div>

                <div class="mb-2">
                    <label for="bmi" class="form-label">BMI</label>
                    <input type="text" name="bmi" class="form-control" id="bmiField"
                           value="<?= $bmi_val ? number_format($bmi_val, 1) : "-" ?> (<?= $bmi_user ?>)" readonly>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
        <!-- Aktivitas Terakhir -->
        <div class="card shadow-sm mt-4">
            <div class="card-header  text-white" style="background-color: rgb(235, 41, 102);">Aktivitas Terakhir</div>
            <div class="card-body">
                <?php if ($result_activities->num_rows > 0): ?>
                    <table class="table table-sm table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Aktivitas</th>
                                <th>Durasi (menit)</th>
                                <th>Kalori</th>
                                <th>EXP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result_activities->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date("d M Y", strtotime($row['activity_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['activity_name']) ?></td>
                                    <td><?= $row['duration_minutes'] ?></td>
                                    <td><?= $row['calories_burned'] ?></td>
                                    <td><?= $row['exp_earned'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">Belum ada aktivitas.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="updateProfileForm" action="../process/update_profile_data.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="updateProfileModalLabel">Update Data Kesehatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id_pengguna" value="<?= $user_id ?>">
            
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama" name="nama" 
                       value="<?= htmlspecialchars($user['name']) ?>">
            </div>
            <div class="mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                    <option value="laki-laki" <?= $user['jenis_kelamin']=="Laki-Laki"?"selected":"" ?>>Laki-Laki</option>
                    <option value="perempuan" <?= $user['jenis_kelamin']=="Perempuan"?"selected":"" ?>>Perempuan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
                <input type="number" class="form-control" id="tinggi_badan" name="tinggi_badan" 
                       value="<?= $height ?>">
            </div>
            <div class="mb-3">
                <label for="berat_badan" class="form-label">Berat Badan (kg)</label>
                <input type="number" class="form-control" id="berat_badan" name="berat_badan" 
                       value="<?= $weight ?>">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Alert feedback -->
<div id="profileAlert" class="alert mt-3 d-none" role="alert"></div>
<!-- Modal Pilih Avatar -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="avatarForm" action="../process/update_avatar.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="avatarModalLabel">Pilih Avatar</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_pengguna" value="<?= $user_id ?>">

          <div class="row g-3 text-center">
            <?php
            // Avatar options berdasarkan gender
            $gender = strtolower($user['jenis_kelamin']);
            $avatars = [];

            if ($gender === "laki-laki") {
                $avatars = ["man1_", "man2_", "man3_"];
            } elseif ($gender === "perempuan") {
                $avatars = ["women1_", "women2_", "women3_"];
            }

            foreach ($avatars as $av): ?>
              <div class="col-4">
                <label>
                  <input type="radio" name="avatar" value="<?= $av ?>" class="d-none" 
                         <?= $avatar_user === $av ? "checked" : "" ?>>
                  <img src="../assets/images/avatar/<?= $av ?>.png" 
                       class="avatar-option img-thumbnail rounded-circle p-2 <?= $avatar_user === $av ? 'border border-3 border-info' : '' ?>" 
                       style="cursor:pointer; width: 100px;">
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan Avatar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
document.querySelectorAll(".avatar-option").forEach(img => {
  img.addEventListener("click", function() {
    document.querySelectorAll(".avatar-option").forEach(i => i.classList.remove("border", "border-3", "border-info"));
    this.classList.add("border", "border-3", "border-info");
    this.previousElementSibling.checked = true;
  });
});

// Trigger modal dari tombol edit-foto
document.getElementById("edit-foto").closest("button").addEventListener("click", function() {
  let modal = new bootstrap.Modal(document.getElementById("avatarModal"));
  modal.show();
});
</script>
