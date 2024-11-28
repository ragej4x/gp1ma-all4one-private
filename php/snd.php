<?php
session_start();
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'])) {
        $message = trim($_POST['message']);
        $user_id = $_SESSION['user_id']; 

        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, created_at) VALUES (?, ?, NOW())");
        if ($stmt->execute([$user_id, $message])) {
            echo 'Message sent'; 
        } else {
            echo 'Failed to send message to the database';
        }
    } else {
        echo 'No message received';
    }
} else {
    echo 'Invalid request method';
}
?>
