<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {

    header('Location: ../auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];

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
"
);
$stmt->execute([$user_id]);

$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

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
    //minsan ayaw mag fetch dahil sa PDO dko na alam
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
    <link rel="icon" type="image/x-icon" href="icons/favicon.png">

</head>
<body>
<div class="chat-container">
    <div class="sidebar">

        <h2>Chat Dashboard</h2>

        <div class="tab-container">
            <div id="privateTab" class="tab active" onclick="showChat('privateChat')">Private Chat</div>
            <div id="groupTab" class="tab" onclick="showChat('groupChat')">Group Chat</div>
        </div>

        <div id="privateChat" class="chat-list active">
            <h3>Private Chat</h3>
            <ul>
                <?php foreach ($friends as $friend): ?>

                    <li onclick="startChat(<?= $friend['id'] ?>, '<?= htmlspecialchars($friend['username']) ?>')">
                        <img src="<?= (!empty($friend['profile_pic']) && file_exists("uploads/" . $friend['profile_pic'])) ? "uploads/" . htmlspecialchars($friend['profile_pic']) : 'uploads/default-profile.png' ?>" alt="Profile Picture">
                        <span><?= htmlspecialchars($friend['username']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>
        <footer>
        <div class='nav-txt' style='bottom:0; position:absolute; margin-bottom: 10px; width:90%;'>
            <a style='color: #ecf0f1;' href="../index.php" >Return</a>
            <a style='color: #ecf0f1; margin-left:13%;' href="friend_dashboard.php">Friend Dashboard</a>
        
        </div>

        </footer>

        <div id="groupChat" class="chat-list">
            <h3>Your Groups</h3>
            <ul>

            <?php foreach ($groups as $group): ?>
                <li onclick="startGroupChat(<?= $group['id'] ?>, '<?= htmlspecialchars($group['name']) ?>')">
                    <img src="<?= !empty($group['profile_pic']) && file_exists("uploads/" . $group['profile_pic']) ? htmlspecialchars($group['profile_pic']) : 'uploads/default-profile.png' ?>" alt="Group Picture">
                    <span><?= htmlspecialchars($group['name']) ?>
                    <a style="" href="add_member.php?group_id=<?= $group['id'] ?>">Add Member</a>
                    </span>
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

    let currentChatId = null;
    let isGroupChat = false;
    //bug stuck when rappidly switchj tab
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
        } 
        
        else {

            document.getElementById('groupChat').classList.add('active');
            document.getElementById('groupTab').classList.add('active');
            isGroupChat = true;
            document.getElementById('chat-header').innerText = 'Select a group to chat with!';
        }

        document.getElementById('chat-box').innerHTML = '';
        currentChatId = null; 
    }

    function startChat(userId, username) {

        currentChatId = userId;

        document.getElementById('chat-header').innerText = `Chat with ${username}`;
        fetchMessages();
    }

    function startGroupChat(groupId, groupName) {
        //chat id
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

    //Minsan ayaw gumana kupal
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
