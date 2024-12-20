<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['group_id'])) {
    echo "Group ID is not specified.";
    exit;
}

$group_id = $_GET['group_id']; 
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
$stmt->execute([$group_id, $user_id]);
$is_member = $stmt->fetch();

if (!$is_member) {
    echo "You are not a member of this group.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    $stmt = $pdo->prepare("INSERT INTO messages (user_id, group_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $group_id, $message]);

    header("Location: group.php?group_id=$group_id"); 
    exit;
}

function fetchMessages($pdo, $group_id) {
    $stmt = $pdo->prepare("SELECT messages.message, messages.created_at, users.username 
                           FROM messages 
                           JOIN users ON messages.user_id = users.id 
                           WHERE messages.group_id = ? 
                           ORDER BY messages.created_at ASC");
    $stmt->execute([$group_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $pdo->prepare("SELECT name, created_by FROM groups WHERE id = ?");
$stmt->execute([$group_id]);
$group = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$group) {
    echo "Group not found.";
    exit;
}

$messages = fetchMessages($pdo, $group_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group Chat: <?php echo htmlspecialchars($group['name']); ?></title>
    <style>
        #chat-box {
            border: 1px solid #000;
            height: 300px;
            overflow-y: scroll;
            margin-bottom: 20px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <h1>Group Chat: <?php echo htmlspecialchars($group['name']); ?></h1>

    <div id="chat-box">
        <?php foreach ($messages as $msg): ?>
            <p>
                <strong><?php echo htmlspecialchars($msg['username']); ?>:</strong> 
                <?php echo htmlspecialchars($msg['message']); ?> 
                <em><?php echo $msg['created_at']; ?></em>
            </p>
        <?php endforeach; ?>
    </div>

    <?php if ($group['created_by'] == $user_id): ?>
        <a href="add_member.php?group_id=<?php echo $group_id; ?>">Add Member</a>
    <?php endif; ?>

    <form id="message-form" method="POST" action="">
        <textarea name="message" rows="3" cols="30" required></textarea><br>
        <button type="submit">Send</button>
    </form>

    <br>
    <a href="index.php">Back to Chat</a>

    <script>
        function fetchMessages() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', window.location.href, true); 
            xhr.onload = function() {
                if (this.status === 200) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(this.responseText, 'text/html');
                    const chatBox = document.getElementById('chat-box');
                    const newMessages = doc.getElementById('new-messages').innerHTML;
                    chatBox.innerHTML = newMessages; 

                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            };
            xhr.send();
        }

        setInterval(fetchMessages, 3000);

        fetchMessages();
    </script>
    
    <div id="new-messages" style="display: none;">
        <?php foreach ($messages as $msg): ?>
            <p>
                <strong><?php echo htmlspecialchars($msg['username']); ?>:</strong> 
                <?php echo htmlspecialchars($msg['message']); ?> 
                <em><?php echo $msg['created_at']; ?></em>
            </p>
        <?php endforeach; ?>
    </div>
</body>
</html>
