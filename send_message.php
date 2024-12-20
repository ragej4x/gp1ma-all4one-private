<?php
include '../../php/db.php'; 

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['sender_id'], $data['receiver_id'], $data['sender_type'], $data['receiver_type'], $data['message'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stmt->execute([$data['sender_id']]);
$sender = $stmt->fetch();

if (!$sender) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO client_msg (sender_id, receiver_id, sender_type, receiver_type, message) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$data['sender_id'], $data['receiver_id'], $data['sender_type'], $data['receiver_type'], $data['message']]);

echo json_encode(['status' => 'success']);
?>
