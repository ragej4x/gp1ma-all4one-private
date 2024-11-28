<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_name = $_POST['group_name'];
    $user_id = $_SESSION['user_id'];
    
    $profile_pic = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/group_pics/";
        $target_file = $target_dir . basename($_FILES['profile_pic']['name']);
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            $profile_pic = $target_file;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO groups (name, created_by, profile_pic) VALUES (?, ?, ?)");
    $stmt->execute([$group_name, $user_id, $profile_pic]);

    $group_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
    $stmt->execute([$group_id, $user_id]);

    header("Location: group.php?group_id=$group_id");
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

    <form method="POST" action="" enctype="multipart/form-data">
        <label>Group Name: </label><br>
        <input type="text" name="group_name" required><br><br>

        <label>Group Profile Picture: </label><br>
        <input type="file" name="profile_pic" accept="image/*"><br><br>

        <button type="submit">Create Group</button>
    </form>

    <br>
    <a href="index.php">Back to Chat</a>
</body>
</html>
