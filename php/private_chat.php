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

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['message'])) {
    $message = $_POST['message'];
    $stmt = $pdo->prepare("INSERT INTO private_chats (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $chat_user_id, $message]);
}

// Fetch private messages between the two users
$stmt = $pdo->prepare("SELECT sender_id, message, timestamp FROM private_chats 
                       WHERE (sender_id = ? AND receiver_id = ?) 
                          OR (sender_id = ? AND receiver_id = ?)
                       ORDER BY timestamp ASC");
$stmt->execute([$user_id, $chat_user_id, $chat_user_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <div class="chat-box">
        <?php if (count($messages) > 0): ?>
            <?php foreach ($messages as $message): ?>
                <p>
                    <span class="sender"><?php echo $message['sender_id'] == $user_id ? 'You' : htmlspecialchars($chat_user['username']); ?>:</span>
                    <?php echo htmlspecialchars($message['message']); ?>
                    <br>
                    <span class="timestamp"><?php echo $message['timestamp']; ?></span>
                </p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages yet.</p>
        <?php endif; ?>
    </div>

    <form method="post" action="">
        <textarea name="message" rows="4" cols="50" placeholder="Type your message here..." required></textarea><br>
        <button type="submit">Send</button>
    </form>

    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
