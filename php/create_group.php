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

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Group</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Create a New Group</h1>
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="group_name">Group Name: </label>
                <input type="text" id="group_name" name="group_name" required placeholder="Enter group name"><br><br>

                <label for="profile_pic">Group Profile Picture: </label>
                <input type="file" id="profile_pic" name="profile_pic" accept="image/*"><br><br>

                <button type="submit" class="btn-submit">Create Group</button>
            </form>
            <br>
            <a href="index.php" class="btn-back">Back to Chat</a>
        </div>
    </div>
</body>
</html>

<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f7fc;
        margin: 0;
        padding: 0;
    }
    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: #f1f6f9;
    }
    .form-container {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 40px;
        max-width: 400px;
        width: 100%;
    }
    .form-container h1 {
        font-size: 24px;
        color: #394867;
        text-align: center;
        margin-bottom: 20px;
    }
    label {
        font-size: 14px;
        color: #394867;
        font-weight: 600;
    }
    input[type="text"], input[type="file"] {
        width: 100%;
        padding: 10px;
        margin-top: 8px;
        border: 1px solid #9ba4b5;
        border-radius: 5px;
        font-size: 14px;
        margin-bottom: 20px;
        background-color: #f1f6f9;
    }
    button, .btn-back {
        padding: 10px 20px;
        background-color: #394867;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        display: inline-block;
        text-align: center;
        width: 100%;
    }
    button:hover, .btn-back:hover {
        background-color: #2c3e50;
    }
    .btn-back {
        background-color: #9ba4b5;
        text-align: center;
        max-width: 90%;
    }
</style>
