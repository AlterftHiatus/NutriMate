<?php
ob_start();
require '../config/chatbotAPI.php';
// Asumsikan user sudah login
$user_id = $_SESSION['user_id'] ?? 1;

// Ambil avatar user dari DB
$stmt = $pdo->prepare("SELECT avatar, name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika tidak ada foto profil, pakai default
$userAvatar = $userData['avatar'] ?? "../assets/images/avatar/avatar1.png";
$botAvatar = "../assets/images/avatar/nut.png";

// ========== SIMPAN CHAT ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $user_message = trim($_POST['message']);

    if (!empty($user_message)) {
        $stmt = $pdo->prepare("INSERT INTO chat_history (user_id, role, message) VALUES (?, 'user', ?)");
        $stmt->execute([$user_id, $user_message]);

        // Kirim ke Gemini API
        $data = [
            "contents" => [[
                "role" => "user",
                "parts" => [["text" => $user_message]]
            ]]
        ];

        $ch = curl_init(GEMINI_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        // DEBUG: simpan raw response ke file
        file_put_contents("gemini_debug.json", $response);

        if (isset($result['error']) && strpos($result['error']['message'], 'overloaded') !== false) {
            $bot_reply = "Server Nut sedang sibuk, coba lagi sebentar ya :)";
        }
        else {
            $bot_reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Maaf, saya tidak mengerti.";
        }

        $stmt = $pdo->prepare("INSERT INTO chat_history (user_id, role, message) VALUES (?, 'bot', ?)");
        $stmt->execute([$user_id, $bot_reply]);
    }
    header("Location: dashboard.php?page=chat");
    exit;
}

// ========== CLEAR CHAT ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_chat'])) {
    $stmt = $pdo->prepare("DELETE FROM chat_history WHERE user_id = ?");
    $stmt->execute([$user_id]);
    header("Location: dashboard.php?page=chat");
    exit;
}

// ========== AMBIL CHAT HISTORY ==========
$stmt = $pdo->prepare("SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$user_id]);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
ob_end_flush();

?>
<style>
.chat-box {
  height: 100vh;          /* tinggi tetap di desktop */
  min-height: 450px;     
  max-height: 70vh;      /* jaga agar tidak melebar ke bawah */
  overflow-y: auto;      /* biar scroll bukan nambah tinggi */
  padding: 1rem;
  background: #fff;
}

.message {
  max-width: 65%;
  padding: 10px 14px;
  border-radius: 18px;
  font-size: 0.95rem;
  line-height: 1.4;
  position: relative;
}

/* Bot (kiri) */
.message.bot {
  background: #f8f9fa;
  color: #212529;
}

.message.user {
  background: #0d6efd;
  color: #fff;
}


/* Avatar sejajar atas */
.chat-row {
  display: flex;
  align-items: flex-start; /* bukan flex-end lagi */
  margin-bottom: 12px;
}

.chat-row.bot {
  justify-content: flex-start;
}

.chat-row.user {
  justify-content: flex-end;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin: 0 8px;
  align-self: flex-start; /* posisikan di atas */
}

.suggestion-btn {
  background: #fff8e1;              /* kuning lembut */
  border: 1px solid #ffe082;
  color: #795548;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.suggestion-btn:hover {
  background: #ffd54f;             /* kuning terang */
  border-color: #ffca28;
  color: #000;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}


/* ==== DROPUP MELET ==== */
.dropup-chat .dropdown-menu {
  min-width: auto !important; 
  width: auto; 
  padding: 0 !important;       /* buang padding bawaan */
  margin: 0;                   /* rapetin jarak */
  font-size: 0.8rem; 
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.dropup-chat .dropdown-item {
  padding: 1px 2px !important; /* kecil, mepet */
  line-height: 1.2;             /* rapat */
  display: block; 
  white-space: nowrap; 
  overflow: hidden;             /* teks 1 baris */
}

.dropup-chat .dropdown-item i {
  font-size: 13px; 
  margin-right: 4px;
}

.dropup-chat .dropdown-item + .dropdown-item {
  border-top: 1px solid #f1f1f1; /* pemisah tipis antar item */
}

/* Khusus keluar / hapus */
#hapusChat, .dropdown-item.text-danger {
  font-size: 0.8rem !important;
  color: #d9534f;
  font-weight: 500;
}

#hapusChat:hover, .dropdown-item.text-danger:hover {
  background-color: #f8d7da;
  color: #b52b27;
}



@media (max-width: 768px) {
  .chat-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;   /* penuh */
    height: 80vh;  /* penuh */
    z-index: 1050;  /* lebih tinggi dari layout lain */
    background: #fff;
    display: flex;
    flex-direction: column;
  }

  .chat-wrapper .card {
    border-radius: 0; /* biar rapih, tanpa rounded */
    height: 100%;
  }

  .chat-wrapper .chat-box {
    flex: 1; /* isi penuh bagian tengah */
    height: auto !important; /* override aturan sebelumnya */
    max-height: none !important;
  }
}

