<?php
session_start();
include '../../php/db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    die("Unauthorized");
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['sender_id'], $data['receiver_id'], $data['sender_type'], $data['receiver_type'], $data['message'])) {
    $sender_id = $data['sender_id'];
    $receiver_id = $data['receiver_id'];
    $sender_type = $data['sender_type'];
    $receiver_type = $data['receiver_type'];
    $message = $data['message'];

    $stmt = $pdo->prepare("INSERT INTO client_msg (sender_id, sender_type, receiver_id, receiver_type, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$sender_id, $sender_type, $receiver_id, $receiver_type, $message]);

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
}
?>
