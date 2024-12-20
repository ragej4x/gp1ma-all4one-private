<?php
session_start();
require '../php/db.php'; 

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'user') {
    die(json_encode(['error' => 'Not authenticated']));
}

$student_id = $_SESSION['id'];

$teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : null;

if (!$teacher_id) {
    die(json_encode(['error' => 'Teacher ID is required']));
}

$stmt = $pdo->prepare("
    SELECT * FROM messages 
    WHERE 
        (sender_id = ? AND sender_type = 'user' AND receiver_id = ? AND receiver_type = 'teacher') 
        OR 
        (sender_id = ? AND sender_type = 'teacher' AND receiver_id = ? AND receiver_type = 'user')
    ORDER BY created_at ASC
");
$stmt->execute([$student_id, $teacher_id, $teacher_id, $student_id]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>
