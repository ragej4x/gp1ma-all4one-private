<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    exit;
}
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT sender_id, users.username FROM friends JOIN users ON users.id = friends.sender_id WHERE friends.receiver_id = ? AND friends.status = 'pending'");
$stmt->execute([$user_id]);
$incoming_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($incoming_requests as $request) {
    echo '<li>' . htmlspecialchars($request['username']) . ' <a href="friend_dashboard.php?action=accept&user_id=' . $request['sender_id'] . '">[Accept]</a> <a href="friend_dashboard.php?action=decline&user_id=' . $request['sender_id'] . '">[Decline]</a></li>';
}

if (count($incoming_requests) === 0) {
    echo '<li>No pending friend requests.</li>';
}
?>
