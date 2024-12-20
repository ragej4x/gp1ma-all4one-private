<?php
session_start();
if ($_SESSION['role'] != 'user') {
    header('Location: ../auth.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Chat</title>
</head>
<body>
    <h1>User Chat</h1>
    <div id="chat-box" style="border: 1px solid #ccc; height: 300px; overflow-y: scroll; margin-bottom: 10px;"></div>
    <form id="chat-form">
        <input type="text" id="message" placeholder="Type a message" required style="width: 80%;">
        <button type="submit">Send</button>
    </form>

    <script>
        const userId = <?php echo $_SESSION['id']; ?>;
        const teacherId = 1;
        const chatBox = document.getElementById('chat-box');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message');

        function fetchMessages() {
            fetch(`fetch_messages.php?partner_id=${teacherId}&partner_role=teacher`)
                .then(response => response.json())
                .then(messages => {
                    chatBox.innerHTML = '';
                    messages.forEach(msg => {
                        const messageDiv = document.createElement('div');
                        messageDiv.textContent = `[${msg.sender_type}] ${msg.message}`;
                        chatBox.appendChild(messageDiv);
                    });
                });
        }

        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `receiver_id=${teacherId}&receiver_type=teacher&message=${encodeURIComponent(messageInput.value)}`
            }).then(() => {
                messageInput.value = '';
                fetchMessages();
            });
        });

        setInterval(fetchMessages, 1000);
    </script>
</body>
</html>
