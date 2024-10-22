<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$action = isset($_POST['action']) ? $_POST['action'] : null;

if ($action) {
    switch ($action) {
        case 'add_friend':
            $username_to_add = trim($_POST['username']);
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username_to_add]);
            $friend = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($friend) {
                $friend_id = $friend['id'];

                // Check if friendship already exists
                $stmt = $pdo->prepare("SELECT * FROM friends WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
                $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
                $friendship = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$friendship) {
                    // Send friend request
                    $stmt = $pdo->prepare("INSERT INTO friends (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
                    $stmt->execute([$user_id, $friend_id]);
                    echo json_encode(['success' => true, 'message' => 'Friend request sent']);
                } else {
                    echo json_encode(['error' => 'Friend request already exists']);
                }
            } else {
                echo json_encode(['error' => 'User not found']);
            }
            break;

        case 'accept_friend':
            $friend_id = $_POST['friend_id'];
            // Accept friend request
            $stmt = $pdo->prepare("UPDATE friends SET status = 'accepted' WHERE sender_id = ? AND receiver_id = ?");
            $stmt->execute([$friend_id, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Friend request accepted']);
            break;

        case 'decline_friend':
            $friend_id = $_POST['friend_id'];
            // Decline friend request
            $stmt = $pdo->prepare("DELETE FROM friends WHERE sender_id = ? AND receiver_id = ?");
            $stmt->execute([$friend_id, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Friend request declined']);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
}
?>
