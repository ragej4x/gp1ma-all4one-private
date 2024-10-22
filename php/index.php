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

// Fetch all users except the logged-in user
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id != ?");
$stmt->execute([$user_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT groups.id, groups.name 
                       FROM groups
                       JOIN group_members ON groups.id = group_members.group_id
                       WHERE group_members.user_id = ?");
$stmt->execute([$user_id]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Dashboard</title>
    <style>
        .group-list, .user-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
        }

        .group-list li, .user-list li {
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
    <h2>Your Groups</h2>
    <div class="group-list">
        <ul>
            <?php if (count($groups) > 0): ?>
                <?php foreach ($groups as $group): ?>
                    <li>
                        <a href="group.php?group_id=<?php echo $group['id']; ?>">
                            <?php echo htmlspecialchars($group['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>You are not a member of any group yet.</li>
            <?php endif; ?>
        </ul>
    </div>

    <h2>Available Users for Private Chat</h2>
    <div class="user-list">
        <ul>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <li>
                        <a href="private.php?user_id=<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No other users available for private chat.</li>
            <?php endif; ?>
        </ul>
    </div>

    <a href="friend_dashboard.php">Friend Dashboard</a><br>
    <a href="create_group.php">Create Group</a><br>
    <a href="../index.php">Return</a>

</body>
</html>
