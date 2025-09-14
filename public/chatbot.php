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
        $bot_reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Maaf, saya tidak mengerti.";

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
  background: #f9f9f9;
}
.chat-row {
  display: flex;
  align-items: flex-end;
  margin-bottom: 12px;
}

.chat-row.bot {
  justify-content: flex-start; /* Bot di kiri */
}

.chat-row.user {
  justify-content: flex-end;   /* User di kanan */
}

.message {
  max-width: 70%;
  padding: 10px 14px;
  border-radius: 16px;
  font-size: 0.95rem;
  line-height: 1.4;
}

.message.bot {
  background: #e9ecef;
  color: #212529;
  border-top-left-radius: 0;
}

.message.user {
  background: #0d6efd;
  color: #fff;
  border-top-right-radius: 0;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  margin: 0 8px;
}



@media (max-width: 768px) {
  .chat-box {
    height: calc(100vh - 200px); /* hampir full layar di HP */
    max-height: calc(100vh - 200px);
  }
}
</style>

<div class="container-fluid mb-5">
  <div class="row g-2">
    <!-- Chatbot -->
    <div class="col-12 col-lg-8">
      <div class="card h-100 shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center">
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
        <div class="card-footer">
          <form action="" method="post" class="d-flex gap-2">
            <input type="text" name="message" class="form-control" placeholder="Ketik pesan..." required>
            <button type="submit" class="btn btn-primary">Kirim</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Saran Pertanyaan (hanya muncul di desktop) -->
    <div class="col-12 col-lg-4 d-none d-lg-block">
      <div class="card h-100 shadow-sm">
        <h5 class="p-3 fw-bold text-center">Saran Pertanyaan</h5>
        <div class="card-body d-flex flex-wrap gap-2">
          <?php 
          $suggestions = [
              "Bagaimana cara menghitung BMI saya?",
              "Apa tips pola makan sehat?",
              "Berapa kebutuhan kalori harian saya?",
              "Apa olahraga yang cocok untuk pemula?",
              "Bagaimana cara menjaga kesehatan mental?"
          ]; 
          foreach ($suggestions as $s): ?>
            <button type="button" class="btn btn-sm btn-outline-info suggestion-btn">
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
  <button type="submit" name="clear_chat" class="btn btn-danger rounded-circle shadow-lg p-3">
    <i class="bi bi-trash3 fs-4"></i>
  </button>
</form>


<script>
document.querySelectorAll(".suggestion-btn").forEach(btn => {
    btn.addEventListener("click", function() {
        const input = document.querySelector("input[name='message']");
        input.value = this.innerText; 
        input.focus(); 
    });
});

// Auto-scroll ke bawah tiap ada pesan baru
var chatBox = document.getElementById("chat-box");
chatBox.scrollTop = chatBox.scrollHeight;
</script>
