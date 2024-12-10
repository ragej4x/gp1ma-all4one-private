<?php
session_start();
require '../../php/db.php'; // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    die(json_encode(['error' => 'Not authenticated']));
}

// Get the session user ID and role
$current_id = $_SESSION['id'];
$current_role = $_SESSION['role']; // 'user' or 'teacher'

// Get the partner's ID and role from GET data
$partner_id = $_GET['partner_id'];
$partner_role = $_GET['partner_role']; // 'user' or 'teacher'

// Retrieve messages from the database
$stmt = $pdo->prepare("
    SELECT * FROM messages 
    WHERE 
        (sender_id = ? AND sender_type = ? AND receiver_id = ? AND receiver_type = ?) 
        OR 
        (sender_id = ? AND sender_type = ? AND receiver_id = ? AND receiver_type = ?)
    ORDER BY created_at ASC
");
$stmt->execute([$current_id, $current_role, $partner_id, $partner_role, $partner_id, $partner_role, $current_id, $current_role]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($messages);
?>
