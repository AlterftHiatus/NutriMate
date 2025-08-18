
    <style>
      .chat-wrapper {
        max-width: 600px;
        margin: auto;
        display: flex;
        flex-direction: column;
        height: 80vh;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
      }

      .chat-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 20px;
        overflow-y: auto;
        scroll-behavior: smooth;
      }

      .chat-bubble {
        padding: 12px 18px;
        border-radius: 20px;
        margin-bottom: 10px;
        max-width: 75%;
        word-wrap: break-word;
      }

      .user-bubble {
        background: #0d6efd;
        color: #fff;
        align-self: flex-end;
      }

      .bot-bubble {
        background: #e9ecef;
        color: #000;
        align-self: flex-start;
      }

      .chat-input {
        display: flex;
        gap: 10px;
        border-top: 1px solid #dee2e6;
        padding: 15px;
        background: #fff;
      }

      .chat-input input {
        flex: 1;
      }

      .chat-input button {
        flex-shrink: 0;
      }
    </style>
<section class="chat-section d-flex gap-4">
  <div class="chat-wrapper" style="width: 70%;">
      <div class="chat-container" id="chat-box">
        <!-- Chat bubbles will be appended here -->
      </div>

      <div class="chat-input">
        <input
          type="text"
          id="question"
          class="form-control"
          placeholder="Tanya tentang kesehatan..."
        />
        <button class="btn btn-primary" onclick="askAI()">Kirim</button>
      </div>
    </div>
  <div class="sideSection" style="width: 30%;">
    <?php include 'sectionSide/streak.php'; ?>
    <?php include "sectionSide/cardTugas.php" ?>
  </div>
</section>
    

    <script>
      function appendBubble(text, type) {
        const bubble = document.createElement("div");
        bubble.className = `chat-bubble ${type}-bubble align-self-${
          type === "user" ? "end" : "start"
        }`;
        bubble.innerText = text;

        const chatBox = document.getElementById("chat-box");
        chatBox.appendChild(bubble);
        chatBox.scrollTop = chatBox.scrollHeight;
      }

      function askAI() {
        const question = document.getElementById("question").value;
        if (!question.trim()) return;

        appendBubble(question, "user");
        document.getElementById("question").value = "";

        // AJAX request ke backend chatbot.php
        fetch("../api/product/chatApi.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "question=" + encodeURIComponent(question),
        })
          .then((res) => res.json())
          .then((data) => {
            // Cek dulu kalau data.error ada
            if (data.error) {
              appendBubble("Error dari API: " + data.error.message, "bot");
              return;
            }

            // Ambil jawaban dari Gemini API response
            const answer = data.candidates[0].content.parts[0].text;
            appendBubble(answer.trim(), "bot");
          })
          .catch((err) => {
            appendBubble("Ups! Terjadi kesalahan.", "bot");
          });
      }
    </script>
  </body>
</html>
