<h2>Private Chat with <?php echo htmlspecialchars($chat_user['username']); ?></h2>

<div id="chat-box" class="chat-box">
    <!-- dynamic load dto -->
</div>

<form id="message-form">
    <textarea name="message" id="message-input" rows="4" cols="50" placeholder="Type your message here..." required></textarea><br>
    <button type="submit">Send</button>
</form>

<script>
    // Fetch messages and render them
    function fetchMessages() {
        const formData = new FormData();
        formData.append('action', 'fetch_messages');

        fetch('private.php?user_id=<?php echo $chat_user_id; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(messages => {
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = '';

            if (messages.length > 0) {
                messages.forEach(message => {
                    const sender = message.sender_id == <?php echo $user_id; ?> ? 'You' : '<?php echo htmlspecialchars($chat_user['username']); ?>';
                    const messageHTML = `
                        <p>
                            <span class="sender">${sender}:</span>
                            ${message.message}
                            <br>
                            <span class="timestamp">${message.timestamp}</span>
                        </p>`;
                    chatBox.innerHTML += messageHTML;
                });
                chatBox.scrollTop = chatBox.scrollHeight;
            } else {
                chatBox.innerHTML = '<p>No messages yet.</p>';
            }
        });
    }

    document.getElementById('message-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const messageInput = document.getElementById('message-input').value;

        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('message', messageInput);

        fetch('private.php?user_id=<?php echo $chat_user_id; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('message-input').value = '';
                fetchMessages();
            }
        });
    });

    // pool ng msg every 3 sec
    setInterval(fetchMessages, 3000);
    fetchMessages(); // Initial load
</script>
