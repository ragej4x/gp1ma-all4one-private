<?php
session_start();
require '../../php/db.php';

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    die(json_encode(['error' => 'Not authenticated']));
}

$current_id = $_SESSION['id'];
$current_role = $_SESSION['role'];

$partner_id = $_GET['partner_id'];
$partner_role = $_GET['partner_role'];

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
