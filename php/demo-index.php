<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];
//check friend
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.profile_pic
    FROM users u
    JOIN friends f ON (u.id = f.sender_id OR u.id = f.receiver_id)
    WHERE (f.sender_id = ? OR f.receiver_id = ?) 
    AND f.status = 'accepted'
    AND u.id != ?
");
$stmt->execute([$user_id, $user_id, $user_id]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT g.id, g.name, g.profile_pic
    FROM groups g
    JOIN group_members gm ON g.id = gm.group_id
    WHERE gm.user_id = ?
");
$stmt->execute([$user_id]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Handle private chat message sending wag din iseparate
    if ($action === 'send_message' && isset($_POST['user_id'])) {
        $receiver_id = $_POST['user_id'];
        $message = $_POST['message'];

        $stmt = $pdo->prepare("INSERT INTO private_chats (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $receiver_id, $message])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send message.']);
        }
        exit;
    }

    // Handle group chat message sending /// wag iseparate
    if ($action === 'send_group_message' && isset($_POST['group_id'])) {
        $group_id = $_POST['group_id'];
        $message = $_POST['message'];

        $stmt = $pdo->prepare("INSERT INTO group_messages (group_id, user_id, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$group_id, $user_id, $message])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send group message.']);
        }
        exit;
    }

    // !!! remider to my self wag tanggalin
    if ($action === 'fetch_messages' && isset($_POST['user_id'])) {
        $chat_user_id = $_POST['user_id'];

        $stmt = $pdo->prepare("
            SELECT message, sender_id, timestamp
            FROM private_chats
            WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
            ORDER BY timestamp ASC
        ");
        $stmt->execute([$user_id, $chat_user_id, $chat_user_id, $user_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($messages);
        exit;
    }

    // Handle group chat message fetching
    //!!! remider to my self wag mo to tanggalin ayaw mag fetch ng data pag separate file kingina
    if ($action === 'fetch_group_messages' && isset($_POST['group_id'])) {
        $group_id = $_POST['group_id'];

        $stmt = $pdo->prepare("
            SELECT gm.message, u.username AS sender_username, gm.created_at
            FROM group_messages gm
            JOIN users u ON gm.user_id = u.id
            WHERE gm.group_id = ?
            ORDER BY gm.created_at ASC
        ");
        $stmt->execute([$group_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($messages);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Dashboard</title>
    <link rel="stylesheet" href="style/index-style.css">
</head>
<body>

    <div class="chat-container">
        <div class="sidebar">
            <h2>Chat Dashboard</h2>
            <div class="tab-container">
                <div id="privateTab" class="tab active" onclick="showChat('privateChat')">Private Chat</div>
                <div id="groupTab" class="tab" onclick="showChat('groupChat')">Group Chat</div>
            </div>

            <!-- Private Chat Section -->
            <div id="privateChat" class="chat-list active">
                <h3>Private Chat</h3>
                <ul>
                    <?php foreach ($friends as $friend): ?>
                        <li onclick="startChat(<?= $friend['id'] ?>, '<?= htmlspecialchars($friend['username']) ?>')">
                            <img src="<?= htmlspecialchars($friend['profile_pic']) ?>" alt="error">
                            <span><?= htmlspecialchars($friend['username']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Group Chat Section -->
            <div id="groupChat" class="chat-list">
                <h3>Your Groups</h3>
                <ul>
                    <?php foreach ($groups as $group): ?>
                        <li onclick="startGroupChat(<?= $group['id'] ?>, '<?= htmlspecialchars($group['name']) ?>')">
                            <img src="<?= htmlspecialchars($group['profile_pic']) ?>" alt="error">

                            <span><?= htmlspecialchars($group['name']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="chat-content">
            <div id="chat-header">Select a chat to start messaging!</div>
            <div class="chat-box" id="chat-box"></div>
            <form id="message-form" onsubmit="sendMessage(event)">
                <input type="text" id="message-input" placeholder="Type your message..." required>
                <button type="submit">Send</button>
            </form>
        </div>
    </div>

    <script>
        //Tangina neto ayaw mag load sa separate file kingina !!!remider ko sa sarili ko wag mo to tanggalin
        //kakamot ka nanaman sa ulo hanggang 3am kka isip bakit ayaw gumana ng func sa msg box
        let currentChatId = null;
        let isGroupChat = false;

        function showChat(chatType) {
            document.getElementById('privateChat').classList.remove('active');
            document.getElementById('groupChat').classList.remove('active');
            document.getElementById('privateTab').classList.remove('active');
            document.getElementById('groupTab').classList.remove('active');

            if (chatType === 'privateChat') {
                document.getElementById('privateChat').classList.add('active');
                document.getElementById('privateTab').classList.add('active');
                isGroupChat = false;
                document.getElementById('chat-header').innerText = 'Select a friend to chat with!';
            } else {
                document.getElementById('groupChat').classList.add('active');
                document.getElementById('groupTab').classList.add('active');
                isGroupChat = true;
                document.getElementById('chat-header').innerText = 'Select a group to chat with!';
            }

            document.getElementById('chat-box').innerHTML = ''; // Clear chat box
            currentChatId = null; // Reset chat ID
        }

        function startChat(userId, username) {
            currentChatId = userId;
            document.getElementById('chat-header').innerText = `Chat with ${username}`;
            fetchMessages();
        }

        function startGroupChat(groupId, groupName) {
            currentChatId = groupId;
            document.getElementById('chat-header').innerText = `Chat in ${groupName}`;
            fetchGroupMessages();
        }

        function sendMessage(event) {
            event.preventDefault();
            const message = document.getElementById('message-input').value;

            if (isGroupChat) {
                fetch('', {
                    method: 'POST',
                    body: new URLSearchParams({ action: 'send_group_message', group_id: currentChatId, message: message })
                }).then(response => response.json()).then(data => {
                    if (data.status === 'success') {
                        document.getElementById('message-input').value = '';
                        fetchGroupMessages();
                    } else {
                        alert(data.message);
                    }
                });
            } else {
                fetch('', {
                    method: 'POST',
                    
                    body: new URLSearchParams({ action: 'send_message', user_id: currentChatId, message: message })
                }).then(response => response.json()).then(data => {
                    if (data.status === 'success') {
                        document.getElementById('message-input').value = '';
                        fetchMessages();
                    } else {
                        alert(data.message);
                    }
                });
            }
        }

        function fetchMessages() {
            if (currentChatId) {
                fetch('', {
                    method: 'POST',
                    body: new URLSearchParams({ action: 'fetch_messages', user_id: currentChatId })
                }).then(response => response.json()).then(messages => {
                    const chatBox = document.getElementById('chat-box');
                    chatBox.innerHTML = '';
                    messages.forEach(msg => {
                        const messageElement = document.createElement('p');
                        messageElement.innerHTML = `<span class="sender">${msg.sender_id == <?= $user_id ?> ? 'You' : 'Friend'}</span>: ${msg.message} <span class="timestamp">${new Date(msg.timestamp).toLocaleTimeString()}</span>`;
                        chatBox.appendChild(messageElement);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight; 
                });
            }
        }

        function fetchGroupMessages() {
            if (currentChatId) {
                fetch('', {
                    method: 'POST',
                    
                    body: new URLSearchParams({ action: 'fetch_group_messages', group_id: currentChatId })
                }).then(response => response.json()).then(messages => {
                    const chatBox = document.getElementById('chat-box');
                    chatBox.innerHTML = '';
                    messages.forEach(msg => {
                        const messageElement = document.createElement('p');
                        messageElement.innerHTML = `<span class="sender">${msg.sender_username}: </span>${msg.message} <span class="timestamp">${new Date(msg.created_at).toLocaleTimeString()}</span>`;
                        chatBox.appendChild(messageElement);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight; 
                });
            }
        }
    </script>
</body>
</html>


body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f1f1f1;
}
.chat-container {
    display: flex;
    height: 100vh;
}
.sidebar {
    flex: 1;
    background-color: #2c3e50;
    color: #ecf0f1;
    padding: 20px;
}
.sidebar h2 {
    text-align: center;
    margin-bottom: 20px;
}
.tab-container {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}
.tab {
    cursor: pointer;
    padding: 10px 20px;
    background-color: #546b91;
    color: white;
    text-align: center;
    border-radius: 5px;
    transition: background-color 0.3s;
}
.tab.active {
    background-color: #3b4165;
}
.tab:hover {
    background-color: #6c80ab;
}
.chat-list {
    margin-bottom: 20px;
}
.chat-list h3 {
    font-size: 18px;
    margin-bottom: 10px;
}
.chat-list ul {
    list-style: none;
    padding: 0;
}
.chat-list li {
    padding: 10px;
    display: flex;
    align-items: center;
    cursor: pointer;
    background-color: #34495e;
    margin-bottom: 10px;
    border-radius: 5px;
    transition: background-color 0.3s;
}
.chat-list li:hover {
    background-color: #6c89c2;
}
.chat-list img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-right: 10px;
}
.chat-list span {
    color: #ecf0f1;
    font-weight: bold;
}
.chat-content {
    flex: 3;
    background-color: #ffffff;
    display: flex;
    flex-direction: column;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
#chat-header {
    font-size: 22px;
    margin-bottom: 20px;
    text-align: center;
    color: #2c3e50;
}
.chat-box {
    flex-grow: 1;
    background-color: #ecf0f1;
    border-radius: 10px;
    padding: 10px;
    overflow-y: auto;
    margin-bottom: 20px;
}
.chat-box p {
    background-color: #2c3e50;
    color: white;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
    max-width: 60%;
}
.sender {
    font-weight: bold;
}
.timestamp {
    font-size: small;
    color: gray;
}
#message-form {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
#message-input {
    width: 80%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 16px;
}
#message-form button {
    padding: 10px 20px;
    background-color: #2980b9;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
#message-form button:hover {
    background-color: #3498db;
}
#privateChat, #groupChat {
    display: none;
}
#privateChat.active, #groupChat.active {
    display: block;
}