</style>

<div class="container-fluid mb-5">
  <div class="row g-2">
    <!-- Chatbot -->
    <div class="col-12 col-lg-8 chat-wrapper">
      <div class="card h-100 shadow-sm">
        <div class="card-header bg-warning text-white d-flex align-items-center">
          <img src="../assets/images/avatar/nut_chat.png" alt="Nut" width="50" class="me-2 rounded-circle">
          <div>
            <h5 class="mb-0 fw-bold">Tanya Seputar Kesehatan!</h5>
            <small>Nut akan membantu menjawab pertanyaanmu</small>
          </div>
        </div>

        <!-- Chat Box -->
        <div class="card-body chat-box" id="chat-box">
          <?php if (!empty($chats)): ?>
            <?php foreach ($chats as $chat): ?>
              <div class="chat-row <?= $chat['role'] ?>">
                <?php if ($chat['role'] === 'bot'): ?>
                  <img src="<?= $botAvatar ?>" alt="Bot" class="avatar">
                  <div class="message bot"><?= nl2br(htmlspecialchars($chat['message'])) ?></div>
                <?php else: ?>
                  <div class="message user"><?= nl2br(htmlspecialchars($chat['message'])) ?></div>
                  <img src="../assets/images/avatar/<?= $userAvatar ?>.png" alt="User" class="avatar">
                <?php endif; ?>
              </div>

            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-muted">Belum ada percakapan. Mulai dengan mengetik pesan di bawah!</p>
          <?php endif; ?>
        </div>

                <!-- Input Chat -->
<div class="bg-white border-0">
  <form action="" method="post">
    <div class="input-group">

      <!-- Dropup di kiri input -->
<div class="dropup dropup-chat">
  <button class="btn btn-light border-0 shadow-sm rounded-circle me-2 d-flex align-items-center justify-content-center" 
          type="button" 
          data-bs-toggle="dropdown" 
          aria-expanded="false"
          style="width: 40px; height: 40px;">
    <i class="bi bi-three-dots"></i>
  </button>
  <ul class="dropdown-menu">
    <li>
      <button class="dropdown-item text-danger" type="button" id="hapusChat">
        <i class="bi bi-trash"></i> Hapus chat
      </button>
    </li>
  </ul>
</div>


      <!-- Input pesan -->
      <input type="text" 
             name="message" 
             class="form-control rounded-pill shadow-sm ps-4" 
             placeholder="Ketik pesan..." 
             required>

      <!-- Tombol kirim -->
      <button class="btn btn-warning rounded-pill shadow-sm ms-2 d-flex align-items-center justify-content-center" type="submit">
        <i class="bi bi-send-fill"></i>
      </button>
    </div>
  </form>
</div>



      </div>
    </div>

    <!-- Saran Pertanyaan (hanya muncul di desktop) -->
    <div class="col-12 col-lg-4 d-none d-lg-block">
      <div class="card h-100 border rounded">
        <div class="card-header bg-warning text-dark text-center rounded-top">
          <h5 class="fw-bold mb-0 text-light">
            <i class="bi bi-lightbulb"></i> Saran Pertanyaan
          </h5>
        </div>
        <div class="card-body d-flex flex-column gap-2">
          <?php 
          $suggestions = [
              "Bagaimana cara menghitung BMI saya?",
              "Apa tips pola makan sehat?",
              "Berapa kebutuhan kalori harian saya?",
              "Apa olahraga yang cocok untuk pemula?",
              "Bagaimana cara menjaga kesehatan mental?"
          ]; 
          foreach ($suggestions as $s): ?>
            <button type="button" 
              class="btn suggestion-btn text-start px-3 py-2 rounded-3 shadow-sm">
              <?= htmlspecialchars($s) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Tombol Clear Chat Floating -->
<form method="post" 
      style="position: fixed; bottom: 20px; right: 20px; z-index: 999;" 
      onsubmit="return confirm('Yakin ingin menghapus semua chat?')">
  <button type="submit" name="clear_chat" class="btn btn-danger rounded-circle shadow-lg p-3 d-none d-md-block">
    <i class="bi bi-trash3 fs-4"></i>
  </button>
</form>



<script>
document.getElementById("hapusChat").addEventListener("click", function(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Hapus semua chat?',
    text: "Chat akan hilang permanen.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#e74c3c'
  }).then((result) => {
    if (result.isConfirmed) {
      // Kirim POST untuk clear_chat
      const form = document.createElement("form");
      form.method = "POST";
      form.action = "";
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "clear_chat";
      input.value = "1";
      form.appendChild(input);
      document.body.appendChild(form);
      form.submit();
    }
  });
});
// ====== Fitur: klik saran pertanyaan masuk ke input ======
document.querySelectorAll(".suggestion-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const inputMessage = document.querySelector("input[name='message']");
    if (inputMessage) {
      inputMessage.value = this.textContent.trim();
      inputMessage.focus();
    }
  });
});
</script>