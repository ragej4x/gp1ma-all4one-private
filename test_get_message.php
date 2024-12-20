<?php
include 'php/db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view the dashboard.";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (isset($_POST['send_message'])) {
    $message = $_POST['message'];
    $receiver_id = 1; 
    $stmt = $conn->prepare("INSERT INTO client_msg (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $receiver_id, $message);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT * FROM client_msg WHERE sender_id = ? OR receiver_id = ?");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo "<h1>Welcome, {$user['first_name']}</h1>";
echo "<a href='?page=logout'>Logout</a><br><br>";

echo "<form method='POST'>
        <textarea name='message' required></textarea><br>
        <button type='submit' name='send_message'>Send Message</button>
      </form>";

echo "<h3>Your Messages:</h3>";
foreach ($messages as $msg) {
    $sender = ($msg['sender_id'] == $user_id) ? 'You' : 'Teacher';
    echo "<p><strong>{$sender}:</strong> {$msg['message']}</p>";
}

if (isset($_GET['page']) && $_GET['page'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location: index.php"); 
    exit();
}

?>
