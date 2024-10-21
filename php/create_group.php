<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_name = $_POST['group_name'];
    $user_id = $_SESSION['user_id'];

    // Insert new group
    $stmt = $pdo->prepare("INSERT INTO groups (name, created_by) VALUES (?, ?)");
    $stmt->execute([$group_name, $user_id]);

    // Get the newly created group ID
    $group_id = $pdo->lastInsertId();

    // Add the creator as the first member of the group
    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
    $stmt->execute([$group_id, $user_id]);

    echo "Group created successfully!";
    header('Location: chat_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Group</title>
</head>
<body>
    <h1>Create a New Group</h1>

    <form method="POST" action="">
        <label>Group Name: </label><br>
        <input type="text" name="group_name" required><br><br>
        <button type="submit">Create Group</button>
    </form>

    <br>
    <a href="chat.php">Back to Chat</a>
</body>
</html>
