<?php
session_start();
include 'db.php'; // Ensure you have a database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the message is set
    if (isset($_POST['message'])) {
        $message = trim($_POST['message']);
        $user_id = $_SESSION['user_id']; // Assuming user ID is stored in the session

        // Optionally, you can also save the message to a database
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, created_at) VALUES (?, ?, NOW())");
        if ($stmt->execute([$user_id, $message])) {
            echo 'Message sent'; // Respond back to the AJAX call
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
