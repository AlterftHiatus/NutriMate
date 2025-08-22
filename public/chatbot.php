<?php
require '../config/chatbotAPI.php';

// Asumsikan user sudah login
$user_id = $_SESSION['user_id'] ?? 1; // sementara default 1 kalau belum login

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
        // Simpan pesan user
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

        // Simpan pesan bot
        $stmt = $pdo->prepare("INSERT INTO chat_history (user_id, role, message) VALUES (?, 'bot', ?)");
        $stmt->execute([$user_id, $bot_reply]);
    }

    // Refresh biar gak resubmit form
    header("Location: dashboard.php?page=chat");
    exit;
}

// ========== AMBIL CHAT HISTORY ==========
$stmt = $pdo->prepare("SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$user_id]);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="header d-flex justify-content-between mb-1">
    <div class="chat-header text-start rounded d-flex">
        <img src="../assets/images/avatar/nut_chat.png" alt="" width="80px">
        <div class="description">
            <h4 class="fw-bold">Tanya Seputar Kesehatan!</h4>
            <p class="">Nut akan membantu dan menjawab semua pertanyaan kamu.</p>
        </div>
    </div>


    <!-- STREAK, XP, PROFILE -->

</div>
    <div class="sectionContainer d-flex gap-1 w-100">
        <div class="chat-container">
            <div class="chat-box" id="chat-box">
                <?php foreach ($chats as $chat): ?>
                    <div class="chat-row <?= $chat['role'] ?>">
                        <?php if ($chat['role'] === 'bot'): ?>
                            <img src="<?= $botAvatar ?>" alt="Bot" class="avatar">
                        <?php else: ?>
                            <img src="../assets/images/avatar/<?= $userAvatar ?>.png" alt="User" class="avatar">
                        <?php endif; ?>
    
                        <div class="message <?= $chat['role'] === 'user' ? 'user' : 'bot' ?>">
                            <?= nl2br(htmlspecialchars($chat['message'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="post" autocomplete="off" class="input-group"> 
                <button type="submit" class="input-group-text"><i class="bi bi-plus-circle fw-bold fs-5"></i></button>
                <input style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px; outline: none;" type="text" name="message" placeholder="Ketik pertanyaan seputar kesehatan..." required>
            </form>
        </div>
<div class="card mt-2" style="width: 30%;">
    <h4 class="p-2 fw-bold text-center">Suggestion Question</h4>
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
