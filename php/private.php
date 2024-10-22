<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$chat_user_id = $_GET['user_id'] ?? null;

if (!$chat_user_id) {
    echo "No user selected for private chat.";
    exit;
}

// Fetch the chat user details
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$chat_user_id]);
$chat_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat_user) {
    echo "User not found.";
    exit;
}

// Handle sending message via AJAX
if (isset($_POST['action']) && $_POST['action'] == 'send_message' && !empty($_POST['message'])) {
    $message = $_POST['message'];
    $stmt = $pdo->prepare("INSERT INTO private_chats (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $chat_user_id, $message]);
    echo json_encode(['status' => 'success']);
    exit;
}

// Handle fetching messages via AJAX
if (isset($_POST['action']) && $_POST['action'] == 'fetch_messages') {
    $stmt = $pdo->prepare("SELECT sender_id, message, timestamp FROM private_chats 
                           WHERE (sender_id = ? AND receiver_id = ?) 
                              OR (sender_id = ? AND receiver_id = ?)
                           ORDER BY timestamp ASC");
    $stmt->execute([$user_id, $chat_user_id, $chat_user_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($messages);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Private Chat with <?php echo htmlspecialchars($chat_user['username']); ?></title>
    <style>
        .chat-box {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
        }

        .chat-box p {
            margin: 5px 0;
        }

        .sender {
            font-weight: bold;
        }

        .timestamp {
            font-size: 0.8em;
            color: gray;
        }
    </style>
</head>
<body>
    <h2>Private Chat with <?php echo htmlspecialchars($chat_user['username']); ?></h2>

    <div id="chat-box" class="chat-box">
        <!-- Messages will be dynamically loaded here -->
    </div>

    <form id="message-form">
        <textarea name="message" id="message-input" rows="4" cols="50" placeholder="Type your message here..." required></textarea><br>
        <button type="submit">Send</button>
    </form>

    <a href="index.php">Back to Dashboard</a>

    <script>
        // Function to fetch messages
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
                chatBox.innerHTML = ''; // Clear current chat

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
                    chatBox.scrollTop = chatBox.scrollHeight; // Scroll to the bottom
                } else {
                    chatBox.innerHTML = '<p>No messages yet.</p>';
                }
            });
        }

        // Function to send a message
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
                    document.getElementById('message-input').value = ''; // Clear the input
                    fetchMessages(); // Refresh the messages
                }
            });
        });

        // Polling the server every 3 seconds for new messages
        setInterval(fetchMessages, 3000);

        // Initial fetch of messages on page load
        fetchMessages();
    </script>
</body>
</html>
