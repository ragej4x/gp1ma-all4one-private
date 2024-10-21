<?php
// Start the session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php'; // Include your database connection file

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$group_id = $_GET['group_id']; // Get the group ID from the URL
$user_id = $_SESSION['user_id'];

// Check if the user is a member of this group
$stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
$stmt->execute([$group_id, $user_id]);
$is_member = $stmt->fetch();

if (!$is_member) {
    echo "You are not a member of this group.";
    exit;
}

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    $stmt = $pdo->prepare("INSERT INTO messages (user_id, group_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $group_id, $message]);

    // Redirect to avoid form resubmission on page reload
    header("Location: group_chat.php?group_id=$group_id");
    exit;
}

// Fetch all messages for the group
$stmt = $pdo->prepare("SELECT messages.message, messages.created_at, users.username 
                       FROM messages 
                       JOIN users ON messages.user_id = users.id 
                       WHERE messages.group_id = ? 
                       ORDER BY messages.created_at ASC");
$stmt->execute([$group_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch group details, including the 'created_by' field
$stmt = $pdo->prepare("SELECT name, created_by FROM groups WHERE id = ?");
$stmt->execute([$group_id]);
$group = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the group exists
if (!$group) {
    echo "Group not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group Chat: <?php echo htmlspecialchars($group['name']); ?></title>
</head>
<body>
    <h1>Group Chat: <?php echo htmlspecialchars($group['name']); ?></h1>

    <div id="chat-box" style="border: 1px solid #000; height: 300px; overflow-y: scroll;">
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

    <form method="POST" action="">
        <textarea name="message" rows="3" cols="30" required></textarea><br>
        <button type="submit">Send</button>
    </form>

    <br>
    <a href="chat.php">Back to Chat</a>
</body>
</html>
