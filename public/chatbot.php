<?php
session_start();
require '../config/chatbotAPI.php';

// Asumsikan user sudah login
$user_id = $_SESSION['user_id'] ?? 1; // sementara default 1 kalau belum ada login

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
    header("Location: chatbot.php");
    exit;
}

// ========== AMBIL CHAT HISTORY ==========
$stmt = $pdo->prepare("SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$user_id]);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Chat Bot Kesehatan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .chat-container {
            width: 500px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
        }

        .chat-box {
            height: 400px;
            overflow-y: auto;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        .message {
            margin: 8px 0;
            padding: 10px 14px;
            border-radius: 16px;
            max-width: 70%;
        }

        .user {
            background: #007bff;
            color: #fff;
            margin-left: auto;
            text-align: right;
        }

        .bot {
            background: #eaeaea;
            color: #333;
            margin-right: auto;
            text-align: left;
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
</head>

<body>

    <a href="index.php" class="mb-4 d-inline-block">⬅ Kembali ke index</a>
    
    <div class="chat-container">
        <div class="chat-box" id="chat-box">
            <?php foreach ($chats as $chat): ?>
                <div class="message <?= $chat['role'] === 'user' ? 'user' : 'bot' ?>">
                    <?= nl2br(htmlspecialchars($chat['message'])) ?>
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
</body>

</html>