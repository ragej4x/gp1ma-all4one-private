<?php
session_start();
require 'db.php';  // Include your database connection

// Get the posted data
$data = json_decode(file_get_contents("php://input"));

// Ensure the required parameters are provided
if (!isset($data->sender_id, $data->receiver_id, $data->sender_type, $data->receiver_type, $data->message)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

// Prepare the message data
$sender_id = $data->sender_id;
$receiver_id = $data->receiver_id;
$sender_type = $data->sender_type;
$receiver_type = $data->receiver_type;
$message = $data->message;

// Insert the message into the database
$stmt = $pdo->prepare("INSERT INTO client_msg (sender_id, sender_type, receiver_id, receiver_type, message) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$sender_id, $sender_type, $receiver_id, $receiver_type, $message]);

echo json_encode(['status' => 'success']);
?>
