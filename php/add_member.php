<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = $_GET['group_id']; 

$stmt = $pdo->prepare("SELECT created_by FROM groups WHERE id = ?");
$stmt->execute([$group_id]);
$group = $stmt->fetch();

if (!$group) {
    echo "Group not found.";
    exit;
}

if ($group['created_by'] != $user_id) {
    echo "Only the group creator can add new members.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $new_user_id = $user['id'];

        $stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->execute([$group_id, $new_user_id]);
        $is_member = $stmt->fetch();

        if (!$is_member) {
            $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
            $stmt->execute([$group_id, $new_user_id]);
            echo "User added to the group successfully.";
        } else {
            echo "User is already a member of the group.";
        }
    } else {
        echo "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member to Group</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #F1F6F9;
            margin: 0;
            padding: 0;
            color: #394867;
        }
        
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: #212A3E;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid #9BA4B5;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
            color: #394867;
        }

        button {
            padding: 12px;
            background-color: #394867;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #212A3E;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            text-decoration: none;
            color: #394867;
            font-size: 16px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #e74c3c;
            font-size: 14px;
            text-align: center;
        }

        .success-message {
            color: #2ecc71;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Member to Group</h2>
    
    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?= $error_message ?></div>
    <?php elseif (!empty($success_message)): ?>
        <div class="success-message"><?= $success_message ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Enter username to add" required>
        <button type="submit">Add Member</button>
    </form>
    
    <div class="back-link">
        <a href="index.php">Back to Group Chat</a>
    </div>
</div>

</body>
</html>
