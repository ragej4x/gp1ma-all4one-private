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

// Handle Add Friend by Username
if (isset($_POST['add_friend_username'])) {
    $username_to_add = trim($_POST['add_friend_username']);

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username_to_add]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($friend) {
        $friend_id = $friend['id'];

        // Check if a friendship already exists
        $stmt = $pdo->prepare("SELECT * FROM friends WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
        $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
        $friendship = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$friendship) {
            // Send friend request
            $stmt = $pdo->prepare("INSERT INTO friends (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user_id, $friend_id]);
        }
    }
    
    // Redirect back to the dashboard to prevent re-submission
    header('Location: friend_dashboard.php');
    exit;
}

// Handle Add Friend, Accept Friend, and Decline Friend actions
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $action = $_GET['action'];
    $friend_id = $_GET['user_id'];

    switch ($action) {
        case 'accept':
            // Accept Friend Logic
            $stmt = $pdo->prepare("UPDATE friends SET status = 'accepted' WHERE sender_id = ? AND receiver_id = ?");
            $stmt->execute([$friend_id, $user_id]);
            break;

        case 'decline':
            // Decline Friend Logic
            $stmt = $pdo->prepare("DELETE FROM friends WHERE sender_id = ? AND receiver_id = ?");
            $stmt->execute([$friend_id, $user_id]);
            break;
    }

    // Redirect back to the dashboard to prevent re-submission
    header('Location: friend_dashboard.php');
    exit;
}

// Fetch confirmed friends
$stmt = $pdo->prepare("SELECT users.id, users.username FROM friends 
                       JOIN users ON (friends.sender_id = users.id OR friends.receiver_id = users.id) 
                       WHERE (friends.sender_id = ? OR friends.receiver_id = ?) 
                         AND friends.status = 'accepted'
                         AND users.id != ?");
$stmt->execute([$user_id, $user_id, $user_id]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending friend requests sent by the logged-in user
$stmt = $pdo->prepare("SELECT receiver_id FROM friends WHERE sender_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_requests = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'receiver_id');

// Fetch incoming friend requests
$stmt = $pdo->prepare("SELECT sender_id, users.username FROM friends 
                       JOIN users ON users.id = friends.sender_id
                       WHERE friends.receiver_id = ? AND friends.status = 'pending'");
$stmt->execute([$user_id]);
$incoming_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all users except the logged-in user and those already friends
$stmt = $pdo->prepare("
    SELECT users.id, users.username 
    FROM users 
    WHERE users.id != ? 
      AND users.id NOT IN (
          SELECT CASE
              WHEN sender_id = ? THEN receiver_id
              ELSE sender_id
          END 
          FROM friends 
          WHERE (sender_id = ? OR receiver_id = ?) 
            AND status = 'accepted'
      )
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Dashboard</title>
    <style>
        .group-list, .user-list, .friend-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
        }

        .group-list li, .user-list li, .friend-list li {
            margin-bottom: 10px;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Your Friends</h2>
    <div class="friend-list">
        <ul>
            <?php if (count($friends) > 0): ?>
                <?php foreach ($friends as $friend): ?>
                    <li>
                        <a href="private_chat.php?user_id=<?php echo $friend['id']; ?>">
                            <?php echo htmlspecialchars($friend['username']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>You have no friends yet.</li>
            <?php endif; ?>
        </ul>
    </div>

    <h2>Available Users for Friendship</h2>
    <div class="user-list">
        <ul>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <li>
                        <?php echo htmlspecialchars($user['username']); ?>
                        <a href="friend_dashboard.php?action=add&user_id=<?php echo $user['id']; ?>">[Add Friend]</a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No users available to add as a friend.</li>
            <?php endif; ?>
        </ul>
    </div>

    <h2>Add Friend by Username</h2>
    <form method="POST" action="friend_dashboard.php">
        <input type="text" name="add_friend_username" placeholder="Enter username" required>
        <button type="submit">Add Friend</button>
    </form>

    <h2>Pending Friend Requests</h2>
    <div class="friend-list">
        <ul>
            <?php if (count($incoming_requests) > 0): ?>
                <?php foreach ($incoming_requests as $request): ?>
                    <li>
                        <?php echo htmlspecialchars($request['username']); ?>
                        <a href="friend_dashboard.php?action=accept&user_id=<?php echo $request['sender_id']; ?>">[Accept]</a>
                        <a href="friend_dashboard.php?action=decline&user_id=<?php echo $request['sender_id']; ?>">[Decline]</a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No pending friend requests.</li>
            <?php endif; ?>
        </ul>
    </div>

    <a href="index.php">Return</a>

</body>
</html>
