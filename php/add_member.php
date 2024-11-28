<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = $_GET['group_id']; 

$stmt = $pdo->prepare("SELECT created_by FROM groups WHERE id = ?");
$stmt->execute([$group_id]);
$group = $stmt->fetch();

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
            // Add the user to the group
            $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
            $stmt->execute([$group_id, $new_user_id]);

            echo "User successfully added to the group.";
        } else {
            echo "User is already a member of this group.";
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
    <title>Add Member</title>
    <link rel="icon" type="image/x-icon" href="icons/favicon.png">

</head>
<body>
    <h1>Add Member to Group</h1>

    <form method="POST" action="">
        <label for="username">Enter username:</label>
        <input type="text" name="username" id="username" required>
        <button type="submit">Add Member</button>
    </form>

    <br>
    <a href="group_chat.php?group_id=<?php echo $group_id; ?>">Back to Group Chat</a>
</body>
</html>
