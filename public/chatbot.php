<?php
require '../config/chatbotAPI.php';

// Asumsikan user sudah login
$user_id = $_SESSION['user_id'] ?? 1; // sementara default 1 kalau belum login

// Ambil avatar user dari DB
$stmt = $pdo->prepare("SELECT avatar, name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika tidak ada foto profil, pakai default
$userAvatar = !empty($userData['avatar']) ? $userData['avatar'] : "assets/images/avatar/user.png";
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
    <style>

        .chat-container{
            height: 100vh;
            margin: 10px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
        }

        .chat-box {
            height: 400px;
            overflow-y: auto;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* baris chat */
        .chat-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }

        /* avatar pakai gambar */
        .avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;  /* bulat */
            object-fit: cover;
            border: 2px solid #ddd;
        }

        /* bubble chat */
        .message {
            padding: 10px 14px;
            border-radius: 16px;
            max-width: 65%;
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
        }

        /* gaya user */
        .chat-row.user {
            justify-content: flex-end;
        }
        .chat-row.user .message {
            background: #007bff;
            color: #fff;
            border-bottom-right-radius: 4px;
        }
        .chat-row.user .avatar {
            order: 2; /* avatar pindah ke kanan */
        }

        /* gaya bot */
        .chat-row.bot {
            justify-content: flex-start;
        }
        .chat-row.bot .message {
            background: #f7f7f7;
            color: #333;
            border-bottom-left-radius: 4px;
        }

        form {
            display: flex;
            padding: 10px;
        }

        input[type=text] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
        }

        button {
            padding: 10px 20px;
            border: none;
            background: #007bff;
            color: #fff;
            border-radius: 20px;
            margin-left: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
    <div class="chat-container">
        <div class="chat-box" id="chat-box">
            <?php foreach ($chats as $chat): ?>
                <div class="chat-row <?= $chat['role'] ?>">
                    <?php if ($chat['role'] === 'bot'): ?>
                        <img src="<?= $botAvatar ?>" alt="Bot" class="avatar">
                    <?php else: ?>
                        <img src="../assets/images/avatar/<?= htmlspecialchars($userAvatar) ?>" alt="User" class="avatar">
                    <?php endif; ?>

                    <div class="message <?= $chat['role'] === 'user' ? 'user' : 'bot' ?>">
                        <?= nl2br(htmlspecialchars($chat['message'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <form method="post" autocomplete="off">
            <input type="text" name="message" placeholder="Ketik pertanyaan seputar kesehatan..." required>
            <button type="submit">Kirim</button>
        </form>
    </div>

    <script>
        // Auto-scroll ke bawah tiap ada pesan baru
        var chatBox = document.getElementById("chat-box");
        chatBox.scrollTop = chatBox.scrollHeight;
    </script>