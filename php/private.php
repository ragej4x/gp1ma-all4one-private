<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$chat_user_id = $_GET['user_id'] ?? null;

if (!$chat_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'No user selected for private chat.']);
    exit;
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$chat_user_id]);
$chat_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat_user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'send_message') {
    if (empty($_POST['message'])) {
        echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty.']);
        exit;
    }

    $message = trim($_POST['message']);
    $stmt = $pdo->prepare("INSERT INTO private_chats (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $chat_user_id, $message]);

    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch_messages') {
    $stmt = $pdo->prepare("
        SELECT sender_id, message, timestamp 
        FROM private_chats 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY timestamp ASC
    ");
    $stmt->execute([$user_id, $chat_user_id, $chat_user_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($messages as &$message) {
        $message['message'] = htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8');
    }

    echo json_encode($messages);
    exit;
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    include 'chat-content.php'; 
    exit;
}
