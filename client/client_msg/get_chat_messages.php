<?php
session_start();
include '../../php/db.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    die("Unauthorized");
}

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

if ($student_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID is required']);
    exit;
}

$teacher_id = $_SESSION['id']; 

$stmt = $pdo->prepare("SELECT * FROM client_msg 
                        WHERE (sender_id = ? AND receiver_id = ? AND sender_type = 'teacher' AND receiver_type = 'user')
                        OR (sender_id = ? AND receiver_id = ? AND sender_type = 'user' AND receiver_type = 'teacher') 
                        ORDER BY created_at ASC");
$stmt->execute([$teacher_id, $student_id, $student_id, $teacher_id]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'messages' => $messages]);
?>